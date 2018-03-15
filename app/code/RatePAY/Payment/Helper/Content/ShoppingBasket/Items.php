<?php
/**
 * Created by PhpStorm.
 * User: SebastianN
 * Date: 09.02.17
 * Time: 16:21
 */

namespace RatePAY\Payment\Helper\Content\ShoppingBasket;


use Magento\Framework\App\Helper\Context;

class Items extends \Magento\Framework\App\Helper\AbstractHelper
{
    /**
     * Items constructor.
     * @param Context $context
     */
    public function __construct(Context $context)
    {
        parent::__construct($context);
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

        foreach (($quoteOrOrder instanceof \Magento\Sales\Model\Order\Invoice) ? $quoteOrOrder->getItems()->getItems() : $quoteOrOrder->getItems() as $item) {

            if ($item instanceof \Magento\Sales\Model\Order\Invoice\Item || $item instanceof \Magento\Sales\Model\Order\Creditmemo\Item) {
                if ($item->getOrderItem()->getProductType() === \Magento\Catalog\Model\Product\Type::TYPE_SIMPLE && $item->getOrderItem()->getParentItem()) {
                    $sku = $item->getOrderItem()->getParentItem()->getSku(); //continue;
                } else {
                    $sku = $item->getSku();
                }

                $quantity = (int) $item->getQty();
                if($quantity == 0){
                    continue;
                }
                $taxRate = $item->getOrderItem()->getTaxPercent();

            } else {

                if ($item->getProductType() === \Magento\Catalog\Model\Product\Type::TYPE_SIMPLE && $item->getParentItem()) {
                    $sku = $item->getParentItem()->getSku();
                } else {
                    $sku = $item->getSku();
                }

                $quantity = (int) $item->getQtyOrdered();
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
                $items[$sku]['Quantity'] =  $quantity;
            }
            if (!isset($items[$sku]['TaxRate']) || $items[$sku]['TaxRate'] < $taxRate) {
                $items[$sku]['TaxRate'] = round($taxRate, 2);
            }
            if ($item->getDiscountAmount() > 0) {
                if (!isset($items[$sku]['Discount']) || $items[$sku]['Discount'] == 0) {
                    $items[$sku]['Discount'] = round($item->getDiscountAmount() / $quantity, 2);
                } else {
                    $items[$sku]['Discount'] += round($item->getDiscountAmount() / $quantity, 2);
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
