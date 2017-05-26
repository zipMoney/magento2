<?php
namespace ZipMoney\ZipMoneyPayment\Helper;

abstract class  AbstractHelper extends \Magento\Framework\App\Helper\AbstractHelper 
{

  /**
   * @var \ZipMoney\ZipMoneyPayment\Helper\Logger
   */
  protected $_logger;
  
  /**
   * @var \ZipMoney\ZipMoneyPayment\Model\Config\Proxy
   */
  protected $_config; 
  /**
   * @var \Magento\Framework\UrlInterface
   */
  protected $_urlBuilder;
  
  /**
   * Set quote and config instances
   */
  public function __construct(
      \Magento\Framework\App\Helper\Context $context,            
      \Magento\Framework\UrlInterface $urlBuilder,
      \ZipMoney\ZipMoneyPayment\Model\Config\Proxy $config,
      \ZipMoney\ZipMoneyPayment\Helper\Logger $logger      )
  {   
    $this->_logger = $config;
    $this->_config = $logger;
    $this->_urlBuilder = $urlBuilder;
    parent::__construct($context);
  }


}

