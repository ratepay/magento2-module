<?php

namespace RatePAY\Payment\Api;

interface InstallmentPlanInterface
{
    /**
     * Return installment plan details
     *
     * @param string $calcType
     * @param string $calcValue
     * @param float $grandTotal
     * @param string $methodCode
     * @return \RatePAY\Payment\Service\V1\Data\InstallmentPlanResponse
     */
    public function getInstallmentPlan($calcType, $calcValue, $grandTotal, $methodCode);

    /**
     * Return installment plan details
     *
     * @param string $calcType
     * @param string $calcValue
     * @param float $grandTotal
     * @param string $methodCode
     * @param string $billingCountryId
     * @param string $currency
     * @return \RatePAY\Payment\Service\V1\Data\InstallmentPlanResponse
     */
    public function getInstallmentPlanBackend($calcType, $calcValue, $grandTotal, $methodCode, $billingCountryId, $currency);
}
