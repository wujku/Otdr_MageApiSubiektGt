<?php
namespace Otdr\MageApiSubiektGt\Controller\Pdf;

use Magento\Framework\App\Action\Context;


class View extends \Magento\Framework\App\Action\Action{

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
		if($file = $this->getRequest()->getParam('file')){
			$pdfs_path = $this->config->getGen('subiekt_api_pdfs_path');

			$file = $pdfs_path.'/'.$file.'.pdf';		
			if(file_exists($file)){
				header("Content-type:application/pdf");
				readfile($file);
				//echo file_get_contents($file);
			}else{
				print('Bad request');
			}
		}
		exit;
	}
}
?>
