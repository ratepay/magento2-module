<?php

namespace RatePAY\Payment\Helper;

use RatePAY\Payment\Model\Entities\ProfileConfiguration;
use RatePAY\Payment\Model\Method\AbstractMethod;

class ProfileConfig extends \Magento\Framework\App\Helper\AbstractHelper
{
    protected $sConfigPath = 'payment/ratepay_config/ratepay/profile_config';

    protected $sConfigPathBackend = 'payment/ratepay_config/ratepay_backend/profile_config_backend';

    /**
     * @var \RatePAY\Payment\Controller\LibraryController
     */
    protected $libraryController;

    /**
     * @var \RatePAY\Payment\Model\LibraryModel
     */
    protected $libraryModel;

    /**
     * @var \RatePAY\Payment\Model\ResourceModel\ProfileConfiguration
     */
    protected $profileConfigResource;

    /**
     * @var \RatePAY\Payment\Model\Entities\ProfileConfigurationFactory
     */
    protected $profileConfigFactory;

    /**
     * Payment constructor.
     * @param \Magento\Framework\App\Helper\Context $context
     * @param \RatePAY\Payment\Controller\LibraryController $libraryController
     * @param \RatePAY\Payment\Model\LibraryModel $libraryModel
     * @param \RatePAY\Payment\Model\ResourceModel\ProfileConfiguration $profileConfigResource
     * @param \RatePAY\Payment\Helper\Data $ratepayHelper
     * @param \RatePAY\Payment\Model\Entities\ProfileConfigurationFactory $profileConfigFactory
     */
    public function __construct(
        \Magento\Framework\App\Helper\Context $context,
        \RatePAY\Payment\Controller\LibraryController $libraryController,
        \RatePAY\Payment\Model\LibraryModel $libraryModel,
        \RatePAY\Payment\Model\ResourceModel\ProfileConfiguration $profileConfigResource,
        \RatePAY\Payment\Helper\Data $ratepayHelper,
        \RatePAY\Payment\Model\Entities\ProfileConfigurationFactory $profileConfigFactory
    ) {
        parent::__construct($context);
        $this->libraryController = $libraryController;
        $this->libraryModel = $libraryModel;
        $this->profileConfigResource = $profileConfigResource;
        $this->ratepayHelper = $ratepayHelper;
        $this->profileConfigFactory = $profileConfigFactory;
    }

    /**
     * @param  string $sProfileId
     * @param  string $sSecurityCode
     * @param  bool   $blSandbox
     * @return bool
     */
    public function importProfileConfiguration($sProfileId, $sSecurityCode, $blSandbox)
    {
        $oHead = $this->libraryModel->getRequestHead(null, null, null, null, $sProfileId, $sSecurityCode);

        $oProfileRequest = $this->libraryController->callProfileRequest($oHead, (bool)$blSandbox);
        if ($oProfileRequest->isSuccessful()) {
            $oResult = $oProfileRequest->getResult();
            $this->profileConfigResource->insertProfileConfiguration($oResult);
            return true;
        }
        return false;
    }

    /**
     * Returns profiles from config
     *
     * @param  string|null $sPaymentMethod
     * @param  string|null $sStoreCode
     * @return array
     */
    public function getConfiguredProfiles($sPaymentMethod = null, $sStoreCode = null)
    {
        $aReturnProfiles = [];

        $sConfigPath = $this->sConfigPath;
        if ($sPaymentMethod && stripos($sPaymentMethod, AbstractMethod::BACKEND_SUFFIX) !== false) {
            $sConfigPath = $this->sConfigPathBackend;
        }

        $sShopConfig = $this->ratepayHelper->getRpConfigDataByPath($sConfigPath, $sStoreCode);
        if (!empty($sShopConfig)) {
            $aProfiles = json_decode($sShopConfig, true);
            if (is_array($aProfiles)) {
                foreach ($aProfiles as $aProfile) {
                    if (!empty($aProfile['profileId'])) {
                        $aReturnProfiles[] = $aProfile;
                    }
                }
            }
        }
        return $aReturnProfiles;
    }

    /**
     * Returns configured security code for given profile id
     *
     * @param  string $sProfileId
     * @return string|null
     */
    public function getSecurityCodeForProfileId($sProfileId, $sPaymentMethod)
    {
        $aProfileData = $this->getConfiguredProfiles($sPaymentMethod);
        foreach ($aProfileData as $aProfile) {
            if ($aProfile['profileId'] == $sProfileId) {
                return $aProfile['securityCode'];
            }
        }
        return null;
    }

