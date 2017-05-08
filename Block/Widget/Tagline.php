<?php
namespace ZipMoney\ZipMoneyPayment\Block\Widget;

use Magento\Catalog\Block as CatalogBlock;
use Magento\Paypal\Helper\Shortcut\ValidatorInterface;
use \ZipMoney\ZipMoneyPayment\Model\Config;


class Tagline extends \ZipMoney\ZipMoneyPayment\Block\AbstractWidget implements CatalogBlock\ShortcutInterface
{
  const WIDGET_TYPE = "tagline";
  
  /**
   * Render the block if needed
   *
   * @return string
   */
  protected function _toHtml()
  {    

    if ($this->_configShow(self::WIDGET_TYPE,$this->getPageType())) {   
      $this->_logger->debug("Rendering Tagline Widget in ".$this->getPageType()." page");
      return parent::_toHtml();
    }
    return '';
  }

 
  /**
   * Check is "OR" label position before shortcut
   *
   * @return bool
   */
  public function isOrPositionBefore()
  {
    return $this->getShowOrPosition() == CatalogBlock\ShortcutButtons::POSITION_BEFORE;
  }

  /**
   * Check is "OR" label position after shortcut
   *
   * @return bool
   */
  public function isOrPositionAfter()
  {
    return $this->getShowOrPosition() == CatalogBlock\ShortcutButtons::POSITION_AFTER;
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
