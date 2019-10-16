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

function updateInstallmentPlanAmount(restUrl, grandTotal, methodCode) {
    require([
        'jquery'
    ], function ($) {
        updateInstallmentPlan(restUrl, 'rate', $('#' + methodCode + '-rate')[0].value, grandTotal, methodCode);
    });
}

function updateInstallmentPlanRuntime(restUrl, grandTotal, methodCode) {
    require([
        'jquery'
    ], function ($) {
        updateInstallmentPlan(restUrl, 'time', $('#' + methodCode + '-time')[0].value, grandTotal, methodCode);
    });
}

function updateInstallmentPlan(restUrl, calcType, calcValue, grandTotal, methodCode) {
    require([
        'jquery'
    ], function ($) {
        var request = {
            calcType: calcType,
            calcValue: calcValue,
            grandTotal: grandTotal,
            methodCode: methodCode
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
                    //paymentRenderer.setIsInstallmentPlanSet(true);
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