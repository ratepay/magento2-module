<?php

/**
 * Copyright (c) Ratepay GmbH
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace RatePAY\Payment\Block\Form;

use Magento\Framework\View\Element\Template;

class Base extends \Magento\Payment\Block\Form
{
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
     * @var \RatePAY\Payment\Helper\Script
     */
    protected $scriptHelper;

    /**
     * @var \RatePAY\Payment\Model\BackendSecciState
     */
    protected $backendSecciState;

    /**
     * Dfp constructor.
     * @param Template\Context $context
     * @param \RatePAY\Payment\Helper\Data $rpDataHelper
     * @param \RatePAY\Payment\Controller\LibraryController $rpLibraryController
     * @param \Magento\Sales\Model\AdminOrder\Create $orderCreate
     * @param \RatePAY\Payment\Helper\Script $scriptHelper
     * @param \RatePAY\Payment\Model\BackendSecciState $backendSecciState
     * @param array $data
     */
    public function __construct(
        Template\Context $context,
        \RatePAY\Payment\Helper\Data $rpDataHelper,
        \RatePAY\Payment\Controller\LibraryController $rpLibraryController,
        \Magento\Sales\Model\AdminOrder\Create $orderCreate,
        \RatePAY\Payment\Helper\Script $scriptHelper,
        \RatePAY\Payment\Model\BackendSecciState $backendSecciState,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->rpDataHelper = $rpDataHelper;
        $this->rpLibraryController = $rpLibraryController;
        $this->orderCreate = $orderCreate;
        $this->scriptHelper = $scriptHelper;
        $this->backendSecciState = $backendSecciState;
    }

    /**
     * @return \RatePAY\Payment\Helper\Script
     */
    public function getScriptHelper()
    {
        return $this->scriptHelper;
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
     * @return \Magento\Quote\Model\Quote\Address|bool
     */
    public function getBillingAddress()
    {
        $order = $this->getCreateOrderModel();
        if ($order) {
            $billingAddress = $order->getBillingAddress();
            if ($billingAddress) {
                return $billingAddress;
            }
        }
        return false;
    }

    /**
     * @return bool
     */
    public function isPhoneVisible()
    {
        $billingAddress = $this->getBillingAddress();
        if ($billingAddress) {
            $telephone = $billingAddress->getTelephone();
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
        $billingAddress = $this->getBillingAddress();
        if ($billingAddress) {
            return $billingAddress->getFirstname().' '.$billingAddress->getLastname();
        }
        return '';
    }

    /**
     * @return string
     */
    public function getCompanyName()
    {
        $billingAddress = $this->getCreateOrderModel()->getQuote()->getBillingAddress();
        if ($billingAddress) {
            return $billingAddress->getCompany();
        }
        return '';
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

    /**
     * Checks payment configuration for b2b mode
     *
     * @return bool
     */
    public function getIsB2BModeEnabled()
    {
        $oMethod = $this->getMethod();
        if ($oMethod instanceof \RatePAY\Payment\Model\Method\AbstractMethod) {
            return $this->getMethod()->getIsB2BModeEnabled($this->getCreateOrderModel()->getQuote());
        }
        return false;
    }

    /**
     * Checks if the SECCI has to be provided for the payment method
     *
     * @return bool
     */
    public function isSecciEnabled()
    {
        $oMethod = $this->getMethod();
        if ($oMethod instanceof \RatePAY\Payment\Model\Method\AbstractMethod) {
            return $oMethod->isSecciEnabled($this->getCreateOrderModel()->getQuote());
        }
        return false;
    }

    /**
     * Returns the email address of the SECCI already sent for the current cart state
     *
     * @return string|null
     */
    public function getSecciSentEmail()
    {
        return $this->backendSecciState->getSentEmail($this->getCreateOrderModel()->getQuote(), $this->getMethodCode());
    }

    /**
     * Returns the document id of the SECCI already sent for the current cart state
     *
     * @return string|null
     */
    public function getSecciSentDocumentId()
    {
        return $this->backendSecciState->getSentDocumentId($this->getCreateOrderModel()->getQuote(), $this->getMethodCode());
    }

    /**
     * Returns the email address to prefill the SECCI recipient with
     *
     * @return string
     */
    public function getSecciDefaultEmail()
    {
        $sSentEmail = $this->getSecciSentEmail();
        if (!empty($sSentEmail)) {
            return $sSentEmail;
        }

        $oQuote = $this->getCreateOrderModel()->getQuote();
        return (string)($oQuote->getCustomerEmail() ?: $oQuote->getBillingAddress()->getEmail());
    }

    /**
     * Returns the Ratepay legal text URLs for the current language
     *
     * @return array
     */
    public function getLegalUrls()
    {
        return $this->rpDataHelper->getLegalUrls();
    }

    /**
     * @return string
     */
    public function getSecciSendUrl()
    {
        return $this->getUrl('ratepay/secci/send');
    }
}
