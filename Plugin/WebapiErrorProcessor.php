<?php

/**
 * Copyright (c) Ratepay GmbH
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace RatePAY\Payment\Plugin;

use RatePAY\Payment\Model\Exception\DisablePaymentMethodException;
use Magento\Framework\Webapi\ErrorProcessor;

class WebapiErrorProcessor
{
    public function beforeMaskException(ErrorProcessor $subject, \Exception $e)
    {
        $oPreviousException = $e->getPrevious();
        if ($oPreviousException && $oPreviousException instanceof DisablePaymentMethodException) {
            return [$oPreviousException];
        }

        return null;
    }
}
