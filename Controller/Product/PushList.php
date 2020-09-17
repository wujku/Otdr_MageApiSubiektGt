<?php
namespace Otdr\MageApiSubiektGt\Controller\Product;

use \Exception;
use Magento\Framework\App\Action\Context;
use Magento\Framework\App\Action\HttpPostActionInterface;
use Magento\Framework\App\CsrfAwareActionInterface;
use Magento\Framework\App\Request\InvalidRequestException;
use Magento\Framework\App\RequestInterface;
use Otdr\MageApiSubiektGt\Cron\CronObject;


class PushList extends \Magento\Framework\App\Action\Action implements CsrfAwareActionInterface, HttpPostActionInterface {

    protected $config;

    protected $request;

    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        RequestInterface $request,
        \Otdr\MageApiSubiektGt\Helper\Config $config
    )
    {
        $this->request = $request;
        $this->config = $config;
        return parent::__construct($context);
    }


	public function execute()
	{
        if(!$this->validateToken()) {
            $json_response = array(
                'state' => 'fail',
                'data' => 'Nieprawidłowy token autoryzacyjny'
            );
            exit(json_encode($json_response,JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
        }

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

		$json_data = $json_request['data'];
		//print_r($json_request);
		//file_put_contents('/tmp/json_request.data',$jsonStr);
		//exit;

		$file_name_full = dirname(__FILE__).'/fullstoreproducts.data';
		$suppliers = dirname(__FILE__).'/suppliers.data';

		if(file_exists($suppliers)){
			$supplier_data = file_get_contents($suppliers);
			$supplier_json_array = unserialize($supplier_data);
		}



		if(file_exists($file_name_full)){
			$data = file_get_contents($file_name_full);
			$full_data = unserialize($data);
		}else{
			$full_data = array('timestamp'=>date('Y-m-d H:i:s'));
		}


		$file_name_onstore = dirname(__FILE__).'/onstoreproducts.data';
		if(file_exists($file_name_onstore)){
			$data = file_get_contents($file_name_onstore);
			$onstore_data = unserialize($data);
		}else{
			$onstore_data = array('timestamp'=>date('Y-m-d H:i:s'));
		}
		$supllier_code = $json_data['code'];
		$upd = 0;
		$ins = 0;
		$onstore_data[$supllier_code]['products'] = array();
		foreach($json_data['products'] as $code => $qty) {
			if(!isset($full_data[$supllier_code]['products'][$code])){
				$full_data[$supllier_code]['products'][$code] = $qty;
				$ins++;
			}elseif(isset($full_data[$supllier_code]['products'][$code])) {
				$full_data[$supllier_code]['products'][$code]  = $qty;
				$upd++;
			}


			if($qty>0){
				if(!isset($onstore_data[$supllier_code]['products'][$code])){
					$onstore_data[$supllier_code]['products'][$code] = $qty;
				}
			}
		}
		$full_data['timestamp'] = date('Y-m-d H:i:s');
		$onstore_data['timestamp'] = date('Y-m-d H:i:s');
		$full_data[$supllier_code]['total'] = count($full_data[$supllier_code]['products']);
		$onstore_data[$supllier_code]['total'] = count($onstore_data[$supllier_code]['products']);


		unset($json_data['products']);
		$supplier_json_array[$supllier_code] =  $json_data;

		file_put_contents($suppliers, serialize($supplier_json_array));
		file_put_contents($file_name_full,serialize($full_data));
		file_put_contents($file_name_onstore,serialize($onstore_data));

		$json_response['data'] = array('updated'=>$upd,'inserted'=>$ins,'total'=>$full_data[$supllier_code]['total']);

		print(json_encode($json_response,JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
		exit;
	}

    protected function validateToken()
    {
        $token = $this->request->getParam("token");

        $configToken = $this->config->getGen("api_post_token");

        if((string) $token !== (string) $configToken) {
            return false;
        }

        return true;
    }

    public function createCsrfValidationException(RequestInterface $request): ?InvalidRequestException
    {
        return new InvalidRequestException();
    }

    public function validateForCsrf(RequestInterface $request): ?bool
    {
        return true;
    }
}
?>
