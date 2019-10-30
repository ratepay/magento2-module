<?php

namespace RatePAY\Payment\Setup\Tables;

use Magento\Framework\DB\Ddl\Table;

class ApiLog
{
    const TABLE_NAME = 'ratepay_api_log';

    /**
     * Definition of the database table ratepay_api_log
     *
     * @var array
     */
    protected static $tableData = [
        'title' => self::TABLE_NAME,
        'columns' => [
            'entity_id'         => ['type' => Table::TYPE_INTEGER,      'length' => null,   'option' => ['identity' => true, 'unsigned' => true, 'nullable' => false, 'primary' => true]],
            'order_id'          => ['type' => Table::TYPE_INTEGER,      'length' => null,   'option' => ['unsigned' => true]],
            'order_increment_id'=> ['type' => Table::TYPE_TEXT,         'length' => 32,     'option' => []],
            'transaction_id'    => ['type' => Table::TYPE_TEXT,         'length' => 255,    'option' => []],
            'date'              => ['type' => Table::TYPE_TIMESTAMP,    'length' => null,   'option' => ['nullable' => false, 'default' => Table::TIMESTAMP_INIT]],
            'name'              => ['type' => Table::TYPE_TEXT,         'length' => 255,    'option' => []],
            'payment_method'    => ['type' => Table::TYPE_TEXT,         'length' => 40,     'option' => []],
            'payment_type'      => ['type' => Table::TYPE_TEXT,         'length' => 40,     'option' => []],
            'payment_subtype'   => ['type' => Table::TYPE_TEXT,         'length' => 40,     'option' => []],
            'result'            => ['type' => Table::TYPE_TEXT,         'length' => 40,     'option' => []],
            'request'           => ['type' => Table::TYPE_TEXT,         'length' => null,   'option' => []],
            'response'          => ['type' => Table::TYPE_TEXT,         'length' => null,   'option' => []],
            'result_code'       => ['type' => Table::TYPE_TEXT,         'length' => 5,      'option' => []],
            'status_code'       => ['type' => Table::TYPE_TEXT,         'length' => 40,     'option' => []],
            'reason'            => ['type' => Table::TYPE_TEXT,         'length' => 255,    'option' => []],
        ],
        'comment' => 'Logs all API requests',
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
