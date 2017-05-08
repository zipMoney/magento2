<?php
namespace ZipMoney\ZipMoneyPayment\Model\Api;


class Factory
{
    /**
     * Object Manager instance
     *
     * @var \Magento\Framework\ObjectManagerInterface
     */
    protected $_objectManager = null;

    protected $_config = null;

    /**
     * Factory constructor
     *
     * @param \Magento\Framework\ObjectManagerInterface $objectManager
     */
    public function __construct(
        \Magento\Framework\ObjectManagerInterface $objectManager,        
        \ZipMoney\ZipMoneyPayment\Model\Config\Proxy $config        
    )
    {
        $this->_objectManager = $objectManager;
        $this->_config = $config;
    }

    /**
     * Create class instance with specified parameters
     *
     * @param string $className
     * @param array $data
     * @return ZipMoney\ZipMoneyPayment\Model\Standard;
     */
    public function create($apiType, array $data = [])
    {
        $apiClassName = \ZipMoney\ZipMoneyPayment\Model\Config::API_NAMESPACE . $apiType;
        
        if(!class_exists($apiClassName)){
            throw new \Magento\Framework\Exception\LocalizedException(__(sprintf('The api class (%s) doesnot exist.',$apiClassName)));
        }

        \zipMoney\Configuration::$merchant_id  = $this->_config->getMerchantId();
        \zipMoney\Configuration::$merchant_key = $this->_config->getMerchantKey();
        \zipMoney\Configuration::$environment  = $this->_config->getEnvironment();
          
        return $this->_objectManager->create($apiClassName, $data);
    }

}
