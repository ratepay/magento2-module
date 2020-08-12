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
        }
    }
};
