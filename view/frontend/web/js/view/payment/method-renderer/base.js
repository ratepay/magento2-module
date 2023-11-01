/**
 * Copyright (c) Ratepay GmbH
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

define(
    [
        'Magento_Checkout/js/view/payment/default',
        'jquery',
        'Magento_Checkout/js/model/quote',
        'Magento_Customer/js/model/customer',
        'Magento_Checkout/js/model/url-builder',
        'Magento_Checkout/js/checkout-data',
        'RatePAY_Payment/js/action/update-checkout-config',
        'RatePAY_Payment/js/action/dfp-sent',
        'mage/translate'
    ],
    function (Component, $, quote, customer, urlBuilder, checkoutData, updateCheckoutConfig, markDfpAsSent, $t) {
        'use strict';
        return Component.extend({
            currentBillingAddress: quote.billingAddress,
            currentCustomerData: customer.customerData,


            getPaymentConfig: function () {
                if (window.checkoutConfig.payment[this.getCode()] !== undefined) {
                    return window.checkoutConfig.payment[this.getCode()];
                }
                if (window.checkoutConfig.payment.ratepayConfigRefreshed === undefined) {
                    var data = updateCheckoutConfig(quote.getQuoteId());
                    window.checkoutConfig.payment.ratepayConfigRefreshed = true;
                    if (data.responseJSON !== undefined) {
                        data = data.responseJSON;
                    }
                    if (data.success !== undefined && data.success === true && data.checkout_config !== undefined) {
                        try {
                            this.updatePaymentConfig(JSON.parse(data.checkout_config));
                            return this.getPaymentConfig();
                        } catch (e) {
                            // response stays false
                        }
                    }
                }
                return false;
            },
            handleDeviceFingerprint: function () {
                if (window.checkoutConfig.payment.ratepay.token) {
                    var diSkriptVar = document.createElement('script');
                    diSkriptVar.type = 'text/javascript';
                    diSkriptVar.text =  "var blInserted = true;var di = {t:'" + window.checkoutConfig.payment.ratepay.token + "',v:'" + window.checkoutConfig.payment.ratepay.snippetId + "',l:'checkout'};";
                    document.getElementsByTagName('head')[0].appendChild(diSkriptVar);

                    var diSkript = document.createElement('script');
                    diSkript.type = 'text/javascript';
                    diSkript.src = '//d.ratepay.com/' + window.checkoutConfig.payment.ratepay.token + '/di.js';
                    document.getElementsByTagName('head')[0].appendChild(diSkript);

                    window.checkoutConfig.payment.ratepay.token = false;

                    markDfpAsSent();
                }
            },
            initialize: function () {
                let parentReturn = this._super();
                if (checkoutData.getSelectedPaymentMethod() === this.getCode()) {
                    this.handleDeviceFingerprint();
                }
                return parentReturn;
            },
            selectPaymentMethod: function () {
                this.handleDeviceFingerprint();
                return this._super();
            },
            updatePaymentConfig: function (newPaymentConfig) {
                $.each(newPaymentConfig, function( index, value ) {
                    if (window.checkoutConfig.payment[index] === undefined) {
                        window.checkoutConfig.payment[index] = value;
                    }
                });
            },
            isPlaceOrderActionAllowedRatePay: function () {
                return this.isDifferentAddressNotAllowed() === false && this.isB2BNotAllowed() === false;
            },
            isDifferentAddressNotAllowed: function () {
                var config = this.getPaymentConfig();
                if (config && config.differentShippingAddressAllowed === true) {
                    return false;
                }
                return (quote.billingAddress() === null || quote.billingAddress().getCacheKey() !== quote.shippingAddress().getCacheKey());
            },
            isB2BNotAllowed: function () {
                if (this.isB2BEnabled() === false && this.isCompanySet() === true) {
                    return true;
                }
                return false;
            },
            getCustomerName: function () {
                if (quote.billingAddress() != null && quote.billingAddress().firstname != undefined) {
                    return quote.billingAddress().firstname + ' ' + quote.billingAddress().lastname;
                }
                if (customer.customerData != null && customer.customerData.firstname != undefined) {
                    return customer.customerData.firstname + ' ' + customer.customerData.lastname;
                }
                return '';
            },
            getB2bAccountholders: function () {
                return [this.getCompany(), this.getCustomerName()];
            },
            isPhoneVisible: function () {
                return false;
            },
            isSandboxModeEnabled: function () {
                var config = this.getPaymentConfig();
                if (config) {
                    return config.sandboxMode;
                }
                return false;
            },
            isDobSet: function () {
                if (customer.customerData.dob == undefined || customer.customerData.dob === null) {
                    return false;
                }
                return true;
            },
            isB2BModeUsable: function () {
                if (this.isB2BEnabled() === true && this.isCompanySet() === true) {
                    return true;
                }
                return false;
            },
            isB2BEnabled: function () {
                var config = this.getPaymentConfig();
                if (config) {
                    return config.b2bActive;
                }
                return false;
            },
            isCompanySet: function () {
                if (quote.billingAddress() != null && quote.billingAddress().company != undefined && quote.billingAddress().company.length > 1) {
                    return true;
                }
                return false;
            },
            getCompany: function () {
                if (this.isCompanySet()) {
                    return quote.billingAddress().company;
                }
                return false;
            },
            getData: function() {
                var returnData = {
                    'method': this.getCode(),
                    'additional_data': {
                        'rp_phone': this.rp_phone,
                        'rp_dob_day': this.rp_dob_day,
                        'rp_dob_month': this.rp_dob_month,
                        'rp_dob_year': this.rp_dob_year
                    }
                };

                if (this.rp_vatid.length > 0) {
                    returnData.additional_data.rp_vatid = this.rp_vatid;
                }
                return returnData;
            },
            addDataMultishipping: function () {
                var data = this.getData();
                if (data.additional_data !== undefined) {
                    for (const [key, value] of Object.entries(data.additional_data)) {
                        $('<input>').attr({
                            type: 'hidden',
                            name: 'payment[additional_data][' + key + ']',
                            value: value
                        }).appendTo('#multishipping-billing-form');
                    }
                }
                return true;
            }
        });
    }
);
