<?php
namespace   ZipMoney\ZipMoneyPayment\Block;

use Magento\Framework\View\Element\Template;


class Form extends \Magento\Payment\Block\Form
{
  
    /**
     * @var \ZipMoney\ZipMoneyPayment\Model\Config
     */
    protected $_config;

    /**
     * @var \ZipMoney\ZipMoneyPayment\Helper\Logger 
     */
    protected $_logger;

    /**
     * @var \Magento\Payment\Helper\Data\Helper
     */
    protected $_paymentHelper;

    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \ZipMoney\ZipMoneyPayment\Model\Config $config,
        \ZipMoney\ZipMoneyPayment\Helper\Logger $logger,
        array $data = []
    ) {
      $this->_config = $config;
      $this->_logger = $logger;
      parent::__construct($context, $data);
    }

    /**
     * Set template and redirect message
     *
     * @return null
     */
    protected function _construct()
    {
        $mark = $this->_getMarkTemplate();
        // known issue: code above will render only static mark image
        $this->_initializeRedirectTemplateWithMark($mark);
        parent::_construct();

        $this->setRedirectMessage(__('You will be redirected to the zipMoney website.'));
    }

    /**
     * Payment method code getter
     *
     * @return string
     */
    public function getMethodCode()
    {
      return $this->_config->getMethodCode();
    }

    /**
     * Get initialized mark template
     *
     * @return Template
     */
    protected function _getMarkTemplate()
    {
      /** @var $mark Template */
      $mark = $this->_layout->createBlock('Magento\Framework\View\Element\Template');
      $mark->setTemplate('ZipMoney_ZipMoneyPayment::payment/mark.phtml');
      return $mark;
    }

    /**
     * Initializes redirect template and set mark
     * @param Template $mark
     *
     * @return void
     */
    protected function _initializeRedirectTemplateWithMark(Template $mark)
    {
      $this->setTemplate(
          'ZipMoney_ZipMoneyPayment::payment/redirect.phtml'
      )->setRedirectMessage(
          __('You will be redirected to the zipMoney website when you place an order.')
      )->setMethodTitle(
          'zipMoney'
      )->setMethodLabelAfterHtml(
          $mark->toHtml()
      );
    }

}