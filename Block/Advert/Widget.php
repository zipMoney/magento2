<?php
namespace ZipMoney\ZipMoneyPayment\Block\Advert;

use Magento\Catalog\Block as CatalogBlock;
use Magento\Paypal\Helper\Shortcut\ValidatorInterface;

use \ZipMoney\ZipMoneyPayment\Model\Config;


class Widget extends  AbstractAdvert implements CatalogBlock\ShortcutInterface
{   
  /**
   * @const string
   */
  const ADVERT_TYPE = "widget";
  
  /**
   * Render the block if needed
   *
   * @return string
   */
  protected function _toHtml()
  {   

    if ($this->_configShow(self::ADVERT_TYPE,$this->getPageType())) { 
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