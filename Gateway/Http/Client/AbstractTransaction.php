<?php
namespace ZipMoney\ZipMoneyPayment\Gateway\Http\Client;

use Magento\Payment\Gateway\Http\ClientInterface;
use \zipMoney\Configuration;

class AbstractTransaction
{
    
    public function __construct(
        \Magento\Framework\Model\Context $context,
        \Magento\Framework\Encryption\EncryptorInterface $encryptor,
        \ZipMoney\ZipMoneyPayment\Helper\Payload $payloadHelper,
        \ZipMoney\ZipMoneyPayment\Helper\Logger $logger,   
        \ZipMoney\ZipMoneyPayment\Helper\Data $helper,
        \ZipMoney\ZipMoneyPayment\Model\Config $config,
        array $data = []
    ) {
        $this->_encryptor = $encryptor;
        $this->_payloadHelper = $payloadHelper;
        $this->_logger = $logger;
        $this->_helper = $helper;
        $this->_config = $config;
    
        // Configure API Credentials
        $apiConfig = Configuration::getDefaultConfiguration();
    
        $apiConfig->setApiKey('Authorization', $this->_config->getMerchantPrivateKey())
              ->setApiKeyPrefix('Authorization', 'Bearer')
              ->setEnvironment($this->_config->getEnvironment())
              ->setPlatform("Magento/".$this->_helper->getMagentoVersion()."ZipMoney_ZipMoneyPayment/".$this->_helper->getExtensionVersion());
    }
}