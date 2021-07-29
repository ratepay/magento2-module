<?php

namespace RatePAY\Payment\Model\Entities;

use Magento\Framework\Model\AbstractModel;
use RatePAY\Payment\Model\Method\AbstractMethod;

/**
 * Profile configuration entity model
 */
class ProfileConfiguration extends AbstractModel
{
    /**
     * Name of object id field
     *
     * @var string
     */
    protected $_idFieldName = 'profile_id';

    protected $blSandboxMode = false;

    protected $sSecurityCode = "";

    protected $_aActivationFields = [
        'activation_status_invoice' => 'invoice',
        'activation_status_installment' => 'installment',
        'activation_status_elv' => 'elv',
        #'activation_status_prepayment' => 'prepayment', // not used in this module?
    ];

    /**
     * Initialize resource model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('RatePAY\Payment\Model\ResourceModel\ProfileConfiguration');
    }

    public function setSandboxMode($blSandboxMode)
    {
        $this->blSandboxMode = $blSandboxMode;
    }

    public function getSandboxMode()
    {
        return $this->blSandboxMode;
    }

    public function setSecurityCode($sSecurityCode)
    {
        $this->sSecurityCode = $sSecurityCode;
    }

    public function getSecurityCode()
    {
        return $this->sSecurityCode;
    }

    /**
     * Returns Ratepay products that are enabled for this profile
     *
     * @return array
     */
    public function getActiveProducts()
    {
        $aActiveProducts = [];
        foreach ($this->_aActivationFields as $sField => $sProduct) {
            if ($this->getData($sField) == 2) { // 1 = off, 2 = on ...
                if ($sProduct == "installment" && intval($this->getData("interestrate_max")) == 0) {
                    $sProduct = "installment0";
                }
                $aActiveProducts[] = $sProduct;
            }
        }
        return $aActiveProducts;
    }

    public function getProductData($sKey, $sIdentifier, $blIsMethodCode = false)
    {
        if ($blIsMethodCode === true) {
            $sIdentifier = $this->getRatepayProduct($sIdentifier);
        }
        $sIdentifier = $this->normalizeProduct($sIdentifier);
        $sKey = str_ireplace("?", $sIdentifier, $sKey);
        return $this->getData($sKey);
    }

    /**
     * Normalizes product for reading from db - since installment0 is only a pseudo-product
     *
     * @param  string $sProduct
     * @return string
     */
    public function normalizeProduct($sProduct)
    {
        if ($sProduct == "installment0") {
            $sProduct = "installment";
        }
        return $sProduct;
    }

    /**
     * Checks if array has different values
     *
     * @param  array $aArray
     * @return bool
     */
    public function hasDifferentValues($aArray)
    {
        if (count($aArray) > 1) {
            $mFirstValue = array_shift($aArray);
            foreach ($aArray as $mValue) {
                if ($mValue !== $mFirstValue) {
                    return true;
                }
            }
        }
        return false;
    }

    /**
     * @return array
     */
    public function getMinOrderTotals()
    {
        $aActiveProducts = $this->getActiveProducts();

        $aMinOrderTotals = [];
        foreach ($aActiveProducts as $sProduct) {
            $aMinOrderTotals[$sProduct] = number_format($this->getProductData("tx_limit_?_min", $sProduct), '2', ',', '');
        }
        return $aMinOrderTotals;
    }

    /**
     * @return array
     */
    public function getMaxOrderTotals()
    {
        $aActiveProducts = $this->getActiveProducts();

        $aMinOrderTotals = [];
        foreach ($aActiveProducts as $sProduct) {
            $aMinOrderTotals[$sProduct] = number_format($this->getProductData("tx_limit_?_max", $sProduct), '2', ',', '');
        }
        return $aMinOrderTotals;
    }

    /**
     * @return array
     */
    public function getB2bValues()
    {
        $aActiveProducts = $this->getActiveProducts();

        $aMinOrderTotals = [];
        foreach ($aActiveProducts as $sProduct) {
            $aMinOrderTotals[$sProduct] = $this->getProductData("b2b_?", $sProduct);
        }
        return $aMinOrderTotals;
    }

    /**
     * @return array
     */
    public function getB2bMaxOrderTotals()
    {
        $aActiveProducts = $this->getActiveProducts();

        $aMinOrderTotals = [];
        foreach ($aActiveProducts as $sProduct) {
            $aMinOrderTotals[$sProduct] = number_format($this->getProductData("tx_limit_?_max_b2b", $sProduct), '2', ',', '');
        }
        return $aMinOrderTotals;
    }

    /**
     * @return array
     */
    public function getDeliveryAddressValues()
    {
        $aActiveProducts = $this->getActiveProducts();

        $aMinOrderTotals = [];
        foreach ($aActiveProducts as $sProduct) {
            $aMinOrderTotals[$sProduct] = $this->getProductData("delivery_address_?", $sProduct);
        }
        return $aMinOrderTotals;
    }

    /**
     * Convert method code to Ratepay product
     *
     * @param  string $sMethodCode
     * @return string
     */
    public function getRatepayProduct($sMethodCode)
    {
        $sProduct = str_ireplace("ratepay_", "", $sMethodCode);
        $sProduct = str_ireplace(AbstractMethod::BACKEND_SUFFIX, "", $sProduct);
        $sProduct = str_ireplace("directdebit", "elv", $sProduct);
        return $sProduct;
    }

    /**
     * Checks if entity is applicable for current order process
     *
     * @param \Magento\Quote\Api\Data\CartInterface $oQuote
     * @param string                                $sMethodCode
     * @return bool
     */
    public function isApplicableForQuote(\Magento\Quote\Api\Data\CartInterface $oQuote, $sMethodCode)
    {
        $sProduct = $this->getRatepayProduct($sMethodCode);

        // check product
        if (!in_array($sProduct, $this->getActiveProducts())) {
            return false;
        }

        // check currency
        if (!in_array($oQuote->getQuoteCurrencyCode(), explode(",", $this->getData("currency")))) {
            return false;
        }

        // check country
        if (!in_array($oQuote->getBillingAddress()->getCountryId(), explode(",", $this->getData("country_code_billing")))) {
            return false;
        }

        $dTotalAmount = $oQuote->getGrandTotal();
        $dMinAmount = $this->getProductData("tx_limit_?_min", $sMethodCode, true);
        $dMaxAmount = $this->getProductData("tx_limit_?_max", $sMethodCode, true);
        if (!empty($oQuote->getBillingAddress()->getCompany()) && ((bool)$this->getProductData("b2b_?", $sMethodCode, true) === true && ($dTotalAmount === null || $dTotalAmount <= $this->getProductData("tx_limit_?_max_b2b", $sMethodCode)))) {
            $dMaxAmount = $this->getProductData("tx_limit_?_max_b2b", $sMethodCode, true);
        }

        // check min_/max_basket
        if ($dTotalAmount < $dMinAmount || $dTotalAmount > $dMaxAmount) {
            return false;
        }
        return true;
    }
}
