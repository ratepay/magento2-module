/**
 * Copyright (c) Ratepay GmbH
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

define(['jquery', 'mage/translate'], function ($, $t) {
    'use strict';

    return function (config) {
        var formBlock = $('#' + config.code + '_secci'),
            doneBlock = $('#' + config.code + '_secci_done'),
            emailInput = $('#' + config.code + '_secci_email'),
            sendButton = $('#' + config.code + '_secci_send'),
            sendAgainLink = $('#' + config.code + '_secci_send_again'),
            errorMessage = $('#' + config.code + '_secci_error');

        function showError(message) {
            errorMessage.text(message || $t('The Standard EU Consumer Credit Information could not be created. Please try again.')).show();
        }

        function showDone(email, documentId) {
            $('#' + config.code + '_secci_done_email').text(email);
            $('#' + config.code + '_secci_done_document_id').text(documentId || '');
            $('#' + config.code + '_secci_done_document').toggle(!!documentId);
            formBlock.hide();
            doneBlock.show();
        }

        function showForm() {
            errorMessage.hide();
            doneBlock.hide();
            formBlock.show();
        }

        function sendSecci() {
            var email = $.trim(emailInput.val()),
                // Installment plan details from the hidden inputs of the calculated installment plan
                data = $('#payment_form_' + config.code + ' :input[name^="payment[rp_' + config.code + '_"]').serializeArray();

            errorMessage.hide();
            if (email === '') {
                showError($t('Please enter a valid email address.'));
                return;
            }

            data.push(
                {name: 'email', value: email},
                {name: 'paymentMethod', value: config.code},
                {name: 'form_key', value: window.FORM_KEY}
            );

            sendButton.prop('disabled', true);
            $.ajax({
                url: config.url,
                type: 'POST',
                dataType: 'json',
                data: $.param(data)
            }).done(function (response) {
                if (response.success === true) {
                    showDone(email, response.documentId);
                } else {
                    showError(response.errormessage);
                }
            }).fail(function () {
                showError();
            }).always(function () {
                sendButton.prop('disabled', false);
            });
        }

        // New customers have no email in the quote yet, so take it from the account information form
        if (emailInput.val() === '' && $('#email').val()) {
            emailInput.val($('#email').val());
        }

        sendButton.off('click.ratepaySecci').on('click.ratepaySecci', sendSecci);
        sendAgainLink.off('click.ratepaySecci').on('click.ratepaySecci', function (event) {
            event.preventDefault();
            showForm();
        });
        emailInput.off('keydown.ratepaySecci').on('keydown.ratepaySecci', function (event) {
            // Prevent submitting the order form
            if (event.key === 'Enter') {
                event.preventDefault();
                sendSecci();
            }
        });
    };
});
