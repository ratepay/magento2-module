<?php


namespace RatePAY\Payment\Observer;

use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Event\Observer;
use Magento\Payment\Model\InfoInterface;

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
     * Constructor
     *
     * @param \Magento\Backend\Model\Session $backendSession
     * @param \RatePAY\Payment\Model\BamsApi\GetStoredBankAccounts $getStoredBankAccounts
     */
    public function __construct(
        \Magento\Backend\Model\Session $backendSession,
        \RatePAY\Payment\Model\BamsApi\GetStoredBankAccounts $getStoredBankAccounts
    ) {
        $this->backendSession = $backendSession;
        $this->getStoredBankAccounts = $getStoredBankAccounts;
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
        }
    }
}
