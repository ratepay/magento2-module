<?php

namespace RatePAY\Payment\Setup\Tables;

use Magento\Framework\DB\Ddl\Table;

class OrderAdjustment
{
    const TABLE_NAME = 'ratepay_order_adjustments';

    /**
     * Definition of the database table ratepay_order_adjustments
     *
     * @var array
     */
    protected static $tableData = [
        'title' => self::TABLE_NAME,
        'columns' => [
            'entity_id'         => ['type' => Table::TYPE_INTEGER,      'length' => null,   'option' => ['identity' => true, 'unsigned' => true, 'nullable' => false, 'primary' => true]],
            'order_id'          => ['type' => Table::TYPE_INTEGER,      'length' => null,   'option' => ['unsigned' => true, 'nullable' => false]],
            'adjustment_type'   => ['type' => Table::TYPE_TEXT,         'length' => 64,     'option' => []],
            'article_number'    => ['type' => Table::TYPE_TEXT,         'length' => 32,     'option' => []],
            'amount'            => ['type' => Table::TYPE_DECIMAL,      'length' => '20,4', 'option' => ['default' => '0']],
            'base_amount'       => ['type' => Table::TYPE_DECIMAL,      'length' => '20,4', 'option' => ['default' => '0']],
            'is_specialitem'    => ['type' => Table::TYPE_SMALLINT,     'length' => null,   'option' => ['unsigned' => true, 'nullable' => false]],
            'is_returned'       => ['type' => Table::TYPE_SMALLINT,     'length' => null,   'option' => ['unsigned' => true, 'nullable' => false]],
        ],
        'comment' => 'Save order sum adjustments for returning them later',
        'indexes' => ['order_id']
    ];

    /**
     * Return the table data needed to create this table
     *
     * @return array
     */
    public static function getData()
    {
        return self::$tableData;
    }
}
