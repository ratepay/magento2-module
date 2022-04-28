/*browser:true*/
/*global define*/
define(
    [
        'jquery',
        'RatePAY_Payment/js/view/payment/method-renderer/invoice',
        'Magento_Checkout/js/model/quote'
    ],
    function ($, Component, quote) {
        'use strict';

        return Component.extend({
            defaults: {
                template: 'RatePAY_Payment/payment/multishipping/invoice',
                submitButtonSelector: '[id="parent-payment-continue"]',
                reviewButtonHtml: '',
            }
        });
    }
);
