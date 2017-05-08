<?php
namespace ZipMoney\ZipMoneyPayment\Model;


use \ZipMoney\ZipMoneyPayment\Model\Config;

class Payment extends \Magento\Payment\Model\Method\AbstractMethod
{    
  /**
   * Availability option
   *
   * @var bool
   */
  protected $_canUseInternal = false;
  
  /**
   * Availability option
   *
   * @var bool
   */
  protected $_canUseCheckout = true;

  /**
   * @var bool
   */
  protected $_isGateway                   = true;
  
  /**
   * @var bool
   */
  protected $_canCapture                  = true;
  
  /**
   * @var bool
   */
  protected $_canCapturePartial           = true;
  
  /**
   * @var bool
   */
  protected $_canRefund                   = true;
 
  /**
   * @var bool
   */
  protected $_canRefundInvoicePartial     = true;

  /**
   * @var int
   */
  protected $_minAmount = null;
 
  /**
   * @var int
   */
  protected $_maxAmount = null;
  
  /**
   * @var array
   */
  protected $_supportedCurrencyCodes = array('AUD');

  /**
   * @var \Magento\Directory\Model\CountryFactory 
   */
  protected $_countryFactory;
  
  /**
   * @var \Magento\Sales\Model\OrderFactory
   */
  protected $_orderFactory;
  
  /**
   * @var \ZipMoney\ZipMoneyPayment\Helper\Api
   */
  protected $_apiHelper;
  
  /**
   * @var \Magento\Store\Model\StoreManagerInterface
   */
  protected $_storeManager;
  
  /**
   * @var \ZipMoney\ZipMoneyPayment\Model\Config
   */
  protected $_config;
  
  /**
   * @var \Magento\Payment\Model\Method\Logger
   */
  protected $_logger;
  
  /**
   * @var \ZipMoney\ZipMoneyPayment\Helper\Creditmemo
   */
  protected $_creditMemoHelper;
  
  /**
   * @var \ZipMoney\ZipMoneyPayment\Helper\Request
   */
  protected $_payloadHelper;
  
  /**
   * @var string
   */
  protected $_code = Config::METHOD_CODE;

  public function __construct(
      \Magento\Framework\Model\Context $context,
      \Magento\Framework\Registry $registry,
      \Magento\Framework\Api\ExtensionAttributesFactory $extensionFactory,
      \Magento\Framework\Api\AttributeValueFactory $customAttributeFactory,
      \Magento\Payment\Helper\Data $paymentData,
      \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
      \Magento\Payment\Model\Method\Logger $logger,
      \Magento\Store\Model\StoreManagerInterface $storeManager,
      \Magento\Framework\UrlInterface $urlBuilder,
      \Magento\Checkout\Model\Session $checkoutSession,
      \Magento\Framework\Exception\LocalizedExceptionFactory $exception,
      \Magento\Sales\Api\TransactionRepositoryInterface $transactionRepository,
      \Magento\Sales\Model\Order\Payment\Transaction\BuilderInterface $transactionBuilder,        
      \Magento\Directory\Model\CountryFactory $countryFactory,        
      \ZipMoney\ZipMoneyPayment\Model\Config $config,        
      \ZipMoney\ZipMoneyPayment\Helper\Logger $logHelper,       
      \Magento\Sales\Model\OrderFactory $orderFactory,                              
      \ZipMoney\ZipMoneyPayment\Helper\Creditmemo $creditMemoHelper,
      \ZipMoney\ZipMoneyPayment\Helper\Api $apiHelper,                              
      \ZipMoney\ZipMoneyPayment\Helper\Payload $payloadHelper,
      \Magento\Framework\Model\ResourceModel\AbstractResource $resource = null,
      \Magento\Framework\Data\Collection\AbstractDb $resourceCollection = null,                
      array $data = []
  ) {
    parent::__construct(
      $context,
      $registry,
      $extensionFactory,
      $customAttributeFactory,
      $paymentData,
      $scopeConfig,
      $logger,
      $resource,
      $resourceCollection,
      $data
    );
  
    $this->_countryFactory = $countryFactory;
    $this->_urlBuilder     = $urlBuilder;
    $this->_storeManager   = $storeManager;
  
    $this->_orderFactory = $orderFactory;
    $this->_creditMemoHelper  = $creditMemoHelper;
    $this->_payloadHelper  = $payloadHelper;
    $this->_apiHelper = $apiHelper;
    $this->_logger = $logHelper;

    $this->_config = $config;
    
    $this->_config->setMethodCode($this->getCode());
    $this->_config->setMethodInstance($this);

    $this->_minAmount = 1;
    $this->_maxAmount = 50000;
  }
    

