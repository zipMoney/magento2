<?php

namespace ZipMoney\ZipMoneyPayment\Controller\Standard;

use \Magento\Framework\App\Action\Action;
use \Magento\Checkout\Controller\Express\RedirectLoginInterface;

/**
 * @category  Zipmoney
 * @package   Zipmoney_ZipmoneyPayment
 * @author    Sagar Bhandari <sagar.bhandari@zipmoney.com.au>
 * @copyright 2017 zipMoney Payments Pty Ltd.
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 * @link      http://www.zipmoney.com.au/
 */
abstract class AbstractStandard extends Action
{

    const CHECKOUT_ID_KEY = 'id';
    /**
     * Common Route
     *
     * @const
     */
    const ZIPMONEY_STANDARD_ROUTE = "zipmoneypayment/standard";
    /**
     * Error Route
     *
     * @const
     */
    const ZIPMONEY_ERROR_ROUTE = "zipmoneypayment/standard/error";
    /**
     * Config
     *
     * @var \ZipMoney\ZipMoneyPayment\Model\Config
     */
    protected $_config;
    /**
     * @var string
     */
    protected $_api;
    /**
     * @var \Magento\Quote\Model\Quote
     */
    protected $_quote;
    /**
     * Config mode type
     *
     * @var string
     */
    protected $_configType;
    /**
     * Config method type
     *
     * @var string
     */
    protected $_configMethod;
    /**
     * Checkout type
     *
     * @var string
     */
    protected $_checkoutModel = "\ZipMoney\ZipMoneyPayment\Model\Checkout";
    /**
     * Checkout mode type
     *
     * @var string
     */
    protected $_chargeModel = "\ZipMoney\ZipMoneyPayment\Model\Charge";
    /**
     * @var \Magento\Checkout\Model\Session
     */
    protected $_checkoutSession;
    /**
     * @var \Magento\Customer\Model\Session
     */
    protected $_customerSession;
    /**
     * @var \Magento\Sales\Model\OrderFactory
     */
    protected $_orderFactory;
    /**
     * @var \Magento\Quote\Api\CartRepositoryInterface
     */
    protected $_quoteRepository;
    /**
     * @var \Magento\Framework\Url\Helper
     */
    protected $_urlHelper;
    /**
     * @var \Magento\Framework\UrlInterface
     */
    protected $_urlBuilder;
    /**
     * @var \Magento\Customer\Model\Url
     */
    protected $_customerUrl;
    /**
     * @var \ZipMoney\ZipMoneyPayment\Helper\Order
     */
    protected $_orderHelper;
    /**
     * @var \Magento\Framework\Json\Helper\Data
     */
    protected $_jsonHelper;
    /**
     * @var \ZipMoney\ZipMoneyPayment\Helper\Logger
     */
    protected $_logger;
    /**
     * @var \ZipMoney\ZipMoneyPayment\Helper\Data
     */
    protected $_helper;
    /**
     * @var \ZipMoney\ZipMoneyPayment\Model\Standard\
     */
    protected $_checkoutFactory;
    /**
     * @var \ZipMoney\ZipMoneyPayment\Model\Checkout
     */
    protected $_checkout;
    /**
     * @var \ZipMoney\ZipMoneyPayment\Model\Charge
     */
    protected $_charge;
    /**
     * @var \Magento\Quote\Model\ResourceModel\Quote\CollectionFactory
     */
    protected $_quoteCollectionFactory;
    /**
     * @var \Magento\Quote\Model\ResourceModel\Quote\Payment\CollectionFactory
     */
    protected $_quotePaymentCollectionFactory;
    /**
     * @var \Magento\Checkout\Model\PaymentInformationManagement
     */
    protected $_paymentInformationManagement;
    /**
     * @var \Magento\Framework\Message\ManagerInterface
     */
    protected $_messageManager;
    /**
     * @var \Magento\Framework\View\Result\PageFactory
     */
    protected $_pageFactory;

    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Magento\Framework\View\Result\PageFactory $pageFactory,
        \Magento\Checkout\Model\Session $checkoutSession,
        \Magento\Customer\Model\Session $customerSession,
        \Magento\Sales\Model\OrderFactory $orderFactory,
        \Magento\Framework\Url\Helper\Data $urlHelper,
        \Magento\Customer\Model\Url $customerUrl,
        \Magento\Framework\Json\Helper\Data $jsonHelper,
        \Magento\Checkout\Model\PaymentInformationManagement $paymentInformationManagement,
        \Magento\Quote\Api\CartRepositoryInterface $quoteRepository,
        \Magento\Quote\Model\ResourceModel\Quote\CollectionFactory $quoteCollectionFactory,
        \Magento\Quote\Model\ResourceModel\Quote\Payment\CollectionFactory $quotePaymentCollectionFactory,
        \ZipMoney\ZipMoneyPayment\Helper\Logger $logger,
        \ZipMoney\ZipMoneyPayment\Helper\Data $helper,
        \ZipMoney\ZipMoneyPayment\Model\Checkout\Factory $checkoutFactory,
        \ZipMoney\ZipMoneyPayment\Model\Config $config,
        \zipMoney\Api\CheckoutsApi $checkoutsApi
    )
    {

        $this->_pageFactory = $pageFactory;

        $this->_checkoutSession = $checkoutSession;
        $this->_customerSession = $customerSession;
        $this->_orderFactory = $orderFactory;
        $this->_quoteRepository = $quoteRepository;
        $this->_quoteCollectionFactory = $quoteCollectionFactory;
        $this->_quotePaymentCollectionFactory = $quotePaymentCollectionFactory;
        $this->_urlHelper = $urlHelper;
        $this->_urlBuilder = $context->getUrl();
        $this->_customerUrl = $customerUrl;
        $this->_jsonHelper = $jsonHelper;
        $this->_paymentInformationManagement = $paymentInformationManagement;

        $this->_helper = $helper;
        $this->_logger = $logger;
        $this->_checkoutFactory = $checkoutFactory;

        $this->_messageManager = $context->getMessageManager();
        $this->_config = $config;
        $this->_api = $checkoutsApi;
        parent::__construct($context);
    }

    /**
     * Sets quote for the customer.
     *
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function _setCustomerQuote()
    {
        // Retrieve a valid quote
        if ($quote = $this->_retrieveQuote()) {

            // Verify that the customer is a valid customer of the quote
            $this->_verifyCustomerForQuote($quote);
            /* Set the session quote if required.
               Needs to be done after verifying the current customer */
            if ($this->_getCheckoutSession()->getQuoteId() != $quote->getId()) {
                $this->_logger->debug(__("Setting quote to current session"));
                // Set the quote in the current object
                $this->_setQuote($quote);
                // Set the quote in the session
                $this->_getCheckoutSession()->setQuoteId($quote->getId());
            }
            // Make sure the qoute is active
            $this->_activateQuote($quote);
        } else {
            throw new \Magento\Framework\Exception\LocalizedException("Could not retrieve the quote");
        }
    }

    /**
     * Checks if the Session Quote is valid, if not use the db quote.
     *
     * @return \Magento\Quote\Model\Quote
     */
    protected function _retrieveQuote()
    {
        $sessionQuote = $this->_getCheckoutSession()->getQuote();
        $zipMoneyCheckoutId = $this->getRequest()->getParam('checkoutId');
        $use_checkout_api_quote = true;
        if ($sessionQuote) {
            $addtionalPaymentInfo = $sessionQuote->getPayment()->getAdditionalInformation();
            $checkout_id = $addtionalPaymentInfo['zipmoney_checkout_id'];
            if ($checkout_id == $zipMoneyCheckoutId) {
                return $sessionQuote;
            }
            if ($checkout_id == 'au-' . $zipMoneyCheckoutId) {
                return $sessionQuote;
            }
            $this->_logger->error(__("Checkout Id doesnot match with the session quote."));
        } else {
            $this->_logger->error(__("Session Quote doesnot exist."));
        }

        //Retrurn Quote by using checkout get api call
        if ($use_checkout_api_quote) {
            $checkoutApiQuote = $this->_getQuoteByUsingCheckoutApi($zipMoneyCheckoutId);
            if (!$checkoutApiQuote) {
                $this->_logger->warn(__("Quote doesnot exist for the given checkout_id."));
                return false;
            } else {
                $this->_logger->info(__("Loading Quote by using checkout get api call"));
            }
            return $checkoutApiQuote;
        }
    }

    /**
     * @param $zip_checkout_id
     * @return false|\Magento\Framework\DataObject|\Magento\Quote\Model\Quote
     * Retrieve quote details by using zip checkout get api call
     */
    protected function _getQuoteByUsingCheckoutApi($zip_checkout_id)
    {
        // Configure API Credentials
        $apiConfig = \zipMoney\Configuration::getDefaultConfiguration();

        $apiConfig->setApiKey('Authorization', $this->_config->getMerchantPrivateKey())
            ->setApiKeyPrefix('Authorization', 'Bearer')
            ->setEnvironment($this->_config->getEnvironment())
            ->setPlatform("Magento/" . $this->_helper->getMagentoVersion() . "ZipMoney_ZipMoneyPayment/" . $this->_helper->getExtensionVersion());
        $checkout = $this->_api->checkoutsGet($zip_checkout_id);
        if (!isset($checkout[self::CHECKOUT_ID_KEY])) {
            return false;
        }

        $quoteId = $checkout->getOrder()->getCartReference();
        $this->_quote = $this->_quoteCollectionFactory
            ->create()
            ->addFieldToFilter("entity_id", $quoteId)
            ->getFirstItem();
        return $this->_quote;
    }

    /**
     * Checks if the Customer is valid for the quote
     *
     * @param \Magento\Quote\Model\Quote $quote
     */
    protected function _verifyCustomerForQuote($quote)
    {
        $currentCustomer = null;
        $customerSession = $this->_getCustomerSession();

        // Get quote customer id
        $quoteCustomerId = $quote->getCustomerId();

        // Get current logged in customer
        if ($customerSession->isLoggedIn()) {
            $currentCustomer = $customerSession->getCustomer();
        }

        $this->_logger->debug(
            __("Current Customer Id:- %s Quote Customer Id:- %s Quote checkout method:- %s",
                $customerSession->getId(), $quoteCustomerId, $quote->getCheckoutMethod())
        );

        $log_in = false;

        if (isset($currentCustomer)) {
            if ($currentCustomer->getId() != $quoteCustomerId) {
                $customerSession->logout(); // Logout the logged in customer
                $customerSession->renewSession();
            }
        }

    }

    /**
     * Return checkout customer session object
     *
     * @return \Magento\Customer\Model\Session
     */
    protected function _getCustomerSession()
    {
        return $this->_customerSession;
    }

    /**
     * @param $quote
     * @return bool
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    protected function _activateQuote($quote)
    {
        if ($quote && $quote->getId()) {
            if (!$quote->getIsActive()) {
                $orderIncId = $quote->getReservedOrderId();
                if ($orderIncId) {
                    $order = $this->_orderFactory->create()->loadByIncrementId($orderIncId);
                    if ($order && $order->getId()) {
                        throw new \Magento\Framework\Exception\LocalizedException(__('Can not activate the quote. It has already been converted to order.'));
                    }
                }
                $quote->setIsActive(1)
                    ->save();
                $this->_logger->warn(__('Activated quote ' . $quote->getId() . '.'));
                return true;
            }
        }
        return false;
    }

    /**
     * Redirects to the referred page.
     *
     */
    public function referredAction()
    {
        $this->_logger->debug(__('Calling referredAction'));
        try {
            $this->loadLayout()
                ->_initLayoutMessages('checkout/session')
                ->_initLayoutMessages('catalog/session')
                ->_initLayoutMessages('customer/session');
            $this->renderLayout();
            $this->_logger->info(__('Successful to redirect to referred page.'));
        } catch (\Exception $e) {
            $this->_logger->error(json_encode($this->getRequest()->getParams()));
            $this->_logger->error($e->getMessage());
            $this->_getCheckoutSession()->addError($this->__('An error occurred during redirecting to referred page.'));
        }
    }

    /**
     * Redirects to the error page.
     *
     */
    public function errorAction()
    {
        $this->_logger->debug(__('Calling errorAction'));
        try {
            $this->loadLayout()
                ->_initLayoutMessages('checkout/session')
                ->_initLayoutMessages('catalog/session')
                ->_initLayoutMessages('customer/session');
            $this->renderLayout();
            $this->_logger->info(__('Successful to redirect to error page.'));
        } catch (\Exception $e) {
            $this->_logger->error(json_encode($this->getRequest()->getParams()));
            $this->_getCheckoutSession()->addError(__('An error occurred during redirecting to error page.'));
        }
    }

    /**
     * Returns login url parameter for redirect
     *
     * @return string
     */
    public function getLoginUrl()
    {
        return $this->_customerUrl->getLoginUrl();
    }

    /**
     * Return Success  url
     *
     * @return string
     */
    public function getSuccessUrl()
    {
        $url = $this->_urlBuilder->getUrl('checkout/onepage/success');

        return $url;
    }

    /**
     * Return Success  url
     *
     * @return string
     */
    public function getReferredUrl()
    {
        $url = $this->_urlBuilder->getUrl('zipmoneypayment/standard/referred');

        return $url;
    }

    /**
     * Instantiate Checkout Model
     *
     * @return \ZipMoney\ZipMoneyPayment\Model\Checkout
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    protected function _initCheckout()
    {
        $quote = $this->_getQuote();

        if (!$quote->getId()) {
            throw new \Magento\Framework\Exception\LocalizedException(__('Quote doesnot exist'));
        }

        if (!$quote->hasItems() || $quote->getHasError()) {
            $this->getResponse()->setStatusHeader(403, '1.1', 'Forbidden');
            throw new \Magento\Framework\Exception\LocalizedException(__('Unable to initialize the Checkout.'));
        }

        return $this->_checkout = $this->_checkoutFactory
            ->create($this->_checkoutModel, ['data' => ['quote' => $quote]]);
    }

    /**
     * Return checkout quote object
     *
     * @return \Magento\Quote\Model\Quote
     */
    protected function _getQuote()
    {
        if (!$this->_quote) {
            $this->_quote = $this->_getCheckoutSession()->getQuote();
        }
        return $this->_quote;
    }

    /**
     * Sets checkout quote object
     *
     * @return \ZipMoney\ZipMoneyPayment\Controller\Standard\AsbtractStandard
     */
    protected function _setQuote($quote)
    {
        $this->_quote = $quote;

        return $this;
    }

    /**
     * Return checkout session object
     *
     * @return \Magento\Checkout\Model\Session
     */
    protected function _getCheckoutSession()
    {
        return $this->_checkoutSession;
    }

    /**
     * Instantiate Charge Model
     *
     * @return Zipmoney_ZipmoneyPayment_Model_Standard_Checkout
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    protected function _initCharge()
    {
        $quote = $this->_getQuote();

        if (!$quote->getId()) {
            throw new \Magento\Framework\Exception\LocalizedException(__('Quote doesnot exist'));
        }

        if (!$quote->hasItems() || $quote->getHasError()) {
            throw new \Magento\Framework\Exception\LocalizedException(__('Quote has error or no items.'));
        }

        return $this->_charge = $this->_checkoutFactory
            ->create($this->_chargeModel);
    }

    /**
     * Sets the Http Headers, Response Code and Responde Body
     */
    protected function _sendResponse($data, $responseCode = \Magento\Framework\Webapi\Response::HTTP_OK)
    {
        $this->getResponse()->setHttpResponseCode($responseCode)
            ->setHeader('Content-type', 'application/json')
            ->setBody($this->_jsonHelper->jsonEncode($data));
    }

    /**
     * Checks if the result passed in the query string is valid
     *
     * @return boolean
     */
    protected function _isResultValid()
    {
        if (!$this->getRequest()->getParam('result') ||
            !in_array($this->getRequest()->getParam('result'), $this->_validResults)) {
            $this->_logger->error(__("Invalid Result"));
            return false;
        }
        return true;
    }

    /**
     * Redirects to the cart or error page.
     *
     */
    protected function _redirectToCartOrError()
    {
        if ($this->_getQuote()->getIsActive()) {
            $this->_redirectToCart();
        } else {
            $this->_redirectToError();
        }
    }

    /**
     * Redirects to the cart page.
     *
     */
    protected function _redirectToCart()
    {
        $this->_redirect("checkout/cart");
    }

    /**
     * Redirects to the error page.
     *
     */
    protected function _redirectToError()
    {
        $this->_redirect(self::ZIPMONEY_ERROR_ROUTE);
    }

    /**
     * Deactivates the quote
     *
     * @param \Magento\Quote\Model\Quote $quote
     * @return bool
     */
    protected function _deactivateQuote($quote)
    {
        if ($quote && $quote->getId()) {
            if ($quote->getIsActive()) {
                $quote->setIsActive(0)->save();
                $this->_logger->warn(__('Deactivated quote ' . $quote->getId() . '.'));
                return true;
            }
        }
        return false;
    }
}
