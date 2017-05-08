<?php
namespace ZipMoney\ZipMoneyPayment\Test\Unit\Model;

use Magento\Paypal\Model\Api\ProcessableException as ApiProcessableException;
use \ZipMoney\ZipMoneyPayment\Model\Config;

class StandardTest extends \PHPUnit_Framework_TestCase
{
    
    /**
     * @var Standard
     */
    protected $_model;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $_apiHelper;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $_orderHelper;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $_payloadHelper;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $_logHelper;

    /**
     * @var \Magento\Framework\TestFramework\Unit\Helper\ObjectManager
     */
    protected $_helper;


 
    protected function setUp()
    {

        $this->_helper = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);

        $this->_apiHelper = $this->getMock('\ZipMoney\ZipMoneyPayment\Helper\Api',[], [], '', false);
        $this->_orderHelper = $this->getMock('\ZipMoney\ZipMoneyPayment\Helper\Order',[], [], '', false);
        $this->_payloadHelper = $this->getMock('\ZipMoney\ZipMoneyPayment\Helper\Request',[], [], '', false);
        $this->_logHelper = $this->_helper->getObject('\ZipMoney\ZipMoneyPayment\Helper\Logger',[], [], '', false);
                
        $this->api  = $this->getMock(
            Config::API_NAMESPACE . Config::API_TYPE_CHECKOUT,
            ['process'],
            [],
            '',
            false
        );

        $this->_model = $this->_helper->getObject(
            'ZipMoney\ZipMoneyPayment\Model\Standard',
            [
                'apiHelper' => $this->_apiHelper,
                'orderHelper' => $this->_orderHelper,
                'requestHelper' => $this->_payloadHelper,
                'logHelper' => $this->_logHelper
            ]
        );

    }


    public function testDoCheckout()
    {
        
        $this->_apiHelper->expects($this->any())
                    ->method('getApi')
                    ->will($this->returnValue( $this->api ) );

        $order = $this->getMock(
            'Magento\Sales\Model\Order',
            [],
            [],
            '',
            false
        ); 

        $order->method('getId')
            ->willReturn(1);
            
        $order->method('getGrandTotal')
            ->willReturn(100);

        $response = $this->getMock(
            'zipMoney\\Response',
            [],
            [],
            '',
            false
        );
        
        $response->method('isSuccess')
            ->willReturn(true); 
        
        $response->method('getRedirectUrl')
            ->willReturn("http://app.zipmoney.com/#31212"); 

        $this->_orderHelper->expects($this->any())
                    ->method('getCurrentOrder')
                    ->willReturn($order);

        $this->api->expects($this->any())
                    ->method('process')
                    ->willReturn($response);
        
        $this->assertEquals($response, $this->_model->doCheckout());
    }
}
