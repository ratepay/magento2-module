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
     * @return \RatePAY\Payment\Service\V1\Data\InstallmentPlanResponse
     */
    public function getInstallmentPlan($calcType, $calcValue)
    {
        /** @var \RatePAY\Payment\Service\V1\Data\InstallmentPlanResponse $response */
        $response = $this->responseFactory->create();
        $response->setData('success', false);

        if (empty($calcType) || empty($calcValue)) {
            $response->setData('errormessage', 'calc data invalid');
        }

        $installmentPlan = $this->getInstallmentPlanFromRatepay($calcType, (int)$calcValue);
        if ($installmentPlan !== false) {
            $this->block->setInstallmentData(json_decode($installmentPlan, true));
            $this->block->setMethodCode('installment');

            $response->setData('success', true);
            $response->setData('installmentPlan', $installmentPlan);
            $response->setData('installmentHtml', $this->block->toHtml());
        } else {
            $response->setData('errormessage', 'quote not found');
        }
        return $response;
    }

    /**
     * get installment plan
     *
     * @param string $calculationType
     * @param string $calculationValue
     * @param string|null $template
     * @return mixed
     */
    protected function getInstallmentPlanFromRatepay($calculationType, $calculationValue, $template = null) {
        $quote = $this->checkoutSession->getQuote();
        if (!$quote || !$quote->getId()) {
            return false;
        }

        $orderAmount = $quote->getGrandTotal();

        $countryId = strtolower($quote->getBillingAddress()->getCountryId());
        $methodCode = "ratepay_".$countryId."_installment";

        $profileId = $this->rpDataHelper->getRpConfigData($methodCode, 'profileId');
        $securitycode = $this->rpDataHelper->getRpConfigData($methodCode, 'securityCode');
        $sandbox = $this->rpDataHelper->getRpConfigData($methodCode, 'sandbox');

        $configurationRequest = $this->rpLibraryController->getInstallmentPlan($profileId, $securitycode, $sandbox, $orderAmount, $calculationType, $calculationValue, $template);

        // ToDo: failure handling

        $installmentPlan = json_decode($configurationRequest, true);
        $this->checkoutSession->setRatepayPaymentAmount($installmentPlan['totalAmount']);
        $this->checkoutSession->setRatepayInstallmentNumber($installmentPlan['numberOfRatesFull']);
        $this->checkoutSession->setRatepayInstallmentAmount($installmentPlan['rate']);
        $this->checkoutSession->setRatepayLastInstallmentAmount($installmentPlan['lastRate']);
        $this->checkoutSession->setRatepayInterestRate($installmentPlan['interestRate']);

        return $configurationRequest;
    }
}
