<?php
namespace Otdr\MageApiSubiektGt\Console;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Otdr\MageApiSubiektGt\Cron\DocPDF;

class DocPDFCommand extends Command
{   

   protected $docPDF = false;
   
   public function __construct(DocPDF $docPDF){      
      $this->docPDF = $docPDF;
      parent::__construct();
   }

   protected function configure()
   {
       $this->setName('mageapisubiektgt:docpdf');
       $this->setDescription('Pobranie dokumentów sprzedaży jako PDF');           
       parent::configure();
   }
   protected function execute(InputInterface $input, OutputInterface $output)
   {
        if($this->docPDF->execute()){
            $output->writeln('Processing OK.');
            $output->writeln($this->docPDF->getLog());
            $output->writeln('Processed sale: '.$this->docPDF->getOrdersProcessed());
        }else{
            $output->writeln('Processing ERROR.');
        }
   }

}