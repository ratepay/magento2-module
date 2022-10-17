<?php

/**
 * Copyright (c) Ratepay GmbH
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace RatePAY\Payment\Model\Method;

class Installment extends AbstractMethod
{
    const METHOD_CODE = 'ratepay_installment';

    protected $_code = self::METHOD_CODE;

    /**
     * @var string
     */
    protected $_infoBlockType = 'RatePAY\Payment\Block\Info\Info';

    /**
     * Can be used to install a different block for backend orders
     *
     * @var string
     */
    protected $_adminFormBlockType = 'RatePAY\Payment\Block\Form\Installment';

    /**
     * Generates allowed months
     *
     * @param double $basketAmount
     * @return array
     */
    public function getAllowedMonths($basketAmount)
    {
        $oProfile = $this->getMatchingProfile();
        if (!$oProfile) {
            return [];
        }

        $rateMinNormal = $oProfile->getProductData('rate_min_normal', $this->getCode(), true);
        $runtimes = explode(",", $oProfile->getProductData('month_allowed', $this->getCode(), true));
        $interestrateMonth = ((float)$oProfile->getProductData('interestrate_default', $this->getCode(), true) / 12) / 100;

        $allowedRuntimes = [];
        if (!empty($runtimes)) {
            foreach ($runtimes as $runtime) {
                if (!is_numeric($runtime)) {
                    continue;
                }
                if ($interestrateMonth > 0) { // otherwise division by zero error will happen
                    $rateAmount = $basketAmount * (($interestrateMonth * pow((1 + $interestrateMonth), $runtime)) / (pow((1 + $interestrateMonth), $runtime) - 1));
                } else {
                    $rateAmount = $basketAmount / $runtime;
                }

                if ($rateAmount >= $rateMinNormal) {
                    $allowedRuntimes[] = $runtime;
                }
            }
        }
        return $allowedRuntimes;
    }

    /**
     * Check if payment method is available
     *
     * 1) Check if parent call succeeds
     * 2) Check if there are allowed installment months
     *
     * @param \Magento\Quote\Api\Data\CartInterface|null $quote
     * @return bool
     */
    public function isAvailable(\Magento\Quote\Api\Data\CartInterface $quote = null)
    {
        if (parent::isAvailable($quote) === false) {
            return false;
        }

        if (empty($this->getAllowedMonths($quote->getGrandTotal()))) {
            return false;
        }

        return true;
    }

    /**
     * Can be extended by derived payment models to add certain mechanics PRE payment request
     *
     * @param  \Magento\Sales\Model\Order $oOrder
     * @return void
     */
    protected function handlePrePaymentRequestTasks(\Magento\Sales\Model\Order $oOrder)
    {
        $this->recalculateInstallmentPlan($oOrder);
    }
}
