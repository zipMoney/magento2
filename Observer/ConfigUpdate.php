<?php
namespace ZipMoney\ZipMoneyPayment\Observer;

use ZipMoney\ZipMoneyPayment\Model\Config;

class ConfigUpdate implements \Magento\Framework\Event\ObserverInterface {
  

  /**
   * @var \ZipMoney\ZipMoneyPayment\Model\Config
   */
  protected $_config;

  public function __construct(Config $config)
  {
    $this->_config = $config;
  }
  
  public function execute(\Magento\Framework\Event\Observer $observer) 
  {

    $merchantId  = $this->_config->getConfigData(Config::PAYMENT_ZIPMONEY_ID);

    try {
      if ($this->refreshApiKeysHash()) {
        // API Keys has been changed, request latest config data from zipMoney
        $this->_config->requestConfigAndUpdate();
      }
    } catch (Exception $e) {
      $this->_config->saveConfigByMatchedScopes(Config::PAYMENT_ZIPMONEY_PAYMENT_HASH, '', $merchantId);
      $message = __('An error occurred during requesting config data from zipMoney.');
      //$this->_exceptionHandling($e, $vMessage);
    }
  }

  public function refreshApiKeysHash()
  {
    $merchantId  = $this->_config->getMerchantId();
    $merchantKey = $this->_config->getMerchantKey();
    $currentHash = $this->_config->getConfigData(Config::PAYMENT_ZIPMONEY_PAYMENT_HASH);
    $updatedHash = md5(serialize(array($merchantId, $merchantKey)));

    if ($currentHash != $updatedHash) {
      $this->_config->saveConfigByMatchedScopes(Config::PAYMENT_ZIPMONEY_PAYMENT_HASH, $updatedHash, $merchantId);
      return true;
    }

    return false;
  }
}