/**
 * Copyright (c) Ratepay GmbH
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/*browser:true*/
/*global define*/
define(
    [
        'jquery',
        'RatePAY_Payment/js/view/payment/method-renderer/base',
        'Magento_Customer/js/customer-data',
        'Magento_Checkout/js/model/quote',
        'Magento_Customer/js/model/customer',
        'mage/translate'
    ],
    function ($, Component, customerData, quote, customer, $t) {
        'use strict';

        return Component.extend({
            defaults: {
                template: 'RatePAY_Payment/payment/directdebit',
                rp_phone: '',
                rp_dob_day: '',
                rp_dob_month: '',
                rp_dob_year: '',
                rp_vatid: '',
                rp_iban: '',
                sepaAccepted: false,
                b2b_accountholder: ''
            },
            validate: function () {
                var blParentReturn = this._super();
                if (!blParentReturn) {
                    return blParentReturn;
                }

                if (this.rp_iban == '') {
                    this.messageContainer.addErrorMessage({'message': $t('Please enter a valid IBAN.')});
                    return false;
                }

                if (this.sepaAccepted === false) {
                    this.messageContainer.addErrorMessage({'message': $t('Please confirm the transmission of the necessary data to Ratepay.')});
                    return false;
                }
                return true;
            },
            getData: function() {
                var parentReturn = this._super();
                if (parentReturn.additional_data === null) {
                    parentReturn.additional_data = {};
                }
                parentReturn.additional_data.rp_iban = this.rp_iban;
                parentReturn.additional_data.rp_accountholder = this.getCustomerName();
                if (this.isB2BModeUsable() === true) {
                    parentReturn.additional_data.rp_accountholder = this.b2b_accountholder;
                }
                return parentReturn;
            },
            showAgreement: function() {
                $('#ratepay_directdebit_sepa_agreement').show();
                $('#ratepay_directdebit_sepa_agreement_link').hide();
            }
        });
    }
);
