<?php
namespace Otdr\MageApiSubiektGt\Cron;

use Otdr\MageApiSubiektGt\Helper\SubiektApi;
use Exception;

class ProductList extends CronObject
{

   protected $products_cnt = 0;
 
   public function __construct(\Otdr\MageApiSubiektGt\Helper\Config $config,\Psr\Log\LoggerInterface $logger, \Magento\Framework\App\State $appState ){
      parent::__construct($config,$logger,$appState);
   }
 
	public function execute(){
		parent::execute();
		
		$subiektApi = new SubiektApi($this->api_key,$this->end_point); 
		$result = $subiektApi->call('product/getlistbystore',array('id_store'=>$this->subiekt_api_warehouse_id));   
		if($result['state']=='success'){
			$products = $result['data'];

			$file_name_full = dirname(__FILE__).'/../Controller/Product/fullstoreproducts.data';
			if(file_exists($file_name_full)){
				$data = file_get_contents($file_name_full);
				$full_json_array = unserialize($data);
			}else{
				$full_json_array = array('timestamp'=>date('Y-m-d H:i:s'),'total'=>0,'products'=>array());
			}


			$file_name_onstore = dirname(__FILE__).'/../Controller/Product/onstoreproducts.data';
			if(file_exists($file_name_onstore)){
				$data = file_get_contents($file_name_onstore);
				$onstore_json_array = unserialize($data);
			}else{
				$onstore_json_array = array('timestamp'=>date('Y-m-d H:i:s'),'total'=>0,'products'=>array());
			}
			$cnt = 0;
			foreach($products as $p){
				if($p['available'] == 0 && isset($full_json_array['products'][$p['code']]) && $full_json_array['products'][$p['code']]['available']>0){
					continue;
				}

				$full_json_array['products'][$p['code']] = array('available'=>intval($p['available']),'supplier_reference'=>'','sku'=>$p['code'],'delivery_time_description'=>intval($p['available'])>0?'Wysyłamy w 24h':'Tymczasowo niedostępne','delivery_time'=>intval($p['available'])>0?24:0);
				if(intval($p['available'])>0){
					$onstore_json_array['products'][$p['code']] = $full_json_array['products'][$p['code']];					
				}
				$cnt++; 				
			}
			$full_json_array['total'] = count($full_json_array['products']);
			$full_json_array['timestamp'] = date('Y-m-d H:i:s'); 
			$onstore_json_array['total'] = count($onstore_json_array['products']);
			$onstore_json_array['timestamp'] = date('Y-m-d H:i:s');
			//print_r($json_array);
			
		 	file_put_contents($file_name_full,serialize($full_json_array));			
		 	file_put_contents($file_name_onstore,serialize($onstore_json_array));
		 	$this->products_cnt = "$cnt/".count($products)."/".$full_json_array['total'];

			return true;
		}
		return false;
	}

	public function count(){
		return $this->products_cnt;
	}

 }


  