<?php
namespace ZipMoney\ZipMoneyPayment\Controller\Complete;
       
use Magento\Checkout\Model\Type\Onepage;
use ZipMoney\ZipMoneyPayment\Controller\Standard\AbstractStandard;

class Index extends AbstractStandard
{   

   /**
   * Valid Application Results
   *
   * @var array
   */  
  protected $_validResults = array('approved','declined','cancelled','referred');

  /**
   * Charges Api Class
   *
   * @var string
   */  
  protected $_apiClass  = '\zipMoney\Client\Api\ChargesApi';
  
  /**
   * Charge Model
   *
   * @var string
   */
  protected $_chargeModel = 'zipmoneypayment/charge';
  
  /**
   * Return from zipMoney and handle the result of the application
   *
   * @throws Mage_Core_Exception
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
          // Redirect to success page
          return $this->getResponse()->setRedirect($this->getSuccessUrl());
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
