<?php
namespace ZipMoney\ZipMoneyPayment\Model;

use zipMoney\Configuration;
use ZipMoney\ZipMoneyPayment\Model\Config;

class Webhook extends \zipMoney\Webhook\Webhook
{ 

  protected $_api;

  /**
   * @var \ZipMoney\ZipMoneyPayment\Model\Api\Factory
   */
  protected $_apiFactory;

  /**
   * @var \ZipMoney\ZipMoneyPayment\Helper\Request
   */
  protected $_payloadHelper;

  /**
   * @var \ZipMoney\ZipMoneyPayment\Helper\Order
   */
  protected $_orderHelper;

  /**
   * @var \ZipMoney\ZipMoneyPayment\Helper\Logger
   */
  protected $_logger;

  /**
   * @var \Magento\Sales\Model\Order\Payment\Transaction
   */
  protected $_paymentTransaction;

  /**
   * @var \Magento\Sales\Model\Order\Creditmemo
   */
  protected $_creditMemo;

  /**
   * @var \ZipMoney\ZipMoneyPayment\Helper\Creditmemo
   */
  protected $_creditMemoHelper;

  /**
   * @var \Magento\Sales\Model\Order\Payment
   */
  protected $_payment;
  
  /**
   * @var \ZipMoney\ZipMoneyPayment\Model\Config
   */
  protected $_config;
  

  public function __construct(\ZipMoney\ZipMoneyPayment\Model\Api\Factory $apiFactory,
                              \ZipMoney\ZipMoneyPayment\Helper\Order $orderHelper,
                              \ZipMoney\ZipMoneyPayment\Helper\Creditmemo $creditMemoHelper,
                              \ZipMoney\ZipMoneyPayment\Helper\Request $requestHelper,
                              \ZipMoney\ZipMoneyPayment\Helper\Logger $logger,
                              \Magento\Sales\Model\Order\Payment $payment,
                              \Magento\Sales\Model\Order\Payment\Transaction $paymentTransaction,
                              \Magento\Sales\Model\Order\Creditmemo $creditMemo,
                              \ZipMoney\ZipMoneyPayment\Model\Config $config    
  )
  {
    $this->_apiFactory = $apiFactory;
    $this->_payloadHelper = $requestHelper;
    $this->_orderHelper = $orderHelper;
    $this->_logger = $logger;
    $this->_config = $config;
    $this->_payment = $payment;
    $this->_paymentTransaction  = $paymentTransaction;
    $this->_creditMemo = $creditMemo;
    $this->_creditMemoHelper  = $creditMemoHelper;

    Configuration::$merchant_id  = $this->_config->getMerchantId();
    Configuration::$merchant_key = $this->_config->getMerchantKey();
    Configuration::$environment  = $this->_config->getEnvironment();
  }


  /**
   * Checks if order is express
   * 
   * @param int $orderIncId
   * @return bool
   */
  protected function _isOrderExpress($orderIncId)
  {
    $isExpressOrder = false;
    if ($orderIncId) {
      $isExpressOrder = $this->_orderHelper->isOrderExpress($orderIncId);
      $vOrderTypeText = $isExpressOrder ? 'Express' : 'Standard';
      $this->_logger->info(__('Order (' . $orderIncId . ') is ' . $vOrderTypeText));
    } else {
      $this->_logger->warn(__('Order id is empty or null.'));
    }
    return $isExpressOrder;
  }

  /**
   * Checks if txnId is available
   * 
   * @param int $txnId
   * @param bool $refund
   * @return bool
   */
  protected function _txnIdAvailable($txnId, $refund = false)
  {
    if ($refund) {
      $collection = $this->_creditMemo->getCollection()->addAttributeToFilter("zipmoney_txn_id", $txnId);
      if ($collection->count() > 0) 
        return false;
    } else {
      $collection = $this->_paymentTransaction->getCollection()->addAttributeToFilter("txn_id", $txnId);
      if ($collection->count() > 0) {
        foreach ($collection as $item) {
          $payment = $this->_payment->load($item->getPaymentId());
          if ($payment && $payment->getId() != 0) {
            if (Config::METHOD_ZC == $payment->getMethod() || Config::METHOD_ZP == $payment->getMethod() ) {
              return false;
            }
          }
        }
      }
    }
    return true;
  }

