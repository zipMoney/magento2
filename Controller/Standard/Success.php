<?php
namespace ZipMoney\ZipMoneyPayment\Controller\Standard;

use Magento\Checkout\Model\Type\Onepage;

class Success extends AbstractStandard
{   

  public function execute()
  {
    $orderId = $this->_getCheckoutSession()->getLastOrderId();
    $order = $orderId ? $this->_orderFactory->create()->load($orderId) : false;

    // Verify token and request
    if(!$this->_verifyToken($order, $this->_request->getParam('token'))){
      $this->_logger->debug("Request Params:-".json_encode($this->_request->getParams()));
      $msg = __('Invalid token');
      $this->_logger->warn($msg);     
      $this->messageManager->addErrorMessage($msg);    
      return $this->_redirect( $this->_url->getUrl('zipmoneypayment/standard/error/'));
    } 
    
  return $this->_redirect('checkout/onepage/success');
  }
}
