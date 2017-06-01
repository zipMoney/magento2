<?php
namespace ZipMoney\ZipMoneyPayment\Model\Config\Source;
/**
 * @category  Zipmoney
 * @package   Zipmoney_ZipmoneyPayment
 * @author    Sagar Bhandari <sagar.bhandari@zipmoney.com.au>
 * @copyright 2017 zipMoney Payments Pty Ltd.
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 * @link      http://www.zipmoney.com.au/
 */


class PaymentAction implements \Magento\Framework\Option\ArrayInterface 
{
  /**
   * Options getter
   *
   * @return array
   */
  public function toOptionArray() {
  
    return [['value' => 'authorise', 'label' => __('Authorise')], ['value' => 'authorize_capture', 'label' => __('Authorise & Capture')]];
  }

}
