<?php
namespace ZipMoney\ZipMoneyPayment\Controller\Standard;


class Error extends \Magento\Framework\App\Action\Action
{   
  protected $pageFactory;

  public function __construct(\Magento\Framework\App\Action\Context $context,\ZipMoney\ZipMoneyPayment\Helper\Logger $logger, \Magento\Framework\View\Result\PageFactory $pageFactory)
  {
      $this->pageFactory = $pageFactory;    
      $this->_logger = $logger;
      return parent::__construct($context);
  }

  public function execute()
  {
    $page_object =  $this->pageFactory->create();   
    $this->_logger->info("In /error");

    $message = __('An error occurred.');
    $this->_logger->warn(__($message));
    return $page_object;
  }
}



