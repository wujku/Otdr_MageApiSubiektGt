<?php 
namespace Otdr\MageApiSubiektGt\Observer; 
use Magento\Framework\Event\ObserverInterface; 
 
class OrderSaver implements ObserverInterface { 
 
    protected $connector; 
    public function __construct() { 
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance(); 
    }
 
    public function execute(\Magento\Framework\Event\Observer $observer) { 
        $order = $observer->getEvent()->getOrder();
        $orderId = $order->getEntityId();

 		if($orderId){
			$objectManager = \Magento\Framework\App\ObjectManager::getInstance(); // Instance of object manager
			$resource = $objectManager->get('Magento\Framework\App\ResourceConnection');
			$connection = $resource->getConnection();
			$tableName = $resource->getTableName('otdr_mageapisubiektgt');

			$dml = 'INSERT INTO '.$tableName.' VALUES(0,\''.$orderId.'\',0,0,0,0,\'\',\'\',\'\',NOW(),NOW(),0)';
            //var_dump($dml);
			$connection->query($dml);
 		}
    }
}