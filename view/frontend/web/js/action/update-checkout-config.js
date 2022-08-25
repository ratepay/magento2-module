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
    'Magento_Checkout/js/model/url-builder'
], function ($, urlBuilder) {
    'use strict';

    return function (quoteId) {
        var serviceUrl = urlBuilder.createUrl('/guest-carts/:quoteId/ratepay-refreshCheckoutConfig', {
            quoteId: quoteId
        });

        return $.ajax({url: "../" + serviceUrl, async: false, type: 'POST'});
    };
});
