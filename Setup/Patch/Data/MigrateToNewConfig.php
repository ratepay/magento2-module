<?php

namespace RatePAY\Payment\Setup\Patch\Data;

use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\Setup\Patch\DataPatchInterface;
use RatePAY\Payment\Model\Method\AbstractMethod;

/**
 * Class MigrateToNewConfig.
 */
class MigrateToNewConfig implements DataPatchInterface
{
    /**
     * @var ModuleDataSetupInterface
     */
    private $moduleDataSetup;

    /**
     * @var \RatePAY\Payment\Helper\ProfileConfig
     */
    protected $profileConfigHelper;

    /**
     * Array of the old ratepay payment configuration method codes
     *
     * @var array
     */
    protected $ratepayOldMethods = [
        "ratepay_de_invoice",
        "ratepay_de_directdebit",
        "ratepay_de_installment",
        "ratepay_de_installment0",
        "ratepay_at_invoice",
        "ratepay_at_directdebit",
        "ratepay_at_installment",
        "ratepay_at_installment0",
        "ratepay_ch_invoice",
        "ratepay_nl_invoice",
        "ratepay_nl_directdebit",
        "ratepay_be_invoice",
        "ratepay_be_directdebit",
    ];

    /**
     * Delimiter used to separate scope and scopeId
     *
     * @var string
     */
    protected $scopeKeyDelimiter = "#|!|#";

    /**
     * MigrateToNewConfig constructor.
     *
     * @param ModuleDataSetupInterface $moduleDataSetup
     * @param \RatePAY\Payment\Helper\ProfileConfig $profileConfigHelper
     */
    public function __construct(
        ModuleDataSetupInterface $moduleDataSetup,
        \RatePAY\Payment\Helper\ProfileConfig $profileConfigHelper
    ) {
        $this->moduleDataSetup = $moduleDataSetup;
        $this->profileConfigHelper = $profileConfigHelper;
    }

    protected function getOldProfileDataByMethodCode($sMethodCode)
    {
        $aReturn = [];

        $select = $this->moduleDataSetup->getConnection()
            ->select()
            ->from($this->moduleDataSetup->getTable('core_config_data'), ['config_id', 'scope', 'scope_id', 'path', 'value'])
            ->where('path = "payment/'.$sMethodCode.'/profileId"')
            ->orWhere('path = "payment/'.$sMethodCode.'/securityCode"')
            ->orWhere('path = "payment/'.$sMethodCode.'/sandbox"')
            ->order(['scope_id', 'scope']);
        $result = $this->moduleDataSetup->getConnection()->fetchAssoc($select);

        $iDefaultSandbox = 0;
        foreach ($result as $item) {
            $sScopeKey = $item['scope'].$this->scopeKeyDelimiter.$item['scope_id'];
            if (array_key_exists($sScopeKey, $aReturn) === false) {
                $aReturn[$sScopeKey] = [];
            }
            if (stripos($item['path'], "profileId") !== false) {
                $aReturn[$sScopeKey]['profileId'] = $item['value'];
            }
            if (stripos($item['path'], "securityCode") !== false) {
                $aReturn[$sScopeKey]['securityCode'] = $item['value'];
            }
            if (stripos($item['path'], "sandbox") !== false) {
                $aReturn[$sScopeKey]['sandbox'] = $item['value'];
                $iDefaultSandbox = $item['value'];
            }
            if (!isset($aReturn[$sScopeKey]['sandbox'])) {
                $aReturn[$sScopeKey]['sandbox'] = $iDefaultSandbox;
            }
        }

        return $aReturn;
    }

