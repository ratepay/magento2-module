<?php

namespace RatePAY\Payment\Setup\Patch\Data;

use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\Setup\Patch\DataPatchInterface;

/**
 * Class UpdateOldOrderPaymentMethods
 */
class UpdateOldOrderPaymentMethods implements DataPatchInterface
{
    /**
     * @var ModuleDataSetupInterface
     */
    private $moduleDataSetup;

    /**
     * Array of the old ratepay payment configuration method codes
     *
     * @var array
     */
    protected $ratepayOldCountryPrefixes = [
        "ratepay_de_",
        "ratepay_at_",
        "ratepay_ch_",
        "ratepay_nl_",
        "ratepay_be_",
    ];

    /**
     * UpdateOldOrderPaymentMethods constructor.
     *
     * @param ModuleDataSetupInterface $moduleDataSetup
     */
    public function __construct(ModuleDataSetupInterface $moduleDataSetup)
    {
        $this->moduleDataSetup = $moduleDataSetup;
    }

    /**
     * @return void
     */
    protected function updateOldOrderPaymentMethods()
    {
        foreach ($this->ratepayOldCountryPrefixes as $sRatepayPrefix) {
            $select = $this->moduleDataSetup->getConnection()
                ->select()
                ->from($this->moduleDataSetup->getTable('sales_order_payment'), ['entity_id', 'method'])
                ->where('method LIKE "'.$sRatepayPrefix.'%"');
            $result = $this->moduleDataSetup->getConnection()->fetchAssoc($select);
            foreach ($result as $item) {
                $data = ['method' => str_ireplace($sRatepayPrefix, 'ratepay_', $item['method'])];
                $where = ['entity_id = ?' => $item['entity_id']];
                $this->moduleDataSetup->getConnection()->update($this->moduleDataSetup->getTable('sales_order_payment'), $data, $where);
            }
        }
    }

    /**
     * {@inheritdoc}
     */
    public function apply()
    {
        $this->moduleDataSetup->getConnection()->startSetup();

        $this->updateOldOrderPaymentMethods();

        $this->moduleDataSetup->getConnection()->endSetup();
    }

    /**
     * {@inheritdoc}
     */
    public static function getDependencies()
    {
        return [
            MigrateToNewConfig::class
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function getAliases()
    {
        return [];
    }
}
