<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace  ZipMoney\ZipMoneyPayment\Test\Unit\Gateway\Http\Client;

use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Payment\Gateway\Http\TransferInterface;
use ZipMoney\ZipMoneyPayment\Model\Config;

/**
 * @category  Zipmoney
 * @package   Zipmoney_ZipmoneyPayment
 * @author    Sagar Bhandari <sagar.bhandari@zipmoney.com.au>
 * @copyright 2017 zipMoney Payments Pty Ltd.
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 * @link      http://www.zipmoney.com.au/
 */

class TransactionRefundTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var Context | \PHPUnit_Framework_MockObject_MockObject
     */
    protected $context;

    /**
     * @var ConfigInterface | \PHPUnit_Framework_MockObject_MockObject
     */
    protected $config;

    protected $messageManager;

    public function setUp()
    {
       
        $objManager = new ObjectManager($this);
        
        $config = $this->getMockBuilder(Config::class)
                    ->disableOriginalConstructor()
                    ->getMock();

        $config->expects(static::any())->method('getLogSetting')->willReturn(10);  

        $monologger = $objManager->getObject("\ZipMoney\ZipMoneyPayment\Logger\Logger");         
        
        $logger = $objManager->getObject("\ZipMoney\ZipMoneyPayment\Helper\Logger",[ "_config" => $config, "_logger" => $monologger]); 
        
        $this->_refundsApiMock = $this->getMockBuilder(\zipMoney\Api\RefundsApi::class)->getMock();
        
        $this->_clientMock = $objManager->getObject("\ZipMoney\ZipMoneyPayment\Gateway\Http\Client\TransactionRefund", 
            [ '_service' => $this->_refundsApiMock]);
        
    }
    /**
     * @param array $expectedRequest
     * @param array $expectedResponse
     *
     * @dataProvider placeRequestDataProvider
     */
    public function testPlaceRequest( $expectedRequest, $expectedResponse)
    {          

        $transferObject = $this->getMock("\Magento\Payment\Gateway\Http\TransferInterface");

        $transferObject->expects(static::any())->method('getBody')->willReturn($expectedRequest);
        $this->_refundsApiMock->expects(static::any())->method('refundsCreate')->willReturn( $expectedResponse  );

        static::assertEquals(
            [ 'api_response' => $expectedResponse ],
            $this->_clientMock->placeRequest($transferObject)
        );
    }


    public function placeRequestDataProvider()
    {   
        $chargeResponse = new \zipMoney\Model\Charge;
      
        $chargeResponse->setId("112343");
        $chargeResponse->setState("refunded");
        return [
            'success' => [
                'expectedRequest' => [
                    'payload' => null,
                    'zipmoney_checkout_id' => 123
                ],
                $chargeResponse
            ]
        ];
    }

}
