<?php
namespace Otdr\MageApiSubiektGt\Console;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Otdr\MageApiSubiektGt\Cron\DocEmail;

class DocEmailCommand extends Command
{   

   protected $docEmail = false;
   
   public function __construct(DocEmail $docEmail){      
      $this->docEmail = $docEmail;
      parent::__construct();
   }

   protected function configure()
   {
       $this->setName('mageapisubiektgt:sendemail');
       $this->setDescription('Wysałanie e-mailem dokumentu sprzedaży do klienta');           
       parent::configure();
   }
   protected function execute(InputInterface $input, OutputInterface $output)
   {
        if($this->docEmail->execute()){
            $output->writeln('Processing OK.');
            $output->writeln($this->docEmail->getLog());
            $output->writeln('Processed sale: '.$this->docEmail->getOrdersProcessed());
        }else{
            $output->writeln('Processing ERROR.');
        }
   }

}