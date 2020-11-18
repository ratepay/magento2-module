/*browser:true*/
/*global define*/
define(
    [
        'jquery',
        'RatePAY_Payment/js/view/payment/method-renderer/base',
        'Magento_Customer/js/customer-data',
        'Magento_Checkout/js/model/quote',
        'Magento_Customer/js/model/customer',
        'mage/translate'
    ],
    function ($, Component, customerData, quote, customer, $t) {
        'use strict';

        return Component.extend({
            defaults: {
                template: 'RatePAY_Payment/payment/directdebit',
                rp_phone: '',
                rp_dob_day: '',
                rp_dob_month: '',
                rp_dob_year: '',
                rp_vatid: '',
                rp_iban: '',
                rp_reference: '',
                sepaAccepted: false,
                b2b_accountholder: '',
                rememberIban: false
            },
            validate: function () {
                var blParentReturn = this._super();
                if (!blParentReturn) {
                    return blParentReturn;
                }

                if (this.rp_iban == '') {
                    this.messageContainer.addErrorMessage({'message': $t('Please enter a valid IBAN.')});
                    return false;
                }

                if (this.sepaAccepted === false) {
                    this.messageContainer.addErrorMessage({'message': $t('Please confirm the transmission of the necessary data to Ratepay.')});
                    return false;
                }
                return true;
            },
            getData: function() {
                var parentReturn = this._super();
                if (parentReturn.additional_data === null) {
                    parentReturn.additional_data = {};
                }
                parentReturn.additional_data.rp_iban = this.rp_iban;
                parentReturn.additional_data.rp_accountholder = this.getCustomerName();
                if (this.isB2BModeUsable() === true) {
                    parentReturn.additional_data.rp_accountholder = this.b2b_accountholder;
                }
                parentReturn.additional_data.rp_rememberiban = false;
                if (this.isSavedIbanSelected()) {
                    parentReturn.additional_data.rp_iban_reference = this.getSavedIbanReference();
                } else if (this.rememberIban === true) {
                    parentReturn.additional_data.rp_rememberiban = true;
                }
                return parentReturn;
            },
            showAgreement: function() {
                $('#ratepay_directdebit_sepa_agreement').show();
                $('#ratepay_directdebit_sepa_agreement_link').hide();
            },
            isRememberIBANEnabled: function() {
                return window.checkoutConfig.payment[this.getCode()].rememberIbanEnabled;
            },
            getSavedMaskedIban: function() {
                if (window.checkoutConfig.payment[this.getCode()].savedBankData !== undefined && window.checkoutConfig.payment[this.getCode()].savedBankData !== false) {
                    return window.checkoutConfig.payment[this.getCode()].savedBankData.iban;
                }
                return false;
            },
            getSavedIbanReference: function() {
                if (window.checkoutConfig.payment[this.getCode()].savedBankData !== undefined && window.checkoutConfig.payment[this.getCode()].savedBankData !== false) {
                    return window.checkoutConfig.payment[this.getCode()].savedBankData.bank_account_reference;
                }
                return false;
            },
            isSavedIbanSelected: function() {
                if (this.rp_iban == this.getSavedMaskedIban() && this.getSavedMaskedIban() != "") {
                    return true;
                }
                return false;
            },
            getDefaultIban: function() {
                var savedIban = this.getSavedMaskedIban();
                if (savedIban !== false) {
                    return savedIban;
                }
                return '';
            },
            onChangeIban: function(data, event) {
                if (event.target.value == this.getSavedMaskedIban()) {
                    $('#' + this.getCode() + '_rememberIban').hide();
                } else {
                    $('#' + this.getCode() + '_rememberIban').show();
                }
                return true;
            },
            displaySaveBankdataOverlay: function() {
                $('#' + this.getCode() + '_overlay').show();
            },
            removeSaveBankdataOverlay: function() {
                $('#' + this.getCode() + '_overlay').hide();
            },
            getManageBankdataUrl: function() {
                return window.checkoutConfig.payment.ratepay.manageBankdataUrl;
            },
            initialize: function () {
                this._super();
                this.rp_iban = this.getDefaultIban();
                return this;
            },
        });
    }
);
