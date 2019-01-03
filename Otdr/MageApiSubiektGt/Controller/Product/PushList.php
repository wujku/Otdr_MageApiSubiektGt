<?php
namespace Otdr\MageApiSubiektGt\Controller\Product;

use \Exception;
use Magento\Framework\App\Action\Context;
use Otdr\MageApiSubiektGt\Cron\CronObject;


class PushList extends \Magento\Framework\App\Action\Action{

	protected $config;
	

	public function __construct(
		\Magento\Framework\App\Action\Context $context,\Otdr\MageApiSubiektGt\Helper\Config $config

		)
	{
		$this->config = $config;		
		return parent::__construct($context);
	}


	public function execute()
	{
		$json_response = array('state'=>'success');

		$jsonStr = @file_get_contents("php://input");
		$jsonStr = trim($jsonStr);		
		if($jsonStr!=NULL){
			$json_request = json_decode($jsonStr,true);
			if(json_last_error()>0){							
				$json_response['state'] = 'fail';
				$json_response['data'] = json_last_error_msg();
				exit(json_encode($json_response,JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));

			}
		}else{
			$json_response['state'] = 'fail';
			$json_response['data'] = 'Brak danych w rządaniu!';
			exit(json_encode($json_response,JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
		}

		//print_r($json_request);
		//file_put_contents(dirname(__FILE__).'/json_request.data',$jsonStr);
		//exit;

		$file_name_full = dirname(__FILE__).'/fullstoreproducts.data';
		if(file_exists($file_name_full)){
			$data = file_get_contents($file_name_full);
			$full_data = unserialize($data);
		}else{
			$full_data = array('timestamp'=>date('Y-m-d H:i:s'),'total'=>0,'products'=>array());
		}


		$file_name_onstore = dirname(__FILE__).'/onstoreproducts.data';
		if(file_exists($file_name_onstore)){
			$data = file_get_contents($file_name_onstore);
			$onstore_data = unserialize($data);
		}else{
			$onstore_data = array('timestamp'=>date('Y-m-d H:i:s'),'total'=>0,'products'=>array());
		}

		$upd = 0;
		$ins = 0;
		foreach($json_request['data'] as $p){
			if(!isset($full_data['products'][$p['sku']])){
				$full_data['products'][$p['sku']] = $p;
				$ins++;
			}elseif(isset($full_data['products'][$p['sku']]) && ($full_data['products'][$p['sku']]['delivery_time']>$p['delivery_time'] || $full_data['products'][$p['sku']]['delivery_time'] == 0)){
				$full_data['products'][$p['sku']]  = $p;
				$upd++;
			}


			if($p['available']>0){				
				if(!isset($onstore_data['products'][$p['sku']])){
					$onstore_data['products'][$p['sku']] = $p;
				}
			}
		}
		$full_data['timestamp'] = date('Y-m-d H:i:s');
		$onstore_data['timestamp'] = date('Y-m-d H:i:s');
		$full_data['total'] = count($full_data['products']);
		$onstore_data['total'] = count($onstore_data['products']);

		file_put_contents($file_name_full,serialize($full_data));
		file_put_contents($file_name_onstore,serialize($onstore_data));

		$json_response['data'] = array('updated'=>$upd,'inserted'=>$ins,'total'=>$full_data['total']);

		print(json_encode($json_response,JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
		exit;
	}
}
?>
