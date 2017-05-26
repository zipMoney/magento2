<?php
namespace ZipMoney\ZipMoneyPayment\Helper;

use \Magento\Framework\App\Helper\AbstractHelper;
use ZipMoney\ZipMoneyPayment\Model\Config;
use \ZipMoney\ZipMoneyPayment\Logger\Logger as ZipMoneyLogger;

class Logger  extends  AbstractHelper
{  

	protected $_modelFactory;  

	/**
	 * @var \ZipMoney\ZipMoneyPayment\Model\Config\Proxy
	 */
	protected $_config;
	
	/**
	 * @var array
	 */
	protected $_logLevelsMap = [  // Corresponding log levels in Magento 1.x and used in zipMoney Admins
															 0 => 600, // 'EMERGENCY', 
															 1 => 550, // 'ALERT', 
															 2 => 500, // 'CRITICAL',
															 3 => 400, // 'ERROR',
															 4 => 300, // 'WARNING',
															 5 => 250, // 'NOTICE',
															 6 => 200, // 'INFO' ,
															 7 => 100, // 'DEBUG'
														 ];

	public function __construct(        
			\Magento\Framework\App\Helper\Context $context ,    
			\ZipMoney\ZipMoneyPayment\Model\Config\Proxy $config      
	) {
		parent::__construct($context);    

    $this->_config = $config;    
	}
 
  /**
   * Returns the mapped log level
   *
   * @param  $logLevel
   * @return string
   */
	protected function _mapLogLevel($logLevel)
	{   

		if(strlen($logLevel) <=2){  
			$max_level  = count($this->_logLevelsMap) - 1 ;

			if($logLevel > $max_level )
				return $this->_logLevelsMap[$max_level];
			else
				return $this->_logLevelsMap[$logLevel];
		}
		 
		return $logLevel;
	}
		
	/**
   * Writes the log to the logfile
   *
   * @param  $message, $logLevel, $storeId
   * @return bool
   */
	protected function _log($message, $logLevel = ZipMoneyLogger::INFO, $storeId = null)
	{      
		$logLevel = $this->_mapLogLevel($logLevel);
    $logSetting = $this->_config->getLogSetting($storeId);

		if ($logSetting < 0) {
			return false;
		}
				
		// Config levels are always in the old (Magento 1 format)    
		$configLevel = $this->_mapLogLevel($logSetting);

		// errors are always logged.
		if ($configLevel > 400) {
			$configLevel = ZipMoneyLogger::INFO; // default log level
		}

		if ($logLevel < $configLevel) {
			return false;
		}
		
		$logFunc =  $this->_logger->getLevelName($logLevel);
		$this->_logger->$logFunc($message);			
		return true;
	}
	
	/**
   * Logs the info message to the logfile
   *
   * @param  $message, $storeId
   */	
	public function info($message, $storeId = null)
	{      
		$this->_log($message,ZipMoneyLogger::INFO);
	} 
	
	/**
   * Logs the debug message to the logfile
   *
   * @param  $message, $storeId
   */	
	public function debug($message, $storeId = null)
	{    
		$this->_log($message,ZipMoneyLogger::DEBUG);
	}
	
	/**
   * Logs the warn message to the logfile
   *
   * @param  $message, $storeId
   */	
	public function warn($message, $storeId = null)
	{      
		$this->_log($message,ZipMoneyLogger::WARNING);
	}
	
	/**
   * Logs the notice message to the logfile
   *
   * @param  $message, $storeId
   */	
	public function notice($message, $storeId = null)
	{      
		$this->_log($message,ZipMoneyLogger::NOTICE);
	} 
	
	/**
   * Logs the error message to the logfile
   *
   * @param  $message, $storeId
   */
	public function error($message, $storeId = null)
	{      
		$this->_log($message,ZipMoneyLogger::ERROR);
	} 
	
	/**
   * Logs the critical message to the logfile
   *
   * @param  $message, $storeId
   */
	public function critical($message, $storeId = null)
	{      
		$this->_log($message,ZipMoneyLogger::CRITICAL);
	}
	
	/**
   * Logs the alert message to the logfile
   *
   * @param  $message, $storeId
   */
	public function alert($message, $storeId = null)
	{      
		$this->_log($message,ZipMoneyLogger::ALERT);
	}
 
  /**
   * Logs the emergency message to the logfile
   *
   * @param  $message, $storeId
   */
	public function emergency($message, $storeId = null)
	{      
		$this->_log($message,ZipMoneyLogger::EMERGENCY);
	}
}