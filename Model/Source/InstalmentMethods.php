<?php

/**
 * Copyright (c) Ratepay GmbH
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace RatePAY\Payment\Model\Source;

use Magento\Framework\Option\ArrayInterface;

class InstalmentMethods implements ArrayInterface
{
    const INSTALMENT = 'ratepay_installment';
    const INSTALMENT0 = 'ratepay_installment0';

    /**
     * Return existing street field usage types
     *
     * @return array
     */
    public function toOptionArray()
    {
        return [
            [
                'value' => self::INSTALMENT,
                'label' => __('Instalment'),
            ],
            [
                'value' => self::INSTALMENT0,
                'label' => __('0% Financing')
            ],
        ];
    }
}
