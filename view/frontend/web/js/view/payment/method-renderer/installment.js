/*browser:true*/
/*global define*/
define(
    [
        'jquery',
        'RatePAY_Payment/js/view/payment/method-renderer/base',
        'Magento_Customer/js/customer-data',
        'Magento_Checkout/js/model/quote',
        'Magento_Customer/js/model/customer',
        'RatePAY_Payment/js/action/installmentplan',
        'mage/translate'
    ],
    function ($, Component, customerData, quote, customer, getInstallmentPlan, $t) {
        'use strict';

        return Component.extend({
            defaults: {
                template: 'RatePAY_Payment/payment/installment',
                rp_phone: '',
                rp_dob_day: '',
                rp_dob_month: '',
                rp_dob_year: '',
                rp_vatid: '',
                rp_iban: '',
                isInstallmentPlanSet: false,
                useDirectDebit: true
            },

            initialize: function () {
                this._super();
                if (this.hasAllowedMonths() === false) {
                    this.updateInstallmentPlan('time', '3', this.getCode(), false);
                } else if(this.hasSingleAllowedMonth()) {
                    this.updateInstallmentPlan('time', this.getAllowedMonths()[0], this.getCode(), false);
                }
                return this;
            },
            validate: function () {
                var blParentReturn = this._super();
                if (!blParentReturn) {
                    return blParentReturn;
                }

                if (this.showSepaBlock() === true && this.useDirectDebit === true && this.rp_iban == '') {
                    this.messageContainer.addErrorMessage({'message': $t('Please enter a valid IBAN.')});
                    return false;
                }

                if (this.showSepaBlock() === true && this.useDirectDebit === true && this.sepaAccepted === false) {
                    this.messageContainer.addErrorMessage({'message': $t('Please confirm the transmission of the necessary data to Ratepay.')});
                    return false;
                }

                if (this.isInstallmentPlanSet === false) {
                    this.messageContainer.addErrorMessage({'message': $t('Please select a installment runtime or installment amount.')});
                    return false;
                }
                return true;
            },
            showSepaBlock: function () {
                var validPaymentFirstdays = window.checkoutConfig.payment[this.getCode()].validPaymentFirstdays;
                if (validPaymentFirstdays == '2' || Array.isArray(validPaymentFirstdays)) {
                    return true;
                }
                this.useDirectDebit = false;
                return false;
            },
            getAllowedMonths: function () {
                return window.checkoutConfig.payment[this.getCode()].allowedMonths;
            },
            hasAllowedMonths: function () {
                if (this.getAllowedMonths().length > 0) {
                    return true;
                }
                return false;
            },
            hasSingleAllowedMonth: function () {
                if (this.getAllowedMonths().length === 1) {
                    return true;
                }
                return false;
            },
            setIsInstallmentPlanSet: function (value) {
                this.isInstallmentPlanSet = value;
            },
            useMonthDropdown: function () {
                if (this.getAllowedMonths().length > 9) {
                    return true;
                }
                return false;
            },
            updateInstallmentPlan: function (calcType, calcValue, methodCode, showMessage) {
                getInstallmentPlan(calcType, calcValue, methodCode, this, showMessage);
            },
            updateInstallmentPlanAmount: function () {
                this.updateInstallmentPlan('rate', $('#' + this.getCode() + '-rate')[0].value, this.getCode(), true);
            },
            updateInstallmentPlanRuntime: function (data, event) {
                this.updateInstallmentPlan('time', event.target.value, this.getCode(), true);
            },
            showAgreement: function() {
                $('#' + this.getCode() + '_sepa_agreement').show();
                $('#' + this.getCode() + '_sepa_agreement_link').hide();
            },
            showBankTransfer: function () {
                $('#' + this.getCode() + '_sepa_use_directdebit').show();
                $('#' + this.getCode() + '_sepa_use_banktransfer').hide();
                $('#ratepay_rate_sepa_block_' + this.getCode()).hide();
                this.useDirectDebit = false;
            },
            showDirectDebit: function () {
                $('#' + this.getCode() + '_sepa_use_banktransfer').show();
                $('#' + this.getCode() + '_sepa_use_directdebit').hide();
                $('#ratepay_rate_sepa_block_' + this.getCode()).show();
                this.useDirectDebit = true;
            },
            getData: function() {
                var parentReturn = this._super();
                if (parentReturn.additional_data === null) {
                    parentReturn.additional_data = {};
                }
                parentReturn.additional_data.rp_iban = this.rp_iban;
                parentReturn.additional_data.rp_directdebit = this.useDirectDebit;
                return parentReturn;
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
