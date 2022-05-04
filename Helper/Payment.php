<?php
/**
 * Created by PhpStorm.
 * User: SebastianN
 * Date: 03.03.17
 * Time: 15:29
 */

namespace RatePAY\Payment\Helper;

use Magento\Framework\App\Helper\Context;

class Payment extends \Magento\Framework\App\Helper\AbstractHelper
{
    /**
     * Payment constructor.
     * @param Context $context
     */
    public function __construct(Context $context)
    {
        parent::__construct($context);
    }

    /**
     * @var array
     */
    private $_productsToMethods = [
        "invoice" => "ratepay_invoice",
        "installment" => "ratepay_installment",
        "installment0" => "ratepay_installment0",
        "elv" => "ratepay_directdebit",
        "prepayment" => "ratepay_vorkasse",
        "ratepay_invoice" => "invoice",
        "ratepay_installment" => "installment",
        "ratepay_installment0" => "installment",
        "ratepay_directdebit" => "elv",
        "ratepay_vorkasse" => "prepayment"
    ];

    /**
     * @param string $id
     * @return string
     */
    public function convertMethodToProduct($id)
    {
        $id = str_ireplace('ratepay_config_', '', $id);
        $id = str_ireplace('_backend', '', $id);
        return $this->_productsToMethods[$id];
    }

    /**
     * @param $code
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
