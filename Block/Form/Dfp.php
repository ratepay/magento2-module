<?php

/**
 * Copyright (c) Ratepay GmbH
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
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
    public function __construct(
        Template\Context $context,
        \Magento\Checkout\Model\Session $checkoutSession,
        \Magento\Customer\Model\Session $customerSession,
        \RatePAY\Payment\Helper\Data $rpDataHelper,
        \RatePAY\Payment\Controller\LibraryController $rpLibraryController,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->checkoutSession = $checkoutSession;
        $this->customerSession = $customerSession;
        $this->rpDataHelper = $rpDataHelper;
        $this->rpLibraryController = $rpLibraryController;
    }

    /**
     * @return string|void
     */
    public function getDeviceIdentCode()
    {
        $dfpSessionToken = $this->customerSession->getRatePayDeviceIdentToken();
        $dfpSnippetId = $this->rpDataHelper->getRpConfigData('ratepay_general', 'snippet_id');
        if (empty($dfpSnippetId)) {
            $dfpSnippetId = 'ratepay'; // default value, so that there is always a device fingerprint
        }

        if (empty($dfpSessionToken)) {
            $dfp = $this->rpLibraryController->getDfpCode($dfpSnippetId, $this->customerSession->getSessionId());
            $this->customerSession->setRatePayDeviceIdentToken($dfp->getToken());
            return $dfp->getDfpSnippetCode();
        }
    }
}
