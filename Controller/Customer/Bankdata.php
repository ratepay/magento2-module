<?php

/**
 * Copyright (c) Ratepay GmbH
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace RatePAY\Payment\Controller\Customer;

use \Magento\Framework\View\Result\PageFactory;

class Bankdata extends \Magento\Framework\App\Action\Action
{
    /**
     * @var \RatePAY\Payment\Helper\Data
     */
    protected $rpDataHelper;

    /**
     * PAYONE base helper
     *
     * @var Base
     */
    protected $baseHelper;

    /**
     * @param \Magento\Framework\App\Action\Context $context
     * @param \Magento\Framework\View\Result\PageFactory $pageFactory
     * @param \RatePAY\Payment\Helper\Data $rpDataHelper
     */
    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Magento\Framework\View\Result\PageFactory $pageFactory,
        \RatePAY\Payment\Helper\Data $rpDataHelper
    ) {
        parent::__construct($context);
        $this->pageFactory = $pageFactory;
        $this->rpDataHelper = $rpDataHelper;
    }

    /**
     * Dispatch request
     *
     * @return \Magento\Framework\Controller\ResultInterface|ResponseInterface
     * @throws \Magento\Framework\Exception\NotFoundException
     */
    public function execute()
    {
        if ((bool)$this->rpDataHelper->getRpConfigDataByPath('ratepay/general/bams_enabled') === true) {
            $resultPage = $this->pageFactory->create();
            $resultPage->getConfig()->getTitle()->set(__('Bank data management'));

            return $resultPage;
        }
        return $this->resultRedirectFactory->create()->setPath('customer/account');
    }
}