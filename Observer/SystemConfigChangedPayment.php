<?php

/**
 * Copyright (c) Ratepay GmbH
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace RatePAY\Payment\Observer;

use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Event\Observer;
use Magento\Payment\Model\InfoInterface;
use RatePAY\Payment\Model\Method\Invoice;

class SystemConfigChangedPayment implements ObserverInterface
{
    /**
     * @var \RatePAY\Payment\Helper\ProfileConfig
     */
    protected $profileConfigHelper;

    /**
     * Constructor
     *
     * @param \RatePAY\Payment\Helper\ProfileConfig $profileConfigHelper
     */
    public function __construct(
        \RatePAY\Payment\Helper\ProfileConfig $profileConfigHelper
    ) {
        $this->profileConfigHelper = $profileConfigHelper;
    }

    /**
     * Handles profile config update
     *
     * @return void
     */
    protected function handleProfileConfigurationUpdate($sChangedPath)
    {
        $sPseudoMethodCode = Invoice::METHOD_CODE;
        if (stripos($sChangedPath, Invoice::BACKEND_SUFFIX) !== false) { ///@TODO: testen
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
            if (stripos($sChangedPath, "profile_config") !== false && stripos($sChangedPath, "ratepay") !== false) {
                $this->handleProfileConfigurationUpdate($sChangedPath);
            }
        }
    }
}
