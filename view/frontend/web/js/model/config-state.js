/**
 * Copyright (c) Ratepay GmbH
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

define([
    'ko',
    'jquery',
    'Magento_Checkout/js/model/quote',
    'Magento_Checkout/js/model/payment/method-list',
    'RatePAY_Payment/js/action/update-checkout-config'
], function (ko, $, quote, methodList, updateCheckoutConfig) {
    'use strict';

    var version = ko.observable(0),
        // The payment methods are reloaded after the addresses were saved. The matching profile of a payment method
        // can be another one then (e.g. after changing the billing country), so its config has to be reloaded
        isOutdated = false,
        // The config is reloaded only once per payment method list, even if a payment method has no config
        isRefreshed = false;

    // beforeChange, so the config is marked as outdated before the renderers of the new payment methods read it
    methodList.subscribe(function () {
        isOutdated = true;
        isRefreshed = false;
    }, null, 'beforeChange');

    function notifyUpdated() {
        version(version() + 1);
    }

    function refresh() {
        var data, newConfig;

        isOutdated = false;
        isRefreshed = true;

        data = updateCheckoutConfig(quote.getQuoteId());
        if (data.responseJSON !== undefined) {
            data = data.responseJSON;
        }
        if (data.success !== true || data.checkout_config === undefined) {
            return;
        }

        try {
            newConfig = JSON.parse(data.checkout_config);
        } catch (e) {
            return;
        }

        $.each(newConfig, function (index, value) {
            // The general config also holds the state of the checkout (e.g. DFP token already sent), so it is only added if missing
            if (index !== 'ratepay' || window.checkoutConfig.payment.ratepay === undefined) {
                window.checkoutConfig.payment[index] = value;
            }
        });
        notifyUpdated();
    }

    return {
        version: version,

        // Returns the config of the given payment method for the matching profile of the current addresses
        // Reads the version, so computeds and bindings are updated when the config is reloaded
        getMethodConfig: function (methodCode) {
            if (isOutdated === true || (window.checkoutConfig.payment[methodCode] === undefined && isRefreshed === false)) {
                refresh();
            }
            version();

            return window.checkoutConfig.payment[methodCode];
        }
    };
});
