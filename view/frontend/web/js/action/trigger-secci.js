/**
 * Copyright (c) Ratepay GmbH
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/*browser:true*/
/*global define*/
define([
    'jquery',
    'Magento_Checkout/js/model/url-builder',
    'mage/storage',
    'Magento_Customer/js/model/customer',
    'Magento_Checkout/js/model/quote',
    'RatePAY_Payment/js/model/secci-state'
], function ($, urlBuilder, storage, customer, quote, secciState) {
    'use strict';

    // Only Safari on iOS / iPadOS, other iOS browsers (Chrome, Firefox, Edge, Opera) download the PDF correctly
    // iPadOS reports itself as MacIntel, but has touch points
    function isIosSafari() {
        var userAgent = navigator.userAgent;
        var isIos = /iPad|iPhone|iPod/.test(userAgent)
            || (navigator.platform === 'MacIntel' && navigator.maxTouchPoints > 1);

        return isIos
            && /Version\/[\d.]+.*Safari\//.test(userAgent)
            && !/CriOS|FxiOS|EdgiOS|OPiOS|OPT\//.test(userAgent);
    }

    return function (method, params) {
        var serviceUrl;
        var deferred = $.Deferred();
        var paymentMethod = quote.paymentMethod();
        // Safari on iOS ignores the download attribute and opens the PDF in the current tab, which leaves the checkout.
        // The new tab has to be opened directly in the click handler, otherwise it is blocked as a popup
        var pdfWindow = (method === 'pdf' && isIosSafari()) ? window.open('', '_blank') : null;
        // Closes the previously opened tab if no PDF was received
        var closePdfWindow = function () {
            if (pdfWindow) {
                pdfWindow.close();
                pdfWindow = null;
            }
        };

        var request = {
            method: method,
            paymentMethod: paymentMethod ? paymentMethod.method : ''
        };
        // Additional params of the payment method, e.g. useDirectDebit for installments
        $.extend(request, params || {});
        var secciCartState = secciState.capture(request.paymentMethod, params);
        if (!customer.isLoggedIn()) {
            // The guest email may not be saved in the quote yet, because set-payment-information runs in parallel
            if (quote.guestEmail) {
                request.email = quote.guestEmail;
            }
            serviceUrl = urlBuilder.createUrl('/guest-carts/:quoteId/ratepay-triggerSecci', {
                quoteId: quote.getQuoteId()
            });
        } else {
            serviceUrl = urlBuilder.createUrl('/carts/mine/ratepay-triggerSecci', {});
        }

        storage.post(
            serviceUrl,
            JSON.stringify(request)
        ).done(
            function (response) {
                if (response.success == true) {
                    secciState.commit(secciCartState);
                    if (method === 'pdf' && response.pdf_string) {
                        var binary = atob(response.pdf_string),
                            bytes = new Uint8Array(binary.length),
                            i, url, link;

                        for (i = 0; i < binary.length; i++) {
                            bytes[i] = binary.charCodeAt(i);
                        }

                        url = URL.createObjectURL(new Blob([bytes], {type: 'application/pdf'}));
                        if (pdfWindow) {
                            // Not revoked, the PDF tab still needs the URL (e.g. for sharing / saving), it is released when the checkout is left
                            pdfWindow.location.href = url;
                            pdfWindow = null;
                        } else {
                            link = document.createElement('a');
                            link.href = url;
                            link.download = response.filename + '.pdf';
                            document.body.appendChild(link);
                            link.click();
                            document.body.removeChild(link);
                            setTimeout(function () { URL.revokeObjectURL(url); }, 1000);
                        }
                    }
                    closePdfWindow();
                    deferred.resolve(response);
                } else {
                    console.log("ERROR", response);
                    closePdfWindow();
                    secciState.reset();
                    deferred.reject(response.errormessage || '');
                }
            }
        ).fail(
            function (response) {
                console.log("ERROR", response);
                closePdfWindow();
                secciState.reset();
                deferred.reject(response.responseJSON && response.responseJSON.message ? response.responseJSON.message : '');
            }
        );

        // Resolved only if the SECCI was created successfully, rejected with the error message otherwise
        return deferred.promise();
    };
});
