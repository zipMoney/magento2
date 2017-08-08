<?php
namespace  ZipMoney\ZipMoneyPayment\Block;

use Magento\Framework\View\Element\Template;

/**
 * @category  Zipmoney
 * @package   Zipmoney_ZipmoneyPayment
 * @author    Sagar Bhandari <sagar.bhandari@zipmoney.com.au>
 * @copyright 2017 zipMoney Payments Pty Ltd.
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 * @link      http://www.zipmoney.com.au/
 */

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

  /**
   * Prepares the layout.
   *
   * @return \Magento\Framework\View\Element\AbstractBlock
   */
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
   * Returns the error body text.
   *
   * @return string
   */
  public function getBodyText()
  {    
    $text = null;
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
    $text = null;
    if(!$this->_messageManager->hasMessages()){
      try {
        print_r($this->getRequest());
        $code = (int)$this->getRequest()->getParam('code');
      } catch (Exception $e) {
        $code = 0;
      }
      switch($code)
      {
        case 0:
          $text =  __('General Error');
          break;
        case 400:
          $text =  __('400 Bad Request');
          break;
        case 401:
          $text =  __('401 Unauthorized');
          break;
        case 403:
          $text =  __('403 Forbidden');
          break;
        case 404:
          $text =  __('404 Not Found');
          break;
        case 409:
          $text =  __('409 Conflict');
          break;
        default:
          $text = $this->getRequest()->getParam('code') . __(' General Error');
          break;
      }
    }
    return $text;
  }
}
