<?php
namespace ZipMoney\ZipMoneyPayment\Model;

use \Magento\Checkout\Model\Type\Onepage;
use \ZipMoney\ZipMoneyPayment\Model\Config;
use \ZipMoney\ZipMoneyPayment\Model\Checkout\AbstractCheckout;

class Checkout extends AbstractCheckout
{ 

  protected $_apiClass = '\zipMoney\Api\CheckoutsApi';

  const STATUS_MAGENTO_AUTHORIZED = "zip_authorised";

  public function __construct(    
    \Magento\Customer\Model\Session $customerSession,
    \Magento\Checkout\Model\Session $checkoutSession,
    \Magento\Checkout\Helper\Data $checkoutHelper,    
    \Magento\Framework\Json\Helper\Data $jsonHelper,
    \ZipMoney\ZipMoneyPayment\Helper\Payload $payloadHelper,
    \ZipMoney\ZipMoneyPayment\Helper\Logger $logger,
    \ZipMoney\ZipMoneyPayment\Model\Config $config,
    array $data = []
  )
  { 
   
    if (isset($data['quote'])) {
      if($data['quote'] instanceof \Magento\Quote\Model\Quote){
        $this->setQuote($data['quote']);
      } else {      
        throw new \Magento\Framework\Exception\LocalizedException(__('Quote instance is required.'));
      }
    }

    parent::__construct($customerSession, $checkoutSession, $checkoutHelper, $jsonHelper, $payloadHelper, $logger, $config);
    
    // Set the api class
    $this->setApi($this->_apiClass);
  }


  /**
   * Create quote in Zip side if not existed, and request for redirect url
   *
   * @param $quote
   * @return null
   * @throws Mage_Core_Exception
   */
  public function start()
  {

    if (!$this->_quote || !$this->_quote->getId()) {
      throw new \Magento\Framework\Exception\LocalizedException(__('The quote does not exist.'));
    }

    if ($this->_quote->getIsMultiShipping()) {
      $this->_quote->setIsMultiShipping(false);
      $this->_quote->removeAllAddresses();
    }

    $checkoutMethod = $this->getCheckoutMethod();
    $isAllowedGuestCheckout = $this->_checkoutHelper->isAllowedGuestCheckout($this->_quote, $this->_quote->getStoreId());
    $isCustomerLoggedIn = $this->_getCustomerSession()->isLoggedIn();
    
    $this->_logger->debug("Checkout Method:- ".$checkoutMethod);
    $this->_logger->debug("Is Allowed Guest Checkout :- ".$isAllowedGuestCheckout);
    $this->_logger->debug("Is Customer Logged In :- ".$isCustomerLoggedIn);

    if ((!$checkoutMethod || $checkoutMethod != Onepage::METHOD_REGISTER) &&
      !$isAllowedGuestCheckout &&
      !$isCustomerLoggedIn) {
      throw new \Magento\Framework\Exception\LocalizedException(__('Please log in to proceed to checkout.'));
    }

    // Calculate Totals
    $this->_quote->collectTotals();

    if (!$this->_quote->getGrandTotal() && !$this->_quote->hasNominalItems()) {
      throw new \Magento\Framework\Exception\LocalizedException(__('Cannot process the order due to zero amount.'));
    }

    $this->_quote->reserveOrderId()->save();

    $request = $this->_payloadHelper->getCheckoutPayload($this->_quote);

    $this->_logger->debug("Checkout Request:- ".$this->_jsonHelper->jsonEncode($request));

    try {

      $checkout = $this->getApi()->checkoutsCreate($request);

      $this->_logger->debug("Checkout Response:- ".$this->_jsonHelper->jsonEncode($checkout));

      if(isset($checkout->error)){
        throw new \Magento\Framework\Exception\LocalizedException(__('Cannot get redirect URL from zipMoney.'));
      }

      $this->_checkoutId  = $checkout->getId();

      $this->_quote->setZipmoneyCid($this->_checkoutId)
                   ->save();

      $this->_redirectUrl = $checkout->getUri();
    } catch(\zipMoney\ApiException $e){
      $this->_logger->debug("Errors:- ".json_encode($e->getResponseBody()));      
      throw new \Magento\Framework\Exception\LocalizedException(__('An error occurred while to requesting the redirect url.'));
    } 

    return $checkout;
  }


  public function getRedirectUrl()
  {
    return $this->_redirectUrl;
  }

  public function getCheckoutId()
  {
    return $this->_checkoutId;
  }


}