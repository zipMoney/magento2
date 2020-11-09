<?php
namespace ZipMoney\ZipMoneyPayment\Model;

use \Magento\Checkout\Model\Type\Onepage;
use \ZipMoney\ZipMoneyPayment\Model\Config;
use \ZipMoney\ZipMoneyPayment\Model\Checkout\AbstractCheckout;

class Checkout extends AbstractCheckout
{   
  /**
   * @var Magento\Checkout\Helper\Data
   */
  protected $_checkoutHelper;

  /**
   * @var string
   */
  protected $_redirectUrl  = null;
 
  /**
   * @var string
   */
  protected $_checkoutId  = null;


  const STATUS_MAGENTO_AUTHORIZED = "zip_authorised";

  public function __construct(    
    \Magento\Customer\Model\Session $customerSession,    
    \Magento\Checkout\Model\Session $checkoutSession,   
    \Magento\Checkout\Helper\Data $checkoutHelper,    
    \Magento\Quote\Api\CartRepositoryInterface $quoteRepository,
    \Magento\Sales\Model\OrderFactory $orderFactory,
    \Magento\Customer\Model\CustomerFactory $customerFactory,
    \Magento\Checkout\Model\PaymentInformationManagement $paymentInformationManagement,
    \Magento\Customer\Api\AccountManagementInterface $accountManagement,
    \Magento\Customer\Api\CustomerRepositoryInterface $customerRepository,
    \Magento\Framework\Message\ManagerInterface $messageManager,
    \ZipMoney\ZipMoneyPayment\Helper\Payload $payloadHelper,
    \ZipMoney\ZipMoneyPayment\Helper\Logger $logger,    
    \ZipMoney\ZipMoneyPayment\Helper\Data $helper,
    \ZipMoney\ZipMoneyPayment\Model\Config $config,    
    \zipMoney\Api\CheckoutsApi $checkoutsApi,
    array $data = []
  )
  { 
    $this->_checkoutHelper = $checkoutHelper;
    $this->_api = $checkoutsApi;

    if (isset($data['quote'])) {
      if($data['quote'] instanceof \Magento\Quote\Model\Quote){
        $this->setQuote($data['quote']);
      } else {      
        throw new \Magento\Framework\Exception\LocalizedException(__('Quote instance is required.'));
      }
    }
  
    parent::__construct( $customerSession, $checkoutSession, $customerFactory, $quoteRepository, $payloadHelper, $logger, $helper, $config);
  }


  /**
   * Create quote in Zip side if not existed, and request for redirect url
   *
   * @param  \Magento\$quote
   * @return \zipMoney\Model\Checkout
   * @throws \Magento\Framework\Exception\LocalizedException
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

    $this->_quote->reserveOrderId();
    /* 
      Commenting out the  following line.
      Apparantly triggering more than one quote save results in "We don't have as many "Produt Name" as you requested." error when the product has 1 item left.
    */
    //$this->_quoteRepository->save($this->_quote);

    $request = $this->_payloadHelper->getCheckoutPayload($this->_quote);

    $this->_logger->debug("Checkout Request:- ".$this->_payloadHelper->jsonEncode($request));

    try {

      $checkout = $this->getApi()->checkoutsCreate($request);

      $this->_logger->debug("Checkout Response:- ".$this->_payloadHelper->jsonEncode($checkout));

      if(isset($checkout->error)){
        throw new \Magento\Framework\Exception\LocalizedException(__('Cannot get redirect URL from zipMoney.'));
      }

      $this->_checkoutId  = $checkout->getId();
      $additionalPaymentInfo = $this->_quote->getPayment()->getAdditionalInformation();
      $additionalPaymentInfo['zipmoney_checkout_id'] = $this->_checkoutId;
      $this->_quote->getPayment()->setAdditionalInformation($additionalPaymentInfo);
      $this->_quoteRepository->save($this->_quote);
      $this->_redirectUrl = $checkout->getUri();      
    } catch(\zipMoney\ApiException $e){
      $this->_logger->debug("Errors:- ".json_encode($e->getResponseBody()));
      $this->_logger->debug("Errors:- ".json_encode($e->getCode()));
      $this->_logger->debug("Errors:- ".json_encode($e->getResponseObject()));
      throw new \Magento\Framework\Exception\LocalizedException(__('An error occurred while to requesting the redirect url.'));
    }

    return $checkout;
  }

  /**
   * Returns the zipMoney Redirect Url
   *
   * @return string
   */
  public function getRedirectUrl()
  {
    return $this->_redirectUrl;
  }
  
  /**
   * Returns the zipMoney Checkout Id
   *
   * @return string
   */
  public function getCheckoutId()
  {
    return $this->_checkoutId;
  }
}