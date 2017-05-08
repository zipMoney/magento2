/**
* ZipMoney_ZipMoneyPayment JS Component
*
* @category    ZipMoney
* @package     ZipMoney_ZipMoneyPayment
* @author      Sagar Bhandari
* @copyright   ZipMoney (http://zipmoney.com.au)
* @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
*/

/*browser:true*/
/*global define*/
define(
    [
        'uiComponent',
        'Magento_Checkout/js/model/payment/renderer-list'
    ],
    function ( Component, rendererList ) {
        'use strict';
        rendererList.push(
            {
                type: 'zipmoneypayment',
                component: 'ZipMoney_ZipMoneyPayment/js/view/payment/method-renderer/zipmoney-zipmoneypayment'
            }
        ); 
        return Component.extend({});
    }
);