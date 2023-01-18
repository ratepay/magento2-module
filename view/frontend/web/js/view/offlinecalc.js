/**
 * Copyright (c) Ratepay GmbH
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

define(
    [
        'uiElement',
        'jquery',
        'RatePAY_Payment/js/action/calculate-offline-instalment'
    ],
    function (Component, $, calculateOfflineInstalment) {
        'use strict';
        return Component.extend({
            defaults: {
                priceBoxSelector: '.price-box',
                ratepayInstalmentSelector: '#ratepayOfflineCalcDisplay',
                amount: null
            },
            priceType: '',
            price: '',

            /**
             * Initialize
             *
             * @returns {*}
             */
            initialize: function () {
                var priceBox;

                this._super();

                priceBox = $(this.priceBoxSelector);
                priceBox.on('priceUpdated', this._onPriceChange.bind(this));

                if (priceBox.priceBox('option') &&
                    priceBox.priceBox('option').prices &&
                    (priceBox.priceBox('option').prices.finalPrice || priceBox.priceBox('option').prices.basePrice)
                ) {
                    this.priceType = priceBox.priceBox('option').prices.finalPrice ? 'finalPrice' : 'basePrice';
                }

                priceBox.trigger('updatePrice');

                return this;
            },

            /**
             * Handle product price change
             *
             * @param {jQuery.Event} event
             * @param {Object} data
             * @private
             */
            _onPriceChange: function (event, data) {
                if (data[this.priceType].amount !== this.price) { // only calculate new when price has changed
                    calculateOfflineInstalment(data[this.priceType].amount, window.checkout.currency, this.ratepayInstalmentSelector);
                    this.price = data[this.priceType].amount;
                }
            }
        });
    }
);
