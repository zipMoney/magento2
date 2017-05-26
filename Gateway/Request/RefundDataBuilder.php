<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace ZipMoney\ZipMoneyPayment\Gateway\Request;

use Magento\Payment\Gateway\Data\PaymentDataObjectInterface;
use Magento\Sales\Api\Data\OrderPaymentInterface;

class RefundDataBuilder extends AbstractDataBuilder
{
    /**
     * Builds ENV request
     *
     * @param array $buildSubject
     * @return array
     */
    public function build(array $buildSubject)
    {   
        $amount =  \Magento\Payment\Gateway\Helper\SubjectReader::readAmount($buildSubject);
        
        if (!isset($buildSubject['payment'])
            || !$buildSubject['payment'] instanceof PaymentDataObjectInterface
        ) {
            throw new \InvalidArgumentException('Payment data object should be provided');
        }

        /** @var PaymentDataObjectInterface $paymentDO */
        $paymentDO = $buildSubject['payment'];
        $payment = $paymentDO->getPayment();
        $order = $payment->getOrder();

        $payload = $this->_payloadHelper->getRefundPayload($order, $amount, "Refund");
        $this->_logger->debug("Refund Request:- ".$this->_helper->json_encode($payload));
        
        if (!$payment instanceof OrderPaymentInterface) {
            throw new \LogicException('Order payment should be provided.');
        }
    
        $return['payload'] = $payload;
        $return['txn_id'] = $payment->getLastTransId();
   
        return $return;
    }
}
