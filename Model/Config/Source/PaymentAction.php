<?php
namespace ZipMoney\ZipMoneyPayment\Model\Config\Source;

/**
 * Copyright © 2016 zipMoney. All rights reserved.
 */

/**
 * Used in creating options for sandbox|production config value selection
 *
 */


class PaymentAction implements \Magento\Framework\Option\ArrayInterface {
    
    /**
     * Options getter
     *
     * @return array
     */
    public function toOptionArray() {
        
        return [['value' => 'authorise', 'label' => __('Authorise')], ['value' => 'authorize_capture', 'label' => __('Authorise & Capture')]];

    }

}
