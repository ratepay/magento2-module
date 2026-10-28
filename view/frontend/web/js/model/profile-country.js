/**
 * Copyright (c) Ratepay GmbH
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

define(['Magento_Checkout/js/model/quote'], function (quote) {
    'use strict';

    function isCountryInList(address, countries) {
        if (!address || !address.countryId || countries === undefined || countries === null) {
            return true;
        }
        return String(countries).split(',').indexOf(address.countryId) !== -1;
    }

    return {
        // Checks the billing and delivery country against the countries of the matching profile, like ProfileConfiguration::isProfileMatching
        // Reads the quote address observables, so computeds and bindings are updated when the address is changed in the checkout
        isAddressCountryAllowed: function (config) {
            var billingAddress = quote.billingAddress(),
                shippingAddress = quote.shippingAddress();

            if (!config) {
                return true;
            }

            return isCountryInList(billingAddress, config.countriesBilling)
                && (quote.isVirtual() || isCountryInList(shippingAddress, config.countriesDelivery));
        }
    };
});
