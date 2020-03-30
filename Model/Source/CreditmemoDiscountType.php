<?php


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
