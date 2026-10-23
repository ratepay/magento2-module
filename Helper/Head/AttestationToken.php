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
     * @var \RatePAY\Payment\Model\SecciAttestationState
     */
    protected $secciAttestationState;

    /**
     * External constructor.
     *
     * @param Context $context
     * @param \RatePAY\Payment\Model\SecciAttestationState $secciAttestationState
     */
    public function __construct(
        Context $context,
        \RatePAY\Payment\Model\SecciAttestationState $secciAttestationState
    ) {
        parent::__construct($context);
        $this->secciAttestationState = $secciAttestationState;
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
        $token = $this->secciAttestationState->getToken();

        if (!empty($token)) {
            $headModel->setArray([
                'AttestationToken' => $token,
            ]);
        }

        return $headModel;
    }
}
