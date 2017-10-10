<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace  ZipMoney\ZipMoneyPayment\Test\Unit\Model;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;

use Magento\Framework\View\Element\Template\Context;
use Magento\Payment\Gateway\ConfigInterface;
use Magento\Framework\Message\Manager;
use Magento\Framework\App\RequestInterface;
use Magento\Payment\Gateway\Data\OrderAdapterInterface;
use Magento\Framework\Message\ManagerInterface;
use ZipMoney\ZipMoneyPayment\Model\Charge;
use ZipMoney\ZipMoneyPayment\Model\Config;
use ZipMoney\ZipMoneyPayment\Helper\Payload;
use ZipMoney\ZipMoneyPayment\Helper\Logger;
use ZipMoney\ZipMoneyPayment\Helper\Data as ZipMoneyDataHelper; 

/**
 * @category  Zipmoney
 * @package   Zipmoney_ZipmoneyPayment
 * @author    Sagar Bhandari <sagar.bhandari@zipmoney.com.au>
 * @copyright 2017 zipMoney Payments Pty Ltd.
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 * @link      http://www.zipmoney.com.au/
 */

class ChargeTest extends \PHPUnit\Framework\TestCase
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

        $this->_chargesApiMock = $this->getMockBuilder(\zipMoney\Api\ChargesApi::class)->getMock();
       
        $quoteManagement = $this->getMockBuilder(Magento\Quote\Api\CartManagementInterface::class)
                                ->setMethods( ['submit'])
                                ->getMock();  

        $orderMock = $this->getMockBuilder(\Magento\Sales\Model\Order::class)
                          ->disableOriginalConstructor()
                          ->getMock();

        $quoteManagement->expects(static::any())->method('submit')->willReturn($orderMock);  
        
        $checkoutSession = $objManager->getObject('\Magento\Checkout\Model\Session');

        $checkoutSession = $this->getMockBuilder(\Magento\Checkout\Model\Session::class)
                                ->setMethods(['setLastSuccessQuoteId','setLastQuoteId','clearHelperData','setLastOrderId','setLastRealOrderId','setLastOrderStatus'])
                                ->disableOriginalConstructor()
                                ->getMock();  

        $checkoutSession->expects(static::any())->method('setLastQuoteId')->willReturn($checkoutSession);  
        $checkoutSession->expects(static::any())->method('setLastSuccessQuoteId')->willReturn($checkoutSession);  
        $checkoutSession->expects(static::any())->method('setLastOrderId')->willReturn($checkoutSession);  
        $checkoutSession->expects(static::any())->method('setLastRealOrderId')->willReturn($checkoutSession);  
 
        $this->_chargeModel = $objManager->getObject("\ZipMoney\ZipMoneyPayment\Model\Charge", 
            [ '_quoteManagement' => $quoteManagement,'_checkoutSession' => $checkoutSession]);

        $this->_chargeModel->setApi($this->_chargesApiMock);
        
    }


    public function getOrderMock()
    {   

        // Order Invoice
        $invoiceMock = $this->getMockBuilder(\Magento\Sales\Model\Order\Invoice::class)
                            ->setMethods(['getIncrementId'])                                
                            ->disableOriginalConstructor()
                            ->getMock(); 

        $invoiceMock->expects(static::any())->method('getIncrementId')->willReturn(1);        

        // Payment Model
        $paymentMock = $this->getMockBuilder(\Magento\Sales\Model\Order\Payment::class)
                            ->setMethods([   'setZipmoneyChargeId','registerCaptureNotification','registerAuthorizationNotification','getCreatedInvoice'])
                            ->disableOriginalConstructor()
                            ->getMock();

        $paymentMock->expects(static::any())->method('setZipmoneyChargeId')->willReturn($paymentMock);        
        $paymentMock->expects(static::any())->method('registerCaptureNotification')->willReturn(true);        
        $paymentMock->expects(static::any())->method('registerAuthorizationNotification')->willReturn(true);        
        $paymentMock->expects(static::any())->method('getCreatedInvoice')->willReturn($invoiceMock);        


        $orderMock = $this->getMockBuilder(\Magento\Sales\Model\Order::class)
            ->setMethods(['getId',
                'getCheckoutMethod',
                'getIsMultiShipping',
                'getStoreId',
                'collectTotals',
                'reserveOrderId',
                'hasNominalItems', 
                'getGrandTotal', 
                'setGrandTotal', 
                'setBaseGrandTotal',
                'getPayment',
                'getState','canInvoice','getBaseTotalDue','addStatusHistoryComment','setIsCustomerNotified'])
            ->disableOriginalConstructor()
            ->getMock(); 

        $orderMock->expects(static::any())->method('getId')->willReturn(1);  
        $orderMock->expects(static::any())->method('getCheckoutMethod')->willReturn('guest'); 
        $orderMock->expects(static::any())->method('getIsMultiShipping')->willReturn(0);   
        $orderMock->expects(static::any())->method('getStoreId')->willReturn(1);
        $orderMock->expects(static::any())->method('collectTotals')->willReturn(true);
        $orderMock->expects(static::any())->method('reserveOrderId')->willReturn(true);   
        $orderMock->expects(static::any())->method('getPayment')->willReturn($paymentMock);        
        $orderMock->expects(static::any())->method('getState')->willReturn(\Magento\Sales\Model\Order::STATE_NEW);        
        $orderMock->expects(static::any())->method('canInvoice')->willReturn(true);        
        $orderMock->expects(static::any())->method('getBaseTotalDue')->willReturn(100);        
        $orderMock->expects(static::any())->method('addStatusHistoryComment')->willReturn($orderMock);        
        $orderMock->expects(static::any())->method('setIsCustomerNotified')->willReturn(true);        

        return $orderMock;
    }


    public function getQuoteMock()
    {
        $quoteMock = $this->getMockBuilder(Magento\Quote\Model\Quote::class)
                            ->setMethods([ 'getId',
                                'getCheckoutMethod',
                                'getIsMultiShipping',
                                'getStoreId',
                                'collectTotals',
                                'reserveOrderId',
                                'hasNominalItems', 
                                'getGrandTotal', 
                                'setGrandTotal', 
                                'setBaseGrandTotal',
                                'getBillingAddress',
                                'getShippingAddress',
                                'getIsVirtual','getCustomerId','setCustomerId','setCustomerEmail','setCustomerIsGuest','setCustomerGroupId'])
                            ->disableOriginalConstructor()
                            ->getMock();

        $billingAddress = $this->getMockBuilder(Magento\Quote\Model\Quote\Address::class)
                               ->setMethods(['getEmail','setShouldIgnoreValidation'])
                               ->disableOriginalConstructor()
                               ->getMock();

        $billingAddress->expects(static::any())->method('getEmail')->willReturn("test@test.cpm");  

        $shippingAddress = $this->getMockBuilder(\Magento\Quote\Model\Quote\Address::class)
                                ->setMethods(['setShouldIgnoreValidation'])
                                ->disableOriginalConstructor()
                                ->getMock();

        $shippingAddress->expects(static::any())->method('setShouldIgnoreValidation')->willReturn(true);  

        $quoteMock->expects(static::any())->method('getId')->willReturn(1);  
        $quoteMock->expects(static::any())->method('getCheckoutMethod')->willReturn('guest'); 
        $quoteMock->expects(static::any())->method('getIsMultiShipping')->willReturn(0);   
        $quoteMock->expects(static::any())->method('getStoreId')->willReturn(1);
        $quoteMock->expects(static::any())->method('collectTotals')->willReturn(true);
        $quoteMock->expects(static::any())->method('reserveOrderId')->willReturn(true);        
        $quoteMock->expects(static::any())->method('getBillingAddress')->willReturn($billingAddress);        
        $quoteMock->expects(static::any())->method('getShippingAddress')->willReturn($shippingAddress);        
        $quoteMock->expects(static::any())->method('getIsVirtual')->willReturn(false);        
        $quoteMock->expects(static::any())->method('setCustomerId')->willReturn($quoteMock);        
        $quoteMock->expects(static::any())->method('setCustomerEmail')->willReturn($quoteMock);        
        $quoteMock->expects(static::any())->method('setCustomerIsGuest')->willReturn($quoteMock);        
        $quoteMock->expects(static::any())->method('setCustomerGroupId')->willReturn($quoteMock);        

        return $quoteMock;
    }

    public function testChargeCapture()
    {  

        $orderMock =  $this->getOrderMock();
        
        $orderMock->expects(static::any())->method('hasNominalItems')->willReturn(true);
        $orderMock->expects(static::any())->method('getGrandTotal')->willReturn(100);
        
        $chargeResponse = new \zipMoney\Model\Charge;
      
        $chargeResponse->setId("112343");
        $chargeResponse->setState("captured");

        $this->_chargesApiMock->expects(static::any())->method('chargesCreate')->willReturn( $chargeResponse  );
        $this->_chargeModel->setOrder($orderMock);
        $response = $this->_chargeModel->charge();

        $this->assertEquals($response->getState(),"captured");
    }

    public function testChargeAuthorise()
    {  

        $orderMock =  $this->getOrderMock();
        
        $orderMock->expects(static::any())->method('hasNominalItems')->willReturn(true);
        $orderMock->expects(static::any())->method('getGrandTotal')->willReturn(100);
        
        $chargeResponse = new \zipMoney\Model\Charge;
      
        $chargeResponse->setId("112343");
        $chargeResponse->setState("authorised");

        $this->_chargesApiMock->expects(static::any())->method('chargesCreate')->willReturn( $chargeResponse  );
        $this->_chargeModel->setOrder($orderMock);
        $response = $this->_chargeModel->charge();

        $this->assertEquals($response->getState(),"authorised");
    }

    /**
     * @test
     * @group Zipmoney_ZipmoneyPayment       
     * @expectedException  Exception
     * @expectedExceptionMessage The order does not exist.
     */
    public function testChargeRaisesOrderDoesnotExistException()
    {  

        $orderMock =  $this->getMockBuilder(Magento\Sales\Model\Order::class)->setMethods(['getId'])
                            ->disableOriginalConstructor()
                            ->getMock();
        
        $this->_chargeModel->setOrder($orderMock);
        $this->_chargeModel->charge();
    }


     /**
     * @test
     * @group Zipmoney_ZipmoneyPayment  
     * @expectedException  Exception
     * @expectedExceptionMessage  Invalid Charge
     */
    public function testChargeRaisesInvalidChargeException()
    {   

        $chargeResponse = new \zipMoney\Model\Charge;

        $this->_chargesApiMock->expects(static::any())->method('chargesCreate')->willReturn( $chargeResponse  );
        
        $orderMock =  $this->getOrderMock();
        
        $orderMock->expects(static::any())->method('hasNominalItems')->willReturn(true);
        $orderMock->expects(static::any())->method('getGrandTotal')->willReturn(100);
        
        $this->_chargeModel->setOrder($orderMock);
        $this->_chargeModel->charge();
     } 


     /**
     * @test
     * @group Zipmoney_ZipmoneyPayment  
     * @expectedException  Exception
     * @expectedExceptionMessage  Could not create the charge
     */
    public function testChargeRaisesCouldnotCreateChargeException()
    {   
        $chargeResponse = new \zipMoney\Model\Charge;
        $chargeResponse->error  = new \stdClass;

        $this->_chargesApiMock->expects(static::any())->method('chargesCreate')->willReturn( $chargeResponse  );
        
        $orderMock =  $this->getOrderMock();
        
        $orderMock->expects(static::any())->method('hasNominalItems')->willReturn(true);
        $orderMock->expects(static::any())->method('getGrandTotal')->willReturn(100);
        
        $this->_chargeModel->setOrder($orderMock);
        $this->_chargeModel->charge();
    }

    public function testPlaceOrder()
    {  
        $quoteMock =  $this->getQuoteMock();
        
        $quoteMock->expects(static::any())->method('hasNominalItems')->willReturn(true);
        $quoteMock->expects(static::any())->method('getGrandTotal')->willReturn(100);
        
        $this->_chargeModel->setQuote($quoteMock);
        $this->_chargeModel->placeOrder();
    }
}
