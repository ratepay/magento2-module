<?php
/**
 * Created by PhpStorm.
 * User: SebastianN
 * Date: 27.02.17
 * Time: 16:03
 */

namespace RatePAY\Payment\Model\Method\DE;


class Invoice extends \RatePAY\Payment\Model\Method\Invoice
{
    const METHOD_CODE = 'ratepay_de_invoice';

    protected $_code = self::METHOD_CODE;
}