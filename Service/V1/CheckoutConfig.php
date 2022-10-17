<?php

/**
 * Copyright (c) Ratepay GmbH
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace RatePAY\Payment\Service\V1;

use RatePAY\Payment\Api\Data\CheckoutConfigResponseInterfaceFactory;
use RatePAY\Payment\Api\CheckoutConfigInterface;

class CheckoutConfig implements CheckoutConfigInterface
{
    /**
     * Factory for the response object
     *
     * @var CheckoutConfigResponseInterfaceFactory
     */
    protected $responseFactory;

    /**
     * Ratepay config provider class
     *
     * @var \RatePAY\Payment\Model\RechnungConfigProvider
     */
    protected $configProvider;

    /**
     * Constructor.
     *
     * @param CheckoutConfigResponseInterfaceFactory $responseFactory
     * @param \RatePAY\Payment\Model\RechnungConfigProvider $configProvider
     */
    public function __construct(
        CheckoutConfigResponseInterfaceFactory $responseFactory,
        \RatePAY\Payment\Model\RechnungConfigProvider $configProvider
    ) {
        $this->responseFactory = $responseFactory;
        $this->configProvider = $configProvider;
    }

    /**
     * Return Ratepay checkout config
     *
     * @return \RatePAY\Payment\Service\V1\Data\CheckoutConfigResponse
     */
    public function refreshCheckoutConfig()
    {
        /** @var \RatePAY\Payment\Service\V1\Data\CheckoutConfigResponse $response */
        $response = $this->responseFactory->create();
        $response->setData('success', false);

        try {
            $aConfig = $this->configProvider->getConfig();
            $aConfig = $aConfig['payment'];
            $response->setData('checkoutConfig', json_encode($aConfig));
            $response->setData('success', true);
        } catch (\Exception $e) {
            $response->setData('errormessage', $e->getMessage());
        }
        return $response;
    }
}
