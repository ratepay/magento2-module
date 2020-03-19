<?php

namespace RatePAY\Payment\Setup;

use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;
use Magento\Framework\Setup\UpgradeSchemaInterface;
use RatePAY\Payment\Setup\Tables\ApiLog;
use RatePAY\Payment\Setup\Tables\HidePaymentType;

/**
 * Update class to create RatePay specific tables
 */
class UpgradeSchema extends BaseSchema implements UpgradeSchemaInterface
{
    /**
     * Update method
     * Adds ratepay_api_log table
     *
     * @param SchemaSetupInterface $setup
     * @param ModuleContextInterface $context
     * @throws \Zend_Db_Exception
     */
    public function upgrade(SchemaSetupInterface $setup, ModuleContextInterface $context)
    {
        $this->addTable($setup, ApiLog::getData());
        $this->addTable($setup, HidePaymentType::getData());
    }
}
