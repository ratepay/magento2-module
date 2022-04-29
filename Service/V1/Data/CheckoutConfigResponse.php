<?php

namespace RatePAY\Payment\Service\V1\Data;

use RatePAY\Payment\Api\Data\CheckoutConfigResponseInterface;

/**
 * Response object for checkout config
 */
class CheckoutConfigResponse extends \Magento\Framework\Api\AbstractExtensibleObject implements CheckoutConfigResponseInterface
{
    /**
     * Returns if the request was a success
     *
     * @return bool
     */
    public function getSuccess()
    {
        return $this->_get('success');
    }

    /**
     * Return json installment plan
     *
     * @return string
     */
    public function getCheckoutConfig()
    {
        return $this->_get('checkoutConfig');
    }

    /**
     * Returns errormessage
     *
     * @return string
     */
    public function getErrormessage()
    {
        return $this->_get('errormessage');
    }
}
