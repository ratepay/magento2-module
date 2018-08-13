<?php

/**
 * RatePAY Payments - Magento 2
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 */

namespace RatePAY\Payment\Helper\Content\ShoppingBasket;

use Magento\Framework\App\Helper\Context;

class Shipping extends \Magento\Framework\App\Helper\AbstractHelper
{
    /**
     * Shipping constructor.
     *
     * @param Context $context
     */
    public function __construct(Context $context)
    {
        parent::__construct($context);
    }

    /**
     * Build Shipping-Items Block of Payment Request.
     *
     * @param $quoteOrOrder
     *
     * @return array
     */
    public function setShipping($quoteOrOrder)
    {
        return [
            'Description' => $quoteOrOrder->getShippingDescription(),
            'UnitPriceGross' => round($quoteOrOrder->getShippingInclTax(), 2),
            'TaxRate' => round(($quoteOrOrder->getShippingTaxAmount() / $quoteOrOrder->getShippingAmount()) * 100),
            //'DescriptionAddition' => "Additional information about the shipping"
        ];
    }
}
