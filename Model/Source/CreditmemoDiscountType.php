<?php

/**
 * Copyright (c) Ratepay GmbH
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace RatePAY\Payment\Model\Source;

use Magento\Framework\Option\ArrayInterface;

class CreditmemoDiscountType implements ArrayInterface
{
    const STANDARD_ITEM = 'standard';
    const SPECIAL_ITEM = 'special';

    /**
     * Return existing address check types
     *
     * @return array
     */
    public function toOptionArray()
    {
        return [
            [
                'value' => self::STANDARD_ITEM,
                'label' => __('Standard item'),
            ],
            [
                'value' => self::SPECIAL_ITEM,
                'label' => __('Special item')
            ],
        ];
    }
}
