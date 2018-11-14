<?php
namespace Otdr\MageApiSubiektGt\Console;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Otdr\MageApiSubiektGt\Cron\OrderState;

class OrderStateCommand extends Command
{   

   protected $orderState = false;
   
   public function __construct(OrderState $orderState){      
      $this->orderState = $orderState;
      parent::__construct();
   }

   protected function configure()
   {
       $this->setName('mageapisubiektgt:orderstate');
       $this->setDescription('Sprawdzenie statusu zamówienia');           
       parent::configure();
   }
   protected function execute(InputInterface $input, OutputInterface $output)
   {
        if($this->orderState->execute()){
            $output->writeln('Processing OK.');
            $output->writeln($this->orderState->getLog());
            $output->writeln('Processed state: '.$this->orderState->getOrdersProcessed());
        }else{
            $output->writeln('Processing ERROR.');
        }
   }

}