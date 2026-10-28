<?php

namespace RatePAY\Payment\Helper;

class Secci extends \Magento\Framework\App\Helper\AbstractHelper
{
    /**
     * Payment methods sending installment details with the SECCI request
     */
    const INSTALLMENT_METHODS = ['ratepay_installment', 'ratepay_installment0', 'ratepay_installment_backend', 'ratepay_installment0_backend'];

    /**
     * Session params (suffixed with the method code) needed for the InstallmentDetails of the SECCI request
     */
    const INSTALLMENT_SESSION_PARAMS = [
        'ratepayPaymentAmount',
        'ratepayInstallmentNumber',
        'ratepayInstallmentAmount',
        'ratepayLastInstallmentAmount',
        'ratepayInterestRate',
    ];

    /**
     * @var \RatePAY\Payment\Controller\LibraryController
     */
    protected $libraryController;

    /**
     * @var \RatePAY\Payment\Model\LibraryModel
     */
    protected $libraryModel;

    /**
     * @var \RatePAY\Payment\Helper\Content\Secci\Secci
     */
    protected $rpContentSecciHelper;

    /**
     * @var \RatePAY\Payment\Helper\ProfileConfig
     */
    protected $profileConfigHelper;

    /**
     * @var \Magento\Checkout\Model\Session
     */
    protected $checkoutSession;

    /**
     * @var \RatePAY\Payment\Service\V1\InstallmentPlan
     */
    protected $installmentPlan;

    /**
     * @var \Magento\Payment\Helper\Data
     */
    protected $paymentHelper;

    /**
     * @var \RatePAY\Payment\Model\SecciAttestationState
     */
    protected $secciAttestationState;

    /**
     * @param \Magento\Framework\App\Helper\Context $context
     * @param \RatePAY\Payment\Controller\LibraryController $libraryController
     * @param \RatePAY\Payment\Model\LibraryModel $libraryModel
     * @param \RatePAY\Payment\Helper\Content\Secci\Secci $rpContentSecciHelper
     * @param \RatePAY\Payment\Helper\ProfileConfig $profileConfigHelper
     * @param \Magento\Checkout\Model\Session $checkoutSession
     * @param \RatePAY\Payment\Service\V1\InstallmentPlan $installmentPlan
     * @param \Magento\Payment\Helper\Data $paymentHelper
     * @param \RatePAY\Payment\Model\SecciAttestationState $secciAttestationState
     */
    public function __construct(
        \Magento\Framework\App\Helper\Context $context,
        \RatePAY\Payment\Controller\LibraryController $libraryController,
        \RatePAY\Payment\Model\LibraryModel $libraryModel,
        \RatePAY\Payment\Helper\Content\Secci\Secci $rpContentSecciHelper,
        \RatePAY\Payment\Helper\ProfileConfig $profileConfigHelper,
        \Magento\Checkout\Model\Session $checkoutSession,
        \RatePAY\Payment\Service\V1\InstallmentPlan $installmentPlan,
        \Magento\Payment\Helper\Data $paymentHelper,
        \RatePAY\Payment\Model\SecciAttestationState $secciAttestationState
    ) {
        parent::__construct($context);
        $this->libraryController = $libraryController;
        $this->libraryModel = $libraryModel;
        $this->rpContentSecciHelper = $rpContentSecciHelper;
        $this->profileConfigHelper = $profileConfigHelper;
        $this->checkoutSession = $checkoutSession;
        $this->installmentPlan = $installmentPlan;
        $this->paymentHelper = $paymentHelper;
        $this->secciAttestationState = $secciAttestationState;
    }

    /**
     * The installment details of the SECCI request are taken from the session, see Helper\Content\Payment\Payment::setPayment()
     * They are normally written by the installment plan request when the payment method is rendered, but that request
     * can be missing or not finished yet (e.g. SECCI triggered in parallel on method preselection, expired session),
     * so the installment plan is requested again in this case
     * Has to be done before the request head is built, since the profile of installment0 depends on the runtime
     *
     * @param  \Magento\Quote\Api\Data\CartInterface $quote
     * @param  string                                $paymentMethod
     * @return void
     */
    protected function ensureInstallmentSessionParams($quote, $paymentMethod)
    {
        if (!in_array($paymentMethod, self::INSTALLMENT_METHODS)) {
            return;
        }

        foreach (self::INSTALLMENT_SESSION_PARAMS as $sessionParam) {
            if (empty($this->checkoutSession->getData($sessionParam.'_'.$paymentMethod))) {
                $this->requestInstallmentPlan($quote, $paymentMethod);
                return;
            }
        }
    }

    /**
     * Requests the installment plan with the last used calculation params or the default runtime like in the frontend
     *
     * @param  \Magento\Quote\Api\Data\CartInterface $quote
     * @param  string                                $paymentMethod
     * @return void
     */
    protected function requestInstallmentPlan($quote, $paymentMethod)
    {
        $calculationType = $this->checkoutSession->getData('ratepayInstallmentCalcType_'.$paymentMethod);
        $calculationValue = $this->checkoutSession->getData('ratepayInstallmentCalcValue_'.$paymentMethod);
        if (empty($calculationType) || empty($calculationValue)) {
            $calculationType = 'time';
            $calculationValue = 3;

            $oMethod = $this->paymentHelper->getMethodInstance($paymentMethod);
            if ($oMethod instanceof \RatePAY\Payment\Model\Method\AbstractMethod) {
                $aAllowedMonths = $oMethod->getAllowedMonths($quote->getGrandTotal());
                if (!empty($aAllowedMonths)) {
                    $calculationValue = reset($aAllowedMonths);
                }
            }
        }

        $this->installmentPlan->getInstallmentPlanFromRatepay($calculationType, (int)$calculationValue, $quote->getGrandTotal(), $paymentMethod);
    }

    public function sendSecciRequest($quote, $deliveryMethod, $paymentMethod, $email = null)
    {
        // Only one SECCI is valid at a time, so a failed request (or exception) also invalidates the SECCI of the previous payment method
        $this->secciAttestationState->clear();

        $this->ensureInstallmentSessionParams($quote, $paymentMethod);

        $aSecciParams['Secci'] = $this->rpContentSecciHelper->setSecci($quote, $deliveryMethod, $email);

        $oHead = $this->libraryModel->getRequestHead($quote, 'SECCI_REQUEST', null, $paymentMethod);
        $oContent = $this->libraryModel->getRequestContent($quote, 'SECCI_REQUEST', null, null, $paymentMethod, null, $aSecciParams);

        $blSandbox = $this->profileConfigHelper->getSandboxModeForProfileId($oHead->getCredential()->getProfileId());

        $oSecciRequest = $this->libraryController->callSecciRequest($oHead, $oContent, (bool)$blSandbox);

        if ($oSecciRequest->isSuccessful() === true) {
            $this->secciAttestationState->commit($quote, $paymentMethod, $oSecciRequest->getAttestationToken());
        }

        return $oSecciRequest;
    }
}