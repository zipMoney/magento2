<?php
namespace ZipMoney\ZipMoneyPayment\Model;

class Cron {
 
  protected $_logger;
  protected $_config;
  protected $_date;
  protected $_orderModel;
  protected $_timezone;
  protected $_registry;
  protected $_eventManager;

  public function __construct(        
    \Magento\Framework\Model\Context $context,
    \ZipMoney\ZipMoneyPayment\Helper\Logger $logger,     
    \Magento\Framework\Stdlib\DateTime\DateTime $date,    
    \Magento\Sales\Model\Order $orderModel,
    \ZipMoney\ZipMoneyPayment\Model\Config $config,   
    \Magento\Framework\Stdlib\DateTime\TimezoneInterface $timezone,
    \Magento\Framework\Registry $registery) {
    $this->_logger = $logger;
    $this->_config = $config;
    $this->_orderModel = $orderModel;
    $this->_date = $date;    
    $this->_timezone= $timezone;   
    $this->_registry = $registery;        
    $this->_eventManager = $context->getEventDispatcher();

  }

  public function execute() 
  { 
    $this->_logger->info("Running Pending Orders Cron");

    if($this->_isEnabledHandlePendingOrders()){
      $new_date = date('Y-m-d H:i:s', strtotime('-24 hour', $this->_date->gmtTimestamp()));    
      $orders = $this->_orderModel->getCollection()
                               ->addFieldToFilter( 'status', 'pending') 
                               ->addFieldToFilter( 'created_at', array('lteq'=>$new_date))
                               ->join(array('payment' => 'sales_order_payment'), 'main_table.entity_id = payment.parent_id && ( payment.method="zm_zipcredit" or payment.method = "zm_zippay")'); 
  
      $order_action = $this->_pendingOrdersAction() ? $this->_pendingOrdersAction() : 'cancel';
      
      $this->_logger->info("Date Filter:- ".$new_date." | Pending Order Action:- ".$order_action." | Total Orders to Action:- ".count($orders));
      
      $this->_registry->register('isSecureArea','true');         //create secure area to delete orders

      foreach ($orders as  $order) {            
        $this->_logger->info("Order Id:- ".$order->getIncrementId());
        switch($order_action){
          case 'cancel':
            if ($order->canCancel()) {
              $order->registerCancellation()->save();
              $this->_eventManager->dispatch('order_cancel_after', ['order' => $order]);

            }
            break; 
          case 'delete': 
            $order->delete()->save();
          break;
        }
      }
      $this->_registry->unregister('isSecureArea');         //create secure area to delete orders
    }
    
    $this->_logger->info("Finished Running Pending Orders Cron");
    return $this;
  }

  protected function _isEnabledHandlePendingOrders()
  {
    return (int)$this->_config->getConfigData(Config::PAYMENT_ZIPMONEY_HANDLE_PENDING_ORDERS) == 1 ? true : false;
  }

  protected function _pendingOrdersAction()
  {
    return $this->_config->getConfigData(Config::PAYMENT_ZIPMONEY_PENDING_ORDERS_ACTION);
  }
}