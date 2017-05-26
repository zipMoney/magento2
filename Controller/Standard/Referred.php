<?php
namespace ZipMoney\ZipMoneyPayment\Controller\Standard;


use Magento\Checkout\Model\Type\Onepage;

class Referred extends AbstractStandard
{   

  public function execute()
  {
    $this->_logger->info("In referredAction");

    try {           
      $page_object =  $this->_pageFactory->create();   
    } catch (\Exception $e) {           
      $this->_logger->error($e->getMessage());
      $this->_messageManager->addError($e, $this->_helper->__('An error occurred during redirecting to referred page'));
    }

   return $page_object;
  }
}
