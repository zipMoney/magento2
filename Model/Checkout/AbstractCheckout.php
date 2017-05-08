<?php
namespace ZipMoney\ZipMoneyPayment\Model\Checkout;

use \Magento\Checkout\Model\Type\Onepage;
use \ZipMoney\ZipMoneyPayment\Model\Config;

abstract class AbstractCheckout
{ 

  /**
   * @var string
   */
  protected $_quote;

  /**
   * @var string
   */
  protected $_order;
  /**
   * @var string
   */
  protected $_api;
  /**
   * @var Magento\Checkout\Helper\Data
   */
  protected $_checkoutHelper;
  
 /**
   * @var Magento\Checkout\Helper\Data
   */
  protected $_jsonHelper;
  
  /**
   * @var \ZipMoney\ZipMoneyPayment\Helper\Payload
   */
  protected $_payloadHelper;
  
  /**
   * @var \ZipMoney\ZipMoneyPayment\Helper\Logger
   */
  protected $_logger;

  /**
   * @var \ZipMoney\ZipMoneyPayment\Model\Config
   */
  protected $_config;

  /**
   * @var \Magento\Customer\Model\Session
   */
  protected $_customerSession;  

  /**
   * @var \Magento\Checkout\Model\Session
   */
  protected $checkoutSession;


  public function __construct(    
    \Magento\Customer\Model\Session $customerSession,
    \Magento\Checkout\Model\Session $checkoutSession,
    \Magento\Checkout\Helper\Data $checkoutHelper,
    \Magento\Framework\Json\Helper\Data $jsonHelper,
    \ZipMoney\ZipMoneyPayment\Helper\Payload $payloadHelper,
    \ZipMoney\ZipMoneyPayment\Helper\Logger $logger,
    \ZipMoney\ZipMoneyPayment\Model\Config $config        
  )
  {
    $this->_checkoutHelper = $checkoutHelper;
    $this->_customerSession = $customerSession;
    $this->_checkoutSession = $checkoutSession;
    $this->_jsonHelper = $jsonHelper;
    $this->_payloadHelper = $payloadHelper;
    $this->_jsonHelper = $jsonHelper;
    $this->_logger  = $logger;
    $this->_config  = $config;

    // Configure API Credentials
    $apiConfig = \zipMoney\Configuration::getDefaultConfiguration();
    
    $apiConfig->setApiKey('Authorization', $this->_config->getMerchantPrivateKey())
              ->setApiKeyPrefix('Authorization', 'Bearer')
              ->setEnvironment($this->_config->getEnvironment());
             // ->setPlatform("Magento/".Mage::getVersion()." Zipmoney_ZipmoneyPayment/".$this->_helper->getExtensionVersion());
  }


  /**
   * Return checkout session object
   *
   * @return \Magento\Checkout\Model\Session
   */
  protected function _getCheckoutSession()
  {
    return $this->_checkoutSession;
  }

  /**
   * Return checkout session object
   *
   * @return \Magento\Checkout\Model\Session
   */
  protected function _getCustomerSession()
  {
    return $this->_customerSession;
  }

  /**
   * Get checkout method
   *
   * @return string
   */
  public function getCheckoutMethod()
  {
    if ($this->_getCustomerSession()->isLoggedIn()) {
      return Onepage::METHOD_CUSTOMER;
    }
    if (!$this->_quote->getCheckoutMethod()) {
      if ($this->_checkoutHelper->isAllowedGuestCheckout($this->_quote)) {
        $this->_quote->setCheckoutMethod(Onepage::METHOD_GUEST);
      } else {
        $this->_quote->setCheckoutMethod(Onepage::METHOD_REGISTER);
      }
    }
    return $this->_quote->getCheckoutMethod();
  }


  public function getCharge()
  {
    return $this->_charge;
  }

  public function getApi()
  {
    if(null === $this->_api){
      throw new \Magento\Framework\Exception\LocalizedException(__('Api class has not been set.'));
    }

    return $this->_api;
  }


  public function setApi($api)
  {
    if(is_object($api)) {
      $this->_api =  $api;
    } else if(is_string($api)) {
      $this->_api = new $api;
    }

    return $this;
  }

  public function getQuote()
  {
    return $this->_quote;
  }

  public function setQuote($quote)
  {
    if ($quote) {
      $this->_quote = $quote;
    }
    return $this;
  }

  public function getOrder()
  {
    return $this->_order;
  }

  public function setOrder($order)
  {
    if ($order) {
      $this->_order = $order;
    }
    return $this;
  } 

  public function genIdempotencyKey()
  {
    return uniqid();
  }

}