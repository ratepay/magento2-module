<?php

/**
 * RatePAY Payments - Magento 2
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 */

namespace RatePAY\Payment\Helper;

use Magento\Framework\App\Helper\Context;

class Payment extends \Magento\Framework\App\Helper\AbstractHelper
{
    /**
     * @var array
     */
    private $_productsToMethods = [
        'invoice' => 'ratepay_invoice',
        'installment' => 'ratepay_installment',
        'installment0' => 'ratepay_installment',
        'elv' => 'ratepay_directdebit',
        'prepayment' => 'ratepay_vorkasse',
        'ratepay_invoice' => 'invoice',
        'ratepay_installment' => 'installment',
        'ratepay_installment0' => 'installment',
        'ratepay_directdebit' => 'elv',
        'ratepay_vorkasse' => 'prepayment',
    ];

    /**
     * Payment constructor.
     *
     * @param Context $context
     */
    public function __construct(Context $context)
    {
        parent::__construct($context);
    }

    /**
     * @param $id
     *
     * @return mixed
     */
    public function convertMethodToProduct($id)
    {
        return $this->_productsToMethods[$id];
    }

    /**
     * @param $code
     *
     * @return bool
     */
    public function isRatepayPayment($code)
    {
        if (strstr($code, 'ratepay')) {
            return true;
        } else {
            return false;
        }
    }
}
