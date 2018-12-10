<?php
namespace Otdr\MageApiSubiektGt\Console;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\{InputInterface, InputArgument, InputOption};
use Symfony\Component\Console\Output\OutputInterface;
use Otdr\MageApiSubiektGt\Cron\OrderManager;

class OrderManagerCommand extends Command
{   

   protected $om = false;
   
   public function __construct(OrderManager $om){      
      $this->om = $om;
      parent::__construct();
   }

   protected function configure()
   {
       $this->setName('mageapisubiektgt:orderm');
       $this->setDescription('Weryfikacja i pobranie zamówień do przetworzenia');  
       $this->addArgument('date', InputArgument::OPTIONAL,'Ustawienie daty dla zakresu zamówień któ®e mają zaostać pobrane');         
       parent::configure();
   }
   protected function execute(InputInterface $input, OutputInterface $output)
   {
        $date = $input->getArgument('date');
        if(!is_null($date)){
          $this->setDate($date);
        }

        if($this->om->execute()){
          $output->writeln('Processed orders: '.$this->om->getOrdersProcessed());
          $output->writeln('Processing OK.');                        
        }else{
            $output->writeln('Processing ERROR.');
        }
   }

   protected function setDate($date){
      $objectManager = \Magento\Framework\App\ObjectManager::getInstance(); 
      $cfg = $objectManager->get('Otdr\MageApiSubiektGt\Helper\Config');      
      $cfg->save('internal/last_order_date',$date);      
   }

}