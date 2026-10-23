/**
 * Copyright (c) Ratepay GmbH
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

define(['ko'], function (ko) {
    'use strict';

    var version = ko.observable(0);

    return {
        version: version,

        // To be called when window.checkoutConfig.payment.ratepay is updated dynamically
        notifyUpdated: function () {
            version(version() + 1);
        }
    };
});
