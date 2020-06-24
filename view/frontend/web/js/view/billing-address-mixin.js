/*jshint browser:true jquery:true*/
/*global alert*/

define([
    'jquery',
    'Magento_Checkout/js/model/quote',
    'Magento_Checkout/js/model/payment/method-list',
    'RatePAY_Payment/js/action/handle-order-buttons',
], function ($, quote, methodList, handleOrderButtons) {
    'use strict';

    var mixin = {
        updateAddress: function () {
            var parentReturn = this._super();

            var differentCountry = (this.selectedAddress().countryId != window.checkoutConfig.payment.ratepay.currentPaymentCountry);
            if (this.isAddressSameAsShipping() === false) { // add warning when checkbox is set to not the same address
                handleOrderButtons(!this.isAddressSameAsShipping(), differentCountry);
            }

            return parentReturn;
        },
        useShippingAddress: function () {
            var parentReturn = this._super();
            
            var differentCountry = null;
            if (window.checkoutConfig.payment.ratepay !== undefined && window.checkoutConfig.payment.ratepay.currentPaymentCountry !== undefined && quote.billingAddress() && quote.billingAddress().countryId != window.checkoutConfig.payment.ratepay.currentPaymentCountry) {
                differentCountry = true;
            }
            handleOrderButtons(!this.isAddressSameAsShipping(), differentCountry);

            return parentReturn;
        },
    };

    return function (billing_address) {
        return billing_address.extend(mixin);
    };
});
