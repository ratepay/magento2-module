<?php
/**
 * Created by PhpStorm.
 * User: SebastianN
 * Date: 26.06.17
 * Time: 17:25
 */

namespace RatePAY\Payment\Observer;

use Magento\Framework\Event\ObserverInterface;
use Magento\Sales\Model\Order\Invoice;
use RatePAY\Payment\Controller\LibraryController;
use Magento\Framework\Exception\PaymentException;
use RatePAY\Payment\Model\Source\CaptureEvent;

class SendRatepayDeliverCallOnInvoice implements ObserverInterface
{
    /**
     * @var \RatePAY\Payment\Helper\Data
     */
    protected $rpDataHelper;

    /**
     * @var \RatePAY\Payment\Helper\Payment
     */
    protected $rpPaymentHelper;

    /**
     * @var \RatePAY\Payment\Model\LibraryModel
     */
    protected $rpLibraryModel;

    /**
     * @var LibraryController
     */
    protected $rpLibraryController;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $storeManager;

    /**
     * @var \RatePAY\Payment\Model\Api\SendConfirmationDeliver
     */
    protected $rpConfirmationDelivery;

    /**
     * SendRatepayDeliverCallOnInvoice constructor.
     * @param \RatePAY\Payment\Model\LibraryModel $rpLibraryModel
     * @param \RatePAY\Payment\Helper\Data $rpDataHelper
     * @param \RatePAY\Payment\Helper\Payment $rpPaymentHelper
     * @param LibraryController $rpLibraryController
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \RatePAY\Payment\Model\Api\SendConfirmationDeliver $rpConfirmationDelivery
     */
    public function __construct(
        \RatePAY\Payment\Model\LibraryModel $rpLibraryModel,
        \RatePAY\Payment\Helper\Data $rpDataHelper,
        \RatePAY\Payment\Helper\Payment $rpPaymentHelper,
        \RatePAY\Payment\Controller\LibraryController $rpLibraryController,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \RatePAY\Payment\Model\Api\SendConfirmationDeliver $rpConfirmationDelivery
    ) {
        $this->rpLibraryModel = $rpLibraryModel;
        $this->rpDataHelper = $rpDataHelper;
        $this->rpPaymentHelper = $rpPaymentHelper;
        $this->rpLibraryController = $rpLibraryController;
        $this->storeManager = $storeManager;
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
        if ($this->rpDataHelper->getRpConfigDataByPath("ratepay/general/capture_event", $order->getStore()->getCode()) != CaptureEvent::TRIGGER_ON_INVOICE) {
            return false;
        }
        return true;
    }

    /**
     * @param \Magento\Framework\Event\Observer $observer
     * @return $this
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        $inv = $observer->getEvent()->getData('invoice');
        if ($inv->getIsUsedForRefund() !== true) { // online refund executes a save on the invoice, which would trigger another confirmation_deliver
            $order = $inv->getOrder();
            $paymentMethod = $order->getPayment()->getMethodInstance()->getCode();
            if(!$this->rpPaymentHelper->isRatepayPayment($paymentMethod) || !$this->isCommunicationToRatepayAllowed($inv, $order)) {
                return $this;
            }
            $this->sendRatepayDeliverCall($order, $inv, $paymentMethod);
        }
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
