<?php
namespace ZipMoney\ZipMoneyPayment\Helper;

use \Magento\Framework\App\Helper\AbstractHelper;
use \Magento\Checkout\Model\Type\Onepage;
use \Magento\Sales\Model\Order;
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

/**
 * @category  Zipmoney
 * @package   Zipmoney_ZipmoneyPayment
 * @author    Zip Plugin Team <integration@zip.co>
 * @copyright 2019 Zip Co.
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 * @link      https://www.zip.co/
 */
class Payload extends AbstractHelper
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

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $_storeManager;

    /**
     * @var \Magento\Framework\App\Request\Http
     */
    protected $_request;

    /**
     * @var \ZipMoney\ZipMoneyPayment\Model\Config
     */
    protected $_config;

    /**
     * @var \Magento\Sales\Model\ResourceModel\Order\Payment\Transaction\Collection
     */
    protected $_transactionCollection;

    /**
     * @var \Magento\Quote\Model\Quote
     */
    protected $_quote;

    /**
     * @var \Magento\Sales\Model\Order
     */
    protected $_order;

    /**
     * @var \Magento\Quote\Model\QuoteFactory
     */
    protected $_quoteFactory;

    /**
     * @var \Magento\Framework\UrlInterface
     */
    protected $_urlBuilder;

    /**
     * @var \ZipMoney\ZipMoneyPayment\Helper\Data
     */
    protected $_helper;

    /**
     * @var bool
     */
    protected $_isVirtual  = true;

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
        \ZipMoney\ZipMoneyPayment\Model\Config $config,
        \Magento\Sales\Model\ResourceModel\Order\Payment\Transaction\Collection $transactionCollection,
        \ZipMoney\ZipMoneyPayment\Helper\Logger $logger,
        \ZipMoney\ZipMoneyPayment\Helper\Data $helper
    ) {
        parent::__construct($context);

        $this->_customerFactory = $customerFactory;
        $this->_productFactory = $productFactory;
        $this->_categoryFactory = $categoryFactory;
        $this->_imageHelper = $imageHelper;
        $this->_orderCollectionFactory = $orderCollectionFactory;
        $this->_customerSession = $customerSession;
        $this->_customerLogger = $customerLogger;
        $this->_storeManager = $storeManager;
        $this->_request = $request;
        $this->_config = $config;
        $this->_transactionCollection = $transactionCollection;
        $this->_logger = $logger;
        $this->_quoteFactory = $quoteFactory;
        $this->_urlBuilder = $context->getUrlBuilder();
        $this->_helper = $helper;
    }

    /**
     * Sets checkout quote object
     *
     * @param \Magento\Quote\Model\Quote $quote
     * @return \ZipMoney\ZipMoneyPayment\Helper\Payload
     */
    public function setQuote($quote)
    {
        if ($quote) {
            $this->_quote = $quote;
        }
        return $this;
    }

    /**
     * Sets checkout quote object
     *
     * @return \Magento\Quote\Model\Quote $quote
     */
    public function getQuote()
    {
        if ($this->_quote) {
            $this->_order = null;
            return $this->_quote;
        }

        return $this->_quote;
    }

    /**
     * Sets checkout quote object
     *
     * @param \Magento\Sales\Model\Order $order
     * @return \ZipMoney\ZipMoneyPayment\Helper\Payload
     */
    public function setOrder($order)
    {
        if ($order) {
            $this->_quote = null;
            $this->_order = $order;
        }
        return $this;
    }

    /**
     * Sets checkout quote object
     *
     * @return \Magento\Sales\Model\Order $order
     */
    public function getOrder()
    {
        if ($this->_order) {
            return $this->_order;
        }
        return null;
    }

    /**
     * Prepares the checkout payload
     *
     * @param \Magento\Quote\Model\Quote $quote
     * @return \zipMoney\Model\CreateCheckoutRequest
     */
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

    /**
     * Prepares the charge payload
     *
     * @param \Magento\Sales\Model\Order $order
     * @return \zipMoney\Model\CreateChargeRequest
     */
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

    /**
     * Prepares the refund payload
     *
     * @param \Magento\Sales\Model\Order $order
     * @param float $amount
     * @param string $reason
     * @return \zipMoney\Model\CreateRefundRequest
     */
    public function getRefundPayload($order, $amount, $reason)
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

    /**
     * Prepares the capture charge payload
     *
     * @param \Magento\Sales\Model\Order $order
     * @param float $amount
     * @return \zipMoney\Model\CaptureChargeRequest
     */
    public function getCapturePayload($order, $amount)
    {
        $captureChargeReq = new CaptureChargeRequest();

        $this->setOrder($order);

        $order = $this->getOrder();

        $captureChargeReq->setAmount((float)$amount);

        return $captureChargeReq;
    }

    /**
     * Prepares the shopper
     *
     * @return \zipMoney\Model\Shopper
     */
    public function getShopper()
    {
        $customer = null;
        $shopper = new Shopper;
        if ($quote = $this->getQuote()) {
            $checkoutMethod = $quote->getCheckoutMethod();

            if ($checkoutMethod == Onepage::METHOD_REGISTER || $checkoutMethod == Onepage::METHOD_GUEST) {
                $shopper = $this->getOrderOrQuoteCustomer(new Shopper, $quote); // get shopper data from quote
            } else {
                $customer = $this->_customerFactory->create()->load($quote->getCustomerId());
            }
            $billing_address = $quote->getBillingAddress();
        } elseif ($order = $this->getOrder()) {
            if ($order->getCustomerIsGuest()) {
                $shopper = $this->getOrderOrQuoteCustomer(new Shopper, $order); // get shopper data from order
            } else {
                $customer = $this->_customerFactory->create()->load($order->getCustomerId());
            }
            $billing_address = $order->getBillingAddress();
        } else {
            return null;
        }

        if (isset($customer) && $customer->getId()) {
            $shopper = $this->getCustomer(new Shopper, $customer);
        }

        if ($billing_address) {
            if ($address = $this->_getAddress($billing_address)) {
                $shopper->setBillingAddress($address);
            }
        }

        return $shopper;
    }

    /**
     * Prepares the shipping details
     *
     * @return \zipMoney\Model\OrderShipping
     */
    public function getShippingDetails()
    {
        $shipping = new OrderShipping;

        if ($this->_isVirtual) {
            $shipping->setPickup(true);
            return $shipping;
        }

        if ($this->getQuote()) {
            $shipping_address = $this->getQuote()->getShippingAddress();
        } elseif ($this->getOrder()) {
            $shipping_address = $this->getOrder()->getShippingAddress();

            if ($shipping_address) {
                if ($shipping_method = $shipping_address->getShippingMethod()) {
                    $tracking = new OrderShippingTracking;
                    $tracking->setNumber($this->getTrackingNumbers())
                        ->setCarrier($shipping_method);

                    $shipping->setTracking($tracking);
                }
            }
        }

        if ($shipping_address) {
            if ($address = $this->_getAddress($shipping_address)) {
                $shipping->setPickup(false)
                    ->setAddress($address);
            }
        }

        return $shipping;
    }

    /**
     * Prepares the Order details
     *
     * @param mixed \zipMoney\Model\CheckoutOrder $reqOrder | \zipMoney\Model\ChargeOrder $reqOrder
     * @return mixed \zipMoney\Model\CheckoutOrder | \zipMoney\Model\ChargeOrder
     */
    public function getOrderDetails($reqOrder)
    {
        $reference = 0;
        $cart_reference = 0;
        $orderItems = $this->getOrderItems();
        $totalItemPrice = 0.0;
        foreach ($orderItems as $item) {
            $totalItemPrice += $item->getAmount() * $item->getQuantity();
        }
        if ($quote = $this->getQuote()) {
            $address = $quote->getShippingAddress();
            //If cart has only virtual items
            if ($quote->getIsVirtual()) {
                $address = $quote->getBillingAddress();
            }
            $reference = $quote->getReservedOrderId() ? $quote->getReservedOrderId() : 'unknown';
            $cart_reference = $quote->getId();
            $shipping_amount = $address ? $address->getShippingInclTax() : 0.0;
            $discount_amount = $address ? $address->getDiscountAmount() : 0.0;
            $tax_amount = $address ? $address->getTaxAmount() : 0.0;
            $grand_total = $quote->getGrandTotal() ?: 0.0;
            $currency = $quote->getQuoteCurrencyCode() ?: null;
            $gift_cards_amount = $quote->getGiftCardsAmount() ?: 0.0;
        } elseif ($order = $this->getOrder()) {
            $reference = $order->getIncrementId() ?: 'unknown';
            $shipping_amount = $order->getShippingInclTax() ?: 0.0;
            $discount_amount = $order->getDiscountAmount() ?: 0.0;
            $tax_amount = $order->getTaxAmount() ?: 0.0;
            $gift_cards_amount = $order->getGiftCardsAmount() ?: 0.0;
            $grand_total = $order->getGrandTotal() ?: 0.0;
        }

        $this->_logger->debug("Gift Card Amount:- " . $gift_cards_amount);

        if ($gift_cards_amount) {
            $discount_amount -= $gift_cards_amount;
        }

        // Discount Item
        if ($discount_amount <  0) {
            $discountItem = new OrderItem;
            $discountItem->setName("Discount");
            $discountItem->setAmount((float)$discount_amount);
            $discountItem->setQuantity(1);
            $discountItem->setType("discount");
            $orderItems[] = $discountItem;
        }

        // Shipping Item
        if ($shipping_amount > 0) {
            $shippingItem = new OrderItem;
            $shippingItem->setName("Shipping");
            $shippingItem->setAmount((float)$shipping_amount);
            $shippingItem->setType("shipping");
            $shippingItem->setQuantity(1);
            $orderItems[] = $shippingItem;
        }

        //re-calculated and support all kind of discounts or surcharges
        $this->_logger->debug($grand_total . '-' . $discount_amount . '-' . $shipping_amount . '-' . $totalItemPrice);
        $balance = round($grand_total - $discount_amount - $shipping_amount - $totalItemPrice, 2);
        if ($balance >= 0.01) {
            $fee = new OrderItem;
            $fee->setName("Fee");
            $fee->setAmount($balance);
            $fee->setType("shipping");
            $fee->setQuantity(1);
            $orderItems[] = $fee;
        } elseif ($balance <= -0.01) {
            $other = new OrderItem;
            $other->setName("other discount");
            $other->setAmount($balance);
            $other->setType("discount");
            $other->setQuantity(1);
            $orderItems[] = $other;
        }

        if (isset($grand_total) && $quote) {
            $reqOrder->setAmount($grand_total);
        }

        if (isset($currency) && $quote) {
            $reqOrder->setCurrency($currency);
        }

        if ($cart_reference) {
            $reqOrder->setCartReference((string)$cart_reference);
        }

        $reqOrder->setReference($reference)
            ->setShipping($this->getShippingDetails())
            ->setItems($orderItems);

        return $reqOrder;
    }

    /**
     * Prepares the Order items
     *
     * @return \zipMoney\Model\OrderItem[]
     */
    public function getOrderItems()
    {
        if ($quote = $this->getQuote()) {
            $items = $quote->getAllItems();
            $storeId   = $quote->getStoreId();
        } elseif ($order = $this->getOrder()) {
            $items = $order->getAllItems();
            $storeId = $order->getStoreId();
        }

        $itemsArray = array();

        // @var Mage_Sales_Model_Order_Item $oItem
        foreach ($items as $item) {
            if (!$item->getProduct()->getIsVirtual()) {
                $this->_isVirtual = false;
            }

            if ($item->getParentItemId()) {
                continue;   // Only sends parent items to zipMoney
            }

            $orderItem = new OrderItem;

            $description = $item->getDescription();

            if (!isset($description) || empty($description)) {
                // Check product description
                $description = $this->_getDescription($item, $storeId);
            }

            if ($quote) {
                $qty = $item->getQty();
            } elseif ($order) {
                $qty = $item->getQtyOrdered();
            }
            //API do not handle long SKU exception so we have to handle in plugin
            $sku = (strlen($item->getSku())) > 49 ? substr($item->getSku(), 0, 49) : $item->getSku();

            $orderItem->setName($item->getName())
                ->setAmount($item->getPriceInclTax() ? (float)$item->getPriceInclTax() : 0.00)
                ->setReference((string)$item->getId())
                ->setDescription($description)
                ->setQuantity(round($qty))
                ->setType("sku")
                ->setImageUri($this->_getProductImage($item))
                ->setItemUri($item->getProduct()->getProductUrl())
                ->setProductCode($sku);
            $itemsArray[] = $orderItem;
        }

        $this->_logger->debug(sprintf("Shipping Required:- %s", !$this->_isVirtual ? "Yes" : "No"));

        return $itemsArray;
    }

    /**
     * Returns the metadata
     *
     * @return \zipMoney\Model\Metadata
     */
    public function getMetadata()
    {
        $metadata = new Metadata;

        return $metadata;
    }

    /**
     * Returns the authority
     *
     * @return \zipMoney\Model\Authority
     */
    public function getAuthority()
    {
        $quoteId = $this->getOrder()->getQuoteId();

        $quote = $this->_quoteFactory->create()->load($quoteId);
        $checkout_id = $quote->getZipmoneyCheckoutId();
        $authority = new Authority;
        $authority->setType('checkout_id')
            ->setValue($checkout_id);

        return $authority;
    }

    /**
     * Returns the checkoutconfiguration
     *
     * @return \zipMoney\Model\CheckoutConfiguration
     */
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
     * @param \zipMoney\Model\Shopper $shopper
     * @param mixed \Magento\Sales\Model\Order | \Magento\Quote\Model\Quote $order_or_quote
     * @return \zipMoney\Model\Shopper
     */
    public function getOrderOrQuoteCustomer($shopper, $order_or_quote)
    {
        if (!$order_or_quote) {
            return null;
        }

        $billing_address = $order_or_quote->getBillingAddress();

        $shopper->setFirstName($billing_address->getFirstname())
            ->setLastName($billing_address->getLastname())
            ->setEmail($billing_address->getEmail());

        if ($billing_address->getPrefix()) {
            $shopper->setTitle($billing_address->getPrefix());
        }

        if ($phone = $billing_address->getTelephone()) {
            //as API do not handle long phone number we have to handle it in plugin
            $phoneNumber = strlen($phone) > 20 ? substr($phone, 0, 20) : $phone;
            $shopper->setPhone($phoneNumber);
        }

        return $shopper;
    }

    /**
     * Get data for customer data
     *
     * @param \zipMoney\Model\Shopper $shopper
     * @param \Magento\Customer\Model\Customer $customer
     * @return \zipMoney\Model\Shopper
     */
    public function getCustomer($shopper, $customer)
    {
        if (!$customer || !$customer->getId()) {
            return null;
        }
        $customerData = array();

        if ($this->_customerSession->isLoggedIn() || $customer->getId()) {
            $orderCollection = $this->_orderCollectionFactory->create($customer->getId())
                ->addFieldToFilter(
                    'state',
                    [
                        ['eq' => Order::STATE_COMPLETE],
                        ['eq' => Order::STATE_CLOSED]
                    ]
                );

            $lifetimeSalesAmount           = 0;        // total amount of complete orders
            $maximumSaleValue              = 0;        // Maximum single order amount among complete orders
            $lifetimeSalesRefundedAmount   = 0;        // Total refunded amount (of closed orders)
            $averageSaleValue              = 0;        // Average order amount
            $orderNum                      = 0;        // Total number of orders
            $declinedBefore                = false;    // the number of declined payments
            $chargeBackBefore              = false;    // any payments that have been charged back.
            //A charge back is when a customer has said they did not make the payment,
            //and the bank forces a refund of the amount
            foreach ($orderCollection as $order) {
                if ($order->getState() == Order::STATE_COMPLETE) {
                    $orderNum++;
                    $lifetimeSalesAmount += $order->getGrandTotal();
                    if ($order->getGrandTotal() > $maximumSaleValue) {
                        $maximumSaleValue = $order->getGrandTotal();
                    }
                } elseif ($order->getState() == Order::STATE_CLOSED) {
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
                if ($phone = $address->getTelephone()) {
                    //as API do not handle long phone number we have to handle it in plugin
                    $phoneNumber = strlen($phone) > 20 ? substr($phone, 0, 20) : $phone;
                    $shopper->setPhone($phoneNumber);
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
                ->setSalesTotalCount((int)$orderNum)
                ->setSalesTotalAmount((float)$lifetimeSalesAmount)
                ->setSalesAvgAmount((float)$averageSaleValue)
                ->setSalesMaxAmount((float)$maximumSaleValue)
                ->setRefundsTotalAmount((float)$lifetimeSalesRefundedAmount)
                ->setPreviousChargeback($chargeBackBefore)
                ->setCurrency($this->_storeManager->getStore()->getCurrentCurrencyCode());

            $lastLoginAt = $this->_customerLogger->get($customer->getId())->getLastLoginAt();

            if ($lastLoginAt) {
                $statistics->setLastLogin($lastLoginAt);
            }

            $shopper->setStatistics($statistics);
        }

        return $shopper;
    }

    /**
     * Gets customer address
     *
     * @param \Magento\Sales\Model\Order\Address $address
     * @return \zipMoney\Model\Address
     */
    protected function _getAddress($address)
    {
        if (!$address) {
            return null;
        }

        if (!$address->getStreet() || !$address->getCity() || !$address->getCountryId() || !$address->getPostcode()) {
            return null;
        }

        $reqAddress = new Address;

        if ($address && ($address->getAddressId() || $address->getEntityId())) {
            $reqAddress->setFirstName($address->getFirstname());
            $reqAddress->setLastName($address->getLastname());
            $street = $address->getStreet();

            if (is_array($street)) {
                if (isset($street[0])) {
                    $reqAddress->setLine1($street[0]);
                }
                if (isset($street[1])) {
                    $reqAddress->setLine1($street[1]);
                }
            } else {
                $reqAddress->setLine1($street);
            }

            $reqAddress->setCountry($address->getCountryId());
            $reqAddress->setPostalCode($address->getPostcode());
            $reqAddress->setCity($address->getCity());

            /**
       * If region_id is null, the state is saved in region directly, so the state can be retrieved from region.
       * If region_id is a valid id, the state should be retrieved by getRegionCode.
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

    /**
     * Gets customer address
     *
     * @param string $gender
     * @return string $genderText
     */
    protected function _getGenderText($gender)
    {
        $genderText = $this->_customerFactory->create()
            ->getAttribute('gender')
            ->getSource()
            ->getOptionText($gender);
        return $genderText;
    }

    /**
     * Returns the child product
     *
     * @param mixed \Magento\Quote\Model\ResourceModel\Quote\Item |  \Magento\Sales\Model\Order\Item $item
     * @return \Magento\Catalog\Model\Product
     */
    public function getChildProduct($item)
    {
        if ($option = $item->getOptionByCode('simple_product')) {
            return $option->getProduct();
        }
        return $item->getProduct();
    }

    /**
     * Returns the child product
     *
     * @param mixed \Magento\Quote\Model\ResourceModel\Quote\Item |  \Magento\Sales\Model\Order\Item $item
     * @return string
     */
    protected function _getProductImage($item)
    {
        $imageUrl = '';
        try {
            //only use visible product do not care type $item must be visible cart product
            $product =  $item->getProduct();
            $imageUrl = (string)$this->_imageHelper->init($product, 'thumbnail')->getUrl();
        } catch (\Exception $e) {
            $this->_logger->warn('An error occurred during getting item image for product ' . $product->getId());
            $this->_logger->error($e->getMessage());
            $this->_logger->debug($e->getTraceAsString());
        }
        return $imageUrl;
    }

    /**
     * Returns the child product
     *
     * @param mixed \Magento\Quote\Model\ResourceModel\Quote\Item |  \Magento\Sales\Model\Order\Item $item
     * @param int $storeId
     * @return string
     */
    private function _getDescription($item, $storeId)
    {
        $product = $this->getChildProduct($item);

        if (!$product) {
            $product = $item->getProduct();
            $description = $this->_getProductDescription($product, $storeId);

            if (!isset($description) || empty($description)) {
                return null;
            }

            return $description;
        }

        $description = $this->_getProductDescription($product, $storeId);

        if (!isset($description) || empty($description)) {
            return null;
        }

        return $description;
    }

    /**
     * Returns the child product
     *
     * @param mixed \Magento\Quote\Model\ResourceModel\Quote\Item |  \Magento\Sales\Model\Order\Item $item
     * @param int $storeId
     * @return string
     */
    private function _getProductDescription($product, $storeId)
    {
        $description = $product->getShortDescription();

        if (!isset($description)) {
            $description = $product->getResource()
                ->getAttributeRawValue($product->getId(), 'short_description', $storeId);
            if (!isset($description)) {
                $description = $product->getDescription();
                if (!isset($description)) {
                    $description = $product->getResource()
                        ->getAttributeRawValue($product->getId(), 'description', $storeId);
                }
            }
        }

        return $description;
    }

    /**
     * Returns the json_encoded string
     *
     * @return string
     */
    public function jsonEncode($object)
    {
        return json_encode(\zipMoney\ObjectSerializer::sanitizeForSerialization($object));
    }
}
