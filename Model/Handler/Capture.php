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
     * @var \Magento\SalesSequence\Model\Manager
     */
    protected $sequenceManager;

    /**
     * SendRatepayDeliverCallOnInvoice constructor.
     * @param \RatePAY\Payment\Helper\Data $rpDataHelper
     * @param \RatePAY\Payment\Model\Api\SendConfirmationDeliver $rpConfirmationDelivery
     * @param \Magento\SalesSequence\Model\Manager $sequenceManager
     */
    public function __construct(
        \RatePAY\Payment\Helper\Data $rpDataHelper,
        \RatePAY\Payment\Model\Api\SendConfirmationDeliver $rpConfirmationDelivery,
        \Magento\SalesSequence\Model\Manager $sequenceManager
    ) {
        $this->rpDataHelper = $rpDataHelper;
        $this->rpConfirmationDelivery = $rpConfirmationDelivery;
        $this->sequenceManager = $sequenceManager;
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
            if ($this->isCommunicationToRatepayAllowed($inv, $order)) {
                if ($inv instanceof Invoice && empty($inv->getIncrementId())) {
                    $this->registerInvoiceIncrementId($inv);
                }
                return $this->sendRatepayDeliverCall($order, $inv, $paymentMethod);
            }
        }
        return false;
    }

    /**
     * Sets incrementId of invoice so that it can be transmitted to Ratepay
     * This would normally happen later in the process in the \Magento\Sales\Model\ResourceModel\EntityAbstract->_beforeSave() method
     * This might not be 100% clean but I didnt find another way to achieve this in Mage2 core
     *
     * @param  Invoice $inv
     * @return void
     */
    protected function registerInvoiceIncrementId(Invoice &$inv)
    {
        $store = $inv->getStore();
        $storeId = $store->getId();
        if ($storeId === null) {
            $storeId = $store->getGroup()->getDefaultStoreId();
        }
        $inv->setIncrementId(
            $this->sequenceManager->getSequence(
                $inv->getEntityType(),
                $storeId
            )->getNextValue()
        );
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

        if (!$resultConfirmationDeliver->isSuccessful()) {
            throw new PaymentException(__('Invoice not successful'));
        }
        return true;
    }
}
