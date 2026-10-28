<?php

/**
 * Copyright (c) Ratepay GmbH
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace RatePAY\Payment\Model\Source;

use Magento\Framework\Option\ArrayInterface;

class SecciDesignVariant implements ArrayInterface
{
    /**
     * Return existing Secci design variant types
     *
     * @return array
     */
    public function toOptionArray()
    {
        return [
            [
                'value' => 1,
                'label' => __('Automatic email delivery'),
            ],
            [
                'value' => 2,
                'label' => __('Explicit SECCI interaction'),
            ],
        ];
    }
}
