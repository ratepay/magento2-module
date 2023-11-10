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
    'Magento_Checkout/js/model/url-builder',
    'mage/storage',
], function ($, urlBuilder, storage) {
    'use strict';

    return function () {
        var serviceUrl = urlBuilder.createUrl('/carts/mine/ratepay-dfpSent', {});
        var request = {};

        return storage.post(
            serviceUrl,
            JSON.stringify(request)
        ).done(
            function (response) {
                if (response.success === true) {
                    // do nothing
                } else {
                    // do nothing
                }
            }
        ).fail(
            function (response) {
                // do nothing
            }
        );
    };
});
