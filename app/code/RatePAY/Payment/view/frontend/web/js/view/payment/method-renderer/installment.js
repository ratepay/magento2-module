/*browser:true*/
/*global define*/
define(
    [
        'jquery',
        'ko',
        'Magento_Checkout/js/view/payment/default',
        'Magento_Customer/js/customer-data',
        'Magento_Checkout/js/model/quote',
        'Magento_Customer/js/model/customer',
        'RatePAY_Payment/js/action/installmentplan',
        'mage/translate'
    ],
    function ($, ko, Component, customerData, quote, customer, getInstallmentPlan, $t) {
        'use strict';

        return Component.extend({
            defaults: {
                template: 'RatePAY_Payment/payment/installment',
                rp_phone: '',
                rp_dob_day: '',
                rp_dob_month: '',
                rp_dob_year: '',
                rp_iban: ''
            },

            currentBillingAddress: quote.billingAddress,
            currentCustomerData: customer.customerData,

            initialize: function () {
                this._super();
                if (this.hasAllowedMonths() === false) {
                    this.updateInstallmentPlan('time', '12', this.getCode());
                }
                return this;
            },
            initObservable: function () {
                this._super()
                    .observe({
                        isPhoneVisible: false,
                        isDobSet: customer.customerData.dob == null
                    });
                return this;
            },
            validate: function () {
                if (this.showSepaBlock() === true && this.rp_iban == '') {
                    this.messageContainer.addErrorMessage({'message': $t('Please enter a valid IBAN.')});
                    return false;
                }
                return true;
            },
            showSepaBlock: function () {return true;
                var validPaymentFirstdays = window.checkoutConfig.payment.ratepay_de_installment.validPaymentFirstdays;
                if (validPaymentFirstdays == '2' || Array.isArray(validPaymentFirstdays)) {
                    return true;
                }
                return false;
            },
            getAllowedMonths: function () {
                return window.checkoutConfig.payment.ratepay_de_installment.allowedMonths;
            },
            hasAllowedMonths: function () {
                if (this.getAllowedMonths().length > 0) {
                    return true;
                }
                return false;
            },
            useMonthDropdown: function () {
                if (this.getAllowedMonths().length > 9) {
                    return true;
                }
                return false;
            },
            updateInstallmentPlan: function (calcType, calcValue, methodCode) {
                getInstallmentPlan(calcType, calcValue, methodCode);
            },
            updateInstallmentPlanAmount: function () {
                this.updateInstallmentPlan('rate', $('#' + this.getCode() + '-rate')[0].value, this.getCode());
            },
            updateInstallmentPlanRuntime: function (data, event) {
                this.updateInstallmentPlan('time', event.target.value, this.getCode());
            },
            showAgreement: function() {
                $('#' + this.getCode() + '_sepa_agreement').show();
                $('#' + this.getCode() + '_sepa_agreement_link').hide();
            },
            getData: function() {
                return {
                    'method': this.getCode(),
                    'additional_data': {
                        'rp_phone': this.rp_phone,
                        'rp_dob_day': this.rp_dob_day,
                        'rp_dob_month': this.rp_dob_month,
                        'rp_dob_year': this.rp_dob_year,
                        'rp_iban': this.rp_iban
                    }
                }
            }
        });
    }
);


function changeDetails(paymentMethod) {
    var hide = document.getElementById("rp-hide-installment-plan-details_" + paymentMethod);
    var show = document.getElementById("rp-show-installment-plan-details_" + paymentMethod);
    var details = document.getElementById("rp-installment-plan-details_" + paymentMethod);
    var nodetails = document.getElementById("rp-installment-plan-no-details_" + paymentMethod);

    if (hide.style.display == "block") {
        hide.style.display = "none";
        nodetails.style.display = "block";
        show.style.display = "block";
        details.style.display = "none";
    } else {
        hide.style.display = "block";
        nodetails.style.display = "none";
        show.style.display = "none";
        details.style.display = "block";
    }
}
