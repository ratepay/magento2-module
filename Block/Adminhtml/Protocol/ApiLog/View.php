<?php

/**
 * Copyright (c) Ratepay GmbH
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

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
     * @var \RatePAY\Payment\Helper\Script
     */
    protected $scriptHelper;

    /**
     * Constructor
     *
     * @param \Magento\Backend\Block\Widget\Context $context
     * @param \RatePAY\Payment\Model\Entities\ApiLogFactory $apiLogFactory
     * @param \RatePAY\Payment\Helper\Script $scriptHelper
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Widget\Context $context,
        \RatePAY\Payment\Model\Entities\ApiLogFactory $apiLogFactory,
        \RatePAY\Payment\Helper\Script $scriptHelper,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->apiLogFactory = $apiLogFactory;
        $this->scriptHelper = $scriptHelper;
    }

    /**
     * @return \RatePAY\Payment\Helper\Script
     */
    public function getScriptHelper()
    {
        return $this->scriptHelper;
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
