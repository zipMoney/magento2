<?php
namespace ZipMoney\ZipMoneyPayment\Helper;


class Creditmemo extends \Magento\Framework\App\Helper\AbstractHelper
{ 

  /**
   * @var Quote
   */
  protected $_quote;
  
  /**
   * @var \Magento\Sales\Model\OrderFactory
   */
  protected $_orderFactory;

  /**
   * @var \Magento\CatalogInventory\Model\Configuration
   */
  protected $_catalogConfig;
  
  /**
   * @var \Magento\Framework\DB\Transaction
   */
  protected $_transaction;

  /**
   * @var \Magento\Sales\Model\Order\CreditmemoFactory
   */
  protected $_creditmemoFactory;

  /**
   * @var \Magento\Sales\Model\Order\Email\Sender\CreditmemoSender
   */
  protected $_creditmemoSender;
 
  /**
   * @var \Magento\Sales\Model\Order\Email\Sender\CreditmemoSender
   */
  protected $_creditmemoManagement;

  /**
   * @var \Magento\CatalogInventory\Api\StockConfigurationInterface
   */
  protected $_stockConfiguration;

  public function __construct(        
      \Magento\Framework\App\Helper\Context $context,        
      \Magento\Sales\Model\OrderFactory $orderFactory,
      \Magento\CatalogInventory\Model\Configuration $catalogConfig,
      \Magento\Framework\DB\Transaction $transaction,
      \Magento\Sales\Model\Order\CreditmemoFactory $creditmemoFactory,
      \Magento\Sales\Api\CreditmemoManagementInterface $creditmemoManagement,
      \Magento\Sales\Model\Order\Email\Sender\CreditmemoSender $creditmemoSender,
      \Magento\CatalogInventory\Api\StockConfigurationInterface $stockConfiguration,   
      \ZipMoney\ZipMoneyPayment\Helper\Logger $logger
  ) {
      
    parent::__construct($context);

    $this->_orderFactory = $orderFactory;
    $this->_catalogConfig = $catalogConfig;
    $this->_transaction = $transaction;
    $this->_creditmemoFactory = $creditmemoFactory;
    $this->_creditmemoSender = $creditmemoSender;
    $this->_creditmemoManagement = $creditmemoManagement;
    $this->_stockConfiguration = $stockConfiguration;
    $this->_logger = $logger;
  }

  /**
   * Generates md5 hash from random number
   *
   * @return string 
   */
  public function genRefundReference()
  {
    $refundRef = md5(rand(0, 9999999));
    return $refundRef;
  } 


  /**
   * Returns the invoice of the given order
   *
   * @param \Magento\Sales\Model\Order $order
   * @return Object|bool
   */
  protected function _initInvoice($order)
  {
    $invoiceId = $this->getInvoiceId();
    if ($invoiceId) {
        $invoice = $this->invoiceRepository->get($invoiceId);
        $invoice->setOrder($order);
        if ($invoice->getId()) {
            return $invoice;
        }
    }
    return false;
  }

  /**
   * Creates creditmemo object
   *
   * @param  Object $response, string $comment
   * @return Object|bool
   */
  public function load($response, $comment)
  {
    $creditmemo = false;

    $orderIncId = isset($response->order_id) ? $response->order_id : null;
    $txnId      = isset($response->txn_id) ? $response->txn_id : null;
    $refundAmount = isset($response->refund_amount) ? $response->refund_amount : null;
    $returnInventory = isset($response->return_inventory) ? (bool)$response->return_inventory : false;
    $reason = isset($response->reason) ? $response->reason : null;
    $comment = $comment . ' ' . $reason;

    if ($orderIncId) {
     // $data = $this->getCreditmemo();
      $order = $this->_orderFactory->create()->loadByIncrementId($orderIncId);

      if (!$order->canCreditmemo()) {
        return false;
      }

      $grandTotal = $order->getGrandTotal();
      $qtys = array();
      $backToStock = array();
      $shippingAmount = 0;
      $adjustmentPositive = 0;
      
      if ($grandTotal == $refundAmount) {
          // full order refund
          $this->_logger->info(__('Full order refund.'));
          $shippingAmount = $order->getShippingInclTax();

          /** @var Mage_Sales_Model_Order_Item $oOrderItem */
          foreach($order->getAllItems() as $orderItem) {
              $orderItemId = $orderItem->getId();
              $qtys[$orderItemId] = $orderItem->getQtyOrdered();
              if ($returnInventory == true) {
                  $backToStock[$orderItemId] = true;
              }
          }
      } else {
          // amount refund
          $this->_logger->info(__('Partial refund.'));
          $adjustmentPositive = $refundAmount;
          foreach($order->getAllItems() as $orderItem) {
            $qtys[$orderItem->getId()] = 0;
          }
      }

      $data = array(
          'qtys'  => $qtys,
          'shipping_amount' => $shippingAmount,
          'adjustment_positive' => $adjustmentPositive,
          'adjustment_negative' => '0'
      );
     
      $this->_logger->info(__('Credit Memo Data":-'.json_encode($data)));

      $creditmemo = $this->_creditmemoFactory->createByOrder($order, $data);
      
      /**
       * Process back to stock flags
       */
      foreach ($creditmemo->getAllItems() as $creditmemoItem) {
        $orderItem = $creditmemoItem->getOrderItem();
        $parentId = $orderItem->getParentItemId();
        if (isset($backToStock[$orderItem->getId()])) {
            $creditmemoItem->setBackToStock(true);
        } elseif ($orderItem->getParentItem() && isset($backToStock[$parentId]) && $backToStock[$parentId]) {
            $creditmemoItem->setBackToStock(true);
        } elseif (empty($savedData)) {
            $creditmemoItem->setBackToStock(
              $this->_stockConfiguration->isAutoReturnEnabled()
            );
        } else {
            $creditmemoItem->setBackToStock(false);
        }
      }
    }

    return $creditmemo;
  }

  /**
   * Creates creditmemo object
   *
   * @param  Object $response, string $comment, boolean $notifyCustomer
   * @throws \Magento\Framework\Exception\LocalizedException
   * @return bool
   */
  public function createCreditMemo($response, $comment, $notifyCustomer = false)
  { 

    if(!$response->order_id){
      throw new \Magento\Framework\Exception\LocalizedException(__("Order Id not found"));
    }

    $creditmemo = $this->load($response,$comment);

    if ($creditmemo) {
      $creditmemo->setTransactionId($response->txn_id);
      $creditmemo->setZipmoneyTxnId($response->txn_id);

      if (!$creditmemo->isValidGrandTotal()) {
        throw new \Magento\Framework\Exception\LocalizedException(
            __('The credit memo\'s total must be positive.')
        );
      }

      if (!empty($comment)) {
          $creditmemo->addComment(
              $comment,
              isset($notifyCustomer)
          );

        $creditmemo->setCustomerNote($comment);
        $creditmemo->setCustomerNoteNotify(isset($notifyCustomer));
      }
     
      $this->_creditmemoManagement->refund($creditmemo, false , $notifyCustomer);

      if ($notifyCustomer) {
        $this->_creditmemoSender->send($creditmemo);
      }

    } else {            
      $this->_logger->debug(__("Could not refund the order"));
      return false;
    }
  }
}