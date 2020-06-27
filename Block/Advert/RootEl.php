<?php
namespace ZipMoney\ZipMoneyPayment\Block\Advert;

use Magento\Store\Model\ScopeInterface;
use Magento\Framework\App\Config\ScopeConfigInterface;

/**
 * @category  Zipmoney
 * @package   Zipmoney_ZipmoneyPayment
 * @author    Sagar Bhandari <sagar.bhandari@zipmoney.com.au>
 * @copyright 2017 zipMoney Payments Pty Ltd.
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 * @link      http://www.zipmoney.com.au/
 */

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

    /**
     * Get country path
     */
    const COUNTRY_CODE_PATH = 'general/country/default';

    /**
     * @var ScopeConfigInterface
     */
    protected $_scopeConfig;

  public function __construct(
    \Magento\Framework\View\Element\Template\Context $context,       
    \ZipMoney\ZipMoneyPayment\Model\Config $config,
    \ZipMoney\ZipMoneyPayment\Helper\Logger $logger,
    ScopeConfigInterface $scopeConfig,
    $template,
    array $data = []
  ) {
    $this->_config = $config;
    $this->_loggger = $logger;
    $this->_scopeConfig = $scopeConfig;
    $this->setTemplate("ZipMoney_ZipMoneyPayment::".$template);

    parent::__construct($context, $data);
  }

  /**
   * Get merchant public key
   *
   * @return string
   */
  public function getMerchantPublicKey()
  {
    return $this->_config->getMerchantPublicKey();
  }

  /**
   * Get API environment sandbox|live
   *
   * @return string
   */
  public function getEnvironment()
  {
    return $this->_config->getEnvironment();
  }

    /** Get Country code by website scope
    *
    * @return string
    */
    public function getCountryByWebsite(): string
    {
        return $this->_scopeConfig->getValue(
            self::COUNTRY_CODE_PATH,
            ScopeInterface::SCOPE_WEBSITES
        );
    }
}
