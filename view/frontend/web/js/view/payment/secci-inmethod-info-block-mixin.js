define(['jquery', 'mage/translate'], function ($, $t) {
    'use strict';

    return {
        initObservable: function () {
            this._super().observe({
                secciV2DoneMethod: '' // '' = selection visible, 'email' or 'pdf' = done block visible
            });
            return this;
        },
        isSecciV1Visible: function () {
            return this.isSecciV1Active();
        },
        isSecciV2Visible: function () {
            return this.isSecciV2Active() && this.secciV2DoneMethod() === '';
        },
        isSecciV2DoneVisible: function () {
            return this.isSecciV2Active() && this.secciV2DoneMethod() !== '';
        },
        getV1MailSentMessage: function () {
            return $t("We've sent the required Standard European Consumer Credit Information to %1.").replace('%1', this.getCustomerEmail());
        },
        getV2DoneHeading: function () {
            if (this.secciV2DoneMethod() === 'pdf') {
                return $t('Downloaded');
            }
            return $t('Sent by email');
        },
        getV2DoneMessage: function () {
            if (this.secciV2DoneMethod() === 'pdf') {
                return $t('Your Standard EU Consumer Credit Information was downloaded to your device.');
            }
            return $t("Your Standard EU Consumer Credit Information was sent to %1.").replace('%1', this.getCustomerEmail());
        },
        toggleSecciV2Details: function () {
            var ccd2MoreInfo = document.getElementById(this.getCode() + '_secciv2_details');

            var isHidden = ccd2MoreInfo.style.display === 'none' || ccd2MoreInfo.style.display === '';
            ccd2MoreInfo.style.display = isHidden ? 'block' : 'none';
        },
        sendByEmail: function () {
            var self = this,
                emailButton = $('#' + this.getCode() + '_secciv2_btn_email');

            emailButton.prop('disabled', true);
            return this.sendSecciByEmail().done(function () {
                self.secciV2DoneMethod('email');
            }).always(function () {
                emailButton.prop('disabled', false);
            });
        },
        backToSecciV2Selection: function () {
            this.secciV2DoneMethod('');
        },
        getByPdf: function () {
            var self = this;

            return this.getSecciByPdf().done(function () {
                if (self.isSecciV2Active()) {
                    self.secciV2DoneMethod('pdf');
                }
            });
        }
    };
});
