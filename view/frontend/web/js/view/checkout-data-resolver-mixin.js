define(['Magento_Checkout/js/checkout-data'], function (checkoutData) {
    'use strict';

    var initialResolveDone = false;

    return function (resolver) {
        var original = resolver.resolvePaymentMethod;

        resolver.resolvePaymentMethod = function () {
            if (!initialResolveDone) {
                initialResolveDone = true;

                if (window.checkoutConfig?.payment?.[checkoutData.getSelectedPaymentMethod()]?.secciActive === true) {
                    checkoutData.setSelectedPaymentMethod(null);
                }
            }

            return original.apply(this, arguments);
        };

        return resolver;
    };
});
