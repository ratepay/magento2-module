<?php

/**
 * Copyright (c) Ratepay GmbH
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace RatePAY\Payment\Api;

interface CheckoutConfigInterface
{
    /**
     * Return Ratepay checkout config
     *
     * @return \RatePAY\Payment\Service\V1\Data\CheckoutConfigResponse
     */
    public function refreshCheckoutConfig();
}
