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

function setDirectDebit(code, value) {
    require(['jquery'], function($) {
        var input = $('#' + code + '_directdebit');
        // The SECCI depends on the debit pay type, the change event triggers the SECCI reset in secci-backend.js
        if (input.val() !== value) {
            input.val(value).trigger('change');
        }
    });
}
function showBankTransfer(code) {
    require(['jquery'], function($) {
        $('#' + code + '_sepa_use_directdebit').show();
        $('#' + code + '_sepa_use_banktransfer').hide();
        $('#ratepay_rate_sepa_block_' + code).hide();
        $('#' + code + '_terms_block_sepa').hide();
        setDirectDebit(code, '0');
    });
}
function showDirectDebit(code) {
    require(['jquery'], function($) {
        $('#' + code + '_sepa_use_banktransfer').show();
        $('#' + code + '_sepa_use_directdebit').hide();
        $('#ratepay_rate_sepa_block_' + code).show();
        $('#' + code + '_terms_block_sepa').show();
        setDirectDebit(code, '1');
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
                        // Also sets the rp_directdebit value, so it matches the displayed debit pay type
                        if (installmentPlan.defaultPaymentFirstday == "2") {
                            showDirectDebit(methodCode);
                        } else if (installmentPlan.defaultPaymentFirstday == "28") {
                            showBankTransfer(methodCode);
                        }
                    }

                    $('#rp-show-installment-plan-details_' + methodCode).on( "click", function() {
                        changeDetails(methodCode);
                    }); // doing it this way since Mage 2.4.7 doesnt allow this inline anymore
                    $('#rp-hide-installment-plan-details_' + methodCode).on( "click", function() {
                        changeDetails(methodCode);
                    }); // doing it this way since Mage 2.4.7 doesnt allow this inline anymore
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
