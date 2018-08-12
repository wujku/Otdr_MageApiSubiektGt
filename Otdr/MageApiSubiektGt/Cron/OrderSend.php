<?php
namespace Otdr\MageApiSubiektGt\Cron;


class OrderSend 
{
   protected $logger;
   protected $config;
   protected $ordersProcessed = 0;


   public function __construct(\Otdr\MageApiSubiektGt\Helper\Config $config){
      $this->config = $config;      
   }


   public function execute()
   {
      $this->config->getGen('subiekt_api_gateway');


      return true;
   }

   public function getOrdersProcessed(){
      return $this->ordersProcessed;
   }
}