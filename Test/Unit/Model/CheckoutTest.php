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
use Magento\Customer\Model\Session as CustomerSession;
use Magento\Checkout\Model\Session as CheckoutSession;
use Magento\Checkout\Helper\Data as CheckoutHelper;
use Magento\Quote\Api\CartRepositoryInterface;
use Magento\Sales\Model\OrderFactory;
use Magento\Customer\Model\CustomerFactory;
use Magento\Checkout\Model\PaymentInformationManagement;
use Magento\Customer\Api\AccountManagementInterface;
use Magento\Customer\Api\CustomerRepositoryInterface;
use ZipMoney\ZipMoneyPayment\Model\Checkout;
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

class CheckoutTest extends \PHPUnit_Framework_TestCase
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
        
        $checkoutHelperMock = $this->getMockBuilder(CheckoutHelper::class)
                                    ->disableOriginalConstructor()
                                    ->getMock();
        $checkoutHelperMock->expects(static::any())->method('isAllowedGuestCheckout')->willReturn(true);  

        $config = $this->getMockBuilder(Config::class)
            ->disableOriginalConstructor()
            ->getMock();

        $config->expects(static::any())->method('getLogSetting')->willReturn(10);  

        $monologger = $objManager->getObject("\ZipMoney\ZipMoneyPayment\Logger\Logger");         
        
        $logger = $objManager->getObject("\ZipMoney\ZipMoneyPayment\Helper\Logger",[ "_config" => $config, "_logger" => $monologger]); 
        
        $this->_checkoutsApiMock = $this->getMockBuilder(\zipMoney\Api\CheckoutsApi::class)->getMock();
      
        $this->_checkoutModel = $objManager->getObject("\ZipMoney\ZipMoneyPayment\Model\Checkout", 
            ['_checkoutHelper' => $checkoutHelperMock, '_logger' => $logger]);

        $this->_checkoutModel->setApi($this->_checkoutsApiMock);
        
    }


    public function getQuoteMock()
    {
        $quoteMock = $this->getMock("\Magento\Quote\Model\Quote" ,
            [   'getId',
                'getCheckoutMethod',
                'getIsMultiShipping',
                'getStoreId',
                'collectTotals',
                'reserveOrderId',
                'hasNominalItems', 
                'getGrandTotal', 
                'setGrandTotal', 
                'setBaseGrandTotal'],
            [],
            '',
            false);

        $quoteMock->expects(static::any())->method('getId')->willReturn(1);  
        $quoteMock->expects(static::any())->method('getCheckoutMethod')->willReturn('guest'); 
        $quoteMock->expects(static::any())->method('getIsMultiShipping')->willReturn(0);   
        $quoteMock->expects(static::any())->method('getStoreId')->willReturn(1);
        $quoteMock->expects(static::any())->method('collectTotals')->willReturn(true);
        $quoteMock->expects(static::any())->method('reserveOrderId')->willReturn(true);        

        return $quoteMock;
    }


    public function testCheckoutStart()
    {  
        $checkoutResponse = new \zipMoney\Model\Checkout;

        $quoteMock =  $this->getQuoteMock();
        
        $quoteMock->expects(static::any())->method('hasNominalItems')->willReturn(true);
        $quoteMock->expects(static::any())->method('getGrandTotal')->willReturn(100.10);
        
        $return_url = "https://account.zipmoney.com.au/?ch=ch_f8h2sz09na";
        $checkout_id = "ch_f8h2sz09na";

        $checkoutResponse->setUri($return_url);
        $checkoutResponse->setId($checkout_id);

        $this->_checkoutsApiMock->expects(static::any())->method('checkoutsCreate')->willReturn( $checkoutResponse  );
        $this->_checkoutModel->setQuote($quoteMock);
        $this->_checkoutModel->start();

        $this->assertEquals($this->_checkoutModel->getCheckoutId(), $checkout_id);
        $this->assertEquals($this->_checkoutModel->getRedirectUrl(), $return_url);
    }

    /**
     * @test
     * @group Zipmoney_ZipmoneyPayment  
     * @expectedException  Exception
     * @expectedExceptionMessage Cannot process the order due to zero amount.
     */
    public function testCheckoutStartRaisesExceptionZeroAmount()
    {    
        $quoteMock =  $this->getQuoteMock();
        $quoteMock->expects(static::any())->method('hasNominalItems')->willReturn(false);
        $quoteMock->expects(static::any())->method('getGrandTotal')->willReturn(0);
        
        $this->_checkoutModel->setQuote($quoteMock);
        $this->_checkoutModel->start();
    }


    /**
     * @test
     * @group Zipmoney_ZipmoneyPayment  
     * @expectedException  Exception
     * @expectedExceptionMessage The quote does not exist.
     */
    public function testCheckoutStartRaisesExceptionQuoteDoesnotExist()
    {   $quoteMock = $this->getMock("\Magento\Quote\Model\Quote",[],[],'',false);
        $this->_checkoutModel->setQuote($quoteMock);
        $this->_checkoutModel->start();
    }   


    /**
     * @test
     * @group Zipmoney_ZipmoneyPayment  
     * @expectedException  Exception
     * @expectedExceptionMessage  Cannot get redirect URL from zipMoney.
     */
    public function testCheckoutStartRaisesExceptionRedirectUrl()
    {   

        $checkout = new \zipMoney\Model\Checkout;
        $return_url = "https://account.zipmoney.com.au/?ch=ch_f8h2sz09na";
        $checkout->error  = new \stdClass;



        $this->_checkoutsApiMock->expects($this->any())
                      ->method('checkoutsCreate')
                      ->willReturn( $checkout  );
        
        $quoteMock =  $this->getQuoteMock();
        
        $quoteMock->expects(static::any())->method('hasNominalItems')->willReturn(true);
        $quoteMock->expects(static::any())->method('getGrandTotal')->willReturn(100.10);
        
        $this->_checkoutModel->setQuote($quoteMock);
        $this->_checkoutModel->start();
     }
   
}
