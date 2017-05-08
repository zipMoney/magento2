<?php
namespace ZipMoney\ZipMoneyPayment\Helper;


class Api extends \Magento\Framework\App\Helper\AbstractHelper
{ 

  /**
   * Api Instances
   *
   * @var array
   */
  protected $_api = [];
  
  /**
   * @var \ZipMoney\ZipMoneyPayment\Model\Api\Factory
   */
  protected $_apiFactory;
  
  /**
   * @var \ZipMoney\ZipMoneyPayment\Model\Config\Proxy
   */
  protected $_config; 

  
  public function __construct(        
      \Magento\Framework\App\Helper\Context $context,        
      \ZipMoney\ZipMoneyPayment\Model\Api\Factory $apiFactory,
      \ZipMoney\ZipMoneyPayment\Model\Config\Proxy $config,
      \ZipMoney\ZipMoneyPayment\Helper\Logger $logger      
  ) {
      $this->_config = $config;
      $this->_apiFactory = $apiFactory;  
      $this->_logger = $logger;  

      parent::__construct($context);
  }

  /**
   * Returns the api from the  array of api cache
   *
   * @return Object
   */
  public function getApi($type)
  {
    if(!$type)
        throw new \Magento\Framework\Exception\LocalizedException( __('Api type must be provided.'));

    $typeInstance = strtolower($type);

    if (!isset($this->_api[$typeInstance])) {
        $this->_api[$typeInstance] = $this->_apiFactory->create($type);
    }

    return $this->_api[$typeInstance];
  }

  /**
   * Returns the merchant public key
   *
   * @return Object
   */
  public function getMerchantPublicKey()
  {
    return $this->_config->getConfigData(\ZipMoney\ZipMoneyPayment\Model\Config::PAYMENT_ZIPMONEY_PUBLIC_KEY);
  }
 
  /**
   * Returns the api environment
   *
   * @return Object
   */
  public function getEnvironment()
  {
    return $this->_config->getConfigData(\ZipMoney\ZipMoneyPayment\Model\Config::PAYMENT_ZIPMONEY_ENVIRONMENT);
  }
}