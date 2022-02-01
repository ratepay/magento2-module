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
                type: 'ratepay_invoice',
                component: 'RatePAY_Payment/js/view/payment/method-renderer/invoice'
            },
            {
                type:'ratepay_directdebit',
                component:'RatePAY_Payment/js/view/payment/method-renderer/directdebit'
            },
            {
                type:'ratepay_installment',
                component:'RatePAY_Payment/js/view/payment/method-renderer/installment'
            },
            {
                type:'ratepay_installment0',
                component:'RatePAY_Payment/js/view/payment/method-renderer/installment'
            }
        );
        /** Add view logic here if needed */
        return Component.extend({});
    }
);