    /**
     * Returns configured sandbox mode for given profile id
     *
     * @param  string       $sProfileId
     * @param  string|null  $sPaymentMethod
     * @return bool|null
     */
    public function getSandboxModeForProfileId($sProfileId, $sPaymentMethod = null)
    {
        $aProfileData = $this->getConfiguredProfiles($sPaymentMethod);
        foreach ($aProfileData as $aProfile) {
            if ($aProfile['profileId'] == $sProfileId) {
                return (bool)$aProfile['sandbox'];
            }
        }
        return null;
    }

    /**
     * Requests profile configuration for all configured profiles from Ratepay API and inserts or updates it in the database
     *
     * @return void
     */
    public function refreshProfileConfigurations($sMethodCode)
    {
        $aProfiles = $this->getConfiguredProfiles($sMethodCode);
        foreach ($aProfiles as $aProfile) {
            $this->importProfileConfiguration($aProfile['profileId'], $aProfile['securityCode'], (bool)$aProfile['sandbox']);
        }
    }

    /**
     * Returns array with all configured profile models
     *
     * @return ProfileConfiguration[]
     */
    public function getProfileData($sMethodCode)
    {
        $aProfileData = $this->getConfiguredProfiles($sMethodCode);

        $aProfiles = [];
        foreach ($aProfileData as $aProfile) {
            $oProfileConfig = $this->profileConfigFactory->create();
            $oProfileConfig->load($aProfile['profileId']);
            if (!empty($oProfileConfig->getData('profile_id'))) {
                $aProfiles[] = $oProfileConfig;
            }
        }
        return $aProfiles;
    }

    /**
     * Returns Profile applicable for the current order process
     *
     * @param \Magento\Quote\Api\Data\CartInterface $oQuote
     * @param string                                $sMethodCode
     * @param string                                $sStoreCode
     * @param double                                $dGrandTotal
     * @param string                                $sBillingCountryId
     * @param string                                $sShippingCountryId
     * @param string                                $sCurrency
     * @return ProfileConfiguration|false
     */
    public function getMatchingProfile(\Magento\Quote\Api\Data\CartInterface $oQuote, $sMethodCode, $sStoreCode = null, $dGrandTotal = null, $sBillingCountryId = null, $sShippingCountryId = null, $sCurrency = null)
    {
        $aProfileData = $this->getConfiguredProfiles($sMethodCode, $sStoreCode);
        foreach ($aProfileData as $aProfile) {
            /** @var ProfileConfiguration $oProfileConfig */
            $oProfileConfig = $this->profileConfigFactory->create();
            $oProfileConfig->load($aProfile['profileId']);
            $oProfileConfig->setSandboxMode((bool)$aProfile['sandbox']);
            $oProfileConfig->setSecurityCode($aProfile['securityCode']);
            if ($oProfileConfig->isApplicableForQuote($oQuote, $sMethodCode, $dGrandTotal, $sBillingCountryId, $sShippingCountryId, $sCurrency) === true) {
                return $oProfileConfig;
            }
        }
        return false;
    }

    /**
     * Returns all matching profiles for the current order process
     *
     * @param \Magento\Quote\Api\Data\CartInterface $oQuote
     * @param string                                $sMethodCode
     * @param string                                $sStoreCode
     * @param double                                $dGrandTotal
     * @param string                                $sBillingCountryId
     * @param string                                $sShippingCountryId
     * @param string                                $sCurrency
     * @return ProfileConfiguration[]
     */
    public function getAllMatchingProfiles(\Magento\Quote\Api\Data\CartInterface $oQuote, $sMethodCode, $sStoreCode = null, $dGrandTotal = null, $sBillingCountryId = null, $sShippingCountryId = null, $sCurrency = null)
    {
        $aProfiles = [];

        $aProfileData = $this->getConfiguredProfiles($sMethodCode, $sStoreCode);
        foreach ($aProfileData as $aProfile) {
            /** @var ProfileConfiguration $oProfileConfig */
            $oProfileConfig = $this->profileConfigFactory->create();
            $oProfileConfig->load($aProfile['profileId']);
            $oProfileConfig->setSandboxMode((bool)$aProfile['sandbox']);
            $oProfileConfig->setSecurityCode($aProfile['securityCode']);
            if ($oProfileConfig->isApplicableForQuote($oQuote, $sMethodCode, $dGrandTotal, $sBillingCountryId, $sShippingCountryId, $sCurrency) === true) {
                $aProfiles[] = $oProfileConfig;
            }
        }
        return $aProfiles;
    }
}
