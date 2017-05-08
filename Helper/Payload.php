<?php
namespace ZipMoney\ZipMoneyPayment\Helper;
use \Magento\Checkout\Model\Type\Onepage;

use \zipMoney\Model\CreateCheckoutRequest as CheckoutRequest;
use \zipMoney\Model\CreateChargeRequest as ChargeRequest;
use \zipMoney\Model\CreateRefundRequest as RefundRequest;
use \zipMoney\Model\CaptureChargeRequest;
use \zipMoney\Model\Shopper;
use \zipMoney\Model\CheckoutOrder;
use \zipMoney\Model\ChargeOrder;
use \zipMoney\Model\Authority;
use \zipMoney\Model\OrderShipping;
use \zipMoney\Model\OrderShippingTracking;
use \zipMoney\Model\Address;
use \zipMoney\Model\OrderItem;
use \zipMoney\Model\ShopperStatistics;
use \zipMoney\Model\Metadata;
use \zipMoney\Model\CheckoutConfiguration;

class Payload extends \Magento\Framework\App\Helper\AbstractHelper
{   


  /**
   * @var \Magento\Customer\Model\CustomerFactory
   */
  protected $_customerFactory;
  
  /**
   * @var \Magento\Catalog\Model\ProductFactory
   */
  protected $_productFactory;

  /**
   * @var \Magento\Catalog\Helper\Image
   */
  protected $_imageHelper;
  
  /**
   * @var \Magento\Catalog\Model\CategoryFactory
   */
  protected $_categoryFactory;
  
  /**
   * @var \Magento\Sales\Model\ResourceModel\Order\CollectionFactory
   */
  protected $_orderCollectionFactory;
  
  /**
   * @var \Magento\Customer\Model\Session
   */
  protected $_customerSession;
  
  /**
   * @var \Magento\Customer\Model\Logger
   */
  protected $_customerLogger;

 
  protected $_apiRequest;
  
  /**
   * @var \ZipMoney\ZipMoneyPayment\Model\StoreScope
   */
  protected $_storeScope;
  
  /**
   * @var \Magento\Store\Model\StoreManagerInterface
   */
  protected $_storeManager;
  
  /**
   * @var \Magento\Framework\App\Request\Http
   */
  protected $_request;
  
  /**
   * @var \Magento\Framework\UrlInterface
   */
  protected $_url;
  
  /**
   * @var \ZipMoney\ZipMoneyPayment\Model\Config
   */
  protected $_config;

  /**
   * @var \Magento\Sales\Model\ResourceModel\Order\Payment\Transaction\Collection
   */
  protected $_transactionCollection;

  /**
   * @var Mage_Customer_Model_Session
   */
  protected $_quote;

  /**
   * @var Mage_Customer_Model_Session
   */
  protected $_order;

  protected $_quoteFactory;
  protected $_urlBuilder;

  public function __construct(        
      \Magento\Framework\App\Helper\Context $context,        
      \Magento\Customer\Model\CustomerFactory $customerFactory,
      \Magento\Catalog\Model\ProductFactory $productFactory,
      \Magento\Catalog\Model\CategoryFactory $categoryFactory,
      \Magento\Catalog\Helper\Image $imageHelper,
      \Magento\Sales\Model\ResourceModel\Order\CollectionFactory $orderCollectionFactory,
      \Magento\Customer\Model\Session $customerSession,
      \Magento\Customer\Model\Logger $customerLogger,
      \Magento\Store\Model\StoreManagerInterface $storeManager,
      \Magento\Framework\App\Request\Http $request, 
      \Magento\Quote\Model\QuoteFactory $quoteFactory, 
      \Magento\Framework\UrlInterface $urlBuilder,     
      \ZipMoney\ZipMoneyPayment\Model\StoreScope $storeScope,
      \ZipMoney\ZipMoneyPayment\Model\Config $config,    
      \Magento\Sales\Model\ResourceModel\Order\Payment\Transaction\Collection $transactionCollection,
      \ZipMoney\ZipMoneyPayment\Helper\Logger $logger

  ) {
    parent::__construct($context);

    $this->_customerFactory = $customerFactory;
    $this->_productFactory = $productFactory;
    $this->_categoryFactory = $categoryFactory;
    $this->_imageHelper = $imageHelper;
    $this->_orderCollectionFactory = $orderCollectionFactory;
    $this->_customerSession = $customerSession;
    $this->_customerLogger = $customerLogger;
    $this->_storeScope = $storeScope;
    $this->_storeManager = $storeManager;
    $this->_request = $request;
    $this->_url = $context->getUrlBuilder();
    $this->_config = $config;
    $this->_transactionCollection = $transactionCollection;
    $this->_logger = $logger;
    $this->_quoteFactory = $quoteFactory;
    $this->_urlBuilder = $urlBuilder;
  }
  
