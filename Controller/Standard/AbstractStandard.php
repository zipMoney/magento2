<?php
namespace ZipMoney\ZipMoneyPayment\Controller\Standard;

use \Magento\Framework\App\Action\Action;
use \Magento\Checkout\Controller\Express\RedirectLoginInterface;

abstract class AbstractStandard extends Action
{
  /**
   * Internal cache of checkout models
   *
   * @var array
   */
  protected $_checkoutTypes = [];
  /**
   * Config 
   *
   * @var string
   */
  protected $_config;
  /**
   * @var \Magento\Quote\Model\Quote
   */
  protected $_quote = false;

  /**
   * Config mode type
   *
   * @var string
   */
  protected $_configType;

  /**
   * Config method type
   *
   * @var string
   */
  protected $_configMethod;

  /**
   * Checkout mode type
   *
   * @var string
   */
  protected $_checkoutType = "\ZipMoney\ZipMoneyPayment\Model\Checkout";

  /**
   * @var \Magento\Checkout\Model\Session
   */
  protected $_checkoutSession; 

  /**
   * @var \Magento\Customer\Model\Session
   */
  protected $_customerSession;

  /**
   * @var \Magento\Sales\Model\OrderFactory
   */
  protected $_orderFactory;  
  /**
   * @var \Magento\Quote\Api\CartRepositoryInterface
   */
  protected $_quoteRepository;  

  /**
   * @var \Magento\Framework\Url\Helper
   */
  protected $_urlHelper;

  /**
   * @var \Magento\Customer\Model\Url
   */
  protected $_customerUrl;
  
  /**
   * @var \ZipMoney\ZipMoneyPayment\Helper\Order
   */
  protected $_orderHelper;

   /**
   * @var \Magento\Framework\Json\Helper\Data
   */
  protected $_jsonHelper;

  /**
   * @var \ZipMoney\ZipMoneyPayment\Helper\Logger
   */
  protected $_logger;
  
  /**
   * @var \ZipMoney\ZipMoneyPayment\Model\Standard\
   */
  protected $_checkoutFactory;
  
  /**
   * @var \ZipMoney\ZipMoneyPayment\Model\Checkout
   */
  protected $_checkout;
  /**
   * @var \ZipMoney\ZipMoneyPayment\Model\Charge
   */
  protected $_charge;
  
  protected $_paymentInformationManagement;
 
  public function __construct(
    \Magento\Framework\App\Action\Context $context,        
    \Magento\Checkout\Model\Session $checkoutSession,
    \Magento\Customer\Model\Session $customerSession,
    \Magento\Sales\Model\OrderFactory $orderFactory,
    \Magento\Framework\Url\Helper\Data $urlHelper,
    \Magento\Customer\Model\Url $customerUrl, 
    \Magento\Framework\Json\Helper\Data $jsonHelper,
    \Magento\Checkout\Model\PaymentInformationManagement $paymentInformationManagement,
    \Magento\Quote\Api\CartRepositoryInterface $quoteRepository,
    \ZipMoney\ZipMoneyPayment\Helper\Logger $logger,
    \ZipMoney\ZipMoneyPayment\Model\Checkout\Factory $checkoutFactory
  ) {
    $this->_checkoutSession = $checkoutSession;
    $this->_customerSession = $customerSession;
    $this->_orderFactory = $orderFactory;
    $this->_quoteRepository = $quoteRepository;
    $this->_urlHelper = $urlHelper;
    $this->_customerUrl = $customerUrl;
    $this->_jsonHelper = $jsonHelper;
    $this->_paymentInformationManagement = $paymentInformationManagement;

    $this->_logger = $logger;
    $this->_checkoutFactory = $checkoutFactory;
    
    parent::__construct($context);
  }

  /**
   * Instantiate Checkout
   *
   * @return void
   * @throws \Magento\Framework\Exception\LocalizedException
   */
  protected function _initCheckout()
  {   
    $quote = $this->_getQuote();
  
    if(!$quote->getId()){
      throw new \Magento\Framework\Exception\LocalizedException(__('Quote doesnot exist'));
    }

    if (!$quote->hasItems() || $quote->getHasError()) {
      $this->getResponse()->setStatusHeader(403, '1.1', 'Forbidden');
      throw new \Magento\Framework\Exception\LocalizedException(__('Unable to initialize the Checkout.'));
    }

    return $this->_checkout = $this->_checkoutFactory
                            ->create($this->_checkoutType, ['data' => ['quote' => $quote]] );
  }


  /**
   * Instantiate checkout model and inject charge api
   *
   * @return Zipmoney_ZipmoneyPayment_Model_Standard_Checkout
   * @throws Mage_Core_Exception
   */
  protected function _initCharge()
  {
    $quote = $this->_getQuote();

    if(!$quote->getId()){
      Mage::throwException(__('Quote doesnot exist'));
    }

    if (!$quote->hasItems() || $quote->getHasError()) {
      Mage::throwException(__('Quote has error or no items.'));
    }
    
    return $this->_charge = $this->_checkoutFactory
                            ->create($this->_chargeType);
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
   * Sets the Http Headers, Response Code and Responde Body
   */
  protected function _sendResponse($data, $responseCode = Mage_Api2_Model_Server::HTTP_OK)
  {
    $this->getResponse()->setHttpResponseCode($responseCode);
    $this->getResponse()->setHeader('Content-type', 'application/json');
    $this->getResponse()->setBody($this->_jsonHelper->jsonEncode($data));
  }


  /**
   * Sets checkout quote object
   *
   * @return Mage_Sales_Model_Quote
   */
  protected function _setQuote($quote)
  {
    $this->_quote = $quote;

    return $this;
  }


  /**
   * Return checkout quote object from database
   *
   * @return Mage_Sales_Model_Quote
   */
  protected function _getDbQuote($zipmoney_cid)
  {
    // if ($zipMoneyCid) {
    //   $this->_quote = Mage::getModel('sales/quote')
    //                         ->getCollection()
    //                         ->addFieldToFilter("zipmoney_cid", $zipmoney_cid)
    //                         ->getFirstItem();
    //   return $this->_quote;
    // }
  }


  /**
   * Return checkout quote object
   *
   * @return \Magento\Quote\Model\Quote
   */
  protected function _getQuote()
  {
    if (!$this->_quote) {
      $this->_quote = $this->_getCheckoutSession()->getQuote();
    }
    return $this->_quote;
  }

  /**
   * Returns login url parameter for redirect
   *
   * @return string
   */
  public function getLoginUrl()
  {
    return $this->_customerUrl->getLoginUrl();
  }

 
 
}
