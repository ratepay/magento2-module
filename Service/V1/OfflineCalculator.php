<?php

/**
 * Copyright (c) Ratepay GmbH
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace RatePAY\Payment\Service\V1;

use RatePAY\ModelBuilder;
use RatePAY\Service\OfflineInstallmentCalculation;
use RatePAY\Payment\Api\OfflineCalculatorInterface;
use RatePAY\Payment\Api\Data\OfflineCalculatorResponseInterfaceFactory;

class OfflineCalculator implements OfflineCalculatorInterface
{
    /**
     * Factory for the response object
     *
     * @var OfflineCalculatorResponseInterfaceFactory
     */
    protected $responseFactory;

    /**
     * @var \RatePAY\Payment\Helper\ProfileConfig
     */
    protected $profileConfig;

    /**
     * @var \Magento\Framework\Pricing\PriceCurrencyInterface
     */
    protected $priceCurrency;

    /**
     * @var \RatePAY\Payment\Helper\Data
     */
    protected $rpDataHelper;

    /**
     * Constructor.
     *
     * @param OfflineCalculatorResponseInterfaceFactory         $responseFactory
     * @param \RatePAY\Payment\Helper\ProfileConfig             $profileConfig
     * @param \Magento\Framework\Pricing\PriceCurrencyInterface $priceCurrency
     * @param \RatePAY\Payment\Helper\Data                      $rpHelper
     */
    public function __construct(
        OfflineCalculatorResponseInterfaceFactory $responseFactory,
        \RatePAY\Payment\Helper\ProfileConfig $profileConfig,
        \Magento\Framework\Pricing\PriceCurrencyInterface $priceCurrency,
        \RatePAY\Payment\Helper\Data $rpHelper
    ) {
        $this->responseFactory = $responseFactory;
        $this->profileConfig = $profileConfig;
        $this->priceCurrency = $priceCurrency;
        $this->rpDataHelper = $rpHelper;
    }

    /**
     * Determine matching profile
     *
     * @param  float  $price
     * @param  string $currency
     * @return \RatePAY\Payment\Model\Entities\ProfileConfiguration|false
     */
    protected function getMatchingProfile($price, $currency)
    {
        $methodCode = $this->rpDataHelper->getRpConfigDataByPath("ratepay/general/instalment_plan_method_code");
        $billingCountry = $this->rpDataHelper->getRpConfigDataByPath("ratepay/general/instalment_plan_billing_country");
        $shippingCountry = $this->rpDataHelper->getRpConfigDataByPath("ratepay/general/instalment_plan_shipping_country");
        return $this->profileConfig->getMatchingProfile(null, $methodCode, null, $price, $billingCountry, $shippingCountry, $currency);
    }

    /**
     * Return runtime in months
     *
     * @param \RatePAY\Payment\Model\Entities\ProfileConfiguration $profile
     * @param array                                                $excludeMonths
     * @return int
     */
    protected function getMonths($profile, $excludeMonths)
    {
        $sAllowedMonths = $profile->getData('month_allowed');
        $aAllowedMonths = explode(",", $sAllowedMonths);

        $aSearchRuntimes = [24, 12, 6];
        foreach ($aSearchRuntimes as $iRuntime) {
            if (in_array($iRuntime, $aAllowedMonths) && !in_array($iRuntime, $excludeMonths)) {
                return $iRuntime;
            }
        }
        return $profile->getData('month_number_min');
    }

    /**
     * Check if calculator was activated in the configuration
     *
     * @return bool
     */
    protected function isCalculatorActivated()
    {
        if (empty($this->rpDataHelper->getRpConfigDataByPath("ratepay/general/instalment_plan_billing_country"))) {
            return false;
        }

        if (empty($this->rpDataHelper->getRpConfigDataByPath("ratepay/general/instalment_plan_shipping_country"))) {
            return false;
        }

        return (bool)$this->rpDataHelper->getRpConfigDataByPath("ratepay/general/product_page_instalment_plan");
    }

    /**
     * Return Ratepay checkout config
     *
     * @param float $price
     * @param string $currency
     * @return \RatePAY\Payment\Service\V1\Data\OfflineCalculatorResponse
     */
    public function getInstallmentInfo($price, $currency)
    {
        /** @var \RatePAY\Payment\Service\V1\Data\OfflineCalculatorResponse $response */
        $response = $this->responseFactory->create();
        $response->setData('success', false);

        if ($this->isCalculatorActivated() === false) {
            return $response;
        }

        $profile = $this->getMatchingProfile($price, $currency);
        if (empty($profile)) {
            return $response;
        }

        $monthlyInstalment = false;
        $previousMonthlyInstalment = false;
        $excludeMonths = [];

        while($monthlyInstalment === false || ($monthlyInstalment < $profile->getData('rate_min_normal') && $monthlyInstalment !== $previousMonthlyInstalment)) {
            $previousMonthlyInstalment = $monthlyInstalment;

            $months = $this->getMonths($profile, $excludeMonths);
            $excludeMonths[] = $months;

            $content = new ModelBuilder('Content');
            $content->setArray([
                'InstallmentCalculation' => [
                    'Amount' => $price,
                    'CalculationTime' => ['Month' => $months],
                    'PaymentFirstday' => $profile->getData('payment_firstday'),
                    'ServiceCharge' => $profile->getData('service_charge'),
                    'InterestRate' => $profile->getData('interestrate_default'),
                ]
            ]);

            $service = new OfflineInstallmentCalculation();
            $monthlyInstalment = $service->callOfflineCalculation($content)->subtype('calculation-by-time');
        }

        if ($monthlyInstalment === $previousMonthlyInstalment) {
            return $response; // no instalment meeting the rate_min_normal requirement found, so return success = false response
        }

        $monthlyInstalment = $this->priceCurrency->format($monthlyInstalment);

        $response->setData('success', true);
        $response->setData('months', $months);
        $response->setData('monthlyInstalment', $monthlyInstalment);
        $response->setData('text', sprintf(__('from %s in %d instalments'), $monthlyInstalment, $months));

        return $response;
    }
}
