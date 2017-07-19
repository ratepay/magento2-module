<?php
/**
 * Created by PhpStorm.
 * User: SebastianN
 * Date: 27.02.17
 * Time: 16:05
 */

namespace RatePAY\Payment\Model\Method\DE;


class Directdebit extends \RatePAY\Payment\Model\Method\Directdebit
{
    const METHOD_CODE = 'ratepay_de_directdebit';

    protected $_code = self::METHOD_CODE;
}
