/*browser:true*/
/*global define*/
define(
    [
        'jquery',
        'RatePAY_Payment/js/view/payment/method-renderer/base',
        'Magento_Customer/js/customer-data',
        'Magento_Checkout/js/model/quote',
        'Magento_Customer/js/model/customer'
    ],
    function ($, Component, customerData, quote, customer) {
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
