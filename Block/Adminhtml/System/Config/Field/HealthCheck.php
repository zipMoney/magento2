<?php
namespace ZipMoney\ZipMoneyPayment\Block\Adminhtml\System\Config\Field;

class HealthCheck extends \Magento\Config\Block\System\Config\Form\Field
{

    /**
    * @var \Magento\Backend\Model\Url
    */
    protected $_url;


    protected $_refreshConfigLabel = "Health Check";
    
    const HEALTH_CHECK_TYPE_API_ENDPOINT       = 'api_endpoint';
    const HEALTH_CHECK_TYPE_SUBSCRIBE_ENDPOINT = 'webhook_endpoint';
    const HEALTH_CHECK_TYPE_CONFIG_OPTIONS     = 'config_options';
  
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Backend\Model\Url $url,
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
            $this->setTemplate('system/config/healthcheck.phtml');
        }
        return $this;
    }

    
    public function getAjaxHealthCheckUrl($type)
    {
        switch ($type) {
            case self::HEALTH_CHECK_TYPE_API_ENDPOINT:
                return $this->_urlBuilder->getUrl('zipmoney/index/checkapiendpoint');
                break;
            
            case self::HEALTH_CHECK_TYPE_SUBSCRIBE_ENDPOINT:
                return $this->_urlBuilder->getUrl('zipmoney/index/checkwebhook');
                break;  
            case self::HEALTH_CHECK_TYPE_CONFIG_OPTIONS:
                return $this->_urlBuilder->getUrl('zipmoney/index/checkconfigs');
                break;
            default:
                break;
        }
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
                'html_id' => $element->getHtmlId()
            ]
        );

        return $this->_toHtml();
    }
}