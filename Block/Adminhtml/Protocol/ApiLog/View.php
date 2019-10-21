<?php

namespace RatePAY\Payment\Block\Adminhtml\Protocol\ApiLog;

class View extends \Magento\Backend\Block\Widget\Container
{
    /**
     * API Log entity
     *
     * @var \RatePAY\Payment\Model\Entities\ApiLog
     */
    protected $apiLog = null;

    /**
     * API Log factory
     *
     * @var \RatePAY\Payment\Model\Entities\ApiLogFactory
     */
    protected $apiLogFactory;

    /**
     * Constructor
     *
     * @param \Magento\Backend\Block\Widget\Context $context
     * @param \RatePAY\Payment\Model\Entities\ApiLogFactory $apiLogFactory
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Widget\Context $context,
        \RatePAY\Payment\Model\Entities\ApiLogFactory $apiLogFactory,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->apiLogFactory = $apiLogFactory;
    }

    /**
     * Returns the requested API log entity
     *
     * @return ApiLog
     */
    public function getApiLogEntry()
    {
        if ($this->apiLog === null) {
            $apiLog = $this->apiLogFactory->create();
            $apiLog->load($this->getRequest()->getParam('id'));
            $this->apiLog = $apiLog;
        }
        return $this->apiLog;
    }

    /**
     * Adding the Back button
     *
     * @return void
     */
    protected function _construct()
    {
        $this->buttonList->add(
            'back',
            [
                'label' => __('Back'),
                'onclick' => "setLocation('".$this->getUrl('ratepay/protocol_apilog/')."')",
                'class' => 'back'
            ]
        );
        parent::_construct();
    }
}
