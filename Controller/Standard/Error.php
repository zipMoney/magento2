<?php
namespace ZipMoney\ZipMoneyPayment\Controller\Standard;

/**
 * @category  Zipmoney
 * @package   Zipmoney_ZipmoneyPayment
 * @author    Sagar Bhandari <sagar.bhandari@zipmoney.com.au>
 * @copyright 2017 zipMoney Payments Pty Ltd.
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 * @link      http://www.zipmoney.com.au/
 */

class Error extends AbstractStandard
{   
  /**
   * Displays the error
   *
   * @return \Magento\Framework\View\Result\PageFactory
   */
  public function execute()
  { 
    $this->_logger->info("In errorAction");

    try {           
      $page_object =  $this->_pageFactory->create();   
      $message = __('An error occurred.');
      $this->_logger->info($this->_helper->__($message));            
    } catch (\Exception $e) {
      $this->_messageManager->addError($this->_helper->__('An error occurred while redirecting to error page.'));
    }    

    return $page_object;
  }
}
