<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace ZipMoney\ZipMoneyPayment\Gateway\Request;

use Magento\Payment\Gateway\ConfigInterface;
use Magento\Payment\Gateway\Request\BuilderInterface;

/**
 * @category  Zipmoney
 * @package   Zipmoney_ZipmoneyPayment
 * @author    Sagar Bhandari <sagar.bhandari@zipmoney.com.au>
 * @copyright 2017 zipMoney Payments Pty Ltd.
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 * @link      http://www.zipmoney.com.au/
 */

abstract class AbstractDataBuilder implements BuilderInterface
{
    /**
     * @var \ZipMoney\ZipMoneyPayment\Helper\Payload 
     */
    protected $_payloadHelper;
    /**
     * @var \ZipMoney\ZipMoneyPayment\Helper\Data
     */
    protected $_helper;
    /**
     * @var \Magento\Sales\Model\Order
     */
    protected $_order;

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
