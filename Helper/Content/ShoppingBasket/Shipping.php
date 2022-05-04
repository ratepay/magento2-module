<?php
/**
 * Created by PhpStorm.
 * User: SebastianN
 * Date: 09.02.17
 * Time: 16:26
 */

namespace RatePAY\Payment\Helper\Content\ShoppingBasket;

use Magento\Framework\App\Helper\Context;

class Shipping extends \Magento\Framework\App\Helper\AbstractHelper
{
    /**
     * Shipping constructor.
     * @param Context $context
     */
    public function __construct(Context $context)
    {
        parent::__construct($context);
    }

    /**
     * Build Shipping-Items Block of Payment Request
     *
     * @param $quoteOrOrder
     * @return array
     */
    public function setShipping($quoteOrOrder)
    {
        $order = $quoteOrOrder;
        if ($quoteOrOrder instanceof \Magento\Sales\Model\Order\Creditmemo) {
            $order = $quoteOrOrder->getOrder();
        }
        $content = [
            'Description' => $order->getShippingDescription(),
            'UnitPriceGross' => round($quoteOrOrder->getShippingInclTax(), 2),
            'TaxRate' => round(($quoteOrOrder->getShippingTaxAmount() / $quoteOrOrder->getShippingAmount()) * 100)
            //'DescriptionAddition' => "Additional information about the shipping"
        ];
        return $content;
    }
}
