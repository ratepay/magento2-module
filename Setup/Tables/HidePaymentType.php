<?php

namespace RatePAY\Payment\Setup\Tables;

use Magento\Framework\DB\Ddl\Table;

class HidePaymentType
{
    const TABLE_NAME = 'ratepay_hide_payment_type';

    /**
     * Definition of the database table ratepay_api_log
     *
     * @var array
     */
    protected static $tableData = [
        'title' => self::TABLE_NAME,
        'columns' => [
            'entity_id'         => ['type' => Table::TYPE_INTEGER,      'length' => null,   'option' => ['identity' => true, 'unsigned' => true, 'nullable' => false, 'primary' => true]],
            'customer_id'       => ['type' => Table::TYPE_INTEGER,      'length' => null,   'option' => ['unsigned' => true, 'nullable' => false]],
            'payment_type'      => ['type' => Table::TYPE_TEXT,         'length' => 64,     'option' => []],
            'to_date'           => ['type' => Table::TYPE_TIMESTAMP,    'length' => null,   'option' => ['nullable' => false]],
        ],
        'comment' => 'Save payment types to hide for a given customer',
        'indexes' => ['customer_id']
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
