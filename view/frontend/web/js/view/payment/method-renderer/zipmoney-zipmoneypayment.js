/**
* Zip_ZipPayment JS Component
*
* @category    Zip
* @package     Zip_ZipPayment
* @author      Zip Plugin Team
* @copyright   Zip (https://zip.co)
* @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
*/

/*browser:true*/
/*global define*/

var isLive = (window.checkoutConfig.payment.zipmoneypayment.environment == "production" );
var inContextCheckoutEnabled = window.checkoutConfig.payment.zipmoneypayment.inContextCheckoutEnabled;

define(
    [   'Magento_Checkout/js/view/payment/default',
        'ZipMoney_ZipMoneyPayment/js/action/place-zip-order',        
        'ZipMoney_ZipMoneyPayment/js/action/set-payment-method',
        'Magento_Ui/js/model/messages',
        'ko',        
        'Magento_Checkout/js/model/quote',
        'jquery',        
        'Magento_Checkout/js/model/error-processor',        
        'Magento_Checkout/js/model/full-screen-loader',          
        'Magento_Checkout/js/model/payment/additional-validators',
        'mage/storage',
        'zipMoneyCheckoutJs'
    ],
    function (Component, placeZipOrderAction, setPaymentMethodAction, Messages,ko,quote,$,errorProcessor,fullScreenLoader,additionalValidators,storage) {
        'use strict';

        return Component.extend({              
            isPlaceOrderActionAllowed: ko.observable(quote.billingAddress() != null),             
            redirectAfterPlaceOrder: true, 
            defaults: {
                template: 'ZipMoney_ZipMoneyPayment/payment/zipmoney'
            },
            initChildren: function () {         
                this.messageContainer = new Messages();
                this.createMessagesComponent();
                return this;
            }, 
            continueToZipMoney: function (x,event) {
                var self = this,
                    placeOrder;

                if (event) {
                    event.preventDefault();
                }

                if (this.validate() && additionalValidators.validate()) {

                    this.isPlaceOrderActionAllowed(false);
                    this.selectPaymentMethod();
                    
                    setPaymentMethodAction(this.messageContainer)
                        .success(function(){
                            placeZipOrderAction(self.getData(),self.messageContainer)
                        });
                    return true;
                }
                return false;
            },
            placeOrder: function (x,event) {
                var self = this,
                    placeOrder;

                if (event) {
                    event.preventDefault();
                }

                if (this.validate() && additionalValidators.validate()) {

                    this.isPlaceOrderActionAllowed(false);
                    this.selectPaymentMethod();

                    setPaymentMethodAction(this.messageContainer)
                        .success(function(){
                            placeZipOrderAction(self.getData(),self.messageContainer)
                        });
                    return true;
                }
                return false;
            },
            getAgreements: function (data) {
                var agreementForm = $('div[data-role=checkout-agreements] input');
                if (typeof agreementForm !== 'undefined') {
                    data['extension_attributes'] = {};
                    var agreementData = agreementForm.serializeArray();
                    var agreementIds = [];
                    agreementData.forEach(function (item) {
                        agreementIds.push(item.value);
                    });
                    data['extension_attributes']['agreement_ids'] = agreementIds;
                }
                return data;
            },
            getData: function () {
                var data = {
                    'method': this.getCode(),
                };

                data['additional_data'] = _.extend(data['additional_data'], this.additionalData);
                data = this.getAgreements(data);

                return data;
            },
            getPaymentAcceptanceMarkSrc: function() {
                return window.checkoutConfig.payment.zipmoneypayment.paymentAcceptanceMarkSrc;
            },
            getTitle:function(){
                return window.checkoutConfig.payment.zipmoneypayment.title;
            }, 
            getContinueText:function(){
                return "Continue";
            },
            getCode: function() {
                return window.checkoutConfig.payment.zipmoneypayment.code;
            },
            isActive: function() {
                return true;
            }
        });
    }
);