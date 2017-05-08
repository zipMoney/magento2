<?php
namespace  ZipMoney\ZipMoneyPayment\Block;

use Magento\Framework\View\Element\Template;
 
class Error extends Template
{    
    protected function _prepareLayout()
    {
     $this->pageConfig->getTitle()->set(__('Error'));

     return parent::_prepareLayout();

    }
}
