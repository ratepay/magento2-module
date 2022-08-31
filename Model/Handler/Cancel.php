<?php

namespace RatePAY\Payment\Model\Handler;

use \Psr\Log\LoggerInterface;
use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Exception\PaymentException;

class Cancel
{
    /**
     * @var \RatePAY\Payment\Helper\Data
     */
    protected $rpDataHelper;

    /**
     * @var \RatePAY\Payment\Model\LibraryModel
     */
    protected $rpLibraryModel;

    /**
     * @var \RatePAY\Payment\Controller\LibraryController
     */
    protected $rpLibraryController;

    /**
     * @var \RatePAY\Payment\Helper\ProfileConfig
     */
    protected $profileConfigHelper;

    /**
     * SendRatepayCancelCall constructor.
     * @param \RatePAY\Payment\Helper\Data $rpDataHelper
     * @param \Ratepay\Payment\Model\LibraryModel $rpLibraryModel
     * @param \RatePAY\Payment\Controller\LibraryController $rpLibraryController
     * @param \RatePAY\Payment\Helper\ProfileConfig $profileConfigHelper
     */
    function __construct(
        \RatePAY\Payment\Helper\Data $rpDataHelper,
        \RatePAY\Payment\Model\LibraryModel $rpLibraryModel,
        \RatePAY\Payment\Controller\LibraryController $rpLibraryController,
        \RatePAY\Payment\Helper\ProfileConfig $profileConfigHelper
    ) {
        $this->rpDataHelper = $rpDataHelper;
        $this->rpLibraryModel = $rpLibraryModel;
        $this->rpLibraryController = $rpLibraryController;
        $this->profileConfigHelper = $profileConfigHelper;
    }

    /**
     * @param \Magento\Payment\Model\InfoInterface $payment
     * @return bool
     */
    public function executeRatepayCancel(\Magento\Payment\Model\InfoInterface $payment)
    {
        $order = $payment->getOrder();

        return $this->sendRatepayCancelCall($order, $payment->getMethodInstance());
    }

    /**
     * @param $order
     * @param $methodInstance
     * @return bool
     */
    public function sendRatepayCancelCall($order, $methodInstance)
    {
        $sProfileId = null;
        $sSecurityCode = null;
        $blSandbox = null;
        if (is_numeric($order->getRatepaySandboxUsed())) {
            $blSandbox = (bool)$order->getRatepaySandboxUsed();
        }
        if ($order->getRatepayProfileId()) {
            $sProfileId = $order->getRatepayProfileId();
            $sSecurityCode = $this->profileConfigHelper->getSecurityCodeForProfileId($sProfileId, $methodInstance->getCode());
        }
        if ($blSandbox === null) {
            $blSandbox = $this->profileConfigHelper->getSandboxModeForProfileId($sProfileId, $methodInstance->getCode());
        }

        $head = $this->rpLibraryModel->getRequestHead($order, 'PAYMENT_CHANGE', null, null, $sProfileId, $sSecurityCode);
        $content = $this->rpLibraryModel->getRequestContent($order, 'PAYMENT_CHANGE', [], 0);

        if ($blSandbox === null) {
            $blSandbox = $this->profileConfigHelper->getSandboxModeForProfileId($head->getCredential()->getProfileId());
        }
        $cancellationRequest = $this->rpLibraryController->callPaymentChange($head, $content, 'cancellation', $order, $blSandbox);
        if (!$cancellationRequest->isSuccessful()) {
            throw new PaymentException(__('Cancellation was not successsfull'));
        }
        return true;
    }
}
