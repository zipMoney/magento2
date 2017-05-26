<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace ZipMoney\ZipMoneyPayment\Gateway\Validator;

use Magento\Payment\Gateway\Validator\AbstractValidator;
use Magento\Payment\Gateway\Validator\ResultInterface;
use Magento\SamplePaymentGateway\Gateway\Http\Client\ClientMock;

class CancelResponseValidator extends AbstractValidator
{
    const RESULT_CODE = 'RESULT_CODE';

    /**
     * Performs validation of result code
     *
     * @param array $validationSubject
     * @return ResultInterface
     */
    public function validate(array $validationSubject)
    {
        if (!isset($validationSubject['response'])) {
            throw new \InvalidArgumentException('Response does not exist');
        }

        $response = $validationSubject['response'];

        if(isset($response['api_response']) && is_object($response['api_response'])){
            if(isset($response['api_response']->error)){
                return $this->createResult(
                    false,
                    [__('Could not cancel the charge')]
                );
            }
        } else if(isset($response['message'])) {
            return $this->createResult(
                    false,
                    [__($response['message'])]
                );
        }

        return $this->createResult(true);
    }
}