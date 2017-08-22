<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace RatePAY\Payment\Block\Form;

use Magento\Framework\View\Element\Template;

class Dfp extends \Magento\Payment\Block\Form
{
    /**
     * Checkmo template
     *
     * @var string
     */
    protected $_template = 'RatePAY_Payment::form/dfp.phtml';

    /**
     * @var \Magento\Checkout\Model\Session
     */
    protected $checkoutSession;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $storeManager;

    /**
     * @var \Magento\Customer\Model\Session
     */
    protected $customerSession;

    /**
     * @var \RatePAY\Payment\Helper\Data
     */
    protected $rpDataHelper;

    /**
     * @var \RatePAY\Payment\Controller\LibraryController
     */
    protected $rpLibraryController;

    /**
     * Dfp constructor.
     * @param Template\Context $context
     * @param \Magento\Checkout\Model\Session $checkoutSession
     * @param \Magento\Customer\Model\Session $customerSession
     * @param \RatePAY\Payment\Helper\Data $rpDataHelper
     * @param \RatePAY\Payment\Controller\LibraryController $rpLibraryController
     * @param array $data
     */
    public function __construct(Template\Context $context,
                                \Magento\Checkout\Model\Session $checkoutSession,
                                \Magento\Customer\Model\Session $customerSession,
                                \RatePAY\Payment\Helper\Data $rpDataHelper,
                                \RatePAY\Payment\Controller\LibraryController $rpLibraryController,
                                array $data = [])
    {
        parent::__construct($context, $data);

        $this->checkoutSession = $checkoutSession;
        $this->storeManager = $context->getStoreManager();
        $this->customerSession = $customerSession;
        $this->rpDataHelper = $rpDataHelper;
        $this->rpLibraryController = $rpLibraryController;
    }

    /**
     * @return string|void
     */
    public function getDeviceIdentCode()
    {
        if(is_null($this->customerSession->getRatePayDeviceIdentToken())) {
            $storeId = $this->storeManager->getStore()->getId();
            if (!(bool) $this->rpDataHelper->getRpConfigData('ratepay_general', 'device_ident', $storeId)) {
                return;
            }
            $dfpSnippetId = $this->rpDataHelper->getRpConfigData('ratepay_general', 'snipped_id', $storeId);
            if (!empty($dfpSnippetId)) {
                $dfp = $this->rpLibraryController->getDfpCode(
                    $dfpSnippetId,
                    $this->customerSession->getSessionId()
                );
                $this->customerSession->setRatePayDeviceIdentToken($dfp->getToken());
                return $dfp->getDfpSnippetCode();
            }
        }
    }
}