    protected function getOldProfileConfig($blUseBackendMethods)
    {
        $sSuffix = "";
        if ($blUseBackendMethods === true) {
            $sSuffix = AbstractMethod::BACKEND_SUFFIX;
        }

        $aOldConfig = [];
        foreach ($this->ratepayOldMethods as $ratepayOldMethod) {
            $aProfileData = $this->getOldProfileDataByMethodCode($ratepayOldMethod.$sSuffix);
            foreach ($aProfileData as $sScopeKey => $aProfile) {
                if (!empty($aProfile['profileId']) && !isset($aOldConfig[$sScopeKey][$aProfile['profileId']])) {
                    if (!isset($aOldConfig[$sScopeKey])) {
                        $aOldConfig[$sScopeKey] = [];
                    }
                    $sKey = $aProfile['profileId'];
                    if ($blUseBackendMethods === true) {
                        $sKey .= "_backend";
                    }
                    $aOldConfig[$sScopeKey][$sKey] = $aProfile;
                }
            }
        }
        return $aOldConfig;
    }

    protected function moveOldProfilesToNewConfig($blUseBackendMethods, $sNewConfigPath)
    {
        $aOldConfig = $this->getOldProfileConfig($blUseBackendMethods);
        $aImported = [];
        foreach ($aOldConfig as $sScopeKey => $aUniqueProfiles) {
            foreach ($aUniqueProfiles as $sKey => $aUniqueProfile) {
                if (in_array($aUniqueProfile['profileId'], $aImported) === false) {
                    try {
                        $blResult = $this->profileConfigHelper->importProfileConfiguration($aUniqueProfile['profileId'], $aUniqueProfile['securityCode'], (bool)$aUniqueProfile['sandbox']);
                    } catch (\Exception $exc) {
                        $blResult = false;
                    }
                    $aImported[] = $aUniqueProfile['profileId'];
                    if ($blResult === false) {
                        unset($aUniqueProfiles[$sKey]);
                    }
                }
            }
            list($scope, $scopeId) = explode($this->scopeKeyDelimiter, $sScopeKey);
            $aData = [
                'scope' => $scope,
                'scope_id' => $scopeId,
                'path' => $sNewConfigPath,
                'value' => json_encode($aUniqueProfiles),
            ];
            $this->moduleDataSetup->getConnection()->insertOnDuplicate($this->moduleDataSetup->getTable('core_config_data'), $aData);
        }
    }

    /**
     * Copy configuration from old german method to new general method
     *
     * @return void
     */
    protected function copyMethodConfigToNewGeneralMethod()
    {
        $aCopyMap = [
            "ratepay_de_invoice" => "ratepay_invoice",
            "ratepay_de_directdebit" => "ratepay_directdebit",
            "ratepay_de_installment" => "ratepay_installment",
            "ratepay_de_installment0" => "ratepay_installment0",
            "ratepay_de_invoice_backend" => "ratepay_invoice_backend",
            "ratepay_de_directdebit_backend" => "ratepay_directdebit_backend",
            "ratepay_de_installment_backend" => "ratepay_installment_backend",
            "ratepay_de_installment0_backend" => "ratepay_installment0_backend",
        ];

        $aCopyFields = [
            "active",
            "title",
            "payment_fee",
            "order_status",
            "sort_order",
        ];

        foreach ($aCopyMap as $sOldMethod => $sNewMethod) {
            foreach ($aCopyFields as $sCopyField) {
                $data = ['path' => "payment/".$sNewMethod."/".$sCopyField];
                $where = ['path = ?' => "payment/".$sOldMethod."/".$sCopyField];
                $this->moduleDataSetup->getConnection()->update($this->moduleDataSetup->getTable('core_config_data'), $data, $where);
            }
        }
    }

    /**
     * Migrate old config format to new config format
     *
     * @return void
     */
    protected function migrateToNewConfig()
    {
        $this->moveOldProfilesToNewConfig(false, "payment/ratepay_config/ratepay/profile_config");
        $this->moveOldProfilesToNewConfig(true, "payment/ratepay_config/ratepay_backend/profile_config_backend");
        $this->copyMethodConfigToNewGeneralMethod();
    }

    /**
     * {@inheritdoc}
     */
    public function apply()
    {
        $this->moduleDataSetup->getConnection()->startSetup();

        $this->migrateToNewConfig();

        $this->moduleDataSetup->getConnection()->endSetup();
    }

    /**
     * {@inheritdoc}
     */
    public static function getDependencies()
    {
        return [];
    }

    /**
     * {@inheritdoc}
     */
    public function getAliases()
    {
        return [];
    }
}
