<?php
namespace Otdr\MageApiSubiektGt\Cron;

use Otdr\MageApiSubiektGt\Helper\SubiektApi;
use Exception;

class ProductList extends CronObject
{

   public function __construct(\Otdr\MageApiSubiektGt\Helper\Config $config,\Psr\Log\LoggerInterface $logger, \Magento\Framework\App\State $appState ){
      parent::__construct($config,$logger,$appState);
   }
 
	public function execute(){
		$subiektApi = new SubiektApi($this->api_key,$this->end_point); 
		$result = $subiektApi->call('product/getlistbystore',array('id_store'=>$this->subiekt_api_warehouse_id));   
		if($result['state']=='success'){
			$products = $result['data'];
			$full_json_array = array('timestamp'=>date('Y-m-d H:i:s'),'total'=>0,'products'=>array());
			$onstore_json_array = array('timestamp'=>date('Y-m-d H:i:s'),'total'=>0,'products'=>array());	
			foreach($products as $p){
				$full_json_array['products'][$p['code']] = array('available'=>intval($p['available']),'supplier_reference'=>'','sku'=>$p['code'],'delivery_time_description'=>intval($p['available'])>0?'Wysyłamy w 24h':'Tymczasowo niedostępne','delivery_time'=>intval($p['available'])>0?24:0);
				if(intval($p['available'])>0){
					$onstore_json_array['products'][$p['code']] = $full_json_array['products'][$p['code']];					
				}				
			}
			$full_json_array['total'] = count($full_json_array['products']);
			$onstore_json_array['total'] = count($onstore_json_array['products']);
			//print_r($json_array);
			$file_name = dirname(__FILE__).'/../Controller/Product/fullstoreproducts.data';
		 	file_put_contents($file_name,serialize($full_json_array));
			$file_name = dirname(__FILE__).'/../Controller/Product/onstoreproducts.data';
		 	file_put_contents($file_name,serialize($onstore_json_array));


			return true;
		}
		return false;
	}

 }


  