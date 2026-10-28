<?php

/**
 * Copyright (c) Ratepay GmbH
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace RatePAY\Payment\Controller\Adminhtml\Secci;

use Magento\Framework\App\Action\HttpPostActionInterface;

class Send extends \Magento\Backend\App\Action implements HttpPostActionInterface
{
    /**
     * Same permission as needed for creating orders in the backend
     */
    const ADMIN_RESOURCE = 'Magento_Sales::create';

    /**
     * Payment methods using the useDirectDebit param
     */
    const INSTALLMENT_METHODS = ['ratepay_installment_backend', 'ratepay_installment0_backend'];

    /**
     * @var \Magento\Framework\Controller\Result\JsonFactory
     */
    protected $resultJsonFactory;

    /**
     * @var \Magento\Backend\Model\Session\Quote
     */
    protected $backendCheckoutSession;

    /**
     * @var \RatePAY\Payment\Helper\Secci
     */
    protected $secciHelper;

    /**
     * @var \RatePAY\Payment\Model\BackendSecciState
     */
    protected $backendSecciState;

    /**
     * @var \Magento\Payment\Helper\Data
     */
    protected $paymentHelper;

    /**
     * Constructor
     *
     * @param \Magento\Backend\App\Action\Context              $context
     * @param \Magento\Framework\Controller\Result\JsonFactory $resultJsonFactory
     * @param \Magento\Backend\Model\Session\Quote             $backendCheckoutSession
     * @param \RatePAY\Payment\Helper\Secci                    $secciHelper
     * @param \RatePAY\Payment\Model\BackendSecciState         $backendSecciState
     * @param \Magento\Payment\Helper\Data                     $paymentHelper
     */
    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Magento\Framework\Controller\Result\JsonFactory $resultJsonFactory,
        \Magento\Backend\Model\Session\Quote $backendCheckoutSession,
        \RatePAY\Payment\Helper\Secci $secciHelper,
        \RatePAY\Payment\Model\BackendSecciState $backendSecciState,
        \Magento\Payment\Helper\Data $paymentHelper
    ) {
        parent::__construct($context);
        $this->resultJsonFactory = $resultJsonFactory;
        $this->backendCheckoutSession = $backendCheckoutSession;
        $this->secciHelper = $secciHelper;
        $this->backendSecciState = $backendSecciState;
        $this->paymentHelper = $paymentHelper;
    }

    /**
     * The installment plan is calculated by a REST call, which doesn't share the admin session.
     * So the installment details are posted from the payment form and written to the admin session,
     * like it is done in AbstractMethod::assignData() when the order is submitted
     *
     * @param  string $sPaymentMethod
     * @return void
     */
    protected function handleInstallmentParams($sPaymentMethod)
    {
        $aPaymentParams = $this->getRequest()->getParam('payment');
        if (empty($aPaymentParams) || !is_array($aPaymentParams)) {
            return;
        }

        $oMethod = $this->paymentHelper->getMethodInstance($sPaymentMethod);
        if ($oMethod instanceof \RatePAY\Payment\Model\Method\AbstractMethod) {
            $oMethod->handleInstallmentSessionParams(new \Magento\Framework\DataObject($aPaymentParams), $sPaymentMethod);
        }
    }

    /**
     * The DebitPayType of the SECCI request is taken from the payment, see Helper\Content\Payment\Payment::setPayment()
     * The payment isn't saved here, the value is assigned again with the payment data when the order is placed
     *
     * @param  \Magento\Quote\Model\Quote $oQuote
     * @param  string                     $sPaymentMethod
     * @return bool|null
     */
    protected function handleDirectDebitParam($oQuote, $sPaymentMethod)
    {
        $sUseDirectDebit = $this->getRequest()->getParam('useDirectDebit');
        if ($sUseDirectDebit === null || !in_array($sPaymentMethod, self::INSTALLMENT_METHODS)) {
            return null;
        }

        $blUseDirectDebit = filter_var($sUseDirectDebit, FILTER_VALIDATE_BOOLEAN);
        $oQuote->getPayment()->setAdditionalInformation('rp_directdebit', $blUseDirectDebit);
        return $blUseDirectDebit;
    }

    /**
     * Sends the SECCI for the current backend order quote by email
     *
     * @return \Magento\Framework\Controller\Result\Json
     */
    public function execute()
    {
        $aData = ['success' => false];

        $sEmail = trim((string)$this->getRequest()->getParam('email'));
        $sPaymentMethod = (string)$this->getRequest()->getParam('paymentMethod');

        try {
            if (strpos($sPaymentMethod, 'ratepay_') !== 0
                || substr($sPaymentMethod, -strlen(\RatePAY\Payment\Model\Method\AbstractMethod::BACKEND_SUFFIX)) !== \RatePAY\Payment\Model\Method\AbstractMethod::BACKEND_SUFFIX
            ) {
                throw new \Magento\Framework\Exception\LocalizedException(__('Invalid payment method.'));
            }

            if (filter_var($sEmail, FILTER_VALIDATE_EMAIL) === false) {
                throw new \Magento\Framework\Exception\LocalizedException(__('Please enter a valid email address.'));
            }

            $oQuote = $this->backendCheckoutSession->getQuote();
            if (!$oQuote || !$oQuote->getId()) {
                throw new \Magento\Framework\Exception\LocalizedException(__('Cart not found.'));
            }

            // Backend quotes are inactive, so Magento doesn't fill the items like it does for active quotes in the QuoteRepository LoadHandler
            if (empty($oQuote->getItems())) {
                $oQuote->setItems($oQuote->getAllVisibleItems());
            }

            // Ratepay wants a user-friendly error message to be shown instead of the API error, like in the frontend
            $sGenericErrorMessage = __('Standard European Consumer Credit Information (SECCI) is temporarily unavailable. Please try again.');

            try {
                $this->handleInstallmentParams($sPaymentMethod);
                $blUseDirectDebit = $this->handleDirectDebitParam($oQuote, $sPaymentMethod);

                $oSecciRequest = $this->secciHelper->sendSecciRequest(
                    $oQuote,
                    \RatePAY\Payment\Helper\Content\Secci\Secci::SECCI_DELIVERY_METHOD_EMAIL,
                    $sPaymentMethod,
                    $sEmail
                );

                if ($oSecciRequest->isSuccessful() === true) {
                    $this->backendSecciState->commit($oQuote, $sPaymentMethod, $sEmail, $oSecciRequest->getDocumentId(), $blUseDirectDebit);
                    $aData['success'] = true;
                    $aData['documentId'] = $oSecciRequest->getDocumentId();
                } else {
                    $this->backendSecciState->clear();
                    $aData['errormessage'] = $sGenericErrorMessage;
                }
            } catch (\Exception $exc) {
                $aData['errormessage'] = $sGenericErrorMessage;
            }
        } catch (\Exception $exc) {
            // Validation errors of the request parameters above
            $aData['errormessage'] = $exc->getMessage();
        }

        /** @var \Magento\Framework\Controller\Result\Json $resultJson */
        $resultJson = $this->resultJsonFactory->create();
        return $resultJson->setData($aData);
    }
}
