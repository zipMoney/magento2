<?php
namespace ZipMoney\ZipMoneyPayment\Test\Unit\Model;

use \ZipMoney\ZipMoneyPayment\Model\Config;

class ZipCredit extends  \PHPUnit_Framework_TestCase
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

    // \Magento\Framework\Model\Context $context,
    // \Magento\Framework\Registry $registry,
    // \Magento\Framework\Api\ExtensionAttributesFactory $extensionFactory,
    // \Magento\Framework\Api\AttributeValueFactory $customAttributeFactory,
    // \Magento\Payment\Helper\Data $paymentData,
    // \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
    // \Magento\Payment\Model\Method\Logger $logger,
    // \Magento\Store\Model\StoreManagerInterface $storeManager,
    // \Magento\Framework\UrlInterface $urlBuilder,
    // \Magento\Paypal\Model\CartFactory $cartFactory,
    // \Magento\Checkout\Model\Session $checkoutSession,
    // \Magento\Framework\Exception\LocalizedExceptionFactory $exception,
    // \Magento\Sales\Api\TransactionRepositoryInterface $transactionRepository,
    // \Magento\Sales\Model\Order\Payment\Transaction\BuilderInterface $transactionBuilder,        
    // \Magento\Directory\Model\CountryFactory $countryFactory,        
    // \ZipMoney\ZipMoneyPayment\Model\Config $config,        
    // \ZipMoney\ZipMoneyPayment\Helper\Logger $logHelper,       
    // \Magento\Sales\Model\OrderFactory $orderFactory,                              
    // \ZipMoney\ZipMoneyPayment\Helper\Creditmemo $creditMemoHelper,
    // \ZipMoney\ZipMoneyPayment\Helper\Api $apiHelper,                              
    // \ZipMoney\ZipMoneyPayment\Helper\Request $requestHelper,
    // \Magento\Framework\Model\ResourceModel\AbstractResource $resource = null,
    // \Magento\Framework\Data\Collection\AbstractDb $resourceCollection = null,                
    // array $data = []

    CONST API_TYPE = "Refund";
 
    protected function setUp()
    {

        $this->_helper = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);

        $this->_apiHelper = $this->getMock('\ZipMoney\ZipMoneyPayment\Helper\Api',[], [], '', false);
        $this->_orderFactory = $this->getMock('\Magento\Sales\Model\OrderFactory',[], [], '', false);
        $this->_creditMemoHelper = $this->getMock('\ZipMoney\ZipMoneyPayment\Helper\Creditmemo',[], [], '', false);        
        $this->_payloadHelper = $this->getMock('\ZipMoney\ZipMoneyPayment\Helper\Request',[], [], '', false);
        $this->_logHelper = $this->getMock('\ZipMoney\ZipMoneyPayment\Helper\Logger',[], [], '', false);


        $this->_config = $this->getMock('\ZipMoney\ZipMoneyPayment\Model\Config',['getValue'], [], '', false);

        $this->api  = $this->getMock(
            Config::API_NAMESPACE . Config::API_TYPE_REFUND,
            ['process'],
            [],
            '',
            false
        );

        $this->_model = $this->_helper->getObject(
            'ZipMoney\ZipMoneyPayment\Model\Method\ZipCredit',
            [
                'apiHelper' => $this->_apiHelper,
                'orderFactory' => $this->_orderFactory,
                'creditMemoHelper' => $this->_creditMemoHelper,
                'requestHelper' => $this->_payloadHelper,
                'logHelper' => $this->_logHelper,
                'config' => $this->_config
            ]
        );

    } 


    public function testRefund()
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

        $order->method('getIncrementId')
              ->willReturn(1222);
            
        $order->method('getGrandTotal')
              ->willReturn(100);
        
        $order->method('loadByIncrementId')
              ->will($this->returnSelf());

        $order->method('setStatus')
              ->will($this->returnSelf());

        $this->_orderFactory->method('create')
                            ->willReturn($order);
      
  
        $creditmemo = $this->getMock(
            'Magento\Sales\Model\Order\Creditmemo',
            [],
            [],
            '',
            false
        ); 
        
        $creditmemo->method('genRefundReference')
              ->willReturn("asasasasas");
  
        $paymentModel = $this->getMock(
            'Magento\Sales\Model\Order\Payment',
            [
                '__wakeup',
                'getBaseCurrency',
                'getOrder',
                'getIsTransactionPending',
                'addStatusHistoryComment',
                'addTransactionCommentsToOrder'
            ],
            [],
            '',
            false        
            );

        $paymentModel->method('getOrder')
              ->willReturn($order);

        $paymentModel->method('getCreditmemo')
              ->willReturn($creditmemo);

        $response = $this->getMock(
            'zipMoney\\Response',
            [],
            [],
            '',
            false
        );

        $resObj = new \stdClass();

        $resObj->status = "refunded";
        $resObj->txn_id = 11112;
        
        $response->method('toObject')
            ->willReturn($resObj); 

        $response->method('isSuccess')
            ->willReturn(true); 
        
        $this->api->expects($this->any())
                    ->method('process')
                    ->willReturn($response);

        $this->assertTrue($this->_model->refund($paymentModel,20));        
        $this->assertEquals($paymentModel->getStatus(), \ZipMoney\ZipMoneyPayment\Model\Method\ZipCredit::STATUS_VOID);
        
    }

}
