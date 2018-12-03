<?php
namespace Otdr\MageApiSubiektGt\Cron;

use Otdr\MageApiSubiektGt\Helper\SubiektApi;
use Exception;

class DocEmail extends CronObject
{


   /**
   * @var \Magento\Framework\Mail\Template\TransportBuilder
   */
   protected $_transportBuilder;
    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
   protected $_storeManager;

    

   public function __construct(\Otdr\MageApiSubiektGt\Helper\Config $config,\Psr\Log\LoggerInterface $logger
        , \Magento\Framework\App\State $appState
       , \Otdr\MageApiSubiektGt\Model\MailTransportBuilder $transportBuilder
        , \Magento\Store\Model\StoreManagerInterface $storeManager
    ){
      $this->_transportBuilder = $transportBuilder;
      $this->_storeManager = $storeManager;
      parent::__construct($config,$logger,$appState);
   }


   protected function getOrdersIds(){
         $connection = $this->resource->getConnection();
         $tableName = $this->resource->getTableName('otdr_mageapisubiektgt');
         $query = 'SELECT id_order FROM '.$tableName.' WHERE is_locked = 0 AND email_sell_doc_pdf_sent = 0 AND gt_sell_doc_pdf_request = 1';
         $result = $connection->fetchAll($query);
         return $result;
   }


   protected function updateOrderStatus($id_order){
      $connection = $this->resource->getConnection();
      $tableName = $this->resource->getTableName('otdr_mageapisubiektgt');
      $dml = "UPDATE {$tableName} SET email_sell_doc_pdf_sent = 1, upd_date = NOW() WHERE id_order = {$id_order}";
      $connection->query($dml);
      $this->addLog($id_order,'Wysłano do klienta e-mail z dokumentem sprzedaży');      
   }


   protected function sendEmail($orderObject){
      $store = $this->_storeManager->getStore()->getId();

      $dataObject = new \Magento\Framework\DataObject();
      $dataObject->setData($orderObject->getData());

      //$order = ;
      //var_dump($orderObject->getData());
      $transport = $this->_transportBuilder->setTemplateIdentifier('mageapisubiektgt_doc_email')
            ->setTemplateOptions(['area' => \Magento\Framework\App\Area::AREA_FRONTEND, 'store' => \Magento\Store\Model\Store::DEFAULT_STORE_ID])
            ->setTemplateVars(
                [
                    'store' => $this->_storeManager->getStore(),
                    'order' => $dataObject,
                ]
            )
            ->setFrom('general')                        
            ->addTo($orderObject->getCustomerEmail(), $orderObject->getCustomerFirstName())     
            ->addPdfAttachment($this->getPdf($orderObject->getIncrementId()),$orderObject->getIncrementId().'.pdf')       
            ->getTransport();        
        return $transport->sendMessage();
        
   }


   public function execute (){
            
      $orders_to_send_email = $this->getOrdersIds();
      
      
      foreach($orders_to_send_email as $order){
         $id_order = $order['id_order'];     
      
         $this->ordersProcessed++;
         print("Prepare for sending selling document: \"{$id_order}\": ");

         /* Locking order for processing */
         $this->lockOrder($id_order);


         /*getting order data*/
         $order_data = $this->getOrderData($id_order);
         
         /* check order status */
         //var_dump($order_data->getStatus());
         $st = $order_data->getStatus();
         if($st != 'complete'){
            $this->unlockOrder($id_order);
            print ("skipped\n");
            continue;
         }
      
         
        $this->sendEmail($order_data); 
        $this->updateOrderStatus($id_order);
        print "OK\n";
      }

      return true;
        
   }
}