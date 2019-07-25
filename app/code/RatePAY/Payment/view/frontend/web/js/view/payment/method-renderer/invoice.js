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
                template: 'RatePAY_Payment/payment/invoice',
                rp_phone: '',
                rp_dob_day: '',
                rp_dob_month: '',
                rp_dob_year: '',
                rp_vatid: ''
            }
        });
    }
);
