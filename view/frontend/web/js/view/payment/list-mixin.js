/**
 * Copyright (c) Ratepay GmbH
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/*jshint browser:true jquery:true*/
/*global alert*/

define([
    'underscore'
], function (_) {
    'use strict';

    var mixin = {
        removeRenderer: function (paymentMethodCode) {
            if (paymentMethodCode.indexOf('ratepay') === -1) {
                return this._super();
            }
            var items;

            _.each(this.paymentGroupsList(), function (group) {
                items = this.getRegion(group.displayArea);

                _.find(items(), function (value) {
                    if (value.item.method === paymentMethodCode) {
                        value.disposeSubscriptions();
                        value.destroy();
                    }
                });
            }, this);
        }
    };

    return function (list) {
        return list.extend(mixin);
    };
});
