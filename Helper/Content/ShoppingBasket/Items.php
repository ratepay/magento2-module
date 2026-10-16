<?php

/**
 * Copyright (c) Ratepay GmbH
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace RatePAY\Payment\Helper\Content\ShoppingBasket;

use Magento\Framework\App\Helper\Context;
use Magento\Sales\Model\Order;
use Magento\Sales\Model\Order\Creditmemo;
use Magento\Tax\Model\Config;

class Items extends \Magento\Framework\App\Helper\AbstractHelper
{
    /**
     * Magento tax config
     *
     * @var Config
     */
    protected $taxConfig;

    /**
     * Items constructor.
     * @param Context $context
     * @param Config  $taxConfig
     */
    public function __construct(Context $context, Config $taxConfig)
    {
        parent::__construct($context);
        $this->taxConfig = $taxConfig;
    }

    /**
     * Takes quantity from first bundle item.
     * This is not yet 100% correct, because there could be different quantities
     * But as long as the bundle is handled as 1 product this is the closest way to solve this
     *
     * @param $aItems
     * @return double
     */
    protected function _getBundleQuantity($aItems, $oDummy)
    {
        foreach ($aItems as $oItem) {
            if ($oItem->getOrderItem()->isDummy() === false) {
                foreach ($oDummy->getOrderItem()->getChildrenItems() as $oChildrenItem) { // only return qty of a part of the bundle
                    if ($oItem->getOrderItemId() == $oChildrenItem->getItemId()) {
                        return $oItem->getQty();
                    }
                }
            }
        }
        return false;
    }

    protected function isSkuDuplicateInOrder($sSku, $quoteOrOrder)
    {
        $iCount = 0;

        $aItemlist = $quoteOrOrder->getItems();
        if (!$quoteOrOrder instanceof Order && !empty($quoteOrOrder->getOrder())) {
            $aItemlist = $quoteOrOrder->getOrder()->getItems();
        }

        foreach ($aItemlist as $item) {
            // Child items (e.g. virtual children of configurable products) are not contained in the quote (only visible items)
            if (!$item->getParentItem()) {
                $itemSku = $item->getSku();
                if ($itemSku == $sSku) {
                    $iCount++;
                }
            }
        }
        if ($iCount > 1) {
            return true;
        }
        return false;
    }

    /**
     * Build Items Block of Payment Request
     *
     * @param $quoteOrOrder
     * @return array
     */
    public function setItems($quoteOrOrder)
    {
        $items = [];
        $itemArray = $quoteOrOrder->getItems();
        $store = $quoteOrOrder->getStore();
        foreach ($itemArray as $item) {
            if ($item instanceof \Magento\Sales\Model\Order\Invoice\Item || $item instanceof \Magento\Sales\Model\Order\Creditmemo\Item) { // backend after-sales processes
                // Child items are handled with their parent item, same as in the payment request
                if ($item->getOrderItem()->getParentItem()) {
                    continue;
                }

                $sku = $item->getSku();
                $quantity = (int) $item->getQty();
                if ($this->isSkuDuplicateInOrder($sku, $quoteOrOrder)) {
                    $sku = $sku.'_'.$item->getOrderItem()->getQuoteItemId();
                }

                if ($quantity == 0) {
                    continue;
                }

                if ($item->getOrderItem()->getProductType() === \Magento\Catalog\Model\Product\Type::TYPE_BUNDLE) {
                    $bundleQuantity = $this->_getBundleQuantity($quoteOrOrder->getItems(), $item); // bundles always have a qty of 1, which is wrong
                    if ($bundleQuantity !== false) {
                        $quantity = (int)$bundleQuantity;
                    }

                    $discount = 0.00;
                    $taxRate = 0;
                    $children = $item->getOrderItem()->getChildrenItems();
                    foreach ($children as $ch) {
                        if ($quoteOrOrder instanceof Creditmemo) {
                            foreach ($itemArray as $creditmemoItem) {
                                if ($creditmemoItem->getOrderItemId() == $ch->getId()) {
                                    $discount += $creditmemoItem->getDiscountAmount();
                                }
                            }
                        } else {
                            $discount += $ch->getDiscountAmount();
                        }
                        $taxRate += $ch->getTaxPercent();
                    }
                    $taxRate = $taxRate / count($children);
                } else {
                    $discount = $item->getDiscountAmount();
                    $taxRate = $item->getOrderItem()->getTaxPercent();
                }
            } else { // order generation
                // Child items (configurable and bundle products) are contained in the order, but not in the quote (only visible items).
                // They are handled with their parent item, otherwise the basket wouldn't match the one attested by the SECCI
                if ($item->getParentItem()) {
                    continue;
                }

                $sku = $item->getSku();
                $quantity = (int) $item->getQtyOrdered();
                if ($item instanceof \Magento\Quote\Model\Quote\Item) {
                    $quantity = (int) $item->getQty();
                }
                if ($this->isSkuDuplicateInOrder($sku, $quoteOrOrder)) {
                    // Quote items don't have a quote_item_id, their own id is the one referenced by the order item
                    $quoteItemId = $item instanceof \Magento\Quote\Model\Quote\Item ? $item->getId() : $item->getQuoteItemId();
                    $sku = $sku.'_'.$quoteItemId;
                }

                $discount = $item->getDiscountAmount();
                $taxRate = $item->getTaxPercent();

                // Bundles with dynamic price: discount and tax are calculated on the child items
                if ($item->getProductType() === \Magento\Catalog\Model\Product\Type::TYPE_BUNDLE && $item->isChildrenCalculated()) {
                    $children = $this->getChildItems($item);
                    if (!empty($children)) {
                        $discount = 0.00;
                        $taxRate = 0;
                        foreach ($children as $ch) {
                            $discount += $ch->getDiscountAmount();
                            $taxRate += $ch->getTaxPercent();
                        }
                        $taxRate = $taxRate / count($children);
                    }
                }
            }

            if (!isset($items[$sku])) {
                $items[$sku]['ArticleNumber'] = $sku;
            }
            $items[$sku]['Description'] = $item->getName();
            if (!isset($items[$sku]['UnitPriceGross']) || $items[$sku]['UnitPriceGross'] < $item->getPriceInclTax()) {
                $calcTaxRate = $taxRate;
                if (empty($calcTaxRate) && $item->getProductType() === \Magento\Catalog\Model\Product\Type::TYPE_BUNDLE) {
                    $calcTaxRate = $this->getAverageBundleTax($item);
                }
                $items[$sku]['UnitPriceGross'] = $this->getUnitGrossPrice($item->getPriceInclTax(), $item->getPrice(), round($calcTaxRate ?? 0, 2));
            }

            if (!isset($items[$sku]['Quantity'])) {
                $items[$sku]['Quantity'] = $quantity;
            } else {
                $items[$sku]['Quantity'] += $quantity;
            }

            if (!isset($items[$sku]['TaxRate']) || $items[$sku]['TaxRate'] < $taxRate) {
                $items[$sku]['TaxRate'] = round($taxRate ?? 0, 2);
            }
            if ($discount > 0) {
                if (!isset($items[$sku]['Discount']) || $items[$sku]['Discount'] == 0) {
                    $items[$sku]['Discount'] = $this->getDiscount($discount, $quantity, $item->getRowTotalInclTax(), $items[$sku]['TaxRate'], $store);
                } else {
                    $items[$sku]['Discount'] += $this->getDiscount($discount, $quantity, $item->getRowTotalInclTax(), $items[$sku]['TaxRate'], $store);
                }
            }

        }

        // Build structure for library
        $return = [];
        foreach ($items as $item) {
            $return[] = ['Item' => $item];
        }

        return $return;
    }

    /**
     * Calculate the discount for a single product.
     *
     * The $item->getDiscount() call only return the discount for the ordered quantity without tax
     * The RatePAY API needs it for a single product and with tax though
     *
     * @param  double $discount
     * @param  double $quantity
     * @param  double $total
     * @param  double $vat
     * @param \Magento\Store\Model\Store $store
     * @return double
     */
    protected function getDiscount($discount, $quantity, $total, $vat, $store)
    {
        if ($this->taxConfig->discountTax($store) === false) { // discountTax() === false equates to config "Apply Discount On Prices" = "Excluding Tax"
            $discount = $discount * ((100 + $vat) / 100);
        }
        $dSingleDiscount = $discount / $quantity;

        return $dSingleDiscount;
    }

    /**
     * @param $item
     * @return float|int
     */
    protected function getAverageBundleTax($item)
    {
        // get average tax rate for bundle elements
        $taxRate = 0;
        $children = $this->getChildItems($item);
        if (empty($children)) {
            return $taxRate;
        }
        foreach ($children as $ch) {
            $taxRate += $ch->getTaxPercent();
        }
        $taxRate = $taxRate / count($children);
        return $taxRate;
    }

    /**
     * Returns the child items of a quote or order item
     *
     * @param \Magento\Quote\Model\Quote\Item\AbstractItem|\Magento\Sales\Model\Order\Item $item
     * @return array
     */
    protected function getChildItems($item)
    {
        if ($item instanceof \Magento\Quote\Model\Quote\Item\AbstractItem) {
            return $item->getChildren() ?? [];
        }
        return $item->getChildrenItems() ?? [];
    }

    /**
     * Determine unit gross price to be communicated to Ratepay API
     * In certain situations price has to be rounded to 4 digits to handle rounding errors on ratepay side
     *
     * @param float $brutPrice
     * @param float $netPrice
     * @param float $taxPercent
     * @return float
     */
    protected function getUnitGrossPrice($brutPrice, $netPrice, $taxPercent)
    {
        $manualBrutPrice = round($netPrice * ((100 + $taxPercent) / 100), 4);
        $shopBrutPrice = round($brutPrice, 2);
        if (($manualBrutPrice - $shopBrutPrice) != 0.0) {
            return $manualBrutPrice; // return rounded to precision 4
        }
        return $shopBrutPrice; // return shop original rounded to precision 2
    }
}
