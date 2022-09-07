<?php

/**
 * Copyright (c) Ratepay GmbH
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace RatePAY\Payment\Controller\Adminhtml\Protocol\ApiLog;

use Magento\Backend\App\Action\Context;
use Magento\Backend\Model\View\Result\ForwardFactory;
use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\View\Result\PageFactory;
use Magento\Framework\View\Result\Page;

/**
 * API Log details controller
 */
class View extends \Magento\Backend\App\Action
{
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
     * Returns result page
     *
     * @return Page
     */
    public function execute()
    {
        /** @var Page $resultPage */
        $resultPage = $this->resultFactory->create(ResultFactory::TYPE_PAGE);

        if ($this->_isAllowed()) {
            $resultPage->setActiveMenu('RatePAY_Payment::ratepay_protocol_apilog');
            $resultPage->getConfig()->getTitle()->prepend(__('Protocol - API Log'));
            $resultPage->getConfig()->getTitle()->prepend(sprintf("#%s", $this->getRequest()->getParam('id')));
        }
        return $resultPage;
    }
}
