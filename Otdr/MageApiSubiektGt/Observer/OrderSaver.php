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
        $id_order = $order->getId();        
        

        $isExists = $this->isExists($id_order);
        if(!empty($id_order) && !$isExists){            
			$objectManager = \Magento\Framework\App\ObjectManager::getInstance(); // Instance of object manager
			$resource = $objectManager->get('Magento\Framework\App\ResourceConnection');
			$connection = $resource->getConnection();
			$tableName = $resource->getTableName('otdr_mageapisubiektgt');

			$dml = 'INSERT INTO '.$tableName.' VALUES(0,\''.$id_order.'\',0,0,0,0,\'\',\'\',\'\',NOW(),NOW(),0)';
			$connection->query($dml);
 		}

        if($isExists){
            //TODO: locking orders for processing
        }
    }


    protected function isExists($id_order){
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();           
        $resource = $objectManager->get('Magento\Framework\App\ResourceConnection');
        $connection = $resource->getConnection();
        $tableName = $resource->getTableName('otdr_mageapisubiektgt');
        $query = "SELECT count(id_order) as cnt FROM {$tableName} WHERE id_order = {$id_order}";
        $result = $connection->fetchAll($query);
        return intval($result[0]['cnt']);
    }
}