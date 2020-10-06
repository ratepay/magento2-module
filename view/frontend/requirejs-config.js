/*jshint browser:true jquery:true*/
/*global alert*/

var config = {
    config: {
        mixins: {
            'Magento_Checkout/js/model/error-processor': {
                'RatePAY_Payment/js/model/error-processor-mixin': true
            },
            'Magento_Checkout/js/view/billing-address': {
                'RatePAY_Payment/js/view/billing-address-mixin': true
            },
            'Magento_Checkout/js/view/payment/list': {
                'RatePAY_Payment/js/view/payment/list-mixin': true
            },
            'Magento_Ui/js/view/messages': {
                'RatePAY_Payment/js/view/messages-mixin': true
            },
        }
    }
};
