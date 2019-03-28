<?php

namespace RatePAY\Payment\Block\Form;

use Magento\Framework\View\Element\Template;

class Base extends \Magento\Payment\Block\Form
{
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
     * @var \Magento\Sales\Model\AdminOrder\Create
     */
    protected $orderCreate;

    /**
     * Dfp constructor.
     * @param Template\Context $context
     * @param \Magento\Checkout\Model\Session $checkoutSession
     * @param \Magento\Customer\Model\Session $customerSession
     * @param \RatePAY\Payment\Helper\Data $rpDataHelper
     * @param \RatePAY\Payment\Controller\LibraryController $rpLibraryController
     * @param \Magento\Sales\Model\AdminOrder\Create $orderCreate
     * @param array $data
     */
    public function __construct(
        Template\Context $context,
        \Magento\Checkout\Model\Session $checkoutSession,
        \Magento\Customer\Model\Session $customerSession,
        \RatePAY\Payment\Helper\Data $rpDataHelper,
        \RatePAY\Payment\Controller\LibraryController $rpLibraryController,
        \Magento\Sales\Model\AdminOrder\Create $orderCreate,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->checkoutSession = $checkoutSession;
        $this->customerSession = $customerSession;
        $this->rpDataHelper = $rpDataHelper;
        $this->rpLibraryController = $rpLibraryController;
        $this->orderCreate = $orderCreate;
    }

    /**
     * @return string
     */
    public function getDeviceIdentCode()
    {
        if(is_null($this->customerSession->getRatePayDeviceIdentToken())) {
            if (!(bool) $this->rpDataHelper->getRpConfigData('ratepay_general', 'device_ident')) {
                return '';
            }
            $dfpSnippetId = $this->rpDataHelper->getRpConfigData('ratepay_general', 'snipped_id');
            if (!empty($dfpSnippetId)) {
                $dfp = $this->rpLibraryController->getDfpCode(
                    $dfpSnippetId,
                    $this->customerSession->getSessionId()
                );
                $this->customerSession->setRatePayDeviceIdentToken($dfp->getToken());
                return $dfp->getDfpSnippetCode();
            }
        }
        return '';
    }

    /**
     * Retrieve create order model object
     *
     * @return \Magento\Sales\Model\AdminOrder\Create
     */
    public function getCreateOrderModel()
    {
        return $this->orderCreate;
    }

    /**
     * @return bool
     */
    public function isPhoneVisible()
    {
        $order = $this->getCreateOrderModel();
        if ($order) {
            $telephone = $order->getBillingAddress()->getTelephone();
            if (!empty($telephone)) {
                return false;
            }
        }
        return true;
    }

    public function isDobVisible()
    {
        if (!empty($this->getBirthday())) {
            return false;
        }
        return true;
    }

    /**
     * @return string
     */
    public function getBillingName()
    {
        $billingAddress = $this->getCreateOrderModel()->getBillingAddress();
        return $billingAddress->getFirstname().' '.$billingAddress->getLastname();
    }

    /**
     * @return null|string
     */
    public function getBirthday()
    {
        return $this->getCreateOrderModel()->getQuote()->getCustomer()->getDob();
    }

    /**
     * @param string $part
     * @return false|string
     */
    public function getBirthdayPart($part)
    {
        $birthday = $this->getBirthday();
        if (!empty($birthday)) {
            $timestamp = strtotime($birthday);
            return date($part, $timestamp);
        }
        return '';
    }
}
