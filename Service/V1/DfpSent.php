<?php

/**
 * Copyright (c) Ratepay GmbH
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace RatePAY\Payment\Service\V1;

use RatePAY\Payment\Api\Data\DfpSentResponseInterfaceFactory;
use RatePAY\Payment\Api\DfpSentInterface;

class DfpSent implements DfpSentInterface
{
    /**
     * Factory for the response object
     *
     * @var DfpSentResponseInterfaceFactory
     */
    protected $responseFactory;

    /**
     * @var \Magento\Checkout\Model\Session
     */
    protected $checkoutSession;

    /**
     * Constructor.
     *
     * @param DfpSentResponseInterfaceFactory $responseFactory
     * @param \Magento\Checkout\Model\Session $checkoutSession
     */
    public function __construct(
        DfpSentResponseInterfaceFactory $responseFactory,
        \Magento\Checkout\Model\Session $checkoutSession
    ) {
        $this->responseFactory = $responseFactory;
        $this->checkoutSession = $checkoutSession;
    }

    /**
     * Mark DeviceFingerprint as sent
     *
     * @return \RatePAY\Payment\Service\V1\Data\DfpSentResponse
     */
    public function markDfpAsSent()
    {
        /** @var \RatePAY\Payment\Service\V1\Data\DfpSentResponse $response */
        $response = $this->responseFactory->create();
        $response->setData('success', true);

        $this->checkoutSession->setRatepayDfpSent(true);
        return $response;
    }
}
