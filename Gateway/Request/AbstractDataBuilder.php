<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace ZipMoney\ZipMoneyPayment\Gateway\Request;

use Magento\Payment\Gateway\ConfigInterface;
use Magento\Payment\Gateway\Request\BuilderInterface;

abstract class AbstractDataBuilder implements BuilderInterface
{
    /**
     * @var ConfigInterface
     */
    protected $_payloadHelper;
    /**
     * @var ConfigInterface
     */
    protected $_helper;
    /**
     * @var OrderRepository
     */
    protected $_order;

    /**
     * @param ConfigInterface $config
     */
    public function __construct(        
        \Magento\Sales\Model\Order $order,
        \ZipMoney\ZipMoneyPayment\Helper\Payload $payloadHelper,
        \ZipMoney\ZipMoneyPayment\Helper\Data $helper,
        \ZipMoney\ZipMoneyPayment\Helper\Logger $logger
    ) {
        $this->_payloadHelper = $payloadHelper;
        $this->_helper = $helper;
        $this->_logger = $logger;
        $this->_order = $order;
    }
}
