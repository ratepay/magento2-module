define([
    'Magento_Checkout/js/checkout-data',
    'Magento_Checkout/js/model/quote'
], function (checkoutData, quote) {
    'use strict';

    var initialResolveDone = false;

    // SECCI is disabled in B2B context (company set in billing address), see base.js isSecciEnabled
    function isCompanySet() {
        var billingAddress = quote.billingAddress();
        return billingAddress != null && billingAddress.company != undefined && billingAddress.company.length > 1;
    }

    return function (resolver) {
        var original = resolver.resolvePaymentMethod;

        resolver.resolvePaymentMethod = function () {
            if (!initialResolveDone) {
                initialResolveDone = true;

                if (window.checkoutConfig?.payment?.[checkoutData.getSelectedPaymentMethod()]?.secciActive === true
                    && isCompanySet() === false
                ) {
                    checkoutData.setSelectedPaymentMethod(null);
                }
            }

            return original.apply(this, arguments);
        };

        return resolver;
    };
});
