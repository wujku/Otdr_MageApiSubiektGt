<?php
namespace Otdr\MageApiSubiektGt\Cron;

use Otdr\MageApiSubiektGt\Helper\SubiektApi;
use Exception;

abstract class CronObject {
   
   protected $logger;
   protected $config;
   protected $ordersProcessed = 0;
   protected $end_point = '';
   protected $api_key = '';
   protected $subiekt_api_ean_attrib = '';
   protected $subiekt_api_newproducts = false;
   protected $subiekt_api_prefix = '';
   protected $subiekt_api_warehouse_id = 1;
   protected $subiekt_api_trans_symbol;
   protected $subiekt_api_pdfs_path = '';


    /*Status*/
   protected $subiekt_api_order_status = '';
   protected $subiekt_api_sell_doc_status = ''; 
   protected $subiekt_api_order_processing = '';
   protected $subiekt_api_order_hold = '';

   /*Flags*/
   protected $subiekt_api_wrapping_id_flag = 0;

   
   protected $resource = false;
   protected $logArray = array();
   public $appState = false;


   public function __construct(\Otdr\MageApiSubiektGt\Helper\Config $config ,\Psr\Log\LoggerInterface $logger, \Magento\Framework\App\State $appState){

      $objectManager = \Magento\Framework\App\ObjectManager::getInstance(); 
      $this->appState = $appState;
      $this->config = $config;    
      $this->logger = $logger;  
      
      /* Setting area of executing */
     //$this->appState->setAreaCode('adminhtml');

      $this->resource = $objectManager->get('Magento\Framework\App\ResourceConnection');

      /* Load module configuration */
      $this->end_point = $this->config->getGen('subiekt_api_gateway');
      $this->api_key = $this->config->getGen('subiekt_api_key');      
      $this->subiekt_api_ean_attrib = $this->config->getGen('subiekt_api_ean_attrib'); 
      $this->subiekt_api_newproducts = $this->config->getGen('subiekt_api_newproducts');
      $this->subiekt_api_prefix  =   $this->config->getGen('subiekt_api_prefix');
      $this->subiekt_api_warehouse_id = $this->config->getGen('subiekt_api_warehouse_id');
      $this->subiekt_api_trans_symbol = $this->config->getGen('subiekt_api_trans_symbol');
      $this->subiekt_api_pdfs_path = $this->config->getGen('subiekt_api_pdfs_path');

      /*Statuses*/
      $this->subiekt_api_order_status = $this->config->getStatus('subiekt_api_order_status');
      $this->subiekt_api_sell_doc_status = $this->config->getStatus('subiekt_api_sell_doc_status');
      $this->subiekt_api_order_processing = $this->config->getStatus('subiekt_api_order_processing');
      $this->subiekt_api_order_hold = $this->config->getStatus('subiekt_api_order_hold');
      
      /*Flags*/
      
   }

   public function getLog(){
      return $this->logArray;
   }

   protected function productQtyUpdate($code,$qty){
      $objectManager = \Magento\Framework\App\ObjectManager::getInstance(); 
      $stockRegistry = $objectManager->get('\Magento\CatalogInventory\Api\StockRegistryInterface');
      $productObject = $objectManager->get('\Magento\Catalog\Model\Product');

      if(!empty($this->subiekt_api_ean_attrib)){     
                             
            $product = $productObject->loadByAttribute($this->subiekt_api_ean_attrib, $code);
            if($product){
               $stockItem = $stockRegistry->getStockItem($product->getId());
               $stockItem->setData('qty',$qty);            
               $stockItem->save();
               return true;
            }           
      }else{            
            $id_product = $productObject->getIdBySku($code);
            if(intval($id_product)>0){                                                     
               $stockItem = $stockRegistry->getStockItem($id_product);
               $stockItem->setQty($qty);            
               $stockItem->save();
               return true;
            }            
      }
      return false;
   }

   
   protected function lockOrder($id_order){
      $connection = $this->resource->getConnection();
      $tableName = $this->resource->getTableName('otdr_mageapisubiektgt');
      $dml = 'UPDATE '.$tableName.' SET is_locked = 1, upd_date = NOW() WHERE id_order = \''.$id_order.'\'';
      $connection->query($dml);
   }

