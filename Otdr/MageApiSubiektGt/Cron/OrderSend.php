<?php
namespace Otdr\MageApiSubiektGt\Cron;

use Otdr\MageApiSubiektGt\Helper\SubiektApi;

class OrderSend 
{
   protected $logger;
   protected $config;
   protected $ordersProcessed = 0;
   protected $end_point = '';
   protected $api_key = '';
   protected $orderRepository = false;


   public function __construct(\Otdr\MageApiSubiektGt\Helper\Config $config,     \Magento\Sales\Model\OrderRepository $orderRepository){
      $this->config = $config;      
      $this->orderRepository = $orderRepository;
      $this->end_point = $this->config->getGen('subiekt_api_gateway');
      $this->api_key = $this->config->getGen('subiekt_api_key');
   }


   public function execute()
   {
      
      
      $subiektApi = new SubiektApi($this->api_key,$this->end_point);
      $result = $subiektApi->call('document/get',array("doc_ref"=>"PA 14588/08/2018"));            
      //var_dump($result);
      if(!$result){
        var_dump($subiektApi->getPlainTextResult());
      }
      
      return true;
   }

   public function getOrdersProcessed(){
      return $this->ordersProcessed;
   }
}