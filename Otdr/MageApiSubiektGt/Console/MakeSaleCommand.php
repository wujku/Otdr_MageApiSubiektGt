<?php
namespace Otdr\MageApiSubiektGt\Console;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Otdr\MageApiSubiektGt\Cron\MakeSale;

class MakeSaleCommand extends Command
{   

   protected $makeSale = false;
   
   public function __construct(MakeSale $makeSale){      
      $this->makeSale = $makeSale;
      parent::__construct();
   }

   protected function configure()
   {
       $this->setName('mageapisubiektgt:makesale');
       $this->setDescription('Generacja dokumentów sprzedaży w SubiektGt');           
       parent::configure();
   }
   protected function execute(InputInterface $input, OutputInterface $output)
   {
        if($this->makeSale->execute()){
            $output->writeln('Processing OK.');
            $output->writeln($this->makeSale->getLog());
            $output->writeln('Made sale: '.$this->makeSale->getOrdersProcessed());
        }else{
            $output->writeln('Processing ERROR.');
        }
   }

}