<?php

/**
 * Copyright (c) Ratepay GmbH
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace RatePAY\Payment\Helper\Content\ShoppingBasket;

use Magento\Framework\App\Helper\Context;

class Discount extends \Magento\Framework\App\Helper\AbstractHelper
{
    /**
     * Discount constructor.
     * @param Context $context
     */
    public function __construct(Context $context)
    {
        parent::__construct($context);
    }

    /**
     * Build Discount-Items Block of Payment Request
     *
     * @param double $unitPriceGross
     * @param string $description
     * @return array
     */
    public function setDiscount($unitPriceGross, $description)
    {
        $content = [
            'Description' => $description,
            'UnitPriceGross' => round($unitPriceGross, 2),
            'TaxRate' => 0
            //'DescriptionAddition' => "Additional information about the shipping"
        ];
        return $content;
    }
}