  /**
   * Checks if refund reference is available
   * 
   * @param int $reference
   * @return bool
   */
  protected function _refundReferenceAvailable($reference)
  {
    $collection = $this->_creditMemo->getCollection()->addAttributeToFilter("refund_reference", $reference);
    if ($collection->count() > 0) {
        return true;
    }
    return false;
  }

  /**
   * Process authorise_succeeded notification
   * 
   * @param Object $response
   */
  protected  function _eventAuthSuccess($response)
  {
    $this->_logger->info("Event:- authorise_succeeded");
    $this->_logger->debug("Event Message:- ". json_encode($response));

    if (!$this->_txnIdAvailable($response->txn_id)) {
      $this->_logger->info(__('Ignoring duplicate authorise_succeeded (txn_id: ' . $response->txn_id . ') notification.'));
      return;
    }

    $this->_orderHelper->authorise($response->order_id, $response->txn_id);
    $this->_logger->info(__('Successful to authorise.'));
  } 

  /**
   * Process authorise_fail notification
   * 
   * @param Object $response
   */
  protected  function _eventAuthFail($response)
  {
    $this->_logger->info("Event:- authorise_fail");
    $this->_logger->debug("Event Message:- ". json_encode($response));

    $this->_orderHelper->addOrderComment($response->order_id,' zipMoney Authorised failed: ' . $response->error_message);
  }

  /**
   * Process authorise_under_review notification
   * 
   * @param Object $response
   */
  protected  function _eventAuthReview($response)
  {
    $this->_logger->info("Event:- authorise_under_review");
    $this->_logger->debug("Event Message:- ". json_encode($response));

    $this->_orderHelper->authReview($response->order_id,$response->txn_id);
  }

  /**
   * Process charge_success notification
   * 
   * @param Object $response
   */
  protected  function _eventChargeSuccess($response)
  {  
    $this->_logger->info("Event:- charge_success");
    $this->_logger->debug("Event Message:- ". json_encode($response));

    if (!$this->_txnIdAvailable($response->txn_id)) {
      $this->_logger->debug(__('Ignoring duplicate charge_success (txn_id: ' . $response->txn_id . ') notification.'));
      return;
    }

    $this->_orderHelper->authoriseAndCapture($response->order_id, $response->txn_id);
  }

  /**
   * Process charge_fail notification
   * 
   * @param Object $response
   */
  protected  function _eventChargeFail($response)
  { 
    $this->_logger->info("Event:- charge_fail");
    $this->_logger->debug("Event Message:- ". json_encode($response));

    $this->_orderHelper->addOrderComment($response->order_id,' zipMoney Charge failed: ' . $response->error_message);
  }

  /**
   * Process cancel_success notification
   * 
   * @param Object $response
   */
  protected  function _eventCancelSuccess($response)
  {    
    $this->_logger->info("Event:- cancel_success");
    $this->_logger->debug("Event Message:- ". json_encode($response));

    if ($response->reference) {
      $this->_logger->info(__('Cancelling order ' . $orderid . ' is successful in zipMoney.'));
    }
  }

  /**
   * Process cancel_fail notification
   * 
   * @param Object $response
   */
  protected  function _eventCancelFail($response)
  {
    $this->_logger->debug("Event:- cancel_fail");
    $this->_logger->info("Event Message:- ". json_encode($response));

    $this->_orderHelper->addOrderComment($response->order_id,' zipMoney Cancel failed: ' . $response->error_message);
  }

