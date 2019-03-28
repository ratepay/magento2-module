<?php
/**
 * Created by PhpStorm.
 * User: SebastianN
 * Date: 27.02.17
 * Time: 16:05
 */

namespace RatePAY\Payment\Model\Method\DE;


class Installment extends \RatePAY\Payment\Model\Method\Installment
{
    const METHOD_CODE = 'ratepay_de_installment';

    protected $_code = self::METHOD_CODE;
}