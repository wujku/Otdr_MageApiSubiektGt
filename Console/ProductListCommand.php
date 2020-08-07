<?php
namespace Otdr\MageApiSubiektGt\Console;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Otdr\MageApiSubiektGt\Cron\ProductList;

class ProductListCommand extends Command
{   

   protected $productList = false;
   
   public function __construct(ProductList $productList){      
      $this->productList = $productList;
      parent::__construct();
   }

   protected function configure()
   {
       $this->setName('mageapisubiektgt:productlist');
       $this->setDescription('Pobiera listę produktów');           
       parent::configure();
   }
   protected function execute(InputInterface $input, OutputInterface $output)
   {        
        if($this->productList->execute()){
            $output->writeln('Processing OK.');
            $output->writeln('Processed products: '.$this->productList->count());
        }else{
            $output->writeln('Processing ERROR.');
        }
   }

}