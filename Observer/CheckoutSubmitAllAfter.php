<?php

/**
 * Copyright (c) Ratepay GmbH
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace RatePAY\Payment\Observer;

use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Event\Observer;

class CheckoutSubmitAllAfter implements ObserverInterface
{
    /**
     * @var \Magento\Checkout\Model\Session
     */
    protected $checkoutSession;

    /**
     * Constructor.
     *
     * @param \Magento\Checkout\Model\Session $checkoutSession
     */
    public function __construct(
        \Magento\Checkout\Model\Session $checkoutSession
    ) {
        $this->checkoutSession = $checkoutSession;
    }

    /**
     * Removes the SECCI attestation token after all orders were placed,
     * so it can't be reused for the next order
     * This event is triggered only once in multishipping checkout, after all sub-orders were placed
     *
     * @param  Observer $observer
     * @return void
     */
    public function execute(Observer $observer)
    {
        $this->checkoutSession->unsRatepayAttestationToken();
    }
}
