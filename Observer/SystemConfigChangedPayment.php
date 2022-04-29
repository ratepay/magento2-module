<?php


namespace RatePAY\Payment\Observer;

use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Event\Observer;
use Magento\Payment\Model\InfoInterface;
use RatePAY\Payment\Model\Method\Invoice;

class SystemConfigChangedPayment implements ObserverInterface
{
    /**
     * @var \Magento\Backend\Model\Session
     */
    protected $backendSession;

    /**
     * @var \RatePAY\Payment\Model\BamsApi\GetStoredBankAccounts
     */
    protected $getStoredBankAccounts;
  
    /**
     * @var \RatePAY\Payment\Helper\ProfileConfig
     */
    protected $profileConfigHelper

    /**
     * Constructor
     *
     * @param \RatePAY\Payment\Helper\ProfileConfig $profileConfigHelper
     * @param \Magento\Backend\Model\Session $backendSession
     * @param \RatePAY\Payment\Model\BamsApi\GetStoredBankAccounts $getStoredBankAccounts
     */
    public function __construct(
        \RatePAY\Payment\Helper\ProfileConfig $profileConfigHelper,
        \Magento\Backend\Model\Session $backendSession,
        \RatePAY\Payment\Model\BamsApi\GetStoredBankAccounts $getStoredBankAccounts
    ) {
        $this->profileConfigHelper = $profileConfigHelper;
        $this->backendSession = $backendSession;
        $this->getStoredBankAccounts = $getStoredBankAccounts;
    }

    /**
     * Handles profile config update
     *
     * @return void
     */
    protected function handleProfileConfigurationUpdate($sChangedPath)
    {
        $sPseudoMethodCode = Invoice::METHOD_CODE;
        if (stripos($sChangedPath, Invoice::BACKEND_SUFFIX) !== false) {
            $sPseudoMethodCode = $sPseudoMethodCode.Invoice::BACKEND_SUFFIX;
        }
        $this->profileConfigHelper->refreshProfileConfigurations($sPseudoMethodCode);
    }

    /**
     * Execute certain tasks after the payone_payment section is being saved in the backend
     *
     * @param Observer $observer
     * @return void
     */
    public function execute(Observer $observer)
    {
        $aChangedPaths = $observer->getChangedPaths();
        foreach ($aChangedPaths as $sChangedPath) {
            if (stripos($sChangedPath, "bams_client_id") !== false || stripos($sChangedPath, "bams_client_secret") !== false) {
                $sAuthToken = $this->getStoredBankAccounts->getAuthToken();
                $this->backendSession->setRatepayBamsOauthChanged(false);
                if ($sAuthToken !== false) {
                    $this->backendSession->setRatepayBamsOauthChanged(true);
                }
            }
            if (stripos($sChangedPath, "profile_config") !== false && stripos($sChangedPath, "ratepay") !== false) {
                $this->handleProfileConfigurationUpdate($sChangedPath);
            }
        }
    }
}
