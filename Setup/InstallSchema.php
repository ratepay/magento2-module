<?php

namespace RatePAY\Payment\Setup;

use Magento\Framework\Setup\InstallSchemaInterface;
use Magento\Framework\Setup\SchemaSetupInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use RatePAY\Payment\Setup\Tables\ApiLog;
use RatePAY\Payment\Setup\Tables\HidePaymentType;
use RatePAY\Payment\Setup\Tables\OrderAdjustment;

/**
 * Setup class to create RatePay specific tables
 */
class InstallSchema extends BaseSchema implements InstallSchemaInterface
{
    /**
     * Install method
     * Adds ratepay_api_log table
     *
     * @param SchemaSetupInterface $installer
     * @param ModuleContextInterface $context
     * @return void
     * @throws \Zend_Db_Exception
     */
    public function install(SchemaSetupInterface $installer, ModuleContextInterface $context)
    {
        $installer->startSetup();

        $this->addTable($installer, ApiLog::getData());
        $this->addTable($installer, HidePaymentType::getData());
        $this->addTable($installer, OrderAdjustment::getData());

        $installer->endSetup();
    }
}
