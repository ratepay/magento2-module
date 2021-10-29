<?php

namespace RatePAY\Payment\Service\V1;

use RatePAY\Payment\Api\Data\InstallmentPlanResponseInterfaceFactory;
use RatePAY\Payment\Api\InstallmentPlanInterface;

class InstallmentPlan implements InstallmentPlanInterface
{
    /**
     * Factory for the response object
     *
     * @var InstallmentPlanResponseInterfaceFactory
     */
    protected $responseFactory;

    /**
     * @var \Magento\Checkout\Model\Session
     */
    protected $checkoutSession;

    /**
     * @var \RatePAY\Payment\Controller\LibraryController
     */
    protected $rpLibraryController;

    /**
     * @var \RatePAY\Payment\Helper\Data
     */
    protected $rpDataHelper;

    /**
     * @var \Magento\Payment\Helper\Data
     */
    protected $paymentHelper;

    /**
     * @var \RatePAY\Payment\Block\Checkout\InstallmentPlan
     */
    protected $block;

    /**
     * Constructor.
     *
     * @param InstallmentPlanResponseInterfaceFactory $responseFactory
     * @param \Magento\Checkout\Model\Session $checkoutSession
     * @param \RatePAY\Payment\Controller\LibraryController $rpLibraryController
     * @param \RatePAY\Payment\Helper\Data $rpDataHelper
     * @param \Magento\Payment\Helper\Data $paymentHelper
     * @param \RatePAY\Payment\Block\Checkout\InstallmentPlan $block
     */
    public function __construct(
        InstallmentPlanResponseInterfaceFactory $responseFactory,
        \Magento\Checkout\Model\Session $checkoutSession,
        \RatePAY\Payment\Controller\LibraryController $rpLibraryController,
        \RatePAY\Payment\Helper\Data $rpDataHelper,
        \Magento\Payment\Helper\Data $paymentHelper,
        \RatePAY\Payment\Block\Checkout\InstallmentPlan $block
    ) {
        $this->responseFactory = $responseFactory;
        $this->checkoutSession = $checkoutSession;
        $this->rpLibraryController = $rpLibraryController;
        $this->rpDataHelper = $rpDataHelper;
        $this->paymentHelper = $paymentHelper;
        $this->block = $block;
    }

    /**
     * Return installment plan details
     *
     * @param string $calcType
     * @param string $calcValue
     * @param float $grandTotal
     * @param string $methodCode
     * @return \RatePAY\Payment\Service\V1\Data\InstallmentPlanResponse
     */
    public function getInstallmentPlan($calcType, $calcValue, $grandTotal, $methodCode)
    {
        return $this->getInstallmentPlanResponse($calcType, $calcValue, $grandTotal, $methodCode);
    }

    /**
     * Return installment plan details
     *
     * @param string $calcType
     * @param string $calcValue
     * @param float $grandTotal
     * @param string $methodCode
     * @param string $billingCountryId
     * @return \RatePAY\Payment\Service\V1\Data\InstallmentPlanResponse
     */
    public function getInstallmentPlanBackend($calcType, $calcValue, $grandTotal, $methodCode, $billingCountryId)
    {
        return $this->getInstallmentPlanResponse($calcType, $calcValue, $grandTotal, $methodCode, $billingCountryId);
    }

    /**
     * Return installment plan details
     *
     * @param string $calcType
     * @param string $calcValue
     * @param float $grandTotal
     * @param string $methodCode
     * @param string $billingCountryId
     * @return \RatePAY\Payment\Service\V1\Data\InstallmentPlanResponse
     */
    protected function getInstallmentPlanResponse($calcType, $calcValue, $grandTotal, $methodCode, $billingCountryId = null)
    {
        /** @var \RatePAY\Payment\Service\V1\Data\InstallmentPlanResponse $response */
        $response = $this->responseFactory->create();
        $response->setData('success', false);

        $sessionGrandTotal = $this->checkoutSession->getQuote()->getGrandTotal();
        if (empty($sessionGrandTotal) || $sessionGrandTotal == 0) {
            $sessionGrandTotal = $grandTotal; // needed for backend orders
        }

        if (empty($calcType) || floatval($calcValue) <= 0) {
            $response->setData('errormessage', 'Calc data invalid');
            return $response;
        }

        try {
            $installmentPlan = $this->getInstallmentPlanFromRatepay($calcType, (int)$calcValue, $sessionGrandTotal, $methodCode, $billingCountryId);
            if ($installmentPlan !== false) {
                $this->block->setInstallmentData($installmentPlan);
                $this->block->setMethodCode($methodCode);

                $response->setData('success', true);
                $response->setData('installmentPlan', json_encode($installmentPlan));
                $response->setData('installmentHtml', $this->block->toHtml());
            } else {
                $response->setData('errormessage', 'quote not found');
            }
        } catch (\Exception $e) {
            $response->setData('errormessage', $e->getMessage());
        }
        return $response;
    }