  public function setQuote($quote)
  {
    if($quote){
      $this->_quote = $quote;
    }
    return $this;
  }

  public function getQuote()
  {
    if($this->_quote){      
      $this->_order = null;
      return $this->_quote;
    }

    return $this->_quote;
  }

  public function setOrder($order)
  { 
    if($order){
      $this->_quote = null;
      $this->_order = $order;
    }
    return $this;
  }

  public function getOrder()
  {
    if($this->_order){
      return $this->_order;
    } 
    return null;
  }

  public function getCheckoutPayload($quote)
  {
    $checkoutReq = new CheckoutRequest();

    $this->setQuote($quote);

    $checkoutReq->setType("standard")
                ->setShopper($this->getShopper())
                ->setOrder($this->getOrderDetails(new CheckoutOrder))
                ->setMetadata($this->getMetadata())
                ->setConfig($this->getCheckoutConfiguration());

              
    return $checkoutReq;
  }


  public function getChargePayload($order)
  {
    $chargeReq = new ChargeRequest();

    $this->setOrder($order);

    $order = $this->getOrder();

    $grand_total = $order->getGrandTotal() ? $order->getGrandTotal() : 0;
    $currency = $order->getOrderCurrencyCode() ? $order->getOrderCurrencyCode() : null;

    $chargeReq->setAmount((float)$grand_total)
              ->setCurrency($currency)
              ->setOrder($this->getOrderDetails(new ChargeOrder))
              ->setMetadata($this->getMetadata())
              ->setCapture($this->_config->isCharge())
              ->setAuthority($this->getAuthority());

    return $chargeReq;
  }

  public function getRefundPayload($order, $amount, $reason )
  {
    $chargeReq = new RefundRequest();

    $this->setOrder($order);

    $currency = $order->getOrderCurrencyCode() ? $order->getOrderCurrencyCode() : null;

    $chargeReq->setAmount((float)$amount)
              ->setReason($reason)
              ->setChargeId($order->getPayment()->getZipmoneyChargeId())
              ->setMetadata($this->getMetadata());

    return $chargeReq;
  }


  public function getCapturePayload($order, $amount)
  {
    $captureChargeReq = new CaptureChargeRequest();

    $this->setOrder($order);

    $order = $this->getOrder();

    $captureChargeReq->setAmount((float)$amount);

    return $captureChargeReq;
  }


  public function getCaptureCancelPayload($order, $amount)
  {
    $captureChargeReq = new CaptureChargeRequest();

    $this->setOrder($order);

    $order = $this->getOrder();

    return $captureChargeReq;
  }

  public function getShopper()
  {
    $customer = null;
    $shopper = new Shopper;
    if($quote = $this->getQuote()){
      $checkoutMethod = $quote->getCheckoutMethod();

      if ($checkoutMethod == Onepage::METHOD_REGISTER || 
          $checkoutMethod == Onepage::METHOD_GUEST) {
        $shopper = $this->getOrderOrQuoteCustomer(new Shopper, $quote);// get shopper data from quote
      } else {
        $customer = $this->_customerFactory->create()->load($quote->getCustomerId());
      }
      $billing_address = $quote->getBillingAddress();
    } else if($order = $this->getOrder()){
      if ($order->getCustomerIsGuest()) {
        $shopper = $this->getOrderOrQuoteCustomer(new Shopper, $order);// get shopper data from order
      } else {
        $customer = $this->_customerFactory->create()->load($order->getCustomerId());
      }  
      $billing_address = $order->getBillingAddress();
    } else {
      return null;
    }

    if(isset($customer) && $customer->getId()) {
      $shopper = $this->getCustomer(new Shopper, $customer);
    }

    if($billing_address){
      if($address = $this->_getAddress($billing_address)){
        $shopper->setBillingAddress($address);
      }
    }
    
    return $shopper;
  }

