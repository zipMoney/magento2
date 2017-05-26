<?php
namespace ZipMoney\ZipMoneyPayment\Model;

use Magento\Payment\Model\Method\ConfigInterface;
use Magento\Store\Model\ScopeInterface;

use \zipMoney\Configuration;

class Config implements ConfigInterface
{
   
  const METHOD_CODE = 'zipmoneypayment';

  const CHECKOUT_FLOW_STANDARD = 'standard';
  const CHECKOUT_FLOW_EXPRESS = 'express';

  const STATUS_MAGENTO_AUTHORIZED = "zip_authorised";
  
  const API_NAMESPACE = "\\zipMoney\\Api\\";    


  const API_TYPE_CANCEL = "Cancel";    
  const API_TYPE_CAPTURE = "Capture";    
  const API_TYPE_CHECKOUT = "Checkout";     
  const API_TYPE_CONFIGURE = "Configure";
  const API_TYPE_QUERY = "Query";
  const API_TYPE_QUOTE = "Quote";
  const API_TYPE_REFUND = "Refund";
  const API_TYPE_SETTINGS = "Settings";

  const PAYMENT_ZIPMONEY_PRIVATE_KEY  = 'merchant_private_key';
  const PAYMENT_ZIPMONEY_PUBLIC_KEY   = 'merchant_public_key';
  const PAYMENT_ZIPMONEY_ENVIRONMENT  = 'environment';
  const PAYMENT_ZIPMONEY_TITLE  = 'title';
  const PAYMENT_ZIPMONEY_PRODUCT  = 'product';
  const PAYMENT_ZIPMONEY_LOG_SETTINGS  = 'log_settings';
  const PAYMENT_ZIPMONEY_PAYMENT_ACTION = 'payment_action';
  const PAYMENT_ZIPMONEY_INCONTEXT_CHECKOUT = 'incontext_checkout';
  const PAYMENT_ZIPMONEY_MINIMUM_TOTAL  = 'min_total';

  const MARKETING_WIDGETS_HOMEPAGE_BANNER_ACTIVE = 'zipmoney_advert/homepage/banner';
  const MARKETING_WIDGETS_PRODUCT_BANNER_ACTIVE = 'zipmoney_advert/productpage/banner';
  const MARKETING_WIDGETS_CART_BANNER_ACTIVE = 'zipmoney_advert/cartpage/banner';
  const MARKETING_WIDGETS_CATEGORY_BANNER_ACTIVE = 'zipmoney_advert/categorypage/banner';
  
  const MARKETING_WIDGETS_PRODUCT_IMAGE_ACTIVE = 'zipmoney_advert/productpage/widget';
  const MARKETING_WIDGETS_CART_IMAGE_ACTIVE = 'zipmoney_advert/cartpage/widget';
  
  const MARKETING_WIDGETS_PRODUCT_TAGLINE_ACTIVE = 'zipmoney_advert/productpage/tagline';
  const MARKETING_WIDGETS_CART_TAGLINE_ACTIVE    = 'zipmoney_advert/cartpage/tagline';

  const PAYMENT_METHOD_LOGO_ZIP = "http://d3k1w8lx8mqizo.cloudfront.net/logo/25px/";

  protected $_error_codes_map = array("account_insufficient_funds" => "MG2-0001",
                                 "account_inoperative" => "MG2-0002",
                                 "account_locked" => "MG3-0003",
                                 "amount_invalid" => "MG4-0004",
                                 "fraud_check" => "MG5-0005");
  /**
   * @var string
   */
  protected $_methodCode;
  
  /**
   * @var int
   */
  protected $_storeId;

  protected $_methodInstance;

  /**
   * @var \Magento\Framework\App\Config\ScopeConfigInterface
   */
  protected $_scopeConfig;

  /**
   * @var \\Magento\Store\Model\StoreManagerInterface
   */
  protected $_storeManager;
  
  /**
   * @var \Magento\Config\Model\ResourceModel\Config
   */
  protected $_resourceConfig;
  
  /**
   * @var \ZipMoney\ZipMoneyPayment\Helper\Logger
   */
  protected $_logger;


