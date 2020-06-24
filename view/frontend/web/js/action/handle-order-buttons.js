/*browser:true*/
/*global define*/
define([
    'jquery',
    'Magento_Checkout/js/model/payment/method-list'
], function ($, methodList) {
    'use strict';

    return function (disabledState, differentCountry) {
        if (window.checkoutConfig.payment.ratepay !== undefined) {
            window.checkoutConfig.payment.ratepay.isAddressSameAsShipping = !disabledState;
        } else {
            window.checkoutConfig.payment['ratepay'] = {isAddressSameAsShipping : !disabledState};
        }
        $.each(methodList(), function( key, value ) {
            var method = value.method;
            if (method.indexOf("ratepay") != -1) {
                if (window.checkoutConfig.payment[method].differentShippingAddressAllowed == false) {
                    $('INPUT#' + method).parents('.payment-method').find('BUTTON.checkout').prop('disabled', disabledState);
                    if (disabledState === true) {
                        $('INPUT#' + method).parents('.payment-method').find('.ratepay_ala_warning').show();
                    } else {
                        $('INPUT#' + method).parents('.payment-method').find('.ratepay_ala_warning').hide();
                    }
                } else {
                    if (differentCountry === true) {
                        $('INPUT#' + method).parents('.payment-method').find('.action.primary.checkout').hide();
                        $('INPUT#' + method).parents('.payment-method').find('.action.primary.updatePaymentMethods').show();
                    } else {
                        $('INPUT#' + method).parents('.payment-method').find('.action.primary.checkout').show();
                        $('INPUT#' + method).parents('.payment-method').find('.action.primary.updatePaymentMethods').hide();
                    }
                }
            }
        });
    };
});
