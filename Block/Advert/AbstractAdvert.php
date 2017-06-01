<?php
namespace ZipMoney\ZipMoneyPayment\Block\Advert;

use Magento\Catalog\Block as CatalogBlock;
use Magento\Paypal\Helper\Shortcut\ValidatorInterface;
use \ZipMoney\ZipMoneyPayment\Model\Config;

/**
 * @category  Zipmoney
 * @package   Zipmoney_ZipmoneyPayment
 * @author    Sagar Bhandari <sagar.bhandari@zipmoney.com.au>
 * @copyright 2017 zipMoney Payments Pty Ltd.
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 * @link      http://www.zipmoney.com.au/
 */

abstract class AbstractAdvert extends \Magento\Framework\View\Element\Template 
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
   * @var \Magento\Framework\Registry
   */
  protected $_registry; 

  /**
   * @var string
   */
  protected $_alias = '';

  /**
   * @var array
   */
  protected $_configConstants = [ 'widget' => [
                                              'product' => Config::ADVERTS_PRODUCT_IMAGE_ACTIVE, 
                                              'cart'    => Config::ADVERTS_CART_IMAGE_ACTIVE 
                                             ],
                                  'tagline' => [
                                              'product' => Config::ADVERTS_PRODUCT_TAGLINE_ACTIVE, 
                                              'cart'    => Config::ADVERTS_CART_TAGLINE_ACTIVE 
                                            ],
                                  'banner' =>[
                                             'product' => Config::ADVERTS_PRODUCT_BANNER_ACTIVE, 
                                             'cart'    => Config::ADVERTS_CART_BANNER_ACTIVE,
                                             'home'    => Config::ADVERTS_HOMEPAGE_BANNER_ACTIVE, 
                                             'category'=> Config::ADVERTS_CATEGORY_BANNER_ACTIVE 
                                            ]
                                ];

  public function __construct(
    \Magento\Framework\View\Element\Template\Context $context,       
    \ZipMoney\ZipMoneyPayment\Model\Config $config,
    \Magento\Framework\Registry $registry,      
    \ZipMoney\ZipMoneyPayment\Helper\Logger $logger,             
    array $data = []
  ) {
    parent::__construct($context, $data);

    $this->_config = $config;
    $this->_registry = $registry;
    $this->_logger   = $logger;

  }
 
  /**
   * Check if widget has been enabled
   *
   * @return bool
   */
  protected function _configShow($widget, $page)
  {    

    $configPath = $this->_getConfigPath($widget,$page);
    return $this->_config->getConfigData($configPath);
  }

  /**
   * Returns the config path
   *
   * @return bool
   */
  protected function _getConfigPath($widget,$page)
  {
    if($widget && $page)
      return isset($this->_configConstants[$widget][$page]) ? $this->_configConstants[$widget][$page]:null ;
    else
      return null;
  }

}
