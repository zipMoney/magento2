<?php
namespace ZipMoney\ZipMoneyPayment\Model\Config\Source;
use \ZipMoney\ZipMoneyPayment\Logger\Logger as ZipMoneyLogger;
use Magento\Sales\Model\Order as Order;

/**
 * @category  Zipmoney
 * @package   Zipmoney_ZipmoneyPayment
 * @author    Sagar Bhandari <sagar.bhandari@zipmoney.com.au> Roger Bi <Roger.bi@zipmoney.com.au>
 * @copyright 2017 zipMoney Payments Pty Ltd.
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 * @link      http://www.zipmoney.com.au/
 */

/**
 * Used in creating options for sandbox|production config value selection
 *
 */
class OrderStatusSettings implements \Magento\Framework\Option\ArrayInterface
{

    /**
     * Options getter
     *
     * @return array
     */
    public function toOptionArray()
    {

        return [['value' => Order::STATE_NEW, 'label' => __('New')],
            ['value' => Order::STATE_PENDING_PAYMENT, 'label' => __('Pending_payment')],
            ['value' => Order::STATE_PROCESSING, 'label' => __('Processing')],
            ['value' => Order::STATE_COMPLETE, 'label' => __('Complete')],
            ['value' => Order::STATE_CANCELED, 'label' => __('Canceled')],
            ['value' => Order::STATE_HOLDED, 'label' => __('Holded')],
            ['value' => Order::STATE_PAYMENT_REVIEW, 'label' => __('Payment_review')]];

    }
}