  public function getShippingDetails()
  {    
    $shipping = new OrderShipping;

    if($this->getQuote()){
      $shipping_address = $this->getQuote()->getShippingAddress();
    } else if($this->getOrder()) {
      $shipping_address = $this->getOrder()->getShippingAddress();

      if($shipping_address){
        if( $shipping_method = $shipping_address->getShippingMethod()){    
          $tracking = new OrderShippingTracking;
          $tracking->setNumber($this->getTrackingNumbers())
                   ->setCarrier($shipping_method);

          $shipping->setTracking($tracking);         
        }
      }
    }
    
    if($shipping_address){      
      if($address = $this->_getAddress($shipping_address)){     
        $shipping->setPickup(false)
                 ->setAddress($address);
      }  
    } else {        
      $shipping->setPickup(true);
    }

    return $shipping;
  }

  public function getOrderDetails($reqOrder)
  {
    $reference = 0;
    $cart_reference = 0;

    if($quote = $this->getQuote()){
      $shipping_address = $quote->getShippingAddress();
      $reference = $quote->getReservedOrderId() ? $quote->getReservedOrderId() : '0';
      $cart_reference = $quote->getId();
      $shipping_amount = $shipping_address ? $shipping_address->getShippingInclTax():0.00;
      $discount_amount = $shipping_address ? $shipping_address->getDiscountAmount():0.00;
      $tax_amount = $shipping_address ? $shipping_address->getTaxAmount():0.00;
      $grand_total = $quote->getGrandTotal() ? $quote->getGrandTotal() : 0.00;
      $currency = $quote->getQuoteCurrencyCode() ? $quote->getQuoteCurrencyCode() : null;
    } else if($order = $this->getOrder()){
      $reference = $order->getIncrementId() ? $order->getIncrementId() : '0';
      $shipping_amount = $order->getShippingAmount() ? $order->getShippingAmount()  + $order->getShippingTaxAmount() : 0;
      $discount_amount = $order->getDiscountAmount() ? $order->getDiscountAmount() : 0;
      $tax_amount = $order->getTaxAmount() ? $order->getTaxAmount() : 0;
     }
  
    $orderItems = $this->getOrderItems();
    

    // Discount Item
    if($discount_amount <  0){
      $discountItem = new OrderItem;
      $discountItem->setName("Discount");
      $discountItem->setAmount((float)$discount_amount);      
      $discountItem->setQuantity(1);      
      $discountItem->setType("discount");
      $orderItems[] = $discountItem;
    }

    // Shipping Item
    if($shipping_amount > 0){
      $shippingItem = new OrderItem;      
      $shippingItem->setName("Shipping");
      $shippingItem->setAmount((float)$shipping_amount);
      $shippingItem->setType("shipping");      
      $shippingItem->setQuantity(1);      
      $orderItems[] = $shippingItem;
    }

    if(isset($grand_total) && $quote)
      $reqOrder->setAmount($grand_total);
    
    if(isset($currency) && $quote)
       $reqOrder->setCurrency($currency);

    if($cart_reference)
     $reqOrder->setCartReference((string)$cart_reference);

    $reqOrder->setReference($reference)
            ->setShipping($this->getShippingDetails())
            ->setItems($orderItems);


    return $reqOrder;      
  }

  public function getOrderItems()
  {

    if($quote = $this->getQuote()){
      $items = $quote->getAllItems();
      $storeId   = $quote->getStoreId();
    } else if($order = $this->getOrder()){
      $items = $order->getAllItems();      
      $storeId = $order->getStoreId();
    }

    $itemsArray = array();

    /** @var Mage_Sales_Model_Order_Item $oItem */
    foreach($items as $item) {
       
        if($item->getParentItemId()) {
          continue;   // Only sends parent items to zipMoney
        }
        
        $orderItem = new OrderItem;
        
        if ($item->getDescription()) {
          $description = $item->getDescription();
        } else {
          $description = $this->_getProductShortDescription($item, $storeId);
        }

        if($quote){
          $qty = $item->getQty();
        } else if($order){
          $qty = $item->getQtyOrdered();
        }
        

        
        //print_r($item->getData());


        $orderItem->setName($item->getName())
                  ->setAmount($item->getPriceInclTax() ? (float)$item->getPriceInclTax() : 0.00)
                  ->setReference((string)$item->getId())
                  ->setDescription($description)
                  ->setQuantity(round($qty))
                  ->setType("sku")
                  ->setImageUri($this->_getProductImage($item))
                  ->setItemUri($item->getProduct()->getProductUrl())
                  ->setProductCode($item->getSku());  
        $itemsArray[] = $orderItem;
    }



   return $itemsArray;       
  }


