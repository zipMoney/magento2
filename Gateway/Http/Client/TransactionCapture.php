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
class TransactionCapture extends AbstractTransaction implements ClientInterface
{   
    protected $_service = null;
    
    public function __construct(
        \Magento\Framework\Model\Context $context,
        \Magento\Framework\Encryption\EncryptorInterface $encryptor,
        \ZipMoney\ZipMoneyPayment\Helper\Payload $payloadHelper,
        \ZipMoney\ZipMoneyPayment\Helper\Logger $logger,   
        \ZipMoney\ZipMoneyPayment\Helper\Data $helper,
        \ZipMoney\ZipMoneyPayment\Model\Config $config,
        array $data = []
    ) {
       
       parent::__construct($context,$encryptor,$payloadHelper,$logger,$helper,$config);

       $this->_service = new \zipMoney\Api\ChargesApi();
    }

    /**
     * @param \Magento\Payment\Gateway\Http\TransferInterface $transferObject
     * @return array
     */
    public function placeRequest(\Magento\Payment\Gateway\Http\TransferInterface $transferObject)
    {
        $request = $transferObject->getBody();
        $payload = $request['payload'];
        $zipmoney_charge_id = $request['zipmoney_checkout_id'];
        
        $response = null;

        try {

            $charge = $this->_service->chargesCapture($zipmoney_charge_id, $payload, $this->_helper->generateIdempotencyKey());
            $response =  ["api_response" => $charge];
            $this->_logger->debug("Capture Charge Response:- ".$this->_helper->json_encode($charge));

        } catch(\zipMoney\ApiException $e){
            $this->_logger->debug("Error:-".$e->getCode().$e->getMessage()."-".json_encode($e->getResponseBody()));
            $message = $this->_helper->__("Could not process the payment");

            if($e->getCode() == 402 && 
                $mapped_error_code = $this->_config->getMappedErrorCode($e->getResponseObject()->getError()->getCode())){
                $message = $this->_helper->__('The payment was declined by Zip.(%s)',$mapped_error_code);
            }
            $response['message'] = $message;
        }   finally {
            $log['response'] = $response;
        }

        return $response;
    }


}