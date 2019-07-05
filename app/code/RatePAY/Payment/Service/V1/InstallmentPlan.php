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
     * @param \RatePAY\Payment\Block\Checkout\InstallmentPlan $block
     */
    public function __construct(
        InstallmentPlanResponseInterfaceFactory $responseFactory,
        \Magento\Checkout\Model\Session $checkoutSession,
        \RatePAY\Payment\Controller\LibraryController $rpLibraryController,
        \RatePAY\Payment\Helper\Data $rpDataHelper,
        \RatePAY\Payment\Block\Checkout\InstallmentPlan $block
    ) {
        $this->responseFactory = $responseFactory;
        $this->checkoutSession = $checkoutSession;
        $this->rpLibraryController = $rpLibraryController;
        $this->rpDataHelper = $rpDataHelper;
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
        /** @var \RatePAY\Payment\Service\V1\Data\InstallmentPlanResponse $response */
        $response = $this->responseFactory->create();
        $response->setData('success', false);

        if (empty($calcType) || floatval($calcValue) < 0) {
            $response->setData('errormessage', 'calc data invalid');
        }

        try {
            $installmentPlan = $this->getInstallmentPlanFromRatepay($calcType, (int)$calcValue, $grandTotal, $methodCode);
            if ($installmentPlan !== false) {
                $this->block->setInstallmentData(json_decode($installmentPlan, true));
                $this->block->setMethodCode($methodCode);

                $response->setData('success', true);
                $response->setData('installmentPlan', $installmentPlan);
                $response->setData('installmentHtml', $this->block->toHtml());
            } else {
                $response->setData('errormessage', 'quote not found');
            }
        } catch (\Exception $e) {
            $response->setData('errormessage', $e->getMessage());
        }
        return $response;
    }

    /**
     * get installment plan
     *
     * @param string $calculationType
     * @param string $calculationValue
     * @param float $grandTotal
     * @param string $methodCode
     * @return mixed
     */
    protected function getInstallmentPlanFromRatepay($calculationType, $calculationValue, $grandTotal, $methodCode) {
        $profileId = $this->rpDataHelper->getRpConfigData($methodCode, 'profileId');
        $securitycode = $this->rpDataHelper->getRpConfigData($methodCode, 'securityCode');
        $sandbox = $this->rpDataHelper->getRpConfigData($methodCode, 'sandbox');

        $configurationRequest = $this->rpLibraryController->getInstallmentPlan($profileId, $securitycode, $sandbox, $grandTotal, $calculationType, $calculationValue);

        $installmentPlan = json_decode($configurationRequest, true);

        $this->checkoutSession->setRatepayPaymentAmount($installmentPlan['totalAmount']);
        $this->checkoutSession->setRatepayInstallmentNumber($installmentPlan['numberOfRatesFull']);
        $this->checkoutSession->setRatepayInstallmentAmount($installmentPlan['rate']);
        $this->checkoutSession->setRatepayLastInstallmentAmount($installmentPlan['lastRate']);
        $this->checkoutSession->setRatepayInterestRate($installmentPlan['interestRate']);

        return $configurationRequest;
    }
}
