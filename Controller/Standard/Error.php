<?php
namespace ZipMoney\ZipMoneyPayment\Controller\Standard;


class Error extends AbstractStandard
{   

  public function execute()
  { 
    $this->_logger->info("In errorAction");

    try {           

      $page_object =  $this->_pageFactory->create();   
      $message = __('An error occurred.');
      $this->_logger->info($this->_helper->__($message));            
    } catch (Exception $e) {
      $this->_messageManager->addError($this->_helper->__('An error occurred while redirecting to error page.'));
    }    

    return $page_object;
  }
}



