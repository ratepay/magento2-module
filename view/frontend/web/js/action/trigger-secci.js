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
    return function (method, params) {
        var serviceUrl;
        var deferred = $.Deferred();
        var paymentMethod = quote.paymentMethod();

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
                        link = document.createElement('a');
                        link.href = url;
                        link.download = response.filename + '.pdf';
                        document.body.appendChild(link);
                        link.click();
                        document.body.removeChild(link);
                        setTimeout(function () { URL.revokeObjectURL(url); }, 1000);
                    }
                    deferred.resolve(response);
                } else {
                    console.log("ERROR", response);
                    secciState.reset();
                    deferred.reject(response.errormessage || '');
                }
            }
        ).fail(
            function (response) {
                console.log("ERROR", response);
                secciState.reset();
                deferred.reject(response.responseJSON && response.responseJSON.message ? response.responseJSON.message : '');
            }
        );

        // Resolved only if the SECCI was created successfully, rejected with the error message otherwise
        return deferred.promise();
    };
});
