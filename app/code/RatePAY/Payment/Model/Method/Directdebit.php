<?php
/**
 * Created by PhpStorm.
 * User: SebastianN
 * Date: 24.02.17
 * Time: 15:48
 */

namespace RatePAY\Payment\Model\Method;


class Directdebit extends AbstractMethod
{
    const METHOD_CODE = 'ratepay_directdebit';

    protected $_code = self::METHOD_CODE;
}
