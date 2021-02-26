<?php

namespace RatePAY\Payment\Model\Handler;

use RatePAY\Payment\Controller\LibraryController;
use RatePAY\Payment\Model\Source\CaptureEvent;
use Magento\Sales\Model\Order\Invoice;
use Magento\Framework\Exception\PaymentException;

class Capture
{
    /**
     * @var \RatePAY\Payment\Helper\Data
     */
    protected $rpDataHelper;

    /**
     * @var \RatePAY\Payment\Model\Api\SendConfirmationDeliver
     */
    protected $rpConfirmationDelivery;

    /**
     * SendRatepayDeliverCallOnInvoice constructor.
     * @param \RatePAY\Payment\Helper\Data $rpDataHelper
     * @param \RatePAY\Payment\Model\Api\SendConfirmationDeliver $rpConfirmationDelivery
     */
    public function __construct(
        \RatePAY\Payment\Helper\Data $rpDataHelper,
        \RatePAY\Payment\Model\Api\SendConfirmationDeliver $rpConfirmationDelivery
    ) {
        $this->rpDataHelper = $rpDataHelper;
        $this->rpConfirmationDelivery = $rpConfirmationDelivery;
    }

    protected function isCommunicationToRatepayAllowed($inv, $order)
    {
        if ($inv->getRequestedCaptureCase() == Invoice::NOT_CAPTURE) {
            return false;
        }
        if ($inv->getRequestedCaptureCase() == Invoice::CAPTURE_OFFLINE && (bool)$this->rpDataHelper->getRpConfigDataByPath("ratepay/general/true_offline_mode", $order->getStore()->getCode()) === true) {
            return false;
        }
        return true;
    }

    /**
     * @param  \Magento\Payment\Model\InfoInterface $payment
     * @param  float                                $amount
     * @return bool
     */
    public function executeRatepayCapture(\Magento\Payment\Model\InfoInterface $payment, $amount)
    {
        $inv = $payment->getOrder()->getInvoiceCollection()->getLastItem();
        if ($inv->getIsUsedForRefund() !== true) { // online refund executes a save on the invoice, which would trigger another confirmation_deliver
            $order = $payment->getOrder();
            $paymentMethod = $payment->getMethodInstance()->getCode();
            if($this->isCommunicationToRatepayAllowed($inv, $order)) {
                return $this->sendRatepayDeliverCall($order, $inv, $paymentMethod);
            }
        }
        return false;
    }

    /**
     * @param $order
     * @param $inv
     * @param $paymentMethod
     * @return bool
     */
    private function sendRatepayDeliverCall($order, $inv, $paymentMethod)
    {
        $resultConfirmationDeliver = $this->rpConfirmationDelivery->sendRatepayDeliverCall($order, $paymentMethod, $inv);

        if(!$resultConfirmationDeliver->isSuccessful()) {
            throw new PaymentException(__('Invoice not successful'));
        }
        return true;
    }
}
