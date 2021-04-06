<?php


namespace RatePAY\Payment\Model\Api;


use RatePAY\Payment\Controller\LibraryController;
use Magento\Framework\Exception\PaymentException;

class SendConfirmationDeliver
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
     * @param LibraryController $rpLibraryController
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     */
    public function __construct(
        \RatePAY\Payment\Model\LibraryModel $rpLibraryModel,
        \RatePAY\Payment\Helper\Data $rpDataHelper,
        \RatePAY\Payment\Controller\LibraryController $rpLibraryController,
        \Magento\Store\Model\StoreManagerInterface $storeManager)
    {
        $this->rpLibraryModel = $rpLibraryModel;
        $this->rpDataHelper = $rpDataHelper;
        $this->rpLibraryController = $rpLibraryController;
        $this->storeManager = $storeManager;
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
     * @param object $order
     * @param object $shipment
     * @return \RatePAY\Model\Request\SubModel\Head\External\Tracking|null
     */
    private function getTrackingInfo($order, $shipment = null)
    {
        $trackInfo = null;

        if ($shipment === null) {
            $aShipments = $order->getShipmentsCollection()->getItems();
            if (!empty($aShipments)) {
                $shipment = array_shift($aShipments);
            }
        }

        if ($shipment) {
            $aAllTracks = $shipment->getAllTracks();
            if (!empty($aAllTracks)) {
                $oTracking = new \RatePAY\Model\Request\SubModel\Head\External\Tracking;
                foreach ($aAllTracks as $oTrack) {
                    $oId = new \RatePAY\Model\Request\SubModel\Head\External\Tracking\Id;
                    $oId->setId($oTrack->getTrackNumber());
                    $oId->setProvider($this->getValidCarrierCode($oTrack->getCarrierCode()));

                    $oTracking->addId($oId);
                }
                return $oTracking;
            }
        }

        return $trackInfo;
    }

    /**
     * Sends confirmationDeliver call to Ratepay API
     *
     * @param object $order
     * @param string $paymentMethod
     * @param object|null $inv
     * @param object|null $shipment
     * @return \RatePAY\RequestBuilder
     */
    public function sendRatepayDeliverCall($order, $paymentMethod, $inv = null, $shipment = null)
    {
        if ($inv === null) {
            $inv = $order;
            if ($shipment !== null) {
                $inv = $shipment;
            }
        }

        $sandbox = (bool)$this->rpDataHelper->getRpConfigData($paymentMethod, 'sandbox', $this->storeManager->getStore()->getId());
        $head = $this->rpLibraryModel->getRequestHead($order, 'CONFIRMATION_DELIVER', null, null, null, null, $this->getTrackingInfo($order, $shipment));
        $content = $this->rpLibraryModel->getRequestContent($inv, 'CONFIRMATION_DELIVER');

        return $this->rpLibraryController->callConfirmationDeliver($head, $content, $order, $sandbox);
    }
}