  /**
   * Process capture_success notification
   * 
   * @param Object $response
   */
  protected  function _eventCaptureSuccess($response)
  {

    $this->_logger->info("Event:- capture_success");
    $this->_logger->debug("Event Message:- ". json_encode($response));
    
    if (!$this->_txnIdAvailable($response->txn_id)) {
      $this->_logger->info(__('Ignoring duplicate authorise_succeeded (txn_id: ' . $response->txn_id . ') notification.'));
      return;
    }

    $this->_orderHelper->capture($response->order_id, $response->txn_id);
  }

  /**
   * Process capture_fail notification
   * 
   * @param Object $response
   */
  protected  function _eventCaptureFail($response)
  {
    $this->_logger->info("Event:- capture_fail");
    $this->_logger->debug("Event Message:- ". json_encode($response));

    $this->_orderHelper->setOrderStatus($response->order_id,\ZipMoney\ZipMoneyPayment\Model\Config::STATUS_MAGENTO_AUTHORIZED);
    $this->_orderHelper->addOrderComment($response->order_id,' zipMoney Capture failed: ' . $response->error_message);

  }
  
  /**
   * Process refund_success notification
   * 
   * @param Object $response
   */
  protected  function _eventRefundSuccess($response)
  {
    $this->_logger->info("Event:- refund_success");
    $this->_logger->debug("Event Message:- ". json_encode($response));

    if ($response->reference) {      
      // Refund request from magento
      if ($this->_refundReferenceAvailable($response->reference)) {
          // ignore
          $this->_logger->notice(__('Refund for order ' . $response->order_id . ' is successful in zipMoney.'));
      } else {
          $message = __('Refund reference ' . $response->reference . ' for order ' . $response->order_id . ' does not exist.');
          $this->_logger->error($message);
          $this->_orderHelper->addOrderComment($response->order_id, $message);
      }
    } else {
      // Refund request from zipMoney
      if (!$this->_txnIdAvailable($response->txn_id, true)) {
        $this->_logger->info(__('Ignoring duplicate refund_succeeded (txn_id: ' . $response->txn_id . ')notification.'));
        return;
      }

      $this->_logger->info(__(sprintf("Initiating refund for order (%s)",$response->order_id)));
      $comment = __('The order has been refunded (Txn ID: %s).' , $response->txn_id);
      $this->_creditMemoHelper->createCreditMemo($response, $comment, true);
    }
  }

  /**
   * Process refund_fail notification
   * 
   * @param Object $response
   */
  protected  function _eventRefundFail($response)
  {
    $this->_logger->info("Event:- refund_fail");
    $this->_logger->debug("Event Message:- ". json_encode($response));
   
    $message = __('Refund for order ' . $orderId . ' failed in zipMoney. ') . $comment;
    $this->_logger->warn($message);
    $this->_orderHelper->addOrderComment($orderId, $message);
  }


  /**
   * Process order_cancel notification
   * 
   * @param Object $response
   */
  protected  function _eventOrderCancel($response)
  { 
    $this->_logger->info("Event:- order_cancel");
    $this->_logger->debug("Event Message:- ". json_encode($response));

    $this->_orderHelper->cancelOrder($response->order_id, $response->txn_id, $message);
  }
    
  /**
   * Process config_update notification
   * 
   * @param Object $response
   */
  protected  function _eventConfigUpdate($response)
  {    
    $this->_logger->info("Event:- config_update");
    $this->_logger->debug("Event Message:- ". json_encode($response));

    $merchantId  = $this->_config->getConfigData(Config::PAYMENT_ZIPMONEY_ID);

    // TODO: Configuration Update
    try {
      $this->_config->requestConfigAndUpdate();
    } catch (Exception $e) {
      $this->_config->saveConfigByMatchedScopes(Config::PAYMENT_ZIPMONEY_PAYMENT_HASH, '', $response->merchant_id);
      $this->_logger->warn(__('An error occurred during requesting config data from zipMoney.'));
    }
    
  }
}   
