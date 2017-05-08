<?php
namespace ZipMoney\ZipMoneyPayment\Model\Config\Source;

/**
 * Copyright © 2016 zipMoney. All rights reserved.
 */

/**
 * Used in creating options for sandbox|production config value selection
 *
 */


class Product implements \Magento\Framework\Option\ArrayInterface {
    
    /**
     * Options getter
     *
     * @return array
     */
    public function toOptionArray() {
        
        return [['value' => 'zipmoney', 'label' => __('zipMoney')], ['value' => 'zippay', 'label' => __('zipPay')]];

    }

}
