<?php
namespace Otdr\MageApiSubiektGt\Cron;

use Otdr\MageApiSubiektGt\Helper\SubiektApi;
use Exception;

class OrderSend extends CronObject
{
    

   public function __construct(\Otdr\MageApiSubiektGt\Helper\Config $config,\Psr\Log\LoggerInterface $logger, \Magento\Framework\App\State $appState ){
      parent::__construct($config,$logger,$appState);
   }


   protected function getOrdersIds(){
         $connection = $this->resource->getConnection();
         $tableName = $this->resource->getTableName('otdr_mageapisubiektgt');
         $query = 'SELECT id_order FROM '.$tableName.' WHERE is_locked = 0 AND gt_order_sent = 0';
         $result = $connection->fetchAll($query);
         return $result;
   }

   protected function updateOrderStatus(){

   }

   protected function getOrderData($id_order){
      $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
      $order = $objectManager->create('Magento\Sales\Api\Data\OrderInterface')->load($id_order);
      return $order;
   }

   public function execute()
   {
      
      $subiektApi = new SubiektApi($this->api_key,$this->end_point);      
      $objectManager = \Magento\Framework\App\ObjectManager::getInstance(); 

      $orders_to_send = $this->getOrdersIds();
                  
      foreach($orders_to_send as $order){
         $id_order = $order['id_order'];     

         /* Locking order for processing */
        // $this->lockOrder($id_order);

         $order_data = $this->getOrderData($id_order);

         /* Bulding order array */
         $payment = $order_data->getPayment()->getData();
         $shipping = $order_data->getShippingDescription();   
         $comments_list =  $order_data->getAllStatusHistory();
         $comments = array();
         foreach($comments_list as $comment){
            if($comment->getData('is_visible_on_front') == 1){
               $comments[] = $comment->getData('comment');
            }
            
         }
         
         $order_json[$id_order] = array(
                           'create_product_if_not_exists'    => $this->subiekt_api_newproducts,
                           'amount' =>$payment['amount_ordered'],
                           'reference' =>  trim($this->subiekt_api_prefix.' '. $id_order),
                           'pay_type' => 'transfer',
                           'comments' => trim('Doręczyciel: '.$shipping.', płatność: '.$payment['additional_information']['method_title'].". ".implode(" ",$comments))
                           );

         
             
         /* Bulding customer array */
         $customer = $order_data->getBillingAddress()->getData();
         $order_json[$id_order]['customer'] = array(
                                                      'firstname'    => $customer['firstname'],
                                                      'lastname'     => $customer['lastname'],
                                                      'email'        => $customer['email'],                     
                                                      'address'      => $customer['street'],
                                                      'address_no'   => '',
                                                      'city'         => $customer['city'],
                                                      'post_code'    => $customer['postcode'],
                                                      'phone'        => $customer['telephone'],
                                                      'ref_id'       => trim($this->subiekt_api_prefix.'CS '.$customer['entity_id']),
                                                      'is_company'   => $customer['vat_is_valid']==true?true:false,
                                                      'company_name' => $customer['company'],
                                                      'tax_id'       => $customer['vat_id'],                                                      
                                             );
   

         /* Bulidnig ordered products array */
         $products = $order_data->getAllItems();

         $products_array = array(); 
         foreach($products as $product){
            //var_Dump($product->getCustomAttribute('ean'));
            $productObject = $objectManager->get('\Magento\Catalog\Model\Product')->load($product->getProductId());            
            $products_array[] =  array(
                                          'name'   =>                      $product->getName(),
                                          'price'  =>                      $product->getPrice(),
                                          'qty'    =>                      $product->getQtyOrdered(),
                                          'price_before_discount' =>       $product->getPrice(),
                                          'code'   =>                      $this->subiekt_api_ean_attrib!=""?$productObject->{"get{$this->subiekt_api_ean_attrib}"}():$product->getSku(),
                                          'time_of_delivery'   =>          2,
                                          'id_store' => $this->subiekt_api_warehouse_id
                                       );
         }

         $order_json[$id_order]['products'] = $products_array;
         
         /* Shippment information */
        if($order_data->getShippingAmount()>0){
            $a_sp = array(
                  'ean'=>$this->subiekt_api_trans_symbol,                  
                  'code'=>$this->subiekt_api_trans_symbol,                  
                  'qty'=> 1,
                  'price' => $order_data->getShippingAmount(),
                  'price_before_discount' => $order_data->getShippingAmount(),
                  'name' => 'Koszty wysyłki',
                  'id_store' => $this->subiekt_api_warehouse_id,
            );
            array_push($order_json[$id_order]['products'],$a_sp);
         }
         
         
      
         var_dump($order_json);
         /* Sending order data to SubiektGt API */
         /*
         $result = $subiektApi->call('order/add',$order_json[$id_order]);                  
         if(!$result){
            throw new Exception('Can\'t connect to API check configuration!', 1);                 
         }
         if($result['state'] == 'fail'){
            throw new Exception($result['message'], 1);         
         }
         */


         /* unlocking order after processing */
         $this->unlockOrder($id_order);

         /* Update order processing status */
         //TODO: HERE

         /* Update products qty on magento */
         $result = $subiektApi->call('product/getqtysbycode',array('products_qtys'=>$order_json[$id_order]['products']));             
         if($result['state'] == 'success'){
            foreach($result['data'] as $ean13 => $pd){                      
               if(is_array($pd)){                  
                  $this->productQtyUpdate($ean13,$pd['available']);
               }
            }
         }     

      }
      
      
      return true;
   }



   public function getOrdersProcessed(){
      return $this->ordersProcessed;
   }
}