  /**
   * @var \ZipMoney\ZipMoneyPayment\Model\StoreScope
   */
  protected $_storeScope;

  /**
   * @var \Magento\Framework\App\Cache\TypeListInterface
   */
  protected $_cacheTypeList;

  /**
   * @var \Magento\Framework\Message\ManagerInterface
   */
  protected $_messageManager;


  public function __construct(
      \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
      \Magento\Store\Model\StoreManagerInterface $storeManager,   
      \Magento\Framework\App\Cache\TypeListInterface $cacheTypeList,
      \Magento\Config\Model\ResourceModel\Config $resourceConfig,        
      \Magento\Framework\Message\ManagerInterface $messageManager,      
      \Magento\Framework\UrlInterface $urlBuilder,
      \ZipMoney\ZipMoneyPayment\Model\StoreScope $storeScope,   
      \ZipMoney\ZipMoneyPayment\Helper\Logger $logger
  ) {
    $this->_scopeConfig  = $scopeConfig;
    $this->_storeManager = $storeManager;
    $this->_logger = $logger;
    $this->_storeScope = $storeScope;
    $this->_resourceConfig = $resourceConfig;
    $this->_cacheTypeList = $cacheTypeList;
    $this->_messageManager = $messageManager;    
    $this->_urlBuilder     = $urlBuilder;


    $this->setStoreId($this->_storeManager->getStore()->getId());      
  }
  

  /**
   * Whether method is active or not
   * 
   * @param $method
   * @param int $storeId
   * @return bool
   */
  public function isMethodActive($method, $storeId = null)
  {   

    if (!isset($storeId)) {
      $storeId = $this->_storeId;
    } 

    $isEnabled = false;

    $isEnabled = $this->_scopeConfig->isSetFlag(
          'payment/' . self::METHOD_CODE .'/active',
          ScopeInterface::SCOPE_STORE,
          $storeId
    );

    return  $isEnabled;
  }

  /**
   * Returns Merchant Id
   * 
   * @return int
   */
  public function getTitle()
  {
    return $this->getConfigData(self::PAYMENT_ZIPMONEY_TITLE);
  }

  /**
   * Returns Merchant Id
   * 
   * @return int
   */
  public function getMerchantPrivateKey()
  {
    return $this->getConfigData(self::PAYMENT_ZIPMONEY_PRIVATE_KEY);
  }
  
  /**
   * Returns Merchant key
   * 
   * @return string
   */
  public function getMerchantPublicKey()
  {
    return $this->getConfigData(self::PAYMENT_ZIPMONEY_PUBLIC_KEY);

  }
  

  /**
   * Returns Api Environment
   * 
   * @return string
   */
  public function getEnvironment()
  {
    return $this->getConfigData(self::PAYMENT_ZIPMONEY_ENVIRONMENT);
  }

  /**
   * Check if in-context checkout is active
   * @return bool
   */
  public function getMethodLogo()
  {   
    return  self::PAYMENT_METHOD_LOGO_ZIP.strtolower($this->getProduct()).".png";
  }
  /**
   * Returns Product
   * 
   * @return string
   */
  public function getProduct()
  {
    return $this->getConfigData(self::PAYMENT_ZIPMONEY_PRODUCT);
  }

  /**
   * Check if in-context checkout is active
   * @return bool
   */
  public function isInContextCheckout()
  {   
    return $this->getConfigData(self::PAYMENT_ZIPMONEY_INCONTEXT_CHECKOUT);
  }
 
  /**
   * Check if in-context checkout is active
   * @return bool
   */
  public function getOrderTotalMinimum()
  {   
    return (float)$this->getConfigData(self::PAYMENT_ZIPMONEY_MINIMUM_TOTAL);
  }
    
  /**
   * Is Capture
   * 
   * @return bool
   */
  public function isCharge()
  {
    return trim($this->getConfigData(self::PAYMENT_ZIPMONEY_PAYMENT_ACTION)) === "capture";
  }

  
  /**
   * Is Capture
   * 
   * @return bool
   */
  public function getLogSetting($storeId = null)
  {
    return $this->getConfigData(self::PAYMENT_ZIPMONEY_LOG_SETTINGS,$storeId);
  }


