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
     */
    public function __construct(
        \RatePAY\Payment\Model\LibraryModel $rpLibraryModel,
        \RatePAY\Payment\Helper\Data $rpDataHelper,
        \RatePAY\Payment\Helper\Payment $rpPaymentHelper,
        \RatePAY\Payment\Controller\LibraryController $rpLibraryController,
        \Magento\Store\Model\StoreManagerInterface $storeManager)
    {
        $this->rpLibraryModel = $rpLibraryModel;
        $this->rpDataHelper = $rpDataHelper;
        $this->rpPaymentHelper = $rpPaymentHelper;
        $this->rpLibraryController = $rpLibraryController;
        $this->storeManager = $storeManager;
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
     * @param \Magento\Framework\Event\Observer $observer
     * @return $this
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        $inv = $observer->getEvent()->getData('invoice');
        if ($inv->getIsUsedForRefund() !== true) { // online refund executes a save on the invoice, which would trigger another confirmation_deliver
            $order = $observer->getEvent()->getData('invoice')->getOrder();
            $paymentMethod = $observer->getEvent()->getData('invoice')->getOrder()->getPayment()->getMethodInstance()->getCode();
            if(!$this->rpPaymentHelper->isRatepayPayment($paymentMethod) || $this->isCommunicationToRatepayAllowed($inv, $order) === false) {
                return $this;
            }
            $this->sendRatepayDeliverCall($order, $inv, $paymentMethod);
        }
    }

    /**
     * The Ratepay API only accepts certain carrier codes
     * Checks if carrier code is valid, otherwise OTH for other is returned
     *
     * @param  string $sShopCarrierCode
     * @return string
     */
    private function getValidCarrierCode($sShopCarrierCode)
    {
        if (in_array(strtoupper($sShopCarrierCode), $this->validCarrierCodes)) {
            return strtoupper($sShopCarrierCode);
        }
        return 'OTH';
    }

    /**
     * Returns trackinfo request parameter array
     *
     * Problem: Documentation suggests that multiple tracking codes can be sent, but der Ratepay API lib didnt let me add multiple tracking codes so only first code is sent
     *
     * @param $order
     * @return array|null
     */
    private function getTrackingInfo($order)
    {
        $trackInfo = null;

        $aShipments = $order->getShipmentsCollection()->getItems();
        if (!empty($aShipments)) {
            $aShipment = array_shift($aShipments);
            if ($aShipment) {
                $aAllTracks = $aShipment->getAllTracks();
                foreach ($aAllTracks as $oTrack) {
                    if (!isset($trackInfo['Id'])) {
                        $trackInfo['Id'] = [];
                    }
                    $trackInfo['Id'] = $oTrack->getTrackNumber();
                    $trackInfo['Provider'] = $this->getValidCarrierCode($oTrack->getCarrierCode());
                    break;
                }
            }
        }
        return $trackInfo;
    }

    /**
     * @param $order
     * @param $inv
     * @param $paymentMethod
     * @return bool
     */
    private function sendRatepayDeliverCall($order, $inv, $paymentMethod)
    {
        $sandbox = (bool)$this->rpDataHelper->getRpConfigData($paymentMethod, 'sandbox', $this->storeManager->getStore()->getId());
        $head = $this->rpLibraryModel->getRequestHead($order, 'CONFIRMATION_DELIVER', null, null, null, null, $this->getTrackingInfo($order));
        $content = $this->rpLibraryModel->getRequestContent($inv, 'CONFIRMATION_DELIVER');
        $resultConfirmationDeliver = $this->rpLibraryController->callConfirmationDeliver($head, $content, $order, $sandbox);

        if(!$resultConfirmationDeliver->isSuccessful())
        {
            throw new PaymentException(__('Invoice not successful'));
        } else {
            return true;
        }
    }
}
