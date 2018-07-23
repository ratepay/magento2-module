/*browser:true*/
/*global define*/
define(
    [
        'jquery',
        'ko',
        'Magento_Checkout/js/view/payment/default',
        'Magento_Customer/js/customer-data',
        'Magento_Checkout/js/model/quote',
        'Magento_Customer/js/model/customer'
    ],
    function ($, ko, Component, customerData, quote, customer) {
        'use strict';

        return Component.extend({
            defaults: {
                template: 'RatePAY_Payment/payment/directdebit',
                rp_phone: '',
                rp_dob_day: '',
                rp_dob_month: '',
                rp_dob_year: '',
                rp_iban: ''
            },

            currentBillingAddress: quote.billingAddress,
            currentCustomerData: customer.customerData,

            initObservable: function () {
                this._super()
                    .observe({
                        isPhoneVisible: quote.billingAddress().telephone == '',
                        isDobSet: customer.customerData.dob == null
                    });
                return this;
            },

            getData: function() {
                return {
                    'method': this.getCode(),
                    'additional_data': {
                        'rp_phone': this.rp_phone,
                        'rp_dob_day': this.rp_dob_day,
                        'rp_dob_month': this.rp_dob_month,
                        'rp_dob_year': this.rp_dob_year,
                        'rp_iban': this.rp_iban
                    }
                }
            },

            showAgreement: function(){
                $('#ratepay_directdebit_sepa_agreement').show();
                $('#ratepay_directdebit_sepa_agreement_link').hide();
            }
        });
    }
);
