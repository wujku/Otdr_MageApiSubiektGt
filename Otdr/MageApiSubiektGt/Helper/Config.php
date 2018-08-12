<?php
namespace Otdr\MageApiSubiektGt\Helper;

use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\App\Config\ScopeConfigInterface;

class Config
{

	const XML_PATH = 'mageapisubiektgt/';
	protected $scopeConfig = false;

	public function __construct(ScopeConfigInterface $scopeConfig){
		$this->scopeConfig = $scopeConfig;		
	}

	public function getConfigValue($field, $storeId = null)
	{		
		return $this->scopeConfig->getValue(
			$field, \Magento\Store\Model\ScopeInterface::SCOPE_STORE, $storeId
		);
	}

	public function getGen($code)
	{

		return $this->getConfigValue(self::XML_PATH .'general/'. $code);
	}

}