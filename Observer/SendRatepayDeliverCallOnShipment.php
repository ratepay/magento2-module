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

class SendRatepayDeliverCallOnShipment implements ObserverInterface
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
     * @var array
     */
    protected $validCarrierCodes = [
        'DHL', 'DPD', 'GLS', 'HLG', 'HVS', 'TNT', 'UPS'
    ];

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

    protected function isCommunicationToRatepayAllowed($order)
    {
        if ($this->rpDataHelper->getRpConfigDataByPath("ratepay/general/capture_event", $order->getStore()->getCode()) != CaptureEvent::TRIGGER_ON_SHIPPING) {
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
        $order = $observer->getEvent()->getData('shipment')->getOrder();
        $paymentMethod = $order->getPayment()->getMethodInstance()->getCode();
        if(!$this->rpPaymentHelper->isRatepayPayment($paymentMethod) || !$this->isCommunicationToRatepayAllowed($order)) {
            return $this;
        }
        $this->sendRatepayDeliverCall($order, $paymentMethod);
    }

    /**
     * Returns first invoice of the order
     *
     * @param $order
     * @return mixed|null
     */
    protected function getInvoice($order)
    {
        $invoiceList = $order->getInvoiceCollection()->getItems();
        if ($invoice = array_shift($invoiceList)) { // get first invoice from invoice collection
            return $invoice;
        }
        return null;
    }

    /**
     * @param $order
     * @param $paymentMethod
     * @return bool
     */
    private function sendRatepayDeliverCall($order, $paymentMethod)
    {
        $invoice = $this->getInvoice($order);

        $resultConfirmationDeliver = $this->rpConfirmationDelivery->sendRatepayDeliverCall($order, $paymentMethod, $invoice);

        if(!$resultConfirmationDeliver->isSuccessful()) {
            throw new PaymentException(__('Invoice not successful'));
        }

        if ($invoice !== null) {
            $invoice->pay(); // set paid status for invoice
            $invoice->save();
            $order->save();
        }

        return true;
    }
}
