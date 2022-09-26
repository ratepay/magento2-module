<?php

/**
 * Copyright (c) Ratepay GmbH
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace RatePAY\Payment\Api;

interface OfflineCalculatorInterface
{
    /**
     * Return Ratepay offline config response
     *
     * @param float $price
     * @param string $currency
     * @return \RatePAY\Payment\Service\V1\Data\OfflineCalculatorResponse
     */
    public function getInstallmentInfo($price, $currency);
}
