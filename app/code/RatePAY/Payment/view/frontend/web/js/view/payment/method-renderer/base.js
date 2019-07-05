define(
    [
        'Magento_Checkout/js/view/payment/default',
        'jquery',
        'Magento_Checkout/js/model/quote',
        'Magento_Customer/js/model/customer',
        'mage/translate'
    ],
    function (Component, $, quote, customer, $t) {
        'use strict';
        return Component.extend({
            currentBillingAddress: quote.billingAddress,
            currentCustomerData: customer.customerData,

            getCustomerName: function () {
                if (quote.billingAddress() != null && quote.billingAddress().firstname != undefined) {
                    return quote.billingAddress().firstname + ' ' + quote.billingAddress().lastname;
                }
                if (customer.customerData != null && customer.customerData.firstname != undefined) {
                    return customer.customerData.firstname + ' ' + customer.customerData.lastname;
                }
                return ''
            },
            isPhoneVisible: function () {
                return false;
            },
            isDobSet: function () {
                if (customer.customerData.dob === null) {
                    return false;
                }
                return true;
            },
            isCompanySet: function () {
                if (quote.billingAddress() != null && quote.billingAddress().company != undefined && quote.billingAddress().company.length > 1) {
                    return true;
                }
                if (customer.customerData != null && customer.customerData.company != undefined && customer.customerData.company.length > 1) {

                }
                return false;
            },
            getCompany: function () {
                if (this.isCompanySet()) {
                    return quote.billingAddress().company;
                }
                return false;
            },
            validate: function () {
                if (this.isCompanySet() && this.rp_vatid == '') {
                    this.messageContainer.addErrorMessage({'message': $t('Please enter your Vat ID')});
                    return false;
                }
                return true;
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
            }
        });
    }
);
