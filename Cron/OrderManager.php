<?php
namespace Otdr\MageApiSubiektGt\Cron;

use Otdr\MageApiSubiektGt\Helper\SubiektApi;
use Exception;

class OrderManager extends CronObject{

   protected $cfg;

   public function __construct(\Otdr\MageApiSubiektGt\Helper\Config $config,\Psr\Log\LoggerInterface $logger, \Magento\Framework\App\State $appState ){
   	$objectManager = \Magento\Framework\App\ObjectManager::getInstance(); 
	  $this->cfg = $objectManager->get('Otdr\MageApiSubiektGt\Helper\Config');
      parent::__construct($config,$logger,$appState);
   }

   protected function getOrdersIds($date){
         $connection = $this->resource->getConnection();
         $moduleTableName = $this->resource->getTableName('otdr_mageapisubiektgt');
         $salesOrderTable = $this->resource->getTableName('sales_order');
         $query = "select increment_id from {$salesOrderTable}  WHERE increment_id NOT IN (SELECT id_order FROM {$moduleTableName} WHERE add_date >='{$date}' ) AND created_at >= '{$date}'";
         $result = $connection->fetchAll($query);
         return $result;
   }

	public function execute(){
		parent::execute();

		$last_order_date = NULL;				
		if(is_null($last_order_date = $this->cfg->getNonCached('internal/last_order_date'))){
			echo "Brak ustwionego czasu pobrania ostatniego zamówienia\n";
			return false;
		}
		echo "Pobieram dane od: {$last_order_date}\n";
		$date_now = date('Y-m-d H:i:s');
		$orders = $this->getOrdersIds($last_order_date);
		foreach($orders as $order){
			$this->ordersProcessed++;
			$this->createOrder($order['increment_id']);
		}
		//var_dump($orders);
		$this->cfg->save('internal/last_order_date',$date_now); 
		return true;
	}

}

