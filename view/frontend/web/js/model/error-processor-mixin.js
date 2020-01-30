/*jshint browser:true jquery:true*/
/*global alert*/

define(
    [
        'jquery',
        'mage/url',
        'mage/utils/wrapper',
        'Magento_Ui/js/model/messageList',
        'Magento_Checkout/js/model/payment/method-list'
    ],
    function ($, url, wrapper, globalMessageList, methodList) {
        'use strict';

        return function (targetModule) {
            targetModule.disablePaymentMethod = function (sPaymentMethod) {
                $('INPUT#' + sPaymentMethod).parents('.payment-method').fadeOut(3000, function() {
                    $('INPUT#' + sPaymentMethod).parents('.payment-method').remove();
                });
            };

            targetModule.process = wrapper.wrap(targetModule.process, function (originalAction, response, messageContainer) {
                var origReturn = originalAction(response, messageContainer);

                if (response.responseJSON.hasOwnProperty('parameters') && response.responseJSON.parameters.hasOwnProperty('disablePaymentMethod') && response.responseJSON.parameters.disablePaymentMethod.length > 0) {
                    $.each(methodList(), function( key, value ) {
                        if (value.method == response.responseJSON.parameters.disablePaymentMethod) {
                            targetModule.disablePaymentMethod(value.method);
                        }
                    });
                }
                return origReturn;
            });
            return targetModule;
        };
    }
);