  public function getMetadata()
  { 
    $metadata = new Metadata;
  
    return $metadata;
  }

  public function getAuthority()
  { 

    $quoteId = $this->getOrder()->getQuoteId();

    $quote = $this->_quoteFactory->create()->load($quoteId);
    $checkout_id = $quote->getZipmoneyCid();

    $authority = new Authority;
    $authority->setType('checkout_id')
              ->setValue($checkout_id);
  
    return $authority;
  }

  public function getCheckoutConfiguration()
  {
    $checkout_config = new CheckoutConfiguration();
    $redirect_url = $this->_urlBuilder->getUrl('zipmoneypayment/complete', array('_secure' => true));

    $checkout_config->setRedirectUri($redirect_url);

   return $checkout_config;

  }
  /**
   * Get customer data for shopper section in json from existing quote if the customer does not exist
   *
   * @return array|null
   */
  public function getOrderOrQuoteCustomer($shopper,$order_or_quote)
  {
    if(!$order_or_quote) {
      return null;
    }
   
    // $shopper->setFirstName($order_or_quote->getCustomerFirstname())
    //         ->setLastName($order_or_quote->getCustomerLastname())
    //         ->setEmail($order_or_quote->getCustomerEmail());
   
    $billing_address = $order_or_quote->getBillingAddress();

    $shopper->setFirstName($billing_address->getFirstname())
            ->setLastName($billing_address->getLastname())
            ->setEmail($billing_address->getEmail());

    // if ($order_or_quote->getCustomerGender()) {      
    //   $shopper->setGender($this->_getGenderText($order_or_quote->getCustomerGender()));
    // }

    // if ($order_or_quote->getCustomerDob()) {      
    //   $shopper->setBirthDate($order_or_quote->getCustomerDob());
    // }

    // if ($order_or_quote->getCustomerPrefix()) {
    //   $shopper->setTitle($order_or_quote->getCustomerPrefix());
    // }
    
    if ($billing_address->getPrefix()) {
      $shopper->setTitle($billing_address->getPrefix());
    }
    
    if ($phone = $billing_address->getTelephone()) {      
      $shopper->setPhone($phone);
    }
               
    return $shopper;
  }


  /**
   * Get data for consumer section in json from existing customer
   *
   * @param $customer
   * @return array|null
   */
  public function getCustomer($shopper, $customer)
  {
    if(!$customer || !$customer->getId()) {
      return null;
    }

    $logCustomer = Mage::getModel('log/customer')->loadByCustomer($customer);
    $customerData = Array();


    if(Mage::helper('customer')->isLoggedIn() || $customer->getId()) {
        // get customer merchant history
      $orderCollection = Mage::getModel('sales/order')->getCollection()
            ->addFieldToFilter('customer_id', array('eq' => array($customer->getId())))
            ->addFieldToFilter('state', array(
                array('eq' => Mage_Sales_Model_Order::STATE_COMPLETE),
                array('eq' => Mage_Sales_Model_Order::STATE_CLOSED)
            ));
      
      $lifetimeSalesAmount           = 0;        // total amount of complete orders
      $maximumSaleValue              = 0;        // Maximum single order amount among complete orders
      $lifetimeSalesRefundedAmount   = 0;        // Total refunded amount (of closed orders)
      $averageSaleValue              = 0;        // Average order amount
      $orderNum                      = 0;        // Total number of orders
      $declinedBefore                = false;    // the number of declined payments
      $chargeBackBefore              = false;    // any payments that have been charged back by their bank or card provider.
                                                //  A charge back is when a customer has said they did not make the payment, and the bank forces a refund of the amount
      foreach ($orderCollection AS $order) {
        if ($order->getState() == Mage_Sales_Model_Order::STATE_COMPLETE) {
            $orderNum++;
            $lifetimeSalesAmount += $order->getGrandTotal();
            if ($oOrder->getGrandTotal() > $maximumSaleValue) {
                $maximumSaleValue = $order->getGrandTotal();
            }
        } else if ($order->getState() == Mage_Sales_Model_Order::STATE_CLOSED) {
            $lifetimeSalesRefundedAmount += $order->getGrandTotal();
        }
      }

      if ($orderNum > 0) {
        $averageSaleValue = (float)round($lifetimeSalesAmount / $orderNum, 2);
      }

      if ($customer->getGender()) {
        $shopper->setGender($this->_getGenderText($customer->getGender()));
      }

      if ($customer->getDob()) {
        $shopper->setBirthDate($customer->getDob());
      }

      foreach ($customer->getAddresses() as $address) {
        if ($address->getTelephone()) {
          $shopper->setPhone($address->getTelephone());
          break;
        }
      }

      if ($customer->getPrefix()) {
        $shopper->setTitle($customer->getPrefix());
      }

      $shopper->setEmail($customer->getEmail());
      $shopper->setFirstName($customer->getFirstname());
      $shopper->setLastName($customer->getLastname());
      
      $statistics = new ShopperStatistics;

      $statistics->setAccountCreated($customer->getCreatedAt())
               ->setSalesTotalCount($lifetimeSalesAmount)
               ->setSalesAvgAmount($averageSaleValue)
               ->setSalesMaxAmount($maximumSaleValue)
               ->setRefundsTotalAmount($lifetimeSalesRefundedAmount)
               ->setPreviousChargeback($chargeBackBefore)
               ->setCurrency(Mage::app()->getStore()->getCurrentCurrencyCode());

      if ($logCustomer->getLoginAtTimestamp()) {
        $statistics->setLastLogin(date('Y-m-d H:i:s', $logCustomer->getLoginAtTimestamp()));
      }      

      $shopper->setStatistics($statistics);
    }

    return $shopper;
  }


