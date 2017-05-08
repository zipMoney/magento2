<?php
namespace ZipMoney\ZipMoneyPayment\Model\Config\Source;
use \ZipMoney\ZipMoneyPayment\Logger\Logger as ZipMoneyLogger;

/**
 * Copyright © 2016 zipMoney. All rights reserved.
 */

/**
 * Used in creating options for sandbox|production config value selection
 *
 */


class LogSettings implements \Magento\Framework\Option\ArrayInterface {
    
    /**
     * Options getter
     *
     * @return array
     */
    public function toOptionArray() {
        
        return [['value' => ZipMoneyLogger::DEBUG, 'label' => __('All')], 
                ['value' => ZipMoneyLogger::INFO, 'label' => __('Default')],
                ['value' => -1, 'label' => __('None')]];

    }

}
