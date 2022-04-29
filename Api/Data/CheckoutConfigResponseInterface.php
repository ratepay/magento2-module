<?php

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
