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
   /*Status*/
   protected $subiekt_api_order_status = '';
   protected $subiekt_api_sell_doc_status = ''; 
   protected $subiekt_api_order_processing = '';

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
      	  
      $this->resource = $objectManager->get('Magento\Framework\App\ResourceConnection');

      /* Load module configuration */
      $this->end_point = $this->config->getGen('subiekt_api_gateway');
      $this->api_key = $this->config->getGen('subiekt_api_key');      
      $this->subiekt_api_ean_attrib = $this->config->getGen('subiekt_api_ean_attrib'); 
      $this->subiekt_api_newproducts = $this->config->getGen('subiekt_api_newproducts');
      $this->subiekt_api_prefix  =   $this->config->getGen('subiekt_api_prefix');
      $this->subiekt_api_warehouse_id = $this->config->getGen('subiekt_api_warehouse_id');
      $this->subiekt_api_trans_symbol = $this->config->getGen('subiekt_api_trans_symbol');

      /*Statuses*/
      $this->subiekt_api_order_status = $this->config->getGen('subiekt_api_order_status');
      $this->subiekt_api_sell_doc_status = $this->config->getGen('subiekt_api_sell_doc_status');
      $this->subiekt_api_order_processing = $this->config->getGen('subiekt_api_order_processing');
      /*Flags*/


      /* Setting area of executing */
     //$this->appState->setAreaCode('adminhtml');
      
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


   protected function addLog($id_order,$comment_txt,$status = 'processing'){
      /*Add comment log from subiekt*/ 
      $comment_txt = 'Subiekt GT info: '.$comment_txt;
      $objectManager = \Magento\Framework\App\ObjectManager::getInstance(); 
      $order = $objectManager->create('\Magento\Sales\Api\Data\OrderInterface')->loadByIncrementId($id_order); 
      $order->addStatusToHistory($status, $comment_txt, false);
      $order->save();      
      //TODO: add to log msg
   }


   protected function addErrorLog($id_order,$comment_txt){
      $this->addLog($id_order,$comment_txt,'holded');
   }

}

?>