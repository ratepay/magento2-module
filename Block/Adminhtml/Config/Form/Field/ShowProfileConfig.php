<?php


namespace RatePAY\Payment\Block\Adminhtml\Config\Form\Field;


use RatePAY\Payment\Model\Method\AbstractMethod;
use RatePAY\Payment\Model\Method\Invoice;

class ShowProfileConfig extends \Magento\Config\Block\System\Config\Form\Field\FieldArray\AbstractFieldArray
{
    /**
     * Template
     *
     * @var string
     */
    protected $_template = 'RatePAY_Payment::system/config/form/field/show_profile_config.phtml';

    /**
     * @var \RatePAY\Payment\Helper\ProfileConfig
     */
    protected $profileConfigHelper;

    /**
     * Constructor
     *
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \RatePAY\Payment\Helper\ProfileConfig $profileConfigHelper
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \RatePAY\Payment\Helper\ProfileConfig $profileConfigHelper,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->profileConfigHelper = $profileConfigHelper;
    }

    /**
     * Initialise form fields
     *
     * @return void
     */
    protected function _construct()
    {
        $this->addColumn('txaction', ['label' => __('Label')]);
        $this->_addAfter = false;
        $this->_addButtonLabel = __('Button label');
        parent::_construct();
    }

    /**
     * Returns configured profiles
     *
     * @return array
     */
    public function getProfileConfigs()
    {
        $sPseudoMethodCode = Invoice::METHOD_CODE;
        if ($this->isBackendPaymentConfiguration() === true) {
            $sPseudoMethodCode = $sPseudoMethodCode.Invoice::BACKEND_SUFFIX;
        }
        return $this->profileConfigHelper->getProfileData($sPseudoMethodCode);
    }

    public function getProfileRefreshUrl()
    {
        return $this->_urlBuilder->getUrl('ratepay/system_config/refresh');
    }

    /**
     * Returns current payment method
     *
     * @return string|false
     */
    public function isBackendPaymentConfiguration()
    {
        $oElement = $this->getDataByKey('element');
        if ($oElement) {
            $aOrigData = $oElement->getOriginalData();
            if (isset($aOrigData['path']) && stripos($aOrigData['path'], AbstractMethod::BACKEND_SUFFIX) !== false) {
                return true;
            }
        }
        return false;
    }
    
    /**
     * Check if inheritance checkbox has to be rendered
     *
     * @param \Magento\Framework\Data\Form\Element\AbstractElement $element
     * @return bool
     */
    protected function _isInheritCheckboxRequired(\Magento\Framework\Data\Form\Element\AbstractElement $element)
    {
        return false;
    }
}
