<?php
namespace Otdr\MageApiSubiektGt\Controller\Pdf;

use Magento\Framework\App\Action\Context;
use Otdr\MageApiSubiektGt\Cron\CronObject;


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
			$pdfs_path = CronObject::getDirForPDF($this->config->getGen('subiekt_api_pdfs_path'),$file);
			$file = $pdfs_path.'/'.$file.'.pdf';		
			if(file_exists($file)){
				header("Content-type:application/pdf");
				readfile($file);				
			}else{
				print('Dokument wygasł lub nie istnieje.');
			}
		}
		exit;
	}
}
?>
