<?php
namespace ZipMoney\ZipMoneyPayment\Model\Checkout;

/**
 * Factory class for \ZipMoney\ZipMoneyPayment\Model\Checkout;
 */
class Factory
{
    /**
     * Object Manager instance
     *
     * @var \Magento\Framework\ObjectManagerInterface
     */
    protected $_objectManager = null;

    /**
     * Factory constructor
     *
     * @param \Magento\Framework\ObjectManagerInterface $objectManager
     */
    public function __construct(\Magento\Framework\ObjectManagerInterface $objectManager)
    {
        $this->_objectManager = $objectManager;
    }

    /**
     * Create class instance with specified parameters
     *
     * @param string $className
     * @param array $data
     * @return ZipMoney\ZipMoneyPayment\Model\Standard;
     */
    public function create($className, array $data = [])
    {
        return $this->_objectManager->create($className, $data);
    }
}
