<?php

/**
 * Copyright (c) Ratepay GmbH
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace RatePAY\Payment\Helper\Head;

use Magento\Framework\App\Helper\Context;

class AttestationToken extends \Magento\Framework\App\Helper\AbstractHelper
{
    /**
     * @var \Magento\Checkout\Model\Session
     */
    protected $checkoutSession;

    /**
     * External constructor.
     *
     * @param Context $context
     * @param \Magento\Checkout\Model\Session $checkoutSession
     */
    public function __construct(
        Context $context,
        \Magento\Checkout\Model\Session $checkoutSession
    ) {
        parent::__construct($context);
        $this->checkoutSession = $checkoutSession;
    }

    /**
     * Build AttestationToken Block of Head Section
     *
     * @param $quoteOrOrder
     * @param $headModel
     * @return mixed
     */
    public function setHeadAttestationToken($quoteOrOrder, $headModel)
    {
        $token = $this->checkoutSession->getRatepayAttestationToken();

        if (!empty($token)) {
            $headModel->setArray([
                'AttestationToken' => $token,
            ]);
        }

        return $headModel;
    }
}
