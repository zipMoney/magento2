<?php 
namespace ZipMoney\ZipMoneyPayment\Model\Ui;

use Magento\Checkout\Model\ConfigProviderInterface;
use Magento\Framework\Locale\ResolverInterface;
use Magento\Customer\Helper\Session\CurrentCustomer;
use Magento\Payment\Helper\Data as PaymentHelper;
use ZipMoney\ZipMoneyPayment\Model\Config;

/**
 * @category  Zipmoney
 * @package   Zipmoney_ZipmoneyPayment
 * @author    Sagar Bhandari <sagar.bhandari@zipmoney.com.au>
 * @copyright 2017 zipMoney Payments Pty Ltd.
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 * @link      http://www.zipmoney.com.au/
 */

class ConfigProvider implements \Magento\Checkout\Model\ConfigProviderInterface
{
  /**
   * @const string
   */
  const CODE = 'zipmoneypayment';

  /**
   * @var ResolverInterface
   */
  protected $localeResolver;

  /**
   * @var Config
   */
  protected $_config;

  /**
   * @var \Magento\Customer\Helper\Session\CurrentCustomer
   */
  protected $currentCustomer;

  /**
   * @var \Magento\Payment\Model\Method\AbstractMethod[]
   */
  protected $methods = [];

  /**
   * @var PaymentHelper
   */
  protected $paymentHelper;
  
  /**
   *
   * @var \ZipMoney\ZipMoneyPayment\Helper\Logger
   */
  protected $_logger;

  /**
   * @param ResolverInterface $localeResolver
   * @param CurrentCustomer $currentCustomer
   * @param PaymentHelper $paymentHelper
   */
  public function __construct(
      ResolverInterface $localeResolver,
      CurrentCustomer $currentCustomer,
      PaymentHelper $paymentHelper,
      Config $config,
      \ZipMoney\ZipMoneyPayment\Helper\Logger $logger

  ) {
    $this->localeResolver = $localeResolver;
    $this->currentCustomer = $currentCustomer;
    $this->paymentHelper = $paymentHelper;
    $this->_config = $config;
    $this->_logger = $logger;
  }

  /**
   * Prepares the Js Config
   * 
   * @return array
   */
  public function getConfig()
  {
    $config = [];  
    $paymentAcceptanceMarkSrc = $this->_config->getPaymentAcceptanceMarkSrc(self::CODE);
    $config['payment'][self::CODE] = [
                                   "code"  => self::CODE,
                                   "paymentAcceptanceMarkSrc" => $paymentAcceptanceMarkSrc,
                                   "checkoutUri"  => $this->_config->getCheckoutUrl(), 
                                   "redirectUri"  => $this->_config->getRedirectUrl(), 
                                   "environment"  => $this->_config->getEnvironment(),
                                   "product"  => $this->_config->getProduct(),
                                   "title"  => $this->_config->getTitle(),
                                   "inContextCheckoutEnabled"  => (bool)$this->_config->isInContextCheckout()
                                  ];
    $this->_logger->debug(json_encode($config));
    return $config;
  }
}