<?php

namespace RatePAY\Payment\Api;

interface SecciInterface
{
    /**
     * @param int $cartId
     * @param string $method
     * @param string $paymentMethod
     * @return \RatePAY\Payment\Service\V1\Data\SecciResponse
     */
    public function triggerSecci($cartId, $method, $paymentMethod);

    /**
     * @param string $cartId Masked cart ID
     * @param string $method
     * @param string $paymentMethod
     * @param string $email Guest email, the guest email isn't reliably saved in the quote yet
     * @return \RatePAY\Payment\Service\V1\Data\SecciResponse
     */
    public function triggerGuestSecci($cartId, $method, $paymentMethod, $email = null);
}