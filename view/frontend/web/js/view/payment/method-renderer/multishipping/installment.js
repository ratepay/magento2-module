/*browser:true*/
/*global define*/
define(
    [
        'jquery',
        'RatePAY_Payment/js/view/payment/method-renderer/installment',
        'Magento_Checkout/js/model/quote'
    ],
    function ($, Component, quote) {
        'use strict';

        return Component.extend({
            defaults: {
                template: 'RatePAY_Payment/payment/multishipping/installment',
                submitButtonSelector: '[id="parent-payment-continue"]',
                reviewButtonHtml: '',
            },
            useMonthDropdown: function () {
                if (this.getAllowedMonths().length > 7) {
                    return true;
                }
                return false;
            }
        });
    }
);
