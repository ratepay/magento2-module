<?php

/**
 * Copyright (c) Ratepay GmbH
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace RatePAY\Payment\Controller\Adminhtml\Protocol\ApiLog;

use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\View\Result\PageFactory;
use Magento\Backend\Model\View\Result\Page as BackendPage;
use Magento\Framework\View\Result\Page;

class Index extends Action
{
    /**
     * Result page
     *
     * @var \Magento\Backend\Model\View\Result\Page
     */
    protected $resultPage;

    /**
     * Return if the user has the needed rights to view this page
     *
     * @return bool
     */
    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('RatePAY_Payment::ratepay_protocol_apilog');
    }

    /**
     * Return result page
     *
     * @return BackendPage|Page
     */
    public function execute()
    {
        if ($this->_isAllowed()) {
            $this->setPageData();
        }
        return $this->getResultPage();
    }

    /**
     * Instantiate result page object
     *
     * @return BackendPage|Page
     */
    public function getResultPage()
    {
        if ($this->resultPage === null) {
            $this->resultPage = $this->resultFactory->create(ResultFactory::TYPE_PAGE);
        }
        return $this->resultPage;
    }

    /**
     * Set page data
     *
     * @return $this
     */
    protected function setPageData()
    {
        $resultPage = $this->getResultPage();
        $resultPage->setActiveMenu('RatePAY_Payment::ratepay_protocol_apilog');
        $resultPage->getConfig()->getTitle()->set((__('Ratepay - API Log')));
        return $this;
    }
}
