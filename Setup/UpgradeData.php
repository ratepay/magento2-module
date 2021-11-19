<?php

namespace RatePAY\Payment\Setup;

use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\Setup\UpgradeDataInterface;
use Magento\Sales\Setup\SalesSetupFactory;
use Magento\Framework\DB\Ddl\Table;
use RatePAY\Payment\Model\Method\AbstractMethod;

/**
 * Class UpgradeData
 */
class UpgradeData implements UpgradeDataInterface
{
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
     * Sales setup factory
     *
     * @var SalesSetupFactory
     */
    protected $salesSetupFactory;

    /**
     * @var \RatePAY\Payment\Helper\ProfileConfig
     */
    protected $profileConfigHelper;

    /**
     * Constructor
     *
     * @param SalesSetupFactory $salesSetupFactory
     * @param \RatePAY\Payment\Helper\ProfileConfig $profileConfigHelper
     */
    public function __construct(
        SalesSetupFactory $salesSetupFactory,
        \RatePAY\Payment\Helper\ProfileConfig $profileConfigHelper
    ) {
        $this->salesSetupFactory = $salesSetupFactory;
        $this->profileConfigHelper = $profileConfigHelper;
    }

    protected function getOldProfileDataByMethodCode(ModuleDataSetupInterface $setup, $sMethodCode)
    {
        $aReturn = [];

        $select = $setup->getConnection()
            ->select()
            ->from($setup->getTable('core_config_data'), ['config_id', 'scope', 'scope_id', 'path', 'value'])
            ->where('path = "payment/'.$sMethodCode.'/profileId"')
            ->orWhere('path = "payment/'.$sMethodCode.'/securityCode"')
            ->orWhere('path = "payment/'.$sMethodCode.'/sandbox"')
            ->order(['scope_id', 'scope']);
        $result = $setup->getConnection()->fetchAssoc($select);

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

    protected function getOldProfileConfig(ModuleDataSetupInterface $setup, $blUseBackendMethods)
    {
        $sSuffix = "";
        if ($blUseBackendMethods === true) {
            $sSuffix = AbstractMethod::BACKEND_SUFFIX;
        }

        $aOldConfig = [];
        foreach ($this->ratepayOldMethods as $ratepayOldMethod) {
            $aProfileData = $this->getOldProfileDataByMethodCode($setup, $ratepayOldMethod.$sSuffix);
            foreach ($aProfileData as $sScopeKey => $aProfile) {
                if (!empty($aProfile['profileId']) && !isset($aOldConfig[$sScopeKey][$aProfile['profileId']])) {
                    if (!isset($aOldConfig[$sScopeKey])) {
                        $aOldConfig[$sScopeKey] = [];
                    }
                    $aOldConfig[$sScopeKey][$aProfile['profileId']] = $aProfile;
                }
            }
        }
        return $aOldConfig;
    }

    protected function moveOldProfilesToNewConfig(ModuleDataSetupInterface $setup, $blUseBackendMethods, $sNewConfigPath)
    {
        $aOldConfig = $this->getOldProfileConfig($setup, $blUseBackendMethods);
        $aImported = [];
        foreach ($aOldConfig as $sScopeKey => $aUniqueProfiles) {
            foreach ($aUniqueProfiles as $sKey => $aUniqueProfile) {
                if (in_array($sKey, $aImported) === false) {
                    try {
                        $blResult = $this->profileConfigHelper->importProfileConfiguration($aUniqueProfile['profileId'], $aUniqueProfile['securityCode'], (bool)$aUniqueProfile['sandbox']);
                    } catch(\Exception $exc) {
                        $blResult = false;
                    }
                    $aImported[] = $sKey;
                    if ($blResult === false) {
                        unset($aUniqueProfiles[$sKey]);
                    }
                }
            }
            list($scope, $scopeId) = explode($this->scopeKeyDelimiter, $sScopeKey);
            $setup->getConnection()->insert(
                $setup->getTable('core_config_data'),
                [
                    'scope' => $scope,
                    'scope_id' => $scopeId,
                    'path' => $sNewConfigPath,
                    'value' => json_encode($aUniqueProfiles),
                ]
            );
        }
    }

    /**
     * Copy configuration from old german method to new general method
     *
     * @param  ModuleDataSetupInterface $setup
     * @return void
     */
    protected function copyMethodConfigToNewGeneralMethod(ModuleDataSetupInterface $setup)
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
                $setup->getConnection()->update($setup->getTable('core_config_data'), $data, $where);
            }
        }
    }

    /**
     * Migrate old config format to new config format
     *
     * @param  ModuleDataSetupInterface $setup
     * @return void
     */
    protected function migrateToNewConfig(ModuleDataSetupInterface $setup)
    {
        $this->moveOldProfilesToNewConfig($setup, false, "payment/ratepay_config/ratepay/profile_config");
        $this->moveOldProfilesToNewConfig($setup, true, "payment/ratepay_config/ratepay_backend/profile_config_backend");
        $this->copyMethodConfigToNewGeneralMethod($setup);
    }

    /**
     * Upgrade method
     *
     * @param  ModuleDataSetupInterface $setup
     * @param  ModuleContextInterface   $context
     * @return void
     */
    public function upgrade(ModuleDataSetupInterface $setup, ModuleContextInterface $context)
    {
        $setup->startSetup();
        $salesInstaller = $this->salesSetupFactory->create(['resourceName' => 'sales_setup', 'setup' => $setup]);
        if (!$setup->getConnection()->tableColumnExists($setup->getTable('sales_order'), 'ratepay_sandbox_used')) {
            $salesInstaller->addAttribute(
                'order',
                'ratepay_sandbox_used',
                ['type' => Table::TYPE_SMALLINT, 'length' => null, 'default' => 0]
            );
        }
        if (!$setup->getConnection()->tableColumnExists($setup->getTable('sales_order'), 'ratepay_profile_id')) {
            $salesInstaller->addAttribute(
                'order',
                'ratepay_profile_id',
                ['type' => Table::TYPE_TEXT, 'length' => 64, 'default' => '']
            );
        }
        if (version_compare($context->getVersion(), '2.0.0', '<')) { // pre update version is less than 2.0.0
            $this->migrateToNewConfig($setup);
        }

        $setup->endSetup();
    }
}
