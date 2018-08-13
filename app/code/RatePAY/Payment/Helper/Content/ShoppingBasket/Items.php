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

class Items extends \Magento\Framework\App\Helper\AbstractHelper
{
    /**
     * Items constructor.
     *
     * @param Context $context
     */
    public function __construct(Context $context)
    {
        parent::__construct($context);
    }

    /**
     * Build Items Block of Payment Request.
     *
     * @param $quoteOrOrder
     *
     * @return array
     */
    public function setItems($quoteOrOrder)
    {
        $items = [];

        foreach ($quoteOrOrder->getItems() as $item) {
            $parentItem = false;

            if ($item instanceof \Magento\Sales\Model\Order\Invoice\Item || $item instanceof \Magento\Sales\Model\Order\Creditmemo\Item) {
                if ($item->getOrderItem()->getProductType() === \Magento\Catalog\Model\Product\Type::TYPE_SIMPLE && $item->getOrderItem()->getParentItem()) {
                    $sku = $item->getOrderItem()->getParentItem()->getSku();
                    $quantity = (int) $item->getOrderItem()->getParentItem()->getQty();
                    $parentItem = true;
                } else {
                    $sku = $item->getSku();
                    $quantity = (int) $item->getQty();
                }

                if ($quantity === 0) {
                    continue;
                }

                if ($item->getOrderItem()->getProductType() === \Magento\Catalog\Model\Product\Type::TYPE_BUNDLE) {
                    $discount = 0.00;
                    $children = $item->getOrderItem()->getChildrenItems();
                    foreach ($children as $ch) {
                        $discount += $ch->getDiscountAmount();
                    }
                } else {
                    $discount = $item->getDiscountAmount();
                }

                $taxRate = $item->getOrderItem()->getTaxPercent();
            } else {
                if ($item->getProductType() === \Magento\Catalog\Model\Product\Type::TYPE_SIMPLE && $item->getParentItem()) {
                    $sku = $item->getParentItem()->getSku();
                    $quantity = (int) $item->getParentItem()->getQtyOrdered();
                    $parentItem = true;
                } else {
                    $sku = $item->getSku();
                    $quantity = (int) $item->getQtyOrdered();
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
                $items[$sku]['UnitPriceGross'] = $items[$sku]['UnitPriceGross'] = round($item->getPriceInclTax(), 2);
            }

            if (!isset($items[$sku]['Quantity'])) {
                $items[$sku]['Quantity'] = $quantity;
            } elseif (!$parentItem) {
                $items[$sku]['Quantity'] += $quantity;
            }

            if (!isset($items[$sku]['TaxRate']) || $items[$sku]['TaxRate'] < $taxRate) {
                $items[$sku]['TaxRate'] = round($taxRate, 2);
            }
            if ($discount > 0) {
                if (!isset($items[$sku]['Discount']) || $items[$sku]['Discount'] === 0) {
                    $items[$sku]['Discount'] = round($discount / $quantity, 2);
                } else {
                    $items[$sku]['Discount'] += round($discount / $quantity, 2);
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
}
