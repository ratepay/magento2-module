<?php

/**
 * Copyright (c) Ratepay GmbH
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace RatePAY\Payment\Model\ResourceModel;

use RatePAY\Payment\Setup\Tables\ProfileConfiguration as ProfileConfigTable;

class ProfileConfiguration extends \Magento\Framework\Model\ResourceModel\Db\AbstractDb
{
    /**
     * Initialize connection and table
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('ratepay_profile_configuration', 'profile_id');
    }

    /**
     * @param array  $aProfileConfigResponse
     * @param string $sKey
     * @param string $sSubArray
     * @param string $sDefault
     * @param bool   $blYesNoToInt
     * @return string
     */
    protected function getDataFromResponse($aProfileConfigResponse, $sKey, $sSubArray, $sDefault = '', $blYesNoToInt = false)
    {
        if (isset($aProfileConfigResponse[$sSubArray][$sKey])) {
            $sValue = (string)$aProfileConfigResponse[$sSubArray][$sKey];
            if ($blYesNoToInt === true) {
                $sValue = str_ireplace("yes", 1, $sValue);
                $sValue = str_ireplace("no", 0, $sValue);
            }
            return $sValue;
        }
        return $sDefault;
    }

    /**
     * Convert API response array to db table format
     *
     * @param  array $aProfileConfigResponse
     * @return array
     */
    protected function formatConfigData($aProfileConfigResponse)
    {
        $aReturnData = [];
        $aReturnData['profile_id']                          = $this->getDataFromResponse($aProfileConfigResponse, 'profile-id', 'merchantConfig');
        $aReturnData['merchant_name']                       = $this->getDataFromResponse($aProfileConfigResponse, 'merchant-name', 'merchantConfig');
        $aReturnData['shop_name']                           = $this->getDataFromResponse($aProfileConfigResponse, 'shop-name', 'merchantConfig');
        $aReturnData['currency']                            = $this->getDataFromResponse($aProfileConfigResponse, 'currency', 'merchantConfig');
        $aReturnData['merchant_status']                     = $this->getDataFromResponse($aProfileConfigResponse, 'merchant-status', 'merchantConfig');
        $aReturnData['activation_status_invoice']           = $this->getDataFromResponse($aProfileConfigResponse, 'activation-status-invoice', 'merchantConfig');
        $aReturnData['activation_status_installment']       = $this->getDataFromResponse($aProfileConfigResponse, 'activation-status-installment', 'merchantConfig');
        $aReturnData['activation_status_elv']               = $this->getDataFromResponse($aProfileConfigResponse, 'activation-status-elv', 'merchantConfig');
        $aReturnData['activation_status_prepayment']        = $this->getDataFromResponse($aProfileConfigResponse, 'activation-status-prepayment', 'merchantConfig');
        $aReturnData['eligibility_ratepay_invoice']         = $this->getDataFromResponse($aProfileConfigResponse, 'eligibility-ratepay-invoice', 'merchantConfig', '', true);
        $aReturnData['eligibility_ratepay_installment']     = $this->getDataFromResponse($aProfileConfigResponse, 'eligibility-ratepay-installment', 'merchantConfig', '', true);
        $aReturnData['eligibility_ratepay_elv']             = $this->getDataFromResponse($aProfileConfigResponse, 'eligibility-ratepay-elv', 'merchantConfig', '', true);
        $aReturnData['eligibility_ratepay_prepayment']      = $this->getDataFromResponse($aProfileConfigResponse, 'eligibility-ratepay-prepayment', 'merchantConfig', '', true);
        $aReturnData['eligibility_ratepay_pq_full']         = $this->getDataFromResponse($aProfileConfigResponse, 'eligibility-ratepay-pq-full', 'merchantConfig', '', true);
        $aReturnData['tx_limit_invoice_min']                = $this->getDataFromResponse($aProfileConfigResponse, 'tx-limit-invoice-min', 'merchantConfig');
        $aReturnData['tx_limit_invoice_max']                = $this->getDataFromResponse($aProfileConfigResponse, 'tx-limit-invoice-max', 'merchantConfig');
        $aReturnData['tx_limit_invoice_max_b2b']            = $this->getDataFromResponse($aProfileConfigResponse, 'tx-limit-invoice-max-b2b', 'merchantConfig');
        $aReturnData['tx_limit_installment_min']            = $this->getDataFromResponse($aProfileConfigResponse, 'tx-limit-installment-min', 'merchantConfig');
        $aReturnData['tx_limit_installment_max']            = $this->getDataFromResponse($aProfileConfigResponse, 'tx-limit-installment-max', 'merchantConfig');
        $aReturnData['tx_limit_installment_max_b2b']        = $this->getDataFromResponse($aProfileConfigResponse, 'tx-limit-installment-max-b2b', 'merchantConfig');
        $aReturnData['tx_limit_elv_min']                    = $this->getDataFromResponse($aProfileConfigResponse, 'tx-limit-elv-min', 'merchantConfig');
        $aReturnData['tx_limit_elv_max']                    = $this->getDataFromResponse($aProfileConfigResponse, 'tx-limit-elv-max', 'merchantConfig');
        $aReturnData['tx_limit_elv_max_b2b']                = $this->getDataFromResponse($aProfileConfigResponse, 'tx-limit-elv-max-b2b', 'merchantConfig');
        $aReturnData['tx_limit_prepayment_min']             = $this->getDataFromResponse($aProfileConfigResponse, 'tx-limit-prepayment-min', 'merchantConfig');
        $aReturnData['tx_limit_prepayment_max']             = $this->getDataFromResponse($aProfileConfigResponse, 'tx-limit-prepayment-max', 'merchantConfig');
        $aReturnData['tx_limit_prepayment_max_b2b']         = $this->getDataFromResponse($aProfileConfigResponse, 'tx-limit-prepayment-max-b2b', 'merchantConfig');
        $aReturnData['b2b_invoice']                         = $this->getDataFromResponse($aProfileConfigResponse, 'b2b-invoice', 'merchantConfig', '', true);
        $aReturnData['b2b_elv']                             = $this->getDataFromResponse($aProfileConfigResponse, 'b2b-elv', 'merchantConfig', '', true);
        $aReturnData['b2b_installment']                     = $this->getDataFromResponse($aProfileConfigResponse, 'b2b-installment', 'merchantConfig', '', true);
        $aReturnData['b2b_prepayment']                      = $this->getDataFromResponse($aProfileConfigResponse, 'b2b-prepayment', 'merchantConfig', '', true);
        $aReturnData['b2b_PQ_full']                         = $this->getDataFromResponse($aProfileConfigResponse, 'b2b-PQ-full', 'merchantConfig', '', true);
        $aReturnData['delivery_address_invoice']            = $this->getDataFromResponse($aProfileConfigResponse, 'delivery-address-invoice', 'merchantConfig', '', true);
        $aReturnData['delivery_address_installment']        = $this->getDataFromResponse($aProfileConfigResponse, 'delivery-address-installment', 'merchantConfig', '', true);
        $aReturnData['delivery_address_elv']                = $this->getDataFromResponse($aProfileConfigResponse, 'delivery-address-elv', 'merchantConfig', '', true);
        $aReturnData['delivery_address_prepayment']         = $this->getDataFromResponse($aProfileConfigResponse, 'delivery-address-prepayment', 'merchantConfig', '', true);
        $aReturnData['delivery_address_PQ_full']            = $this->getDataFromResponse($aProfileConfigResponse, 'delivery-address-PQ-full', 'merchantConfig', '', true);
        $aReturnData['country_code_billing']                = $this->getDataFromResponse($aProfileConfigResponse, 'country-code-billing', 'merchantConfig');
        $aReturnData['country_code_delivery']               = $this->getDataFromResponse($aProfileConfigResponse, 'country-code-delivery', 'merchantConfig');
        $aReturnData['interestrate_min']                    = $this->getDataFromResponse($aProfileConfigResponse, 'interestrate-min', 'installmentConfig');
        $aReturnData['interestrate_default']                = $this->getDataFromResponse($aProfileConfigResponse, 'interestrate-default', 'installmentConfig');
        $aReturnData['interestrate_max']                    = $this->getDataFromResponse($aProfileConfigResponse, 'interestrate-max', 'installmentConfig');
        $aReturnData['interest_rate_merchant_towards_bank'] = $this->getDataFromResponse($aProfileConfigResponse, 'interest-rate-merchant-towards-bank', 'installmentConfig');
        $aReturnData['month_number_min']                    = $this->getDataFromResponse($aProfileConfigResponse, 'month-number-min', 'installmentConfig');
        $aReturnData['month_number_max']                    = $this->getDataFromResponse($aProfileConfigResponse, 'month-number-max', 'installmentConfig');
        $aReturnData['month_longrun']                       = $this->getDataFromResponse($aProfileConfigResponse, 'month-longrun', 'installmentConfig');
        $aReturnData['amount_min_longrun']                  = $this->getDataFromResponse($aProfileConfigResponse, 'amount-min-longrun', 'installmentConfig');
        $aReturnData['month_allowed']                       = $this->getDataFromResponse($aProfileConfigResponse, 'month-allowed', 'installmentConfig');
        $aReturnData['valid_payment_firstdays']             = $this->getDataFromResponse($aProfileConfigResponse, 'valid-payment-firstdays', 'installmentConfig');
        $aReturnData['payment_firstday']                    = $this->getDataFromResponse($aProfileConfigResponse, 'payment-firstday', 'installmentConfig');
        $aReturnData['payment_amount']                      = $this->getDataFromResponse($aProfileConfigResponse, 'payment-amount', 'installmentConfig');
        $aReturnData['payment_lastrate']                    = $this->getDataFromResponse($aProfileConfigResponse, 'payment-lastrate', 'installmentConfig');
        $aReturnData['rate_min_normal']                     = $this->getDataFromResponse($aProfileConfigResponse, 'rate-min-normal', 'installmentConfig');
        $aReturnData['rate_min_longrun']                    = $this->getDataFromResponse($aProfileConfigResponse, 'rate-min-longrun', 'installmentConfig');
        $aReturnData['service_charge']                      = $this->getDataFromResponse($aProfileConfigResponse, 'service-charge', 'installmentConfig');
        $aReturnData['min_difference_dueday']               = $this->getDataFromResponse($aProfileConfigResponse, 'min-difference-dueday', 'installmentConfig');
        return $aReturnData;
    }

    public function insertProfileConfiguration($aProfileConfigResponse)
    {
        $aData = $this->formatConfigData($aProfileConfigResponse);

        if ($this->isProfileExisting($aData['profile_id']) === true) {
            $this->updateProfileConfiguration($aData);
            return;
        }
        $this->getConnection()->insert($this->getMainTable(), $aData);
    }

    public function updateProfileConfiguration($aData)
    {
        $aWhere = ['profile_id = ?' => $aData['profile_id']];
        unset($aData['profile_id']);
        $this->getConnection()->update($this->getMainTable(), $aData, $aWhere);
    }

    /**
     * Get profile configs by given ids
     *
     * @param  array $aProfileIds
     * @return array
     */
    public function getProfileConfigsByIds($aProfileIds)
    {
        $oSelect = $this->getConnection()->select()
            ->from($this->getMainTable());

        if (!empty($aProfileIds)) {
            $oSelect->where("profile_id IN ('".implode("','", $aProfileIds)."')");
        }

        $aResult = $this->getConnection()->fetchAll($oSelect);

        $aReturn = [];

        // sort results in configured order
        foreach ($aProfileIds as $sProfileId) {
            foreach ($aResult as $aItem) {
                if ($aItem['profile_id'] == $sProfileId) {
                    $aReturn[] = $aItem;
                    break;
                }
            }
        }

        return $aReturn;
    }

    /**
     * Checks if given profile exists in the database
     *
     * @param  string $sProfileId
     * @return bool
     */
    public function isProfileExisting($sProfileId)
    {
        $aProfileConfigs = $this->getProfileConfigsByIds([$sProfileId]);
        if (!empty($aProfileConfigs)) {
            return true;
        }
        return false;
    }
}
