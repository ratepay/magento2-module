<?php


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
