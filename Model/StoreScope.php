<?php
namespace ZipMoney\ZipMoneyPayment\Model;


class StoreScope extends \Magento\Framework\DataObject
{
     
 
  protected $_matchedScopes = null;

  /**
   * @var \Magento\Store\Model\StoreManagerInterface
   */
  protected $_storeManager;

  /**
   * @var \Magento\Framework\App\Request\Http
   */
  protected $_request;

  public function __construct(\Magento\Store\Model\StoreManagerInterface $storeManager,
                              \Magento\Framework\App\Request\Http $request,
                              array $data = [])
  {
    $this->_storeManager = $storeManager;
    $this->_request = $request;

    parent::__construct($data);
  }

  /**
   * get matched scopes by merchant_id
   *
   * @param null $iMerchantId
   * @return array|null
   */
  public function getMatchedScopes($merchantId = null)
  {
    if (!$this->_matchedScopes) {
      if ($merchantId === null) {
        $merchantId = $this->getMerchantId();
      }
      $this->_matchedScopes = $this->_getScopesByMerchantId($merchantId);
    }
    return $this->_matchedScopes;
  }

  /**
   * get Scopes (scope/scope_id) on websites level
   *
   * @param $merchantId
   * @return array|null
   */
  protected function _getScopesByMerchantId($merchantId)
  {
    if (!$merchantId) {
        return null;
    }

    $matched  = array();
    $websites = $this->_storeManager->getWebsites();
    foreach ($websites as $website) {
        $path   = "payment/".Config::COMMON_CONFIG_ELEMENT."/".Config::PAYMENT_ZIPMONEY_ID;
        $configMerchantId = $this->_storeManager->getWebsite($website->getId())->getConfig($path);

        if ($merchantId != $configMerchantId) {
            continue;
        }

        /**
         * todo: will need to check if zipMoney is enabled on this website
         */

        $scope = array(
            'scope' => 'websites',
            'scope_id' => $website->getId(),
        );
        $matched[] = $scope;
    }

    $this->_matchedScopes = $matched;
    return $this->_matchedScopes;
  }

  /**
   * Get current scope
   *
   * @return array
   */
  public function getCurrentScope()
  {
    if (!$this->getScope()) {
      $websiteCode =  $this->_request->getParam('website');
      if ($websiteCode) {
          // from magento admin
        $website = \Magento\Core\Model\ObjectManager::getInstance('Magento\Store\Model\Store')->load($websiteCode);

        $this->setScope('websites');
        $this->setScopeId($website->getId());

      } else {
        // get scope based on current merchant_id (when 'configuration_updated' notification comes)
        $matched = $this->_getScopesByMerchantId($this->getMerchantId());
        if ($matched && is_array($matched) && count($matched)) {
          foreach ($matched as $item) {
            // get/return the first matched scope
            $this->setScope($item['scope']);
            $this->setScopeId($item['scope_id']);
            break;
          }
        } else {
          // from frontend
          $website = $this->_storeManager->getWebsite();
          $this->setScope('websites');
          $this->setScopeId($website->getId());
        }
      }
   }

   $scope = array(
    'scope' => $this->getScope(),
    'scope_id' => $this->getScopeId(),
   );

   return $scope;
  }
}