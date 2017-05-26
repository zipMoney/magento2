<?php
namespace ZipMoney\ZipMoneyPayment\Block\Advert;

class RootEl extends \Magento\Framework\View\Element\Template 
{

  /**
   * @var boolean
   */
  protected $_render = false; 

  /**
   * @var \ZipMoney\ZipMoneyPayment\Model\Config
   */
  protected $_config; 
  
  /**
   * @var \ZipMoney\ZipMoneyPayment\Helper\Logger
   */
  protected $_logger; 

  public function __construct(
    \Magento\Framework\View\Element\Template\Context $context,       
    \ZipMoney\ZipMoneyPayment\Model\Config $config,
    \ZipMoney\ZipMoneyPayment\Helper\Logger $logger,
    $template,
    array $data = []
  ) {
    $this->_config = $config;
    $this->_loggger = $logger;
    
    $this->setTemplate("ZipMoney_ZipMoneyPayment::".$template);

    parent::__construct($context, $data);
  }


  public function getMerchantPublicKey()
  {
    return $this->_config->getMerchantPublicKey();
  }


  public function getEnvironment()
  {
    return $this->_config->getEnvironment();
  }
}
