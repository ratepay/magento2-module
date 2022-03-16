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
     * @var \Magento\Sales\Model\ResourceModel\Order\Tax\Item
     */
    protected $taxItem;

    /**
     * Shipping constructor.
     * @param Context $context
     * @param \Magento\Sales\Model\ResourceModel\Order\Tax\Item $item
     */
    public function __construct(
        Context $context,
        \Magento\Sales\Model\ResourceModel\Order\Tax\Item $item
    ) {
        parent::__construct($context);
        $this->taxItem = $item;
    }

    /**
     * Determine shipping vat by reading it from the order object which is needlessly complicated...
     *
     * @param  $quoteOrOrder
     * @return float|mixed
     */
    protected function getShippingVat($quoteOrOrder)
    {
        if ($quoteOrOrder instanceof \Magento\Sales\Model\Order\Invoice || $quoteOrOrder instanceof \Magento\Sales\Model\Order\Creditmemo) {
            $quoteOrOrder = $quoteOrOrder->getOrder();
        }

        $tax_items = $this->taxItem->getTaxItemsByOrderId($quoteOrOrder->getId());
        if (is_array($tax_items)) { // only works when order already is created
            foreach ($tax_items as $item) {
                if ($item['taxable_item_type'] === 'shipping') {
                    return $item['tax_percent'];
                }
            }
        }

        $extensionAttribute = $quoteOrOrder->getExtensionAttributes();
        if ($extensionAttribute) {
            $taxesForItems = $extensionAttribute->getItemAppliedTaxes();
            foreach ($taxesForItems as $tax) {
                if ($tax->getType() == "shipping") {
                    $appliedTaxes = $tax->getAppliedTaxes();
                    if (is_array($appliedTaxes) && !empty($appliedTaxes)) {
                        $shippingTax = array_shift($appliedTaxes);
                        if ($shippingTax instanceof \Magento\Tax\Model\Sales\Order\Tax) {
                            return $shippingTax->getPercent();
                        }
                    }
                }
            }
        }

        // old mechanism as fallback
        return round(($quoteOrOrder->getShippingTaxAmount() / $quoteOrOrder->getShippingAmount()) * 100);
    }

    /**
     * Build Shipping-Items Block of Payment Request
     *
     * @param $quoteOrOrder
     * @return array
     */
    public function setShipping($quoteOrOrder)
    {
        $content = [
            'Description' => $quoteOrOrder->getShippingDescription(),
            'UnitPriceGross' => round($quoteOrOrder->getShippingInclTax(),2),
            'TaxRate' => (float)$this->getShippingVat($quoteOrOrder)
            //'DescriptionAddition' => "Additional information about the shipping"
        ];
        return $content;
    }
}
