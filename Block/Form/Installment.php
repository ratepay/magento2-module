<?php

namespace RatePAY\Payment\Block\Form;

class Installment extends Base
{
    protected $allowedMonths = null;

    /**
     * @return bool
     */
    public function isSepaBlockVisible()
    {
        $validPaymentFirstdays = $this->getValidPaymentFirstdays();
        if (is_array($validPaymentFirstdays) || $validPaymentFirstdays == '2') {
            return true;
        }
        return false;
    }

    /**
     * @return array|string
     */
    public function getValidPaymentFirstdays()
    {
        $oProfile = $this->getMethod()->getMatchingProfile();
        if (!$oProfile) {
            return [];
        }

        $validPaymentFirstdays = $oProfile->getData("valid_payment_firstdays");
        if(strpos($validPaymentFirstdays, ',') !== false) {
            $validPaymentFirstdays = explode(',', $validPaymentFirstdays);
        }
        return $validPaymentFirstdays;
    }

    /**
     * @return array|string
     */
    public function getPaymentFirstday()
    {
        $oProfile = $this->getMethod()->getMatchingProfile();
        if (!$oProfile) {
            return [];
        }

        return $oProfile->getData("payment_firstday");
    }

    /**
     * @return array
     */
    public function getAllowedMonths()
    {
        if ($this->allowedMonths === null) {
            $allowedMonths = [];
            if ($this->getMethod() instanceof \RatePAY\Payment\Model\Method\AbstractMethod) {
                $allowedMonths = $this->getMethod()->getAllowedMonths($this->getCreateOrderModel()->getQuote()->getGrandTotal());
            }
            $this->allowedMonths = $allowedMonths;
        }
        return $this->allowedMonths;
    }

    /**
     * @return bool
     */
    public function hasAllowedMonths()
    {
        if (!empty($this->getAllowedMonths())) {
            return true;
        }
        return false;
    }

    /**
     * @return string
     */
    public function getRestUrl()
    {
        return $this->_urlBuilder->getDirectUrl('rest/V1/carts/mine/ratepay-installmentPlanBackend?isAjax=1');
    }

    /**
     * @return double
     */
    public function getQuoteGrandTotal()
    {
        return $this->getCreateOrderModel()->getQuote()->getGrandTotal();
    }

    /**
     * Returns current currency code
     *
     * @return string
     */
    public function ratepayGetCurrentCurrencyCode()
    {
        $oQuote = $this->getCreateOrderModel()->getQuote();
        if (!$oQuote) {
            return '';
        }

        $oStore = $oQuote->getStore();
        if (!$oStore) {
            return '';
        }
        return $oStore->getCurrentCurrencyCode();
    }
}
