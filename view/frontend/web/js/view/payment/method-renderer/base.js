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
        'RatePAY_Payment/js/action/trigger-secci',
        'RatePAY_Payment/js/model/config-state',
        'RatePAY_Payment/js/model/secci-state',
        'RatePAY_Payment/js/view/payment/secci-inmethod-info-block-mixin',
        'mage/translate'
    ],
    function (Component, $, quote, customer, urlBuilder, checkoutData, updateCheckoutConfig, markDfpAsSent, triggerSecci, configState, secciState, secciInmethodMixin, $t) {
        'use strict';
        return Component.extend(secciInmethodMixin).extend({
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
                    window.di = {
                        t: window.checkoutConfig.payment.ratepay.token,
                        v: window.checkoutConfig.payment.ratepay.snippetId,
                        l:'checkout'
                    };

                    var diSkript = document.createElement('script');
                    diSkript.type = 'text/javascript';
                    diSkript.src = '//d.ratepay.com/' + window.checkoutConfig.payment.ratepay.snippetId + '/di.js';
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
            sendSecciByEmail: function () {
                return triggerSecci('email').fail(this.showSecciError.bind(this));
            },
            getSecciByPdf: function () {
                return triggerSecci('pdf').fail(this.showSecciError.bind(this));
            },
            showSecciError: function (errorMessage) {
                this.messageContainer.addErrorMessage({
                    'message': errorMessage || $t('The Standard EU Consumer Credit Information could not be created. Please try again.')
                });
            },
            handleSecciBehaviour: function () {
                if (this.isSecciEnabled() === true && window.checkoutConfig?.payment?.ratepay?.triggerSecciOnMethodSelect === true) {
                    window.checkoutConfig.payment.ratepay.triggerSecciOnMethodSelect = false;
                    this.sendSecciByEmail().fail(function () {
                        // Allow a new attempt on the next payment method selection
                        window.checkoutConfig.payment.ratepay.triggerSecciOnMethodSelect = true;
                    });
                }
            },
            selectPaymentMethod: function () {
                this.handleDeviceFingerprint();
                let parentReturn = this._super();
                this.handleSecciBehaviour();
                return parentReturn;
            },
            updatePaymentConfig: function (newPaymentConfig) {
                $.each(newPaymentConfig, function( index, value ) {
                    if (window.checkoutConfig.payment[index] === undefined) {
                        window.checkoutConfig.payment[index] = value;
                    }
                });
                configState.notifyUpdated();
            },
            validate: function () {
                var blParentReturn = this._super();
                if (!blParentReturn) {
                    return blParentReturn;
                }

                if (this.isSecciV2Active() === true) {
                    if (secciState.isCreated() === false) {
                        this.messageContainer.addErrorMessage({'message': $t('Please download or request the SECCI document by email before completing your purchase.')});
                        return false;
                    }
                    if (secciState.isValidFor(this.getCode()) === false) {
                        this.messageContainer.addErrorMessage({'message': $t('Your cart has changed since the SECCI was created. Please request the SECCI again.')});
                        return false;
                    }
                }

                return true;
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
            getCustomerEmail: function () {
                if (quote.guestEmail) {
                    return quote.guestEmail;
                }

                if (window.checkoutConfig?.customerData?.email) {
                    return window.checkoutConfig.customerData.email;
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
            isTelephoneNumberInvalid: function () {
                if (quote.billingAddress() != null && quote.billingAddress().telephone != undefined) {
                    let phone = quote.billingAddress().telephone;
                    phone = phone.replace(" ", "");
                    if (phone.length > 0 && phone.length < 6) {
                        return true;
                    }
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
            isSecciEnabled: function () {
                var config = this.getPaymentConfig();
                if (config) {
                    return config.secciActive;
                }
                return false;
            },
            isSecciV1Active: function () {
                if (this.isSecciEnabled() === true && window.checkoutConfig?.payment?.ratepay?.secciDesignVariant == '1') {
                    return true;
                }
                return false;
            },
            isSecciV2Active: function () {
                if (this.isSecciEnabled() === true && window.checkoutConfig?.payment?.ratepay?.secciDesignVariant == '2') {
                    return true;
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
