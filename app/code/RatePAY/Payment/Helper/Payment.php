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
     * @var array
     */
    private $_productsToMethods = array(
        "invoice" => "ratepay_invoice",
        "installment" => "ratepay_installment",
        "installment0" => "ratepay_installment",
        "elv" => "ratepay_directdebit",
        "prepayment" => "ratepay_vorkasse",
        "ratepay_invoice" => "invoice",
        "ratepay_installment" => "installment",
        "ratepay_installment0" => "installment",
        "ratepay_directdebit" => "elv",
        "ratepay_vorkasse" => "prepayment"
    );

    /**
     * Payment constructor.
     * @param Context $context
     */
    public function __construct(Context $context)
    {
        parent::__construct($context);
    }

    /**
     * @param $id
     * @return mixed
     */
    public function convertMethodToProduct($id)
    {
        return $this->_productsToMethods[$id];
    }
}
