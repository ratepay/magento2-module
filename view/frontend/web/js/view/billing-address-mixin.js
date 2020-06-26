/*jshint browser:true jquery:true*/
/*global alert*/

define([
    'jquery',
    'Magento_Checkout/js/model/quote',
    'Magento_Checkout/js/model/payment/method-list',
    'RatePAY_Payment/js/action/handle-order-buttons',
    'Magento_Checkout/js/action/set-billing-address',
    'Magento_Ui/js/model/messageList'
], function ($, quote, methodList, handleOrderButtons, setBillingAddressAction, globalMessageList) {
    'use strict';

    var mixin = {
        updateAddresses: function () {
            var paymentMethod = quote.paymentMethod().method;
            if (paymentMethod.indexOf('ratepay') === -1) {
                return this._super();
            }

            if (!window.checkoutConfig.payment[paymentMethod].differentShippingAddressAllowed) {
                handleOrderButtons(!this.isAddressSameAsShipping());
                if (!this.isAddressSameAsShipping()) {
                    return;
                }
            }

            setBillingAddressAction(globalMessageList); // always update for ratepay payment methods
        },
    };

    return function (billing_address) {
        return billing_address.extend(mixin);
    };
});
