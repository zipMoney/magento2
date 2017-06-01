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

class Tagline extends  AbstractAdvert implements CatalogBlock\ShortcutInterface
{ 
  /**
   * @const string
   */
  const WIDGET_TYPE = "tagline";
  
  /**
   * Render the block if needed
   *
   * @return string
   */
  protected function _toHtml()
  {    

    if ($this->_configShow(self::WIDGET_TYPE,$this->getPageType())) {   
      return parent::_toHtml();
    }
    return '';
  }  

  /**
   * Get shortcut alias
   *
   * @return string
   */
  public function getAlias()
  {
    return $this->_alias;
  }

}
