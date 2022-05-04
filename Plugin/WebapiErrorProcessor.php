<?php

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
