<?php

namespace RatePAY\Payment\Plugin;

use Magento\Payment\Model\Checks\TotalMinMax as OrigTotalMinMax;
use Magento\Payment\Model\MethodInterface;
use Magento\Quote\Model\Quote;
use RatePAY\Payment\Model\Method\AbstractMethod;

class TotalMinMax
{
    /**
     * Plugin for methot getAvailableMethods
     *
     * Used to filter out payment methods
     *
     * @param  OrigTotalMinMax    $subject
     * @param  MethodInterface[] $aPaymentMethods
     * @return MethodInterface[]
     */

    /**
     * Check whether payment method is applicable to quote
     *
     * @param OrigTotalMinMax $subject
     * @param \Closure $proceed
     * @param MethodInterface $paymentMethod
     * @param Quote $quote
     * @return bool
     */
    public function aroundIsApplicable(OrigTotalMinMax $subject, \Closure $proceed, MethodInterface $paymentMethod, Quote $quote)
    {
        $blResult = $proceed($paymentMethod, $quote);
        if ($paymentMethod instanceof AbstractMethod && $blResult === false) {
            if (!empty($quote->getBillingAddress()->getCompany()) && $paymentMethod->getIsB2BModeEnabled($quote) === true) {
                return true;
            }
        }
        return $blResult;
    }
}