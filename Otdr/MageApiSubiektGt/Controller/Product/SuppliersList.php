<?php
namespace Otdr\MageApiSubiektGt\Controller\Product;

use Magento\Framework\App\Action\Context;
use Otdr\MageApiSubiektGt\Cron\CronObject;


class SuppliersList extends \Magento\Framework\App\Action\Action{

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
		$json_response = array('sources'=>array());
		$type = $this->getRequest()->getParam('type');
		$delete = intval($this->getRequest()->getParam('delete'));
		$forcedelete = intval($this->getRequest()->getParam('forcedelete'));
		$file_name = dirname(__FILE__).'/suppliers.data';



		if(file_exists($file_name)){
			$data = file_get_contents($file_name);
			$json_response['sources'] = unserialize($data);				
		}
		
		print(json_encode($json_response,JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
		exit;
	}
}
?>
