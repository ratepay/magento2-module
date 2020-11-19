<?php


namespace RatePAY\Payment\Block\System\Config;

class BamsOauthCheck extends \Magento\Config\Block\System\Config\Form\Field\FieldArray\AbstractFieldArray
{
    /**
     * Template
     *
     * @var string
     */
    protected $_template = 'RatePAY_Payment::system/config/bamsoauthcheck.phtml';

    /**
     * @var \Magento\Backend\Model\Session
     */
    protected $backendSession;

    /**
     * @var bool
     */
    protected $blStatus;

    /**
     * Constructor
     *
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\Backend\Model\Session $backendSession
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Backend\Model\Session $backendSession,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->backendSession = $backendSession;
    }

    /**
     * Unset some non-related element parameters
     *
     * @param \Magento\Framework\Data\Form\Element\AbstractElement $element
     * @return string
     */
    public function render(\Magento\Framework\Data\Form\Element\AbstractElement $element)
    {
        $this->blStatus = $this->backendSession->getRatepayBamsOauthChanged();
        if ($this->blStatus === null) {
            return '';
        }

        $this->backendSession->unsRatepayBamsOauthChanged();

        $element->unsScope()->unsCanUseWebsiteValue()->unsCanUseDefaultValue();
        return parent::render($element);
    }

    /**
     * Initialise form fields
     *
     * @return void
     */
    protected function _construct()
    {
        $this->addColumn('txaction', ['label' => __('Transactionstatus-message')]);
        $this->_addAfter = false;
        $this->_addButtonLabel = __('Add Minimum Qty');
        parent::_construct();
    }

    /**
     * @return bool
     */
    public function getStatus()
    {
        return $this->blStatus;
    }
}
