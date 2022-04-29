<?php

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
