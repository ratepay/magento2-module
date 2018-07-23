/*browser:true*/
/*global define*/
define(
    [
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
                template: 'RatePAY_Payment/payment/installment',
                rp_phone: '',
                rp_dob_day: '',
                rp_dob_month: '',
                rp_dob_year: ''
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
                        'rp_dob_year': this.rp_dob_year
                    }
                }
            }
        });
    }
);
