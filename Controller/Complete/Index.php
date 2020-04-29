<?php
namespace ZipMoney\ZipMoneyPayment\Controller\Complete;
       
use Magento\Checkout\Model\Type\Onepage;
use ZipMoney\ZipMoneyPayment\Controller\Standard\AbstractStandard;
use Magento\Framework\Encryption\EncryptorInterface;

/**
 * @category  Zipmoney
 * @package   Zipmoney_ZipmoneyPayment
 * @author    Sagar Bhandari <sagar.bhandari@zipmoney.com.au>
 * @copyright 2017 zipMoney Payments Pty Ltd.
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 * @link      http://www.zipmoney.com.au/
 */

class Index extends AbstractStandard
{   

   /**
   * Valid Application Results
   *
   * @var array
   */  
  protected $_validResults = array('approved','declined','cancelled','referred');

  /**
   * Return from zipMoney and handle the result of the application
   *
   * @throws \Magento\Framework\Exception\LocalizedException
   */
  public function execute() 
  {
    $this->_logger->debug(__("On Complete Controller"));

    try {
      // Is result valid ?
      if(!$this->_isResultValid()){            
        $this->_redirectToCartOrError();
        return;
      }
      $result = $this->getRequest()->getParam('result');

      $this->_logger->debug(__("Result:- %s", $result));
      // Is checkout id valid?
      if(!$this->getRequest()->getParam('checkoutId')){  
        throw new \Magento\Framework\Exception\LocalizedException(__('The checkoutId doesnot exist in the querystring.'));   
      }
      // Set the customer quote
      $this->_setCustomerQuote();
      // Initialise the charge
      $this->_initCharge();
      // Set quote to the chekout model
      $this->_charge->setQuote($this->_getQuote());
    } catch (\Exception $e) {        
      
      $this->_logger->debug($e->getMessage());

      $this->_messageManager->addError(__('Unable to complete the checkout.'));
      $this->_redirectToCartOrError();
      return;
    }  

    $order_status_history_comment = '';

    /* Handle the application result */
    switch ($result) {

      case 'approved':
        /**
         * - Create order
         * - Charge the customer using the checkout id
         */
        try {      
          // Create the Order
          $order = $this->_charge->placeOrder();

          $this->_charge->charge();

          //update order status when successfully paid fix bug all order is pending deal to order and payment are async
          $orderState = \Magento\Sales\Model\Order::STATE_PROCESSING;
          $orderStatus = $order->getConfig()->getStateDefaultStatus($orderState);
          $this->_logger->debug("Order captured setting order state: " . $orderState . " status: " . $orderStatus);
          $order->setState($orderState)->setStatus($orderStatus);
          $order->save();
          $resultRedirect = $this->resultRedirectFactory->create();
          /** @var EncryptorInterface $encryptor */
          $encryptor = $this->_objectManager->get(EncryptorInterface::class);
          $orderId = urlencode($encryptor->encrypt($order->getId()));
           // Redirect to success page with encrypted order id
          $resultRedirect->setPath('checkout/onepage/success',['order_id' => $orderId]);
          return $resultRedirect;
        } catch (\Magento\Framework\Exception\LocalizedException $e) {
          $this->_messageManager->addError($e->getMessage());      
          $this->_logger->debug($e->getMessage());
        } 
        $this->_redirectToCartOrError();
        break;
      case 'declined':
        $this->_logger->debug(__('Calling declinedAction'));
        $this->_redirectToCart();
        break;
      case 'cancelled':  
        $this->_logger->debug(__('Calling cancelledAction'));
        $this->_redirectToCart();
        break;
      case 'referred':
        // Make sure the qoute is active
        $this->_deactivateQuote($this->_getQuote());
        // Dispatch the referred action
        $this->_redirect($this->getReferredUrl());
        break;
      default:       
        // Dispatch the referred action
        $this->_redirectToCartOrError();
        break;
    }
  }
}