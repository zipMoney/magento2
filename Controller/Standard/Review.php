<?php
namespace ZipMoney\ZipMoneyPayment\Controller\Standard;


use Magento\Checkout\Model\Type\Onepage;

class Review extends AbstractStandard
{   

  public function execute()
  {
    try {      
      $eMsg = null;
      $this->_logger->info("In /review");
      // Cancel if the order has been created 
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

    } catch (\Magento\Framework\Exception\LocalizedException $e) {       
      $eMsg = $e->getMessage();
      $this->messageManager->addExceptionMessage($e, $e->getMessage());
    } catch (\Exception $e) {           
      $eMsg = $e->getMessage();
      $this->messageManager->addExceptionMessage($e, __('An error occurred'));
    }
    
    $message = __('zipMoney Payments has put your order in review.');
    $this->messageManager->addWarningMessage($message);
    $this->_logger->info($message);

    if(isset($eMsg)){
      $this->_logger->warn(__($eMsg));
    }

    return $this->_redirect('checkout/cart');
  }
}
