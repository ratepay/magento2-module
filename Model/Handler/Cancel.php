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
     * SendRatepayCancelCall constructor.
     * @param \RatePAY\Payment\Helper\Data $rpDataHelper
     * @param \Ratepay\Payment\Model\LibraryModel $rpLibraryModel
     * @param \RatePAY\Payment\Controller\LibraryController $rpLibraryController
     */
    function __construct(
        \RatePAY\Payment\Helper\Data $rpDataHelper,
        \RatePAY\Payment\Model\LibraryModel $rpLibraryModel,
        \RatePAY\Payment\Controller\LibraryController $rpLibraryController
    ) {
        $this->rpDataHelper = $rpDataHelper;
        $this->rpLibraryModel = $rpLibraryModel;
        $this->rpLibraryController = $rpLibraryController;

    }

    /**
     * @param \Magento\Payment\Model\InfoInterface $payment
     * @return bool
     */
    public function executeRatepayCancel(\Magento\Payment\Model\InfoInterface $payment)
    {
        $order = $payment->getOrder();
        $paymentMethod = $payment->getMethodInstance()->getCode();

        return $this->sendRatepayCancelCall($order, $paymentMethod);
    }

    /**
     * @param $order
     * @param $paymentMethod
     * @return bool
     */
    public function sendRatepayCancelCall($order, $paymentMethod)
    {
        $sandbox = (bool)$this->rpDataHelper->getRpConfigData($paymentMethod, 'sandbox', $order->getStore()->getId());
        $head = $this->rpLibraryModel->getRequestHead($order, 'PAYMENT_CHANGE');
        $content = $this->rpLibraryModel->getRequestContent($order, 'PAYMENT_CHANGE', [], 0);
        $cancellationRequest = $this->rpLibraryController->callPaymentChange($head, $content, 'cancellation', $order, $sandbox);
        if (!$cancellationRequest->isSuccessful()){
            throw new PaymentException(__('Cancellation was not successsfull'));
        }
        return true;
    }
}
