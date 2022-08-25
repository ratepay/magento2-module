/**
 * Copyright (c) Ratepay GmbH
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

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

            setBillingAddressAction(globalMessageList); // always update for ratepay payment methods
        },
    };

    return function (billing_address) {
        return billing_address.extend(mixin);
    };
});
