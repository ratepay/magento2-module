/*browser:true*/
/*global define*/
define(
    [
        'uiComponent',
        'Magento_Checkout/js/model/payment/renderer-list'
    ],
    function (Component,
              rendererList) {
        'use strict';
        rendererList.push(
            {
                type: 'ratepay_de_invoice',
                component: 'RatePAY_Payment/js/view/payment/method-renderer/invoice'
            },
            {
                type:'ratepay_at_invoice',
                component:'RatePAY_Payment/js/view/payment/method-renderer/invoice'
            },
            {
                type:'ratepay_ch_invoice',
                component:'RatePAY_Payment/js/view/payment/method-renderer/invoice'
            },
            {
                type:'ratepay_nl_invoice',
                component:'RatePAY_Payment/js/view/payment/method-renderer/invoice'
            },
            {
                type:'ratepay_be_invoice',
                component:'RatePAY_Payment/js/view/payment/method-renderer/invoice'
            },
            {
                type:'ratepay_de_directdebit',
                component:'RatePAY_Payment/js/view/payment/method-renderer/directdebit'
            },
            {
                type:'ratepay_at_directdebit',
                component:'RatePAY_Payment/js/view/payment/method-renderer/directdebit'
            },
            {
                type:'ratepay_nl_directdebit',
                component:'RatePAY_Payment/js/view/payment/method-renderer/directdebit'
            },
            {
                type:'ratepay_be_directdebit',
                component:'RatePAY_Payment/js/view/payment/method-renderer/directdebit'
            },
            {
                type:'ratepay_de_installlment',
                component:'RatePAY_Payment/js/view/payment/method-renderer/installment'
            },
            {
                type:'ratepay_at_installment',
                component:'RatePAY_Payment/js/view/payment/method-renderer/installment'
            },
            {
                type:'ratepay_de_installment',
                component:'RatePAY_Payment/js/view/payment/method-renderer/installment'
            },
            {
                type:'ratepay_at_installment',
                component:'RatePAY_Payment/js/view/payment/method-renderer/installment'
            }
        );
        /** Add view logic here if needed */
        return Component.extend({});
    }
);
