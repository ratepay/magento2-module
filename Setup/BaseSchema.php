<?php

namespace RatePAY\Payment\Setup;

use Magento\Framework\Setup\SchemaSetupInterface;

/**
 * Base schema class to handle creation of new tables
 */
class BaseSchema
{
    /**
     * @param SchemaSetupInterface $installer
     * @param array $tableData
     * @throws \Zend_Db_Exception
     */
    protected function addTable(SchemaSetupInterface $installer, $tableData)
    {
        $connection = $installer->getConnection();
        $tableName = $installer->getTable($tableData['title']);
        if (!$connection->isTableExists($tableName)) {
            $table = $connection->newTable($tableName);

            foreach ($tableData['columns'] as $sColumnName => $aColumnData) {
                $table->addColumn($sColumnName, $aColumnData['type'], $aColumnData['length'], $aColumnData['option']);
            }

            if (!empty($tableData['indexes'])) {
                foreach ($tableData['indexes'] as $sIndex) {
                    $table->addIndex($installer->getIdxName($tableData['title'], $sIndex), $sIndex);
                }
            }

            $table->setComment($tableData['comment']);

            $connection->createTable($table);
        }
    }
}
