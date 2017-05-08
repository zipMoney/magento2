<?php
namespace ZipMoney\ZipMoneyPayment\Block\Adminhtml\Order\View;

use \ZipMoney\ZipMoneyPayment\Model\Config;

class History extends \Magento\Sales\Block\Adminhtml\Order\View\History
{ 

  public function getStatuses()
  {
    $arry_status_zip = array(
      Config::STATUS_MAGENTO_NEW,
      Config::STATUS_MAGENTO_AUTHORIZED,
      Config::STATUS_MAGENTO_PROCESSING,
      Config::STATUS_MAGENTO_CANCELLED,
      Config::STATUS_MAGENTO_REFUND,
      Config::STATUS_MAGENTO_AUTHORIZED_REVIEW,
      Config::STATUS_MAGENTO_ORDER_CANCELLED,
      Config::STATUS_MAGENTO_ORDER_DECLINED    
    );

    $state = $this->getOrder()->getState();
    $statuses = $this->getOrder()->getConfig()->getStateStatuses($state);
    //$this->_logger->info($this->getOrder()->getPayment()->getMethodInstance()->getCode());
    //$this->_logger->info(json_encode($statuses));

    if($this->getOrder()->getPayment()->getMethodInstance()->getCode()!= Config::METHOD_ZC && $this->getOrder()->getPayment()->getMethodInstance()->getCode()!= Config::METHOD_ZP)
    {     
      foreach($statuses as  $key=>$value)
      {
        if(in_array($key,$arry_status_zip)) 
          unset($statuses[$key]);
      }
    }

    return $statuses;
  }
}
