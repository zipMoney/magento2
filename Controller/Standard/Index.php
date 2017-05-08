<?php
namespace ZipMoney\ZipMoneyPayment\Controller\Standard;
       

use Magento\Checkout\Model\Type\Onepage;

class Index extends AbstractStandard
{   

  public function execute()
  {
    try {    
      $this->_logger->debug("Starting Checkout");
      // Do the checkout
      $this->_initCheckout()->start();

      // Get the redirect url
      if($redirectUrl = $this->_checkout->getRedirectUrl()) {
      
        $this->_logger->info(__('Successful to get redirect url [ %s ] ', $redirectUrl));

        $aData = array(
            'redirect_uri'      => $redirectUrl,
            'message'    => __('Redirecting to zipMoney.')
        );                    

        return $this->sendResponse($aData,\Magento\Framework\Webapi\Response::HTTP_OK);
      } else {        
        throw new \Magento\Framework\Exception\LocalizedException(__('Could not get the redirect url'));
      }
    } catch (\Exception $e) {                
      $this->_logger->debug($e->getMessage());
    }


    if(empty($result['error'])){
      $result['error'] = __('Can not get the redirect url from zipMoney.');
    }

    $this->_sendResponse($result, \Magento\Framework\Webapi\Exception::HTTP_INTERNAL_ERROR);
  }

  public function executenew($paymentData)
  {
    try {    
      $this->_logger->debug("Starting Checkout");
        //print_r($cartId);
      // print_r($this->_request->getParams());
    print_r($paymentData);
    print_r($this->getRequest()->getParam("email"));
    print_r($this->getRequest()->getParams());
    $data = $this->getRequest()->getPost();
    print_r($data);
      // print_r($this->getRequest()->getParam('cartId'));

      //$this->_paymentInformationManagement->savePaymentInformation();

      // Do the checkout
      $this->_initCheckout()->start();

      // Get the redirect url
      if($redirectUrl = $this->_checkout->getRedirectUrl()) {
      
        $this->_logger->info(__('Successful to get redirect url [ %s ] ', $redirectUrl));

        $aData = array(
            'redirect_uri'      => $redirectUrl,
            'message'    => __('Redirecting to zipMoney.')
        );                    

        return $this->sendResponse($aData,\Magento\Framework\Webapi\Response::HTTP_OK);
      } else {        
        throw new \Magento\Framework\Exception\LocalizedException(__('Could not get the redirect url'));
      }
    } catch (\Exception $e) {                
      $this->_logger->debug($e->getMessage());
    }


    if(empty($result['error'])){
      $result['error'] = __('Can not get the redirect url from zipMoney.');
    }

    $this->_sendResponse($result, \Magento\Framework\Webapi\Exception::HTTP_INTERNAL_ERROR);
  }

}
