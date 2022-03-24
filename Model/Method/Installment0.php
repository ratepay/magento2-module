<?php
/**
 * Created by PhpStorm.
 * User: SebastianN
 * Date: 24.02.17
 * Time: 15:48
 */

namespace RatePAY\Payment\Model\Method;


class Installment0 extends AbstractMethod
{
    const METHOD_CODE = 'ratepay_installment0';

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
        $aProfiles = $this->getMatchingProfiles();
        if (empty($aProfiles)) {
            return [];
        }

        $allowedRuntimes = [];
        foreach ($aProfiles as $oProfile) {
            $tmp = $this->getAllowedMonthsForProfile($basketAmount, $oProfile);
            $allowedRuntimes = array_merge($allowedRuntimes, $tmp);
        }
        $allowedRuntimes = array_unique($allowedRuntimes);
        sort($allowedRuntimes, SORT_NUMERIC);

        return $allowedRuntimes;
    }

    /**
     * Returns allowed runtimes for given profile
     *
     * @param  double                                               $basketAmount
     * @param  \RatePAY\Payment\Model\Entities\ProfileConfiguration $oProfile
     * @return array
     */
    public function getAllowedMonthsForProfile($basketAmount, $oProfile)
    {
        $rateMinNormal = $oProfile->getProductData('rate_min_normal', $this->getCode(), true);
        $runtimes = explode(",", $oProfile->getData('month_allowed'));
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
     * @param  \Magento\Quote\Api\Data\CartInterface|null $oQuote
     * @param  string|null $sStoreCode
     * @param  double $dGrandTotal
     * @param  string $sBillingCountryId
     * @param  string $sShippingCountryId
     * @param  string $sCurrency
     * @param  int $installmentRuntime
     * @return \RatePAY\Payment\Model\Entities\ProfileConfiguration|false
     */
    public function getMatchingProfile(\Magento\Quote\Api\Data\CartInterface $oQuote = null, $sStoreCode = null, $dGrandTotal = null, $sBillingCountryId = null, $sShippingCountryId = null, $sCurrency = null, $installmentRuntime = null)
    {
        if ($this->profile === null) {
            if ($oQuote === null) {
                if ($this->isBackendMethod() === false) {
                    $oQuote = $this->checkoutSession->getQuote();
                } else {
                    $oQuote = $this->backendCheckoutSession->getQuote();
                }
            }
            if ($sStoreCode === null) {
                $sStoreCode = $oQuote->getStore()->getCode();
            }

            if (empty($installmentRuntime) && !empty($this->checkoutSession->getData('ratepayInstallmentNumber_'.$this->getCode()))) {
                $installmentRuntime = $this->checkoutSession->getData('ratepayInstallmentNumber_'.$this->getCode());
            }

            if (!empty($installmentRuntime)) {
                if (empty($dGrandTotal)) {
                    $dGrandTotal = $oQuote->getGrandTotal();
                }

                $aProfiles = $this->getMatchingProfiles($oQuote, $sStoreCode, $dGrandTotal, $sBillingCountryId, $sShippingCountryId, $sCurrency);
                if (empty($aProfiles)) {
                    $this->profile = false;
                    return $this->profile;
                }

                foreach ($aProfiles as $oMatchingProfile) {
                    $aAllowedMonths = $this->getAllowedMonthsForProfile($dGrandTotal, $oMatchingProfile);
                    if (in_array($installmentRuntime, $aAllowedMonths)) {
                        $this->profile = $oMatchingProfile;
                        return $this->profile;
                    }
                }
            }

            $this->profile = $this->profileConfig->getMatchingProfile($oQuote, $this->getCode(), $sStoreCode, $dGrandTotal, $sBillingCountryId, $sShippingCountryId, $sCurrency);
        }
        return $this->profile;
    }
}
