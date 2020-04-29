<?php
	/**
	 *
	 * This will help us to resolve the issue success page redirect to empty cart page.
	 *
	 */

	namespace ZipMoney\ZipMoneyPayment\Plugin\Controller\Onepage;
    use Magento\Framework\Encryption\EncryptorInterface;
	/**
	 * Class Success
	 * @package ZipMoney\ZipMoneyPayment\Plugin\Controller\Onepage
	 */
	class Success
	{
	    /**
	     * @var \Magento\Framework\Registry
	     */
	    protected $_coreRegistry;
	    /**
	     * @var \Magento\Checkout\Model\Session
	     */
	    protected $_checkoutSession;
        /** @var \Magento\Sales\Model\OrderFactory **/
        protected $_orderFactory;
	    /**
	     * Success constructor.
	     * @param \Magento\Framework\Registry $coreRegistry
	     * @param \Magento\Checkout\Model\Session $checkoutSession
         * @param \Magento\Framework\Encryption\EncryptorInterface $encryptor
	     */
	    public function __construct(
		\Magento\Framework\Registry $coreRegistry,
        \Magento\Checkout\Model\Session $checkoutSession,
        \Magento\Sales\Model\OrderFactory $orderFactory,
        \Magento\Framework\Encryption\EncryptorInterface $encryptor
	    ) {
		$this->_coreRegistry = $coreRegistry;
        $this->_checkoutSession = $checkoutSession;
        $this->_orderFactory = $orderFactory;
        $this->encryptor     = $encryptor;
	    }

	    /**
	     * @param \Magento\Checkout\Controller\Onepage\Success $subject
	     */
	    public function beforeExecute(\Magento\Checkout\Controller\Onepage\Success $subject)
	    {
            $order_Id = $subject->getRequest()->getParam('order_id', false);         
            $orderId = $this->encryptor->decrypt(urldecode($order_Id));
            if ($orderId){        
                $order = $this->_orderFactory->create()->load($orderId);
                $this->_checkoutSession->setLastQuoteId($order->getQuoteId());
                $this->_checkoutSession->setLastSuccessQuoteId($order->getQuoteId());
                $this->_checkoutSession->setLastOrderId($order->getId());
                $this->_checkoutSession->setLastRealOrderId($order->getIncrementId());
                $this->_checkoutSession->setLastOrderStatus($order->getStatus());
            }
        }
	}