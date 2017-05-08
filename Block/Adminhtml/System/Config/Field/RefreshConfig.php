<?php
namespace ZipMoney\ZipMoneyPayment\Block\Adminhtml\System\Config\Field;

class RefreshConfig extends \Magento\Config\Block\System\Config\Form\Field
{
    /**
     * @var \Magento\Backend\Model\Url
     */
    protected $_url;

    protected $_refreshConfigLabel = "Refresh Config";

    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Backend\Model\Url $url,
        \Magento\Framework\View\Helper\Js $jsHelper,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->_url = $url;
    }
    /**
     * Set template to itself
     *
     * @return \Magento\Customer\Block\Adminhtml\System\Config\Validatevat
     */
    protected function _prepareLayout()
    {
        parent::_prepareLayout();
        if (!$this->getTemplate()) {
            $this->setTemplate('system/config/refreshconfig.phtml');
        }
        return $this;
    }

    public function getAjaxCheckUrl()
    {
        return $this->_urlBuilder->getUrl('zipmoney/index/refreshconfig');
    }
        /**
     * Unset some non-related element parameters
     *
     * @param \Magento\Framework\Data\Form\Element\AbstractElement $element
     * @return string
     */
    public function render(\Magento\Framework\Data\Form\Element\AbstractElement $element)
    {
        $element->unsScope()->unsCanUseWebsiteValue()->unsCanUseDefaultValue();
        return parent::render($element);
    }
 
    protected function _getElementHtml(\Magento\Framework\Data\Form\Element\AbstractElement $element)
    {
        $originalData = $element->getOriginalData();
        $buttonLabel = !empty($originalData['button_label']) ? $originalData['button_label'] : $this->_refreshConfigLabel;
        $this->addData(
            [
                'button_label' => __($buttonLabel),
                'html_id' => $element->getHtmlId(),
                'ajax_url' => $this->_urlBuilder->getUrl('zipmoneypayment/index/refreshConfig'),
            ]
        );

        return $this->_toHtml();
    }
}