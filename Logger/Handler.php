<?php
namespace ZipMoney\ZipMoneyPayment\Logger;
  
class Handler extends \Magento\Framework\Logger\Handler\Base
{
    /**
     * File name
     * @var string
     */
    protected $fileName = '/var/log/zipMoneyPayment.log';
}