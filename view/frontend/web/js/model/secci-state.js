/**
 * Copyright (c) Ratepay GmbH
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

define(['ko', 'Magento_Checkout/js/model/quote'], function (ko, quote) {
    'use strict';

    var snapshot = null,
        resetVersion = ko.observable(0);

    function getBillingCountry() {
        var billingAddress = quote.billingAddress();

        return billingAddress && billingAddress.countryId ? billingAddress.countryId : null;
    }

    function reset() {
        snapshot = null;
        resetVersion(resetVersion() + 1);
    }

    // A SECCI is only valid for the billing country it was created for
    quote.billingAddress.subscribe(function () {
        var billingCountry = getBillingCountry();

        if (snapshot !== null && snapshot.billingCountry !== null && billingCountry !== null && snapshot.billingCountry !== billingCountry) {
            reset();
        }
    });

    function getGrandTotal(totals) {
        var segment;

        if (totals && Array.isArray(totals.total_segments)) {
            segment = totals.total_segments.find(function (item) {
                return item.code === 'grand_total';
            });
            if (segment) {
                return segment.value;
            }
        }
        return totals ? totals.grand_total : null;
    }

    function getCurrentState(paymentMethod) {
        var totals = quote.totals(),
            grandTotal = getGrandTotal(totals);

        return {
            grandTotal: grandTotal !== null && grandTotal !== undefined ? Math.round(parseFloat(grandTotal) * 100) : null,
            currency: totals ? totals.quote_currency_code : null,
            paymentMethod: paymentMethod || null,
            billingCountry: getBillingCountry()
        };
    }

    return {
        // Incremented every time the SECCI state is reset, e.g. because the billing country changed
        resetVersion: resetVersion,

        // Returns the current cart state, to be committed once the SECCI was created successfully
        capture: function (paymentMethod) {
            return getCurrentState(paymentMethod);
        },

        commit: function (state) {
            snapshot = state;
        },

        isCreated: function () {
            return snapshot !== null;
        },

        // Checks if the SECCI was created for the given payment method, regardless of later cart changes
        isCreatedFor: function (paymentMethod) {
            return snapshot !== null && snapshot.paymentMethod === paymentMethod;
        },

        // Checks if the current cart state still matches the state at the time of the SECCI creation
        isValidFor: function (paymentMethod) {
            var current = getCurrentState(paymentMethod);

            return snapshot !== null
                && snapshot.grandTotal !== null
                && snapshot.grandTotal === current.grandTotal
                && snapshot.currency === current.currency
                && snapshot.paymentMethod === current.paymentMethod
                && snapshot.billingCountry === current.billingCountry;
        }
    };
});
