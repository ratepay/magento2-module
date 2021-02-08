<?php


namespace RatePAY\Payment\Model\Source;

use Magento\Framework\Option\ArrayInterface;

class CaptureEvent implements ArrayInterface
{
    const TRIGGER_ON_INVOICE = 'triggerOnInvoice';
    const TRIGGER_ON_SHIPPING = 'triggerOnShipping';

    /**
     * Return capture trigger events
     *
     * @return array
     */
    public function toOptionArray()
    {
        return [
            [
                'value' => self::TRIGGER_ON_INVOICE,
                'label' => __('Send capture when creating the invoice'),
            ],
            [
                'value' => self::TRIGGER_ON_SHIPPING,
                'label' => __('Send capture when creating the shipment')
            ],
        ];
    }
}
