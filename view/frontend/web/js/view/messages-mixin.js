/*jshint browser:true jquery:true*/
/*global alert*/

define([
    'jquery',
    'Magento_Checkout/js/model/quote'
], function ($, quote) {
    'use strict';

    var mixin = {
        isRatepayPayment: function () {
            if (quote.paymentMethod() === undefined || !quote.paymentMethod()) {
                return false;
            }

            var paymentMethod = quote.paymentMethod().method;
            if (paymentMethod.indexOf('ratepay') === -1) {
                return false;
            }
            return true;
        },
        initialize: function (config, messageContainer) {
            this._super()
                .initObservable();

            if (config.name.indexOf('ratepay') !== -1) {
                this.template = 'RatePAY_Payment/messages';
            }

            return this;
        },
        onHiddenChange: function (isHidden) {
            if (this.isRatepayPayment() === false) {
                return this._super();
            }

            var self = this;
            // Hide message block if needed
            if (isHidden) {
                if (this.messageContainer.errorMessages().length > 0 && $('#' + quote.paymentMethod().method).offset() !== undefined) {
                    $('html, body').animate({
                        scrollTop: $('#' + quote.paymentMethod().method).offset().top - 20
                    }, 200);
                }

                setTimeout(function () {
                    $(self.selector).hide('blind', {}, 500)
                }, 20000); // show errorbox for 20 seconds instead auf 5 sec standard
            }
        },
        removeAll: function () {
            if (this.isRatepayPayment() === false) {
                return this._super();
            }
            // do nothing for ratepay methods
        },

    };

    return function (messages) {
        return messages.extend(mixin);
    };
});
