<?php
namespace Otdr\MageApiSubiektGt\Console;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Otdr\MageApiSubiektGt\Cron\OrderSend;

class OrderSendCommand extends Command
{   

   protected $orderSend = false;
   
   public function __construct(OrderSend $orderSend){      
      $this->orderSend = $orderSend;
      parent::__construct();
   }

   protected function configure()
   {
       $this->setName('mageapisubiektgt:sendorders');
       $this->setDescription('Eksport zamówień do Subiekta GT');           
       parent::configure();
   }
   protected function execute(InputInterface $input, OutputInterface $output)
   {        
        if($this->orderSend->execute()){
            $output->writeln('Processing OK.');
            $output->writeln('Processed orders: '.$this->orderSend->getOrdersProcessed());
        }else{
            $output->writeln('Processing ERROR.');
        }
   }

}