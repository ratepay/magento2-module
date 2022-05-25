<?php

namespace RatePAY\Payment\Setup\Patch\Schema;

use Magento\Framework\Setup\SchemaSetupInterface;
use Magento\Framework\Setup\Patch\SchemaPatchInterface;
use Magento\Sales\Setup\SalesSetupFactory;
use Magento\Framework\DB\Ddl\Table;
use Magento\Framework\Setup\ModuleDataSetupInterface;

/**
 * Class AddOrderStatusColumns.
 */
class AddOrderStatusColumns implements SchemaPatchInterface
{
    /**
     * @var SchemaSetupInterface
     */
    private $schemaSetup;

    /**
     * Sales setup factory
     *
     * @var SalesSetupFactory
     */
    protected $salesSetupFactory;

    /**
     * @var ModuleDataSetupInterface
     */
    protected $moduleDataSetup;

    /**
     * AddOrderStatusColumns constructor.
     *
     * @param SchemaSetupInterface $schemaSetup
     * @param SalesSetupFactory $salesSetupFactory
     * @param ModuleDataSetupInterface $moduleDataSetup

     */
    public function __construct(
        SchemaSetupInterface $schemaSetup,
        SalesSetupFactory $salesSetupFactory,
        ModuleDataSetupInterface $moduleDataSetup
    )
    {
        $this->schemaSetup = $schemaSetup;
        $this->salesSetupFactory = $salesSetupFactory;
        $this->moduleDataSetup = $moduleDataSetup;
    }

    /**
     * {@inheritdoc}
     */
    public function apply()
    {
        $this->schemaSetup->startSetup();
        $setup = $this->moduleDataSetup;

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

        $this->schemaSetup->endSetup();
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
