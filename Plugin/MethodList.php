<?php

namespace RatePAY\Payment\Plugin;

use Magento\Payment\Model\MethodList as OrigMethodList;
use Magento\Payment\Model\MethodInterface;
use Magento\Quote\Model\Quote;

class MethodList
{
    /**
     * Checkout session
     *
     * @var \Magento\Checkout\Model\Session
     */
    protected $checkoutSession;

    /**
     * HidePaymentType resource model
     *
     * @var \RatePay\Payment\Model\ResourceModel\HidePaymentType
     */
    protected $hidePaymentType;

    /**
     * Constructor
     *
     * @param \Magento\Checkout\Model\Session                       $checkoutSession
     * @param \RatePay\Payment\Model\ResourceModel\HidePaymentType  $hidePaymentType
     */
    public function __construct(\Magento\Checkout\Model\Session $checkoutSession, \RatePay\Payment\Model\ResourceModel\HidePaymentType $hidePaymentType) {
        $this->checkoutSession = $checkoutSession;
        $this->hidePaymentType = $hidePaymentType;
    }

    /**
     * Return hidden payment types for the current user
     *
     * @return array
     */
    protected function getHiddenPaymentTypes()
    {
        $aPaymentTypes = [];

        $oQuote = $this->checkoutSession->getQuote();
        if (!empty($oQuote->getCustomerId())) {
            $aPaymentTypes = $this->hidePaymentType->getHiddenPaymentTypes($oQuote->getCustomerId());
        } else {
            $aDisabledFromSession = $this->checkoutSession->getRatePayDisabledPaymentMethods();
            if (!empty($aDisabledFromSession)) {
                $aPaymentTypes = $aDisabledFromSession;
            }
        }
        return $aPaymentTypes;
    }

    /**
     * Remove disabled paymenttypes
     *
     * @param  array $aPaymentMethods
     * @return array
     */
    protected function removeDisabledPaymentMethods($aPaymentMethods)
    {
        $aHiddenPaymentTypes = $this->getHiddenPaymentTypes();
        if (empty($aHiddenPaymentTypes)) {
            return $aPaymentMethods;
        }

        $aReturnMethods = $aPaymentMethods;
        for($i = 0; $i < count($aPaymentMethods); $i++) {
            $sCode = $aPaymentMethods[$i]->getCode();
            if (in_array($sCode, $aHiddenPaymentTypes)) {
                unset($aReturnMethods[$i]);
            }
        }
        return $aReturnMethods;
    }

    /**
     * Plugin for methot getAvailableMethods
     *
     * Used to filter out payment methods
     *
     * @param  OrigMethodList    $subject
     * @param  MethodInterface[] $aPaymentMethods
     * @return MethodInterface[]
     */
    public function afterGetAvailableMethods(OrigMethodList $subject, $aPaymentMethods)
    {
        $aPaymentMethods = $this->removeDisabledPaymentMethods($aPaymentMethods);

        return $aPaymentMethods;
    }
}