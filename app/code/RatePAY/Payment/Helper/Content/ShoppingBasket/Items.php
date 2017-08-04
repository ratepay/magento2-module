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
        $i = 0;

        foreach (($quoteOrOrder instanceof \Magento\Sales\Model\Order\Invoice) ? $quoteOrOrder->getItems()->getItems() : $quoteOrOrder->getItems() as $item) {

            if($item instanceof \Magento\Sales\Model\Order\Invoice\Item || $item instanceof \Magento\Sales\Model\Order\Creditmemo\Item){
                if ($item->getOrderItem()->getProductType() === \Magento\Catalog\Model\Product\Type::TYPE_SIMPLE && $item->getOrderItem()->getParentItem()) {
                    continue;
                }
                $quantity = (int)$item->getQty();
                if($quantity == 0){
                    continue;
                }
                $taxRate = round($item->getOrderItem()->getTaxPercent(),2);
            } else{
                if ($item->getProductType() === \Magento\Catalog\Model\Product\Type::TYPE_SIMPLE && $item->getParentItem()) {
                    continue;
                }
                $quantity = (int)$item->getQtyOrdered();
                $taxRate = round($item->getTaxPercent(), 2);
            }

            $items[] = [
                'Item' => [
                    'Description' => $item->getName(),
                    'ArticleNumber' => $item->getSku(),
                    //'UniqueArticleNumber' => "ArtNo1-variation123",
                    'Quantity' => $quantity,
                    'UnitPriceGross' => round($this->getCalculatedPrice($item, $quantity, $taxRate), 2), //if discount is applied getPriceInclTax() returns wrong value
                    'TaxRate' => $taxRate,
                    //'Category' => "Additional information about the product"
                    //'DescriptionAddition' => "Additional information about the product"

                ]
            ];
            if($item->getDiscountAmount() > 0){
                $items[$i]['Item']['Discount'] = round($item->getDiscountAmount() / $quantity, 2);
            }
            $i++;
        }

        return $items;
    }


    /*
     * the combination of tax and discount can cause problems
     * the calculated values offered by magento are sometimes not correct
     * to cover these cases the values get calculated here
     */
    protected function getCalculatedPrice($item, $qty, $taxRate)
    {
        $basePrice = $item->getPrice();
        $discountPercent = $item->getDiscountPercent();
        $discountAmount = $item->getDiscountAmount();
        $taxPercent = $taxRate;
        $itemQty = $qty;

        // if no discount is applied getPriceInclTax() returns correct value
        if ($discountAmount == 0 && $itemQty > 1){
            return $item->getPriceInclTax();

        // calculate Price if ordered quantity of item is over 1 and discount is applied
        } elseif ($discountAmount > 0 && $itemQty > 1){
            // check if discount is absolute or in percent
            //TODO solution required if absolute and discount in percent applied at the same time
            if($discountPercent != 0){
                // calculate price with discount in percent
                $priceWithDiscount = $basePrice - ($basePrice * $discountPercent / 100);
            } else {
                // calculate price with absolute discount
                $priceWithDiscount = $basePrice - round($discountAmount / $itemQty, 2);
            }
            $priceInclTax = round($basePrice + ($priceWithDiscount * $taxPercent / 100), 2);
            return $priceInclTax;
        } else {
            //if discount is applied and the item quantity is 1 getTaxAmount offers the right value
            return $basePrice + $item->getTaxAmount();
        }

    }
}
