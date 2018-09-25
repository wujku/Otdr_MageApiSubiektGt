<?php
namespace Otdr\MageApiSubiektGt\Cron;

use Otdr\MageApiSubiektGt\Helper\SubiektApi;
use Exception;

class MakeSale extends CronObject
{

   public function __construct(\Otdr\MageApiSubiektGt\Helper\Config $config,\Psr\Log\LoggerInterface $logger, \Magento\Framework\App\State $appState ){
      parent::__construct($config,$logger,$appState);
   }


   protected function getOrdersIds(){
         $connection = $this->resource->getConnection();
         $tableName = $this->resource->getTableName('otdr_mageapisubiektgt');
         $query = 'SELECT id_order FROM '.$tableName.' WHERE is_locked = 0 AND gt_order_sent = 1 AND gt_sell_doc_request = 0';
         $result = $connection->fetchAll($query);
         return $result;
   }

   protected function updateOrderStatus($id_order,$order_reference){
      $connection = $this->resource->getConnection();
      $tableName = $this->resource->getTableName('otdr_mageapisubiektgt');
      $dml = "UPDATE {$tableName} SET gt_sell_doc_request = 1, gt_sell_doc_ref =  '{$order_reference}, upd_date = NOW() WHERE id_order = {$id_order}";
      $connection->query($dml);
      $this->addLog($id_order,'Wygenerowano paragon/fakturę nr'.$order_reference,!empty($this->subiekt_api_sell_doc_status)?$this->subiekt_api_sell_doc_status:NULL);
   }

	public function execute(){
          	
      $subiektApi = new SubiektApi($this->api_key,$this->end_point);
      $orders_to_send = $this->getOrdersIds();
                  
      foreach($orders_to_send as $order){
         $id_order = $order['id_order'];     
      
         $this->ordersProcessed++;

         $this->lockOrder($id_order);

         $fail = false;
         $result = $subiektApi->call('order/add',$order_json[$id_order]);                  
         if(!$result){ 
            $fail = true;
            $this->unlockOrder($id_order);
            $this->addErrorLog($id_order,'Can\'t connect to API check configuration!');
            return false;

         }
         if($result['state'] == 'fail'){            
            $fail = true;
            $this->addErrorLog($id_order,$result['message']);            
         }
         


         /* unlocking order after processing */
         $this->unlockOrder($id_order);

   
         /* Is all Okey */
         if(!$fail){
            /* Update order processing status */
            $this->updateOrderStatus($id_order,$result['data']['order_ref']);            

            print("OK\n");
         }else{
            print("Error\n");
         }
      }

      return true;

	}

   public function getOrdersProcessed(){
      return $this->ordersProcessed;
   }

}
?>