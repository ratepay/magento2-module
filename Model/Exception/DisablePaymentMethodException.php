<?php

/**
 * Copyright (c) Ratepay GmbH
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace RatePAY\Payment\Model\Exception;

use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Phrase;

class DisablePaymentMethodException extends LocalizedException
{
    /**
     * @var string
     */
    protected $paymentMethodToDisable;

    /**
     * Constructor
     *
     * The $paymentMethodToDisable will be removed from the payment list in the checkout
     *
     * @param \Magento\Framework\Phrase $phrase
     * @param string[]|null             $paymentMethodToDisable
     */
    public function __construct(Phrase $phrase, $paymentMethodToDisable = null)
    {
        parent::__construct($phrase, null, 0);

        $this->paymentMethodToDisable = $paymentMethodToDisable;
    }

    /**
     * Get parameters, these will be returned in the place-order ajax call
     *
     * @return array
     */
    public function getParameters()
    {
        if (!empty($this->paymentMethodToDisable)) {
            return ['disablePaymentMethod' => $this->paymentMethodToDisable];
        }
        return [];
    }
}