  /**
   * Get data for shipping_address/billing_address section in json from quote_address/order_address which depends on whether the quote is converted to order.
   *
   * @param $address
   * @param $bShippingRates
   * @return array|null
   */
  protected function _getAddress($address)
  {
    if(!$address) {
      return null;
    }
    // print_r($address->getData());
     $this->_logger->debug($address->getQuoteId());

    if(!$address->getStreet1()
        || !$address->getCity()
        || !$address->getCountryId()
        || !$address->getPostcode()
    ) {
      return null;
    }

    $reqAddress = new Address;

    if($address && $address->getId()) {
      $reqAddress->setFirstName($address->getFirstname());
      $reqAddress->setLastName($address->getLastname());
      $reqAddress->setLine1($address->getStreet1());
      $reqAddress->setLine2($address->getStreet2());
      $reqAddress->setCountry($address->getCountryId());
      $reqAddress->setPostalCode($address->getPostcode());
      $reqAddress->setCity($address->getCity());

     //$this->_logger->debug($address->getRegion());

      /**
       * If region_id is null, the state is saved in region directly, so the state can be got from region.
       * If region_id is a valid id, the state should be got by getRegionCode.
       */
      if ($address->getRegionId()) {
        $reqAddress->setState($address->getRegionCode());
      } else {              
        $reqAddress->setState($address->getRegion());
      }
      return $reqAddress;
    }

    return null;
  }


  protected function _getGenderText($gender)
  {
      $genderText = Mage::getModel('customer/customer')->getResource()
          ->getAttribute('gender')
          ->getSource()
          ->getOptionText($gender);
      return $genderText;
  }

  public function getChildProduct($item)
  {
    if ($option = $item->getOptionByCode('simple_product')) {
        return $option->getProduct();
    }
    return $item->getProduct();
  }

  protected function _getProductImage($item)
  {
    $imageUrl = '';
    try {
      $product = $this->getChildProduct($item);
      if (!$product || !$product->getData('thumbnail')
          || ($product->getData('thumbnail') == 'no_selection')
          //|| (Mage::getStoreConfig("checkout/cart/configurable_product_image") == 'parent')
          ) {
          $product =  $item->getProduct();
      }           
      $imageUrl = (string)$this->_imageHelper->init($product, 'thumbnail')->getUrl();
    } catch (Exception $e) {
      $this->_logger->warn($this->__('An error occurred during getting item image for product ' . $product->getId() . '.'));
      $this->_logger->error($e->getMessage());
      $this->_logger->debug($e->getTraceAsString());
    }
    return $imageUrl;
  }

  private function _getProductShortDescription($item, $storeId)
  {
    $product = $this->getChildProduct($item);
    
    if (!$product) {
      $product = $item->getProduct();
        
      $description = $product->getShortDescription();

      if (!$description) {
        $description = $product->getResource()->getAttributeRawValue($product->getId(), 'short_description', $storeId);
      } 
      return $description;
    }    
    $description = $product->getShortDescription();
    if (!$description) {
      $description = $product->getResource()->getAttributeRawValue($product->getId(), 'short_description', $storeId);
    }  
    return $description;
  }


}