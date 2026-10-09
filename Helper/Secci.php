<?php

namespace RatePAY\Payment\Helper;

class Secci extends \Magento\Framework\App\Helper\AbstractHelper
{
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
     * @param \Magento\Framework\App\Helper\Context $context
     * @param \RatePAY\Payment\Controller\LibraryController $libraryController
     * @param \RatePAY\Payment\Model\LibraryModel $libraryModel
     * @param \RatePAY\Payment\Helper\Content\Secci\Secci $rpContentSecciHelper
     * @param \RatePAY\Payment\Helper\ProfileConfig $profileConfigHelper
     * @param \Magento\Checkout\Model\Session $checkoutSession
     */
    public function __construct(
        \Magento\Framework\App\Helper\Context $context,
        \RatePAY\Payment\Controller\LibraryController $libraryController,
        \RatePAY\Payment\Model\LibraryModel $libraryModel,
        \RatePAY\Payment\Helper\Content\Secci\Secci $rpContentSecciHelper,
        \RatePAY\Payment\Helper\ProfileConfig $profileConfigHelper,
        \Magento\Checkout\Model\Session $checkoutSession
    ) {
        parent::__construct($context);
        $this->libraryController = $libraryController;
        $this->libraryModel = $libraryModel;
        $this->rpContentSecciHelper = $rpContentSecciHelper;
        $this->profileConfigHelper = $profileConfigHelper;
        $this->checkoutSession = $checkoutSession;
    }

    public function sendSecciRequest($quote, $deliveryMethod, $paymentMethod, $email = null)
    {
        $aSecciParams['Secci'] = $this->rpContentSecciHelper->setSecci($quote, $deliveryMethod, $email);

        $oHead = $this->libraryModel->getRequestHead($quote, 'SECCI_REQUEST', null, $paymentMethod);
        $oContent = $this->libraryModel->getRequestContent($quote, 'SECCI_REQUEST', null, null, $paymentMethod, null, $aSecciParams);

        $blSandbox = $this->profileConfigHelper->getSandboxModeForProfileId($oHead->getCredential()->getProfileId());
        $oSecciRequest = $this->libraryController->callSecciRequest($oHead, $oContent, (bool)$blSandbox);

        $this->checkoutSession->setRatepayAttestationToken($oSecciRequest->getAttestationToken());

        return $oSecciRequest;
    }
}