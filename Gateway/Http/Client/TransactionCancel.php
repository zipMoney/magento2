<?php
namespace ZipMoney\ZipMoneyPayment\Gateway\Http\Client;

use Magento\Payment\Gateway\Http\ClientInterface;
use Magento\Payment\Gateway\Http\ClientException;

/**
 * @category  Zipmoney
 * @package   Zipmoney_ZipmoneyPayment
 * @author    Sagar Bhandari <sagar.bhandari@zipmoney.com.au>
 * @copyright 2017 zipMoney Payments Pty Ltd.
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 * @link      http://www.zipmoney.com.au/
 */

/**
 * Class TransactionCapture
 */
class TransactionCancel extends AbstractTransaction implements ClientInterface
{   
    protected $_service = null;
    
    public function __construct(
        \Magento\Framework\Model\Context $context,
        \Magento\Framework\Encryption\EncryptorInterface $encryptor,
        \ZipMoney\ZipMoneyPayment\Helper\Payload $payloadHelper,
        \ZipMoney\ZipMoneyPayment\Helper\Logger $logger,   
        \ZipMoney\ZipMoneyPayment\Helper\Data $helper,
        \ZipMoney\ZipMoneyPayment\Model\Config $config,
        \zipMoney\Api\ChargesApi $chargesApi,
        array $data = []
    ) {
       
       parent::__construct($context,$encryptor,$payloadHelper,$logger,$helper,$config);

       $this->_service = $chargesApi;

    }

    /**
     *
     * @param \Magento\Payment\Gateway\Http\TransferInterface $transferObject
     * @return array
     */
    public function placeRequest(\Magento\Payment\Gateway\Http\TransferInterface $transferObject)
    {
        $request = $transferObject->getBody();
        $zipmoney_charge_id = $request['zipmoney_charge_id'];
        
        $response = null;

        try {
            $cancel = $this->_service->chargesCancel($zipmoney_charge_id, $this->_helper->generateIdempotencyKey());
            $response =  ["api_response" => $cancel];
            $this->_logger->debug("Cancel Response:- ".$this->_helper->json_encode($cancel));

        } catch(\zipMoney\ApiException $e){
            list($apiError, $message, $logMessage) = $this->_helper->handleException($e);  

            $response['message'] = $message;
        }   finally {
            $log['response'] = $response;
        }

        return $response;
    }


}