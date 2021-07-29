<?php

namespace RatePAY\Payment\Model\Method;

class Installment0Backend extends Installment0
{
    const METHOD_CODE = parent::METHOD_CODE.self::BACKEND_SUFFIX;

    protected $_code = self::METHOD_CODE;
}
