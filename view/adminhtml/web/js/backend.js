/**
 * Copyright (c) Ratepay GmbH
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

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

function showAgreement(code) {
    require(['jquery'], function($) {
        $('#' + code + '_sepa_agreement').show();
        $('#' + code + '_sepa_agreement_link').hide();
    });
}

function showBankTransfer(code) {
    require(['jquery'], function($) {
        $('#' + code + '_sepa_use_directdebit').show();
        $('#' + code + '_sepa_use_banktransfer').hide();
        $('#ratepay_rate_sepa_block_' + code).hide();
        $('#' + code + '_directdebit').val('0');
    });
}
function showDirectDebit(code) {
    require(['jquery'], function($) {
        $('#' + code + '_sepa_use_banktransfer').show();
        $('#' + code + '_sepa_use_directdebit').hide();
        $('#ratepay_rate_sepa_block_' + code).show();
        $('#' + code + '_directdebit').val('1');
    });
}

function updateInstallmentPlanAmount(restUrl, grandTotal, methodCode, currency) {
    require([
        'jquery'
    ], function ($) {
        var calcValue = $('#' + methodCode + '-rate')[0].value;
        if (parseFloat(calcValue) > 0) {
            updateInstallmentPlan(restUrl, 'rate', calcValue, grandTotal, methodCode, currency);
        } else {
            alert("Please enter a valid instalment value");
        }
    });
}

function updateInstallmentPlanRuntime(restUrl, grandTotal, methodCode, currency) {
    require([
        'jquery'
    ], function ($) {
        var calcValue = $('#' + methodCode + '-time')[0].value;
        if (parseFloat(calcValue) > 0) {
            updateInstallmentPlan(restUrl, 'time', calcValue, grandTotal, methodCode, currency);
        }
    });
}

function updateInstallmentPlan(restUrl, calcType, calcValue, grandTotal, methodCode, currency) {
    require([
        'jquery'
    ], function ($) {
        var billingCountryId = "";
        if ($("#order-billing_address_country_id")){
            billingCountryId = $("#order-billing_address_country_id").val();
        }

        var shippingCountryId = "";
        if ($("#order-shipping_address_country_id")){
            shippingCountryId = $("#order-shipping_address_country_id").val();
        }

        if ($("#currency_switcher") && $("#currency_switcher").val() !== undefined && $("#currency_switcher").val() != ""){
            currency = $("#currency_switcher").val();
        }
        var request = {
            calcType: calcType,
            calcValue: calcValue,
            grandTotal: grandTotal,
            methodCode: methodCode,
            billingCountryId: billingCountryId,
            shippingCountryId: shippingCountryId,
            currency: currency
        };
        var data = JSON.stringify(request);
        $.ajax({
            url: restUrl,
            type: 'POST',
            dataType: 'json',
            contentType: 'application/json',
            data: data,
            showLoader: true,
            beforeSend: function(xhr){
                //Empty to remove magento's default handler
            },
            success: function(response) {
                if (response.success === true) {
                    $('#' + methodCode + '_ResultContainer').html(response.installment_html);
                    $('#' + methodCode + '_ContentSwitch').show();

                    var installmentPlan = JSON.parse(response.installment_plan);
                    if (installmentPlan && installmentPlan.validPaymentFirstdays !== undefined && methodCode.indexOf("_installment0") !== -1) {
                        if (installmentPlan.validPaymentFirstdays.indexOf(",") !== -1) {
                            $('#' + methodCode + "_payment_type_selector").show();
                        } else {
                            $('#' + methodCode + "_payment_type_selector").hide();
                        }
                        if (installmentPlan.defaultPaymentFirstday == "2") {
                            $('#ratepay_rate_sepa_block_' + methodCode).show();
                            $('#' + methodCode + '_sepa_use_banktransfer').show();
                            $('#' + methodCode + '_sepa_use_directdebit').hide();
                        } else if (installmentPlan.defaultPaymentFirstday == "28") {
                            $('#ratepay_rate_sepa_block_' + methodCode).hide();
                            $('#' + methodCode + '_sepa_use_banktransfer').hide();
                            $('#' + methodCode + '_sepa_use_directdebit').show();
                        }
                    }
                } else {
                    alert(response.errormessage);
                }
            },
            error: function (xhr, status, errorThrown) {
                alert('An error occured. ' + status);
            }
        });
    });
}

function handleAjustmentRefund(element, dAdjPositive, dAdjNegative) {
    var elemAdjPositive = document.getElementById("adjustment_positive");
    var elemAdjNegative = document.getElementById("adjustment_negative");
    if (element.checked === true) {
        if (window.ratepayAdjustmentPositiveManualData === undefined || (elemAdjPositive.value !== dAdjPositive && elemAdjPositive.value !== window.ratepayAdjustmentPositiveManualData)) {
            window.ratepayAdjustmentPositiveManualData = elemAdjPositive.value;
        }
        if (window.ratepayAdjustmentNegativeManualData === undefined || (elemAdjNegative.value !== dAdjNegative && elemAdjNegative.value !== window.ratepayAdjustmentNegativeManualData)) {
            window.ratepayAdjustmentNegativeManualData = elemAdjNegative.value;
        }
        elemAdjPositive.value = dAdjPositive;
        elemAdjNegative.value = dAdjNegative;

        var event = new Event('change');
        elemAdjPositive.dispatchEvent(event);
    } else if (window.ratepayAdjustmentPositiveManualData !== undefined) {
        elemAdjPositive.value = window.ratepayAdjustmentPositiveManualData;
        elemAdjNegative.value = window.ratepayAdjustmentNegativeManualData;
    }
}
