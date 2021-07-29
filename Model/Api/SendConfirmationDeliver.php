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
     * @var \Magento\Payment\Helper\Data
     */
    protected $paymentHelper;

    /**
     * @var \RatePAY\Payment\Helper\ProfileConfig
     */
    protected $profileConfigHelper;

    /**
     * SendRatepayDeliverCallOnInvoice constructor.
     * @param \RatePAY\Payment\Model\LibraryModel $rpLibraryModel
     * @param \RatePAY\Payment\Helper\Data $rpDataHelper
     * @param LibraryController $rpLibraryController
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Magento\Payment\Helper\Data $paymentHelper
     * @param \RatePAY\Payment\Helper\ProfileConfig $profileConfigHelper
     */
    public function __construct(
        \RatePAY\Payment\Model\LibraryModel $rpLibraryModel,
        \RatePAY\Payment\Helper\Data $rpDataHelper,
        \RatePAY\Payment\Controller\LibraryController $rpLibraryController,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Payment\Helper\Data $paymentHelper,
        \RatePAY\Payment\Helper\ProfileConfig $profileConfigHelper
    ) {
        $this->rpLibraryModel = $rpLibraryModel;
        $this->rpDataHelper = $rpDataHelper;
        $this->rpLibraryController = $rpLibraryController;
        $this->storeManager = $storeManager;
        $this->paymentHelper = $paymentHelper;
        $this->profileConfigHelper = $profileConfigHelper;
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
     * Loads shipment info from order or from current post request when data was added directly to invoice
     * In the second case the data cant be loaded through the invoice or order model, because it is added to the database AFTER this method is executed
     *
     * @param $order
     * @return array|bool|mixed
     */
    protected function getShipments($order)
    {
        $aReturn = false;

        $aShipments = $order->getShipmentsCollection()->getItems();
        if (!empty($aShipments)) {
            $shipment = array_shift($aShipments);
            $aAllTracks = $shipment->getAllTracks();
            if (!empty($aAllTracks)) {
                $aReturn = [];
                foreach ($aAllTracks as $oTrack) {
                    $aReturn[] = [
                        'number' => $oTrack->getTrackNumber(),
                        'carrier_code' => $oTrack->getCarrierCode(),
                    ];
                }
            }
        }

        if (!$aReturn) {
            $data = $this->rpDataHelper->getRequestParameter('invoice');
            $tracking = $this->rpDataHelper->getRequestParameter('tracking');
            if (!empty($data['do_shipment']) && !empty($tracking)) {
                $aReturn = $tracking;
            }
        }

        return $aReturn;
    }

    /**
     * Returns trackinfo request parameter array
     *
     * @param object $order
     * @return \RatePAY\Model\Request\SubModel\Head\External\Tracking|null
     */
    private function getTrackingInfo($order)
    {
        $aShipmentData = $this->getShipments($order);
        if (!empty($aShipmentData)) {
            $oTracking = new \RatePAY\Model\Request\SubModel\Head\External\Tracking;
            foreach ($aShipmentData as $aShipment) {
                $oId = new \RatePAY\Model\Request\SubModel\Head\External\Tracking\Id;
                $oId->setId($aShipment['number']);
                $oId->setProvider($this->getValidCarrierCode($aShipment['carrier_code']));

                $oTracking->addId($oId);
            }
            return $oTracking;
        }
        return null;
    }

    /**
     * Sends confirmationDeliver call to Ratepay API
     *
     * @param object $order
     * @param string $paymentMethod
     * @param object|null $inv
     * @return \RatePAY\RequestBuilder
     */
    public function sendRatepayDeliverCall($order, $paymentMethod, $inv = null)
    {
        if ($inv === null) {
            $inv = $order;
        }

        $sProfileId = null;
        $sSecurityCode = null;
        $blSandbox = false;
        if ($order->getRatepayProfileId()) {
            $sProfileId = $order->getRatepayProfileId();
            $sSecurityCode = $this->profileConfigHelper->getSecurityCodeForProfileId($sProfileId, $paymentMethod);
            $blSandbox = $this->profileConfigHelper->getSandboxModeForProfileId($sProfileId, $paymentMethod);
        }

        $head = $this->rpLibraryModel->getRequestHead($order, 'CONFIRMATION_DELIVER', null, null, $sProfileId, $sSecurityCode, $this->getTrackingInfo($order));
        $content = $this->rpLibraryModel->getRequestContent($inv, 'CONFIRMATION_DELIVER');

        return $this->rpLibraryController->callConfirmationDeliver($head, $content, $order, $blSandbox);
    }
}