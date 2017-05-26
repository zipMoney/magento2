<?php
namespace  ZipMoney\ZipMoneyPayment\Block;

use Magento\Framework\View\Element\Template;
 
class Error extends Template
{  

  /**
   * @const string
   */
  const ERROR_BODY = 'payment/zipmoneypayment/zipmoney_messages/error_body';
  /**
   * @const string
   */
  const ERROR_HEADER = 'payment/zipmoneypayment/zipmoney_messages/error_header';

  protected $_messageManager;
  protected $_config;

  public function __construct(
      Template\Context $context,
      \Magento\Framework\Message\ManagerInterface $messageManager,    
      \ZipMoney\ZipMoneyPayment\Model\Config $config,
      array $data = [])
  {
      $this->_messageManager = $messageManager;
      $this->_config = $config;
      parent::__construct($context, $data);
  }

  protected function _prepareLayout()
  {
    $text = $this->_config->getStoreConfig(self::ERROR_HEADER);
    
    if(!$text){
      $text = "An error occurred";
    }
   
   $this->pageConfig->getTitle()->set(__($text));

   return parent::_prepareLayout();

  }
  /**
   * Returns the error heading text.
   *
   * @return string
   */
  public function getBodyText()
  {    
    $text = "";
    if(!$this->_messageManager->hasMessages()){

      $text = $this->_config->getStoreConfig(self::ERROR_BODY);
      if (!$text) {
        $text = __('There was an error processing your request. Please try again later.');
      }
    }      
    return $text;
  }

  /**
   * Returns the error type text.
   *
   * @return string
   */
  public function getErrorTypeText()
  {  
    $vText = "";
    if(!$this->_messageManager->hasMessages()){
      try {
        $iCode = (int)$this->getRequest()->getParam('code');
      } catch (Exception $e) {
        $iCode = 0;
      }
      switch($iCode)
      {
        case 0:
          $vText =  __('General Error');
          break;
        case 400:
          $vText =  __('400 Bad Request');
          break;
        case 401:
          $vText =  __('401 Unauthorized');
          break;
        case 403:
          $vText =  __('403 Forbidden');
          break;
        case 404:
          $vText =  __('404 Not Found');
          break;
        case 409:
          $vText =  __('409 Conflict');
          break;
        default:
          $vText = $this->getRequest()->getParam('code') . __(' General Error');
          break;
      }
    }
    return $vText;
  }
}
