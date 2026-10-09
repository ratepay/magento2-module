define([
    'uiComponent',
    'jquery',
    'ko',
    'Magento_Checkout/js/model/quote',
    'Magento_Checkout/js/model/payment/method-list',
    'Magento_Checkout/js/model/step-navigator',
    'RatePAY_Payment/js/model/config-state',
    'mage/translate'
], function (Component, $, ko, quote, methodList, stepNavigator, configState, $t) {
    'use strict';

    return Component.extend({
        defaults: {
            template: 'RatePAY_Payment/payment/secci-v1-info-block'
        },

        initialize: function () {
            this._super();

            this.customerEmail = ko.pureComputed(function () {
                if (quote.guestEmail) {
                    return quote.guestEmail;
                }

                if (window.checkoutConfig?.customerData?.email) {
                    return window.checkoutConfig.customerData.email;
                }
                return '';
            });

            this.secciMethods = ko.pureComputed(function () {
                var activeTitles = [];

                methodList().forEach(function (item) {
                    if (item.method.indexOf('ratepay_') === 0 && window.checkoutConfig?.payment?.[item.method]?.secciActive === true) {
                        activeTitles.push(item.title);
                    }
                });

                if (activeTitles.length === 0) {
                    return '';
                }

                if (activeTitles.length === 1) {
                    return activeTitles[0];
                }

                var lastTitle = activeTitles.pop();
                return activeTitles.join(', ') + " " + $t('or') + " " + lastTitle;
            });

            this.secciV1InfoText = ko.pureComputed(function () {
                return $t("EU consumer credit rules have changed and now require additional information for %1 purchases. This information helps you understand the key credit terms before completing your purchase. Our payment partner Ratepay will use your email address solely to send it, using a third-party email delivery provider.").replace('%1', this.secciMethods());
            }, this);

            this.isPaymentStepActive = ko.pureComputed(function () {
                var paymentStep = stepNavigator.steps().find(function (step) {
                    return step.code === 'payment';
                });

                return !!paymentStep && paymentStep.isVisible();
            });

            this.activeSecciMethods = ko.pureComputed(function () {
                // Observables IMMER zuerst lesen
                var methods = methodList();
                configState.version();

                return methods.filter(function (item) {
                    return item.method.indexOf('ratepay_') === 0
                        && window.checkoutConfig?.payment?.[item.method]?.secciActive === true;
                });
            });

            this.isVisible = ko.pureComputed(function () {
                var stepActive = this.isPaymentStepActive();
                var activeMethods = this.activeSecciMethods();

                if (!stepActive || activeMethods.length === 0) {
                    return false;
                }

                return window.checkoutConfig?.payment?.ratepay?.secciDesignVariant == '1';
            }, this);

            this.isVisible.subscribe(function (visible) {
                if (window.checkoutConfig?.payment?.ratepay) {
                    window.checkoutConfig.payment.ratepay.triggerSecciOnMethodSelect = visible;
                }
            });

            return this;
        },

        toggleDetails: function () {
            var ccd2MoreInfo = document.getElementById('rp-ccd2-more-info');

            var isHidden = ccd2MoreInfo.style.display === 'none' || ccd2MoreInfo.style.display === '';
            ccd2MoreInfo.style.display = isHidden ? 'block' : 'none';
        }
    });
});