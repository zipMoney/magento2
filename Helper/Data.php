<?php
namespace ZipMoney\ZipMoneyPayment\Helper;

use \Magento\Framework\App\Helper\AbstractHelper;
/**
 * @category  Zipmoney
 * @package   Zipmoney_ZipmoneyPayment
 * @author    Sagar Bhandari <sagar.bhandari@zipmoney.com.au>
 * @copyright 2017 zipMoney Payments Pty Ltd.
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 * @link      http://www.zipmoney.com.au/
 */

class Data extends AbstractHelper 
{

  private $_orderFactory  = null;
  private $_moduleList  = null;
  private $_productMetadata  = null;

  /**
   * Set quote and config instances
   */
  public function __construct(
    \Magento\Framework\App\Helper\Context $context,         
    \Magento\Framework\UrlInterface $urlBuilder,
    \Magento\Sales\Model\OrderFactory $orderFactory,           
    \Magento\Framework\App\ProductMetadataInterface $productMetadata,
    \Magento\Framework\Module\ModuleListInterface $moduleList,
    \ZipMoney\ZipMoneyPayment\Model\Config\Proxy $config,
    \ZipMoney\ZipMoneyPayment\Helper\Logger $logger )
  {   
    $this->_orderFactory = $orderFactory;        
    $this->_productMetadata = $productMetadata;
    $this->_moduleList = $moduleList;
    parent::__construct($context,$urlBuilder,$config,$logger);
  }


  public function __()
  {
    $args = func_get_args();
    $text = array_shift($args);

    return vsprintf(__($text),$args);
  }
  
  /**
   * Returns the json_encoded string
   *
   * @return string
   */
  public function json_encode($object)
  {
    return json_encode(\zipMoney\ObjectSerializer::sanitizeForSerialization($object));
  }

   /**
   * @param $oQuote
   * @return bool
   * @throws Mage_Core_Exception
   */
  protected function _activateQuote($quote)
  {
    if ($quote && $quote->getId()) {
      if (!$quote->getIsActive()) {
        $orderIncId = $quote->getReservedOrderId();
        if ($orderIncId) {
          $order = $this->_orderFactory->create()->loadByIncrementId($orderIncId);
          if ($order && $order->getId()) {
            throw new \Magento\Framework\Exception\LocalizedException(__('Can not activate the quote. It has already been converted to order.'));
          }
        }
        $quote->setIsActive(1)
              ->save();
        $this->_logger->warn(__('Activated quote ' . $quote->getId() . '.'));
        return true;
      }
    }
    return false;
  }

  /**
   * Deactivates the quote 
   * 
   * @param Mage_Sales_Model_Quote $quote 
   * @return bool
   */
  protected function _deactivateQuote($quote)
  {
    if ($quote && $quote->getId()) {
      if ($quote->getIsActive()) {
        $quote->setIsActive(0)->save();
        $this->_logger->warn(__('Deactivated quote ' . $quote->getId() . '.'));
        return true;
      }
    }
    return false;
  }

  public function generateIdempotencyKey()
  {
    return uniqid();
  }

  public function getMagentoVersion()
  {
    return $this->_productMetadata->getVersion();
  }

  public function getExtensionVersion()
  {
    $moduleInfo = $this->_moduleList->getOne("ZipMoney_ZipMoneyPayment");
    return $moduleInfo['setup_version'];
  }

}
