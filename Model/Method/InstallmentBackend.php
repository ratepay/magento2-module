<?php

/**
 * Copyright (c) Ratepay GmbH
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace RatePAY\Payment\Model\Method;

class InstallmentBackend extends Installment
{
    const METHOD_CODE = parent::METHOD_CODE.self::BACKEND_SUFFIX;

    protected $_code = self::METHOD_CODE;
}
