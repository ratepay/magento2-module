<?php
/**
 * Created by PhpStorm.
 * User: SebastianN
 * Date: 09.02.17
 * Time: 16:21
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
        if (!$quoteOrOrder instanceof Order) {
            $aItemlist = $quoteOrOrder->getOrder()->getItems();
        }

        foreach ($aItemlist as $item) {
            if (!($item->getProductType() === \Magento\Catalog\Model\Product\Type::TYPE_SIMPLE && $item->getParentItem())) {
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
        $skuMap = [];
        $itemArray = $quoteOrOrder->getItems();
        $store = $quoteOrOrder->getStore();
        foreach ($itemArray as $item) {

            $parentItem = false;

            if ($item instanceof \Magento\Sales\Model\Order\Invoice\Item || $item instanceof \Magento\Sales\Model\Order\Creditmemo\Item) { // backend after-sales processes
                if ($item->getOrderItem()->getProductType() === \Magento\Catalog\Model\Product\Type::TYPE_SIMPLE && $item->getOrderItem()->getParentItem()) {
                    $sku = $item->getOrderItem()->getParentItem()->getSku();
                    if (isset($skuMap[$item->getOrderItem()->getParentItem()->getQuoteItemId()])) {
                        $sku = $skuMap[$item->getOrderItem()->getParentItem()->getQuoteItemId()];
                    }
                    $quantity = (int) $item->getOrderItem()->getParentItem()->getQty();
                    $parentItem = true;
                } else {
                    $sku = $item->getSku();
                    $quantity = (int) $item->getQty();
                    if ($this->isSkuDuplicateInOrder($sku, $quoteOrOrder)) {
                        $sku = $sku.'_'.$item->getOrderItem()->getQuoteItemId();
                    }
                    $skuMap[$item->getOrderItem()->getQuoteItemId()] = $sku;
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
                if ($item->getProductType() === \Magento\Catalog\Model\Product\Type::TYPE_SIMPLE && $item->getParentItem()) {
                    $sku = $item->getParentItem()->getSku();
                    if (isset($skuMap[$item->getParentItem()->getQuoteItemId()])) {
                        $sku = $skuMap[$item->getParentItem()->getQuoteItemId()];
                    }
                    $quantity = (int) $item->getParentItem()->getQtyOrdered();
                    $parentItem = true;
                } else {
                    $sku = $item->getSku();
                    $quantity = (int) $item->getQtyOrdered();
                    if ($this->isSkuDuplicateInOrder($sku, $quoteOrOrder)) {
                        $sku = $sku.'_'.$item->getQuoteItemId();
                    }
                    $skuMap[$item->getQuoteItemId()] = $sku;
                }

                $discount = $item->getDiscountAmount();
                $taxRate = $item->getTaxPercent();
            }

            if (!isset($items[$sku])) {
                $items[$sku]['ArticleNumber'] = $sku;
            }
            if ((!isset($items[$sku]['Description']) || strlen($items[$sku]['Description']) < $item->getName())) {
                $items[$sku]['Description'] = $item->getName();
            }
            if (!isset($items[$sku]['UnitPriceGross']) || $items[$sku]['UnitPriceGross'] < $item->getPriceInclTax()) {
                $items[$sku]['UnitPriceGross'] =  $items[$sku]['UnitPriceGross'] = round($item->getPriceInclTax(), 2);
            }

            if (!isset($items[$sku]['Quantity'])) {
                $items[$sku]['Quantity'] = $quantity;
            } elseif (!$parentItem) {
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
}
