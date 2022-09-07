<?php

/**
 * Copyright (c) Ratepay GmbH
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace RatePAY\Payment\Api\Data;

/**
 * Response interface for checkout config
 */
interface CheckoutConfigResponseInterface
{
    /**
     * Returns if the request was a success
     *
     * @return bool
     */
    public function getSuccess();

    /**
     * Return json checkout config
     *
     * @return string
     */
    public function getCheckoutConfig();

    /**
     * Returns errormessage
     *
     * @return string
     */
    public function getErrormessage();
}
