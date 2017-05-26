<?php
namespace ZipMoney\ZipMoneyPayment\Observer;

use ZipMoney\ZipMoneyPayment\Model\Config;

class OrderCancel implements \Magento\Framework\Event\ObserverInterface {
  
  /**
   * @var \ZipMoney\ZipMoneyPayment\Model\Config
   */
  protected $_config;
  
  /**
   * @var \ZipMoney\ZipMoneyPayment\Helper\Logger
   */
  protected $_logger;
  
  /**
   * @var \ZipMoney\ZipMoneyPayment\Model\StoreScope
   */
  protected $_storeScope;
 
  /**
   * @var \ZipMoney\ZipMoneyPayment\Helper\Api
   */
  protected $_apiHelper;



  public function __construct(
    \ZipMoney\ZipMoneyPayment\Model\Config $config, 
    \ZipMoney\ZipMoneyPayment\Model\StoreScope $storeScope,
    \ZipMoney\ZipMoneyPayment\Helper\Logger $logger,
    \ZipMoney\ZipMoneyPayment\Helper\Api $apiHelper                            
  )
  {
    $this->_config     = $config;
    $this->_logger     = $logger;
    $this->_storeScope = $storeScope;
    $this->_apiHelper  = $apiHelper;
  } 

  // /**
  //  * Checks if order was created by zipMoney
  //  *
  //  * @param \Magento\Sales\Model\Order $order
  //  * @return bool
  //  */
  // private function _isZipMoneyOrder(\Magento\Sales\Model\Order $order)
  // {
  //   if (!$order || !$order->getId()) {
  //     return false;
  //   }

  //   // check if the order was created by zipMoney
  //   $payment = $order->getPayment();
  //   if ($payment && $payment->getId()) {
  //     if (Config::METHOD_ZC == $payment->getMethod() || Config::METHOD_ZP == $payment->getMethod() ) {
  //       return true;
  //     }
  //   }

  //   return false;
  // }

  // /**
  //  * Check whether order is cancel, and decide to notify zipMoney or not
  //  *
  //  * @param Mage_Sales_Model_Order $oOrder
  //  * @return bool
  //  */
  // private function _notifyOrderCancelled(\Magento\Sales\Model\Order $order)
  // {
  //   if (!$order || !$order->getId()) {
  //     return false;
  //   }

  //   // check if the order was created by zipMoney
  //   if (!$this->_isZipMoneyOrder($order)) {
  //     $this->_logger->debug(__('Order ' . $order->getIncrementId() . ' was not created by zipMoney. Will not notify zipMoney to cancel order.'));
  //     return false;
  //   }

  //   $originalState = $order->getOrigData('state');
  //   $currState = $order->getState();
  //   if ($currState != \Magento\Sales\Model\Order::STATE_CANCELED || $originalState == $currState) {
  //     return false;
  //   } else {
  //     return true;
  //   }
  // }
  
  // public function execute(\Magento\Framework\Event\Observer $observer) 
  // {       

  //   $order = $observer->getOrder();
  //   // set scope
  //   if ($order) 
  //     $this->_storeScope->setStoreId($order->getStoreId());
    
  //   if (!$this->_notifyOrderCancelled($order)) 
  //     return;
  
  //   $this->_logger->debug('Starting cancellation for the order (' . $order->getIncrementId() . ') ');

  //   try {
  //     $cancelApi = $this->_apiHelper->getApi(Config::API_TYPE_CANCEL); 
     
  //     $this->_payloadHelper->prepareDataForCancel($cancelApi->request, ['order' => $order ] );
  //     // Log Request
  //     $this->_logger->debug("Request:- " . json_encode($cancelApi->request));
  //     $response = $cancelApi->process();
      
  //     // Log Response
  //     $this->_logger->debug("Response:- " . json_encode($response->toArray()));
     
  //     if($response->isSuccess()){
  //       $this->_logger->debug('Order has been cancelled at zipMoney.');
  //     } else {
  //       $this->_logger->debug('Could not cancel the order at zipMoney.');
  //     }

  //   } catch (\Exception $e) {
  //     $this->_logger->debug(__("An error occurred while cancelling order at zipMoney. Error Message:- ".$e->getMessage()));
  //   }

  // }
 
}