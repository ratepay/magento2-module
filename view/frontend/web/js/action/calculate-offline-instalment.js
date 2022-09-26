/**
 * Copyright (c) Ratepay GmbH
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/*browser:true*/
/*global define*/
define([
    'jquery',
    'mage/storage'
], function ($, storage) {
    'use strict';

    return function (price, currency, ratepayInstalmentSelector) {
        var request = {
            price: price,
            currency: currency
        };

        return storage.post(
            window.checkout.restApiUrl,
            JSON.stringify(request)
        ).done(
            function (response) {
                if (response.success === true) {
                    $(ratepayInstalmentSelector).html(response.text);
                    $(ratepayInstalmentSelector).parent().show();
                } else {
                    $(ratepayInstalmentSelector).parent().hide();
                }
            }
        ).fail(
            function (response) {
                $(ratepayInstalmentSelector).parent().hide();
            }
        );
    };
});