   protected function unlockOrder($id_order){
      $connection = $this->resource->getConnection();
      $tableName = $this->resource->getTableName('otdr_mageapisubiektgt');
		$dml = 'UPDATE '.$tableName.' SET is_locked = 0, upd_date = NOW() WHERE id_order = \''.$id_order.'\'';
      $connection->query($dml);
   }

   protected function getOrderData($id_order){
      $objectManager = \Magento\Framework\App\ObjectManager::getInstance();      
      $order = $objectManager->create('\Magento\Sales\Api\Data\OrderInterface')->loadByIncrementId($id_order);
      return $order;
   }

   protected function setStatus($id_order,$comment_txt,$status = 'processing'){
      $comment_txt = 'Subiekt GT info: '.$comment_txt;
      $objectManager = \Magento\Framework\App\ObjectManager::getInstance(); 
      $order = $objectManager->create('\Magento\Sales\Api\Data\OrderInterface')->loadByIncrementId($id_order); 
      $order->addStatusToHistory($status, $comment_txt, false);
      $order->save(); 
   }

   protected function addLog($id_order,$comment_txt){
      /*Add comment log from subiekt*/ 
      $comment_txt = 'Subiekt GT info: '.$comment_txt;
      $objectManager = \Magento\Framework\App\ObjectManager::getInstance(); 
      $order = $objectManager->create('\Magento\Sales\Api\Data\OrderInterface')->loadByIncrementId($id_order);       
      $order->addStatusHistoryComment($comment_txt);
      $order->save();            
   }

   /**
   * Save pdf of selling document
   */
   protected function savePdf($id_order,$pdf_data, $file_name = ''){
      if($file_name==''){
        $file_name = md5($pdf_data).".pdf";
      }
      if(file_put_contents("{$this->subiekt_api_pdfs_path}/".$file_name, base64_encode($pdf_data))){
          $connection = $this->resource->getConnection();
          $tableName = $this->resource->getTableName('otdr_mageapisubiektgt');
          $dml = "UPDATE {$tableName} SET gt_sell_doc_pdf_request = 1, doc_file_pdf_name = '{$file_name}', upd_date = NOW() WHERE id_order = {$id_order}";
          $connection->query($dml);
      }else{
        return false;
      }
      return $file_name;
   }

   /**
   * Getting of pdf selling document
   */
   protected function getPdf($id_order,$in_base64 = false){
      //file_
   }


   protected function deletePdf($id_order){
    
   }

   protected function createInvoice($id_order){
      $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
      $order = $objectManager->create('Magento\Sales\Api\Data\OrderInterface')->loadByIncrementId($id_order); 


      if ($order->canInvoice()) {
          // Create invoice for this order
          $invoice = $objectManager->create('Magento\Sales\Model\Service\InvoiceService')->prepareInvoice($order);

          // Make sure there is a qty on the invoice
          if (!$invoice->getTotalQty()) {
              throw new \Magento\Framework\Exception\LocalizedException(
                          __('You can\'t create an invoice without products.')
                      );
          }

          // Register as invoice item
          $invoice->setRequestedCaptureCase(\Magento\Sales\Model\Order\Invoice::CAPTURE_OFFLINE);
          $invoice->register();

          // Save the invoice to the order
          $transaction = $objectManager->create('Magento\Framework\DB\Transaction')
              ->addObject($invoice)
              ->addObject($invoice->getOrder());

          return $transaction->save();
        }
        return false;
   }

   protected function addErrorLog($id_order,$comment_txt){
      $this->setStatus($id_order,$comment_txt,$this->subiekt_api_order_hold);
   }

}

?>