    protected function getProfiles($calculationType, $calculationValue, $grandTotal, $methodCode, $billingCountryId = null)
    {
        if (stripos($methodCode, "installment0") !== false) {
            if ($calculationType == "time") {
                return [$this->paymentHelper->getMethodInstance($methodCode)->getMatchingProfile(null, null, $grandTotal, $billingCountryId, $calculationValue)];
            } elseif ($calculationType == "rate") {
                return $this->paymentHelper->getMethodInstance($methodCode)->getMatchingProfiles(null, null, $grandTotal, $billingCountryId);
            }
        }
        return [$this->paymentHelper->getMethodInstance($methodCode)->getMatchingProfile(null, null, $grandTotal, $billingCountryId)];
    }

    protected function selectInstallmentPlan($responses, $calculationType, $calculationValue, $grandTotal)
    {
        $return = $responses[0];
        if (count($responses) > 1) {
            $dDiff = false;
            foreach ($responses as $installmentPlan) {
                $dCurrentDiff = $calculationValue - $installmentPlan['rate'];
                if ($dCurrentDiff < 0) {
                    $dCurrentDiff = $dCurrentDiff * -1;
                }
                if ($dDiff === false || $dCurrentDiff < $dDiff) {
                    $dDiff = $dCurrentDiff;
                    $return = $installmentPlan;
                }
            }
        }
        return $return;
    }

    /**
     * get installment plan
     *
     * @param string $calculationType
     * @param string $calculationValue
     * @param float $grandTotal
     * @param string $methodCode
     * @param string $billingCountryId
     * @return mixed
     */
    protected function getInstallmentPlanFromRatepay($calculationType, $calculationValue, $grandTotal, $methodCode, $billingCountryId = null) {
        $aProfiles = $this->getProfiles($calculationType, $calculationValue, $grandTotal, $methodCode, $billingCountryId);
        if (empty($aProfiles)) {
            return false;
        }

        $responses = [];
        foreach ($aProfiles as $oProfile) {
            $profileId = $oProfile->getData('profile_id');
            $securitycode = $oProfile->getSecurityCode();
            $sandbox = $oProfile->getSandboxMode();
            $installmentPlan = json_decode($this->rpLibraryController->getInstallmentPlan($profileId, $securitycode, $sandbox, $grandTotal, $calculationType, $calculationValue), true);;
            $installmentPlan['validPaymentFirstdays'] = $oProfile->getData('valid_payment_firstdays');
            $installmentPlan['defaultPaymentFirstday'] = $oProfile->getData('payment_firstday');
            $responses[] = $installmentPlan;
        }

        $installmentPlan = $this->selectInstallmentPlan($responses, $calculationType, $calculationValue, $grandTotal);
        $this->checkoutSession->setData('ratepayPaymentAmount_'.$methodCode, $installmentPlan['totalAmount']);
        $this->checkoutSession->setData('ratepayInstallmentNumber_'.$methodCode, $installmentPlan['numberOfRatesFull']);
        $this->checkoutSession->setData('ratepayInstallmentAmount_'.$methodCode, $installmentPlan['rate']);
        $this->checkoutSession->setData('ratepayLastInstallmentAmount_'.$methodCode, $installmentPlan['lastRate']);
        $this->checkoutSession->setData('ratepayInterestRate_'.$methodCode, $installmentPlan['interestRate']);
        return $installmentPlan;
    }
}
