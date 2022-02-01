<?php

namespace RatePAY\Payment\Model\Method;

class DirectdebitBackend extends Directdebit
{
    const METHOD_CODE = parent::METHOD_CODE.self::BACKEND_SUFFIX;

    protected $_code = self::METHOD_CODE;
}
