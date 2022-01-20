<?php

namespace RatePAY\Payment\Setup\Tables;

use Magento\Framework\DB\Ddl\Table;

class ProfileConfiguration
{
    const TABLE_NAME = 'ratepay_profile_configuration';

    /**
     * Definition of the database table ratepay_profile_configuration
     *
     * @var array
     */
    protected static $tableData = [
        'title' => self::TABLE_NAME,
        'columns' => [
            'profile_id'                            => ['type' => Table::TYPE_TEXT,     'length' => 32,     'option' => ['nullable' => false, 'primary' => true]],
            'merchant_name'                         => ['type' => Table::TYPE_TEXT,     'length' => 32,     'option' => ['nullable' => true, 'default' => NULL]],
            'shop_name'                             => ['type' => Table::TYPE_TEXT,     'length' => 32,     'option' => ['nullable' => true, 'default' => NULL]],
            'currency'                              => ['type' => Table::TYPE_TEXT,     'length' => 32,     'option' => ['nullable' => true, 'default' => NULL]],
            'merchant_status'                       => ['type' => Table::TYPE_INTEGER,  'length' => 2,      'option' => ['nullable' => true, 'default' => NULL]],
            'activation_status_invoice'             => ['type' => Table::TYPE_INTEGER,  'length' => 2,      'option' => ['nullable' => true, 'default' => NULL]],
            'activation_status_installment'         => ['type' => Table::TYPE_INTEGER,  'length' => 2,      'option' => ['nullable' => true, 'default' => NULL]],
            'activation_status_elv'                 => ['type' => Table::TYPE_INTEGER,  'length' => 2,      'option' => ['nullable' => true, 'default' => NULL]],
            'activation_status_prepayment'          => ['type' => Table::TYPE_INTEGER,  'length' => 2,      'option' => ['nullable' => true, 'default' => NULL]],
            'eligibility_ratepay_invoice'           => ['type' => Table::TYPE_INTEGER,  'length' => 1,      'option' => ['nullable' => true, 'default' => NULL]],
            'eligibility_ratepay_installment'       => ['type' => Table::TYPE_INTEGER,  'length' => 1,      'option' => ['nullable' => true, 'default' => NULL]],
            'eligibility_ratepay_elv'               => ['type' => Table::TYPE_INTEGER,  'length' => 1,      'option' => ['nullable' => true, 'default' => NULL]],
            'eligibility_ratepay_prepayment'        => ['type' => Table::TYPE_INTEGER,  'length' => 1,      'option' => ['nullable' => true, 'default' => NULL]],
            'eligibility_ratepay_pq_full'           => ['type' => Table::TYPE_INTEGER,  'length' => 1,      'option' => ['nullable' => true, 'default' => NULL]],
            'tx_limit_invoice_min'                  => ['type' => Table::TYPE_DECIMAL,  'length' => '20,4', 'option' => ['nullable' => true, 'default' => NULL]],
            'tx_limit_invoice_max'                  => ['type' => Table::TYPE_DECIMAL,  'length' => '20,4', 'option' => ['nullable' => true, 'default' => NULL]],
            'tx_limit_invoice_max_b2b'              => ['type' => Table::TYPE_DECIMAL,  'length' => '20,4', 'option' => ['nullable' => true, 'default' => NULL]],
            'tx_limit_installment_min'              => ['type' => Table::TYPE_DECIMAL,  'length' => '20,4', 'option' => ['nullable' => true, 'default' => NULL]],
            'tx_limit_installment_max'              => ['type' => Table::TYPE_DECIMAL,  'length' => '20,4', 'option' => ['nullable' => true, 'default' => NULL]],
            'tx_limit_installment_max_b2b'          => ['type' => Table::TYPE_DECIMAL,  'length' => '20,4', 'option' => ['nullable' => true, 'default' => NULL]],
            'tx_limit_elv_min'                      => ['type' => Table::TYPE_DECIMAL,  'length' => '20,4', 'option' => ['nullable' => true, 'default' => NULL]],
            'tx_limit_elv_max'                      => ['type' => Table::TYPE_DECIMAL,  'length' => '20,4', 'option' => ['nullable' => true, 'default' => NULL]],
            'tx_limit_elv_max_b2b'                  => ['type' => Table::TYPE_DECIMAL,  'length' => '20,4', 'option' => ['nullable' => true, 'default' => NULL]],
            'tx_limit_prepayment_min'               => ['type' => Table::TYPE_DECIMAL,  'length' => '20,4', 'option' => ['nullable' => true, 'default' => NULL]],
            'tx_limit_prepayment_max'               => ['type' => Table::TYPE_DECIMAL,  'length' => '20,4', 'option' => ['nullable' => true, 'default' => NULL]],
            'tx_limit_prepayment_max_b2b'           => ['type' => Table::TYPE_DECIMAL,  'length' => '20,4', 'option' => ['nullable' => true, 'default' => NULL]],
            'b2b_invoice'                           => ['type' => Table::TYPE_INTEGER,  'length' => 1,      'option' => ['nullable' => true, 'default' => NULL]],
            'b2b_elv'                               => ['type' => Table::TYPE_INTEGER,  'length' => 1,      'option' => ['nullable' => true, 'default' => NULL]],
            'b2b_installment'                       => ['type' => Table::TYPE_INTEGER,  'length' => 1,      'option' => ['nullable' => true, 'default' => NULL]],
            'b2b_prepayment'                        => ['type' => Table::TYPE_INTEGER,  'length' => 1,      'option' => ['nullable' => true, 'default' => NULL]],
            'b2b_PQ_full'                           => ['type' => Table::TYPE_INTEGER,  'length' => 1,      'option' => ['nullable' => true, 'default' => NULL]],
            'delivery_address_invoice'              => ['type' => Table::TYPE_INTEGER,  'length' => 1,      'option' => ['nullable' => true, 'default' => NULL]],
            'delivery_address_installment'          => ['type' => Table::TYPE_INTEGER,  'length' => 1,      'option' => ['nullable' => true, 'default' => NULL]],
            'delivery_address_elv'                  => ['type' => Table::TYPE_INTEGER,  'length' => 1,      'option' => ['nullable' => true, 'default' => NULL]],
            'delivery_address_prepayment'           => ['type' => Table::TYPE_INTEGER,  'length' => 1,      'option' => ['nullable' => true, 'default' => NULL]],
            'delivery_address_PQ_full'              => ['type' => Table::TYPE_INTEGER,  'length' => 1,      'option' => ['nullable' => true, 'default' => NULL]],
            'country_code_billing'                  => ['type' => Table::TYPE_TEXT,     'length' => 32,     'option' => ['nullable' => true, 'default' => NULL]],
            'country_code_delivery'                 => ['type' => Table::TYPE_TEXT,     'length' => 32,     'option' => ['nullable' => true, 'default' => NULL]],
            'interestrate_min'                      => ['type' => Table::TYPE_DECIMAL,  'length' => '20,4', 'option' => ['nullable' => true, 'default' => NULL]],
            'interestrate_default'                  => ['type' => Table::TYPE_DECIMAL,  'length' => '20,4', 'option' => ['nullable' => true, 'default' => NULL]],
            'interestrate_max'                      => ['type' => Table::TYPE_DECIMAL,  'length' => '20,4', 'option' => ['nullable' => true, 'default' => NULL]],
            'interest_rate_merchant_towards_bank'   => ['type' => Table::TYPE_DECIMAL,  'length' => '20,4', 'option' => ['nullable' => true, 'default' => NULL]],
            'month_number_min'                      => ['type' => Table::TYPE_INTEGER,  'length' => 2,      'option' => ['nullable' => true, 'default' => NULL]],
            'month_number_max'                      => ['type' => Table::TYPE_INTEGER,  'length' => 2,      'option' => ['nullable' => true, 'default' => NULL]],
            'month_longrun'                         => ['type' => Table::TYPE_INTEGER,  'length' => 2,      'option' => ['nullable' => true, 'default' => NULL]],
            'amount_min_longrun'                    => ['type' => Table::TYPE_INTEGER,  'length' => 2,      'option' => ['nullable' => true, 'default' => NULL]],
            'month_allowed'                         => ['type' => Table::TYPE_TEXT,     'length' => 255,    'option' => ['nullable' => true, 'default' => NULL]],
            'valid_payment_firstdays'               => ['type' => Table::TYPE_TEXT,     'length' => 32,     'option' => ['nullable' => true, 'default' => NULL]],
            'payment_firstday'                      => ['type' => Table::TYPE_TEXT,     'length' => 32,     'option' => ['nullable' => true, 'default' => NULL]],
            'payment_amount'                        => ['type' => Table::TYPE_DECIMAL,  'length' => '20,4', 'option' => ['nullable' => true, 'default' => NULL]],
            'payment_lastrate'                      => ['type' => Table::TYPE_DECIMAL,  'length' => '20,4', 'option' => ['nullable' => true, 'default' => NULL]],
            'rate_min_normal'                       => ['type' => Table::TYPE_DECIMAL,  'length' => '20,4', 'option' => ['nullable' => true, 'default' => NULL]],
            'rate_min_longrun'                      => ['type' => Table::TYPE_DECIMAL,  'length' => '20,4', 'option' => ['nullable' => true, 'default' => NULL]],
            'service_charge'                        => ['type' => Table::TYPE_DECIMAL,  'length' => '20,4', 'option' => ['nullable' => true, 'default' => NULL]],
            'min_difference_dueday'                 => ['type' => Table::TYPE_INTEGER,  'length' => 2,      'option' => ['nullable' => true, 'default' => NULL]],
        ],
        'comment' => 'Holds information for Ratepay profile',
        'indexes' => null
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
