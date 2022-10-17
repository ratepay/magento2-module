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
    'Magento_Checkout/js/model/full-screen-loader'
], function ($, urlBuilder, storage, fullScreenLoader) {
    'use strict';

    return function (calcType, calcValue, methodCode, paymentRenderer, showMessage) {
        var serviceUrl;
        var request = {
            calcType: calcType,
            calcValue: calcValue,
            grandTotal: window.checkoutConfig.quoteData.grand_total,
            methodCode: methodCode
        };

        //if (!customer.isLoggedIn()) {
        //    serviceUrl = urlBuilder.createUrl('/guest-carts/:quoteId/ratepay-installmentPlan', {
        //        quoteId: quote.getQuoteId()
        //    });
        //} else {
            serviceUrl = urlBuilder.createUrl('/carts/mine/ratepay-installmentPlan', {});
        //}

        fullScreenLoader.startLoader();

        return storage.post(
            serviceUrl,
            JSON.stringify(request)
        ).done(
            function (response) {
                if (response.success === true) {
                    var installmentPlan = JSON.parse(response.installment_plan);
                    if (installmentPlan && installmentPlan.validPaymentFirstdays !== undefined && methodCode.indexOf("_installment0") !== -1 && window.checkoutConfig.payment[methodCode] !== undefined) {
                        if (installmentPlan.validPaymentFirstdays.indexOf(",") !== -1) {
                            window.checkoutConfig.payment[methodCode].validPaymentFirstdays = installmentPlan.validPaymentFirstdays.split(",");
                        } else {
                            window.checkoutConfig.payment[methodCode].validPaymentFirstdays = installmentPlan.validPaymentFirstdays;
                        }
                        paymentRenderer.togglePaymentTypeSelector();
                        window.checkoutConfig.payment[methodCode].defaultPaymentFirstday = installmentPlan.defaultPaymentFirstday;
                        if (installmentPlan.defaultPaymentFirstday == "2") {
                            paymentRenderer.showDirectDebit();
                        } else if (installmentPlan.defaultPaymentFirstday == "28") {
                            paymentRenderer.showBankTransfer();
                        }
                    }
                    $('#' + methodCode + '_ResultContainer').html(response.installment_html);
                    $('#' + methodCode + '_ContentSwitch').show();
                    paymentRenderer.setIsInstallmentPlanSet(true);
                    if (showMessage === true) {
                        if (calcType == 'time') {
                            paymentRenderer.messageContainer.addSuccessMessage({'message': 'The runtime has been updated successfully.'});
                        } else if(calcType == 'rate') {
                            paymentRenderer.messageContainer.addSuccessMessage({'message': 'The instalment amount has been updated successfully.'});
                        }
                    }
                } else {
                    alert(response.errormessage);
                }
                fullScreenLoader.stopLoader();
            }
        ).fail(
            function (response) {
                alert('An error occured.');
                fullScreenLoader.stopLoader();
            }
        );
    };
});
