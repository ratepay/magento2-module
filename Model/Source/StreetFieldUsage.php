<?php

/**
 * Copyright (c) Ratepay GmbH
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace RatePAY\Payment\Model\Source;

use Magento\Framework\Option\ArrayInterface;

class StreetFieldUsage implements ArrayInterface
{
    const HOUSENR = 'housenr';
    const ADDITIONAL = 'additional';

    /**
     * Return existing street field usage types
     *
     * @return array
     */
    public function toOptionArray()
    {
        return [
            [
                'value' => self::HOUSENR,
                'label' => __('House number'),
            ],
            [
                'value' => self::ADDITIONAL,
                'label' => __('Additional info')
            ],
        ];
    }
}