  /**
   *  Sets stores
   *
   * @param \Magento\Store\Model\Store|int $store
   * @return $this
   */
  public function setStore($store)
  {
      $this->setData('store', $store);
      if (null === $store) {
          $store = $this->_storeManager->getStore()->getId();
      }
      return $this;
  }
  
  /**
   * Whether method is available for specified country
   *
   * @param  string $country
   * @return bool
   */
  public function canUseForCountry($country)
  {
    return true;
  }
   
  /**
   * Whether method is available for specified currency
   *
   * @param string $currencyCode
   * @return bool
   */
  public function canUseForCurrency($currencyCode)
  {
    return true;
  }
 
  /**
   * Reurns method code
   *
   * @return string
   */
  public function getMethodCode()
  {
    return $this->_code;
  }

  /**
   * Check whether payment method can be used
   * @param \Magento\Quote\Api\Data\CartInterface|Quote|null $quote
   * @return bool
   */
  public function isAvailable(\Magento\Quote\Api\Data\CartInterface $quote = NULL)
  {   
    return $this->isActive();
  }
  /**
   * Return Order place redirect url
   *
   * @param string $checkout_flow
   * @return string
   */
  public function getRedirectUrl()
  {
    $url = $this->_urlBuilder->getUrl('zipmoneypayment/complete');

   return $url;
  }
  /**
   * Return Order place redirect url
   *
   * @param string $checkout_flow
   * @return string
   */
  public function getCheckoutUrl()
  {
   // $url = $this->_urlBuilder->getUrl('rest/default/V1/zipmoney/standard');
    $url = $this->_urlBuilder->getUrl('zipmoneypayment/standard');

   return $url;
  }

  /**
   * Online refund (from Magento backend)
   *
   * @param \Magento\Payment\Model\InfoInterface $payment
   * @param float $amount
   * @return $this
   */
  public function refund(\Magento\Payment\Model\InfoInterface  $payment, $amount)
  {

    // TODO

    // Mage::getSingleton('core/session')->setTxnflagrefund(1);

    // // set scope to singleton
    // if ($payment && $payment->getOrder()) {
    //     Mage::getSingleton('zipmoneypayment/storeScope')->setStoreId($payment->getOrder()->getStoreId());
    // }
    

    // firstly, set order status to zip_refund_pending
    $orderIncId = $payment->getOrder()->getIncrementId();
    $order  = $this->_orderFactory->create()->loadByIncrementId($orderIncId);
    $this->_logger->debug('Starting refund for the order (' . $orderIncId . ') ');
    
    $order->setStatus('zip_refund_pending')
          ->save();

    $creditmemo = $payment->getCreditmemo();

    if ($creditmemo) {
      $refundRef  = $this->_creditMemoHelper->genRefundReference();
      $creditmemo->setRefundReference($refundRef);
    }

    try{
      $refundApi = $this->_apiHelper->getApi(Config::API_TYPE_REFUND); 
      $data  = [ 
                  'payment' => $payment,
                  'refund_amount' => $amount,
                  'comment' => 'bla bla'
                  ];
      // Prepare request data
      $this->_payloadHelper->prepareDataForRefund($refundApi->request,$data);
      // Log Request
      $this->_logger->debug("Request:- " . json_encode($refundApi->request));

      $response = $refundApi->process();
      // Log Response
      $this->_logger->debug("Response:- " . json_encode($response->toArray()));
      
      if($response->isSuccess()){   
        $responseData = $response->toObject();
        
        $txnId   = $responseData->txn_id;
        $status  = $responseData->status;

        if($txnId && strtolower($status) == "refunded"){
          $this->_logger->info('Refund request for order ' . $orderIncId . ' has been completed successfully.');

          $payment->setTransactionId($txnId);
          $payment->setIsTransactionClosed(false);
          $payment->setStatus(self::STATUS_VOID); 
          return true;
        } else {
          $this->_logger->warn('Could not refund the order ' . $orderIncId );
        }
      } else {
        $this->_logger->warn('Could not refund the order.');
      }
    } catch (\Exception  $e){
      $this->_logger->warn('An error occurred while trying to refund the order.'. $e->getMessage());
    } 

    return $this;
  }
      
  public function getPaymentAcceptanceMarkSrc()
  {
    return \ZipMoney\ZipMoneyPayment\Model\Config::PAYMENT_METHOD_LOGO_ZIP.$this->_config->getProduct().".png";
  }
}