  public function getMappedErrorCode($errorCode)
  {
    if(!in_array($errorCode, array_keys($this->_error_codes_map)))
    {
      return false;
    }

    return $this->_error_codes_map[$errorCode];
  }
  /**
   * Retrieve information from payment configuration
   *
   * @param string $field
   * @param int|string|null|\Magento\Store\Model\Store $storeId
   *
   * @return mixed
   */
  public function getConfigData($field, $storeId = null)
  {

    if ('order_place_redirect_url' === $field) {
      return $this->getOrderPlaceRedirectUrl();
    } 

    if (!$storeId) {
      $storeId = $this->_storeId;
    }
      
    return $this->getValue($field, $storeId);
  }


  /**
   * Returns payment configuration value
   *
   * @param string $key
   * @param null $storeId
   * @return null|string
   */
  public function getValue($key, $storeId = null)
  {
    if (!$storeId) {
      $storeId = $this->_storeId;
    }

    $underscored = strtolower(preg_replace('/(.)([A-Z])/', "$1_$2", $key));

    $path = "payment/".self::METHOD_CODE."/".$underscored;
    
    $value = $this->_scopeConfig->getValue($path, 
      ScopeInterface::SCOPE_STORE, 
      $storeId
    );

    return $value;
  }

  /**
   * Sets method instance used for retrieving method specific data
   *
   * @param MethodInterface $method
   * @return $this
   */
  public function setMethodInstance($methodInstance)
  {
    $this->_methodInstance = $methodInstance;
    return $this;
  }

  /**
   * Sets method code
   *
   * @param string $methodCode
   * @return void
   */
  public function setMethodCode($methodCode)
  {
    $this->_methodCode = $methodCode;
  }
    
  /**
   * Sets method code
   *
   * @param string $methodCode
   * @return void
   */
  public function getMethodCode()
  {
    if(isset($this->_methodCode))
      return $this->_methodCode;
    else
      return false;
  }

  /**
   * Sets path pattern
   *
   * @param string $pathPattern
   * @return void
   */
  public function setPathPattern($pathPattern)
  {
    $this->pathPattern = $pathPattern;
  }

  /**
   * Store ID setter
   *
   * @param int $storeId
   * @return $this
   */
  public function setStoreId($storeId)
  {
      $this->_storeId = (int)$storeId;
      return $this;
  }

  /**
   *  Save config
   * 
   * @param $vPath
   * @param $value
   * @param null $iMerchantId
   * @return $this
   */
  public function saveConfigByMatchedScopes($path, $value, $merchantId = null)
  {
    if ($merchantId === null) {
      $merchantId = $this->getMerchantId();
    }

    $matched = $this->_storeScope->getMatchedScopes($merchantId);

    if (!is_array($matched)) {
      return $this;
    }

    $path = $this->_getCommonConfigPath($path);

    /**
     * save the config to each matched scope
     */
    foreach ($matched as $scopeArr) {
      if (!is_array($scopeArr)) {
        continue;
      }
      $scope = isset($scopeArr['scope']) ? $scopeArr['scope'] : 'default';
      $scopeId = isset($scopeArr['scope_id']) ? $scopeArr['scope_id'] : 0;
      $this->_resourceConfig->saveConfig($path, $value, $scope, $scopeId);
    }
    
    return $this;
  }

  public function getPaymentAcceptanceMarkSrc()
  {
    return self::PAYMENT_METHOD_LOGO_ZIP.$this->getProduct().".png";
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
   * Whether method is active or not
   * 
   * @param $method
   * @param int $storeId
   * @return bool
   */
  public function getStoreConfig($path, $storeId = null)
  {   

    if (!isset($storeId)) {
      $storeId = $this->_storeId;
    } 

    $value = $this->_scopeConfig->getValue(
          $path,
          ScopeInterface::SCOPE_STORE,
          $storeId
    );

    return  $value;
  }

}