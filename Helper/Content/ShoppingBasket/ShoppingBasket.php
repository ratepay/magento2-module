<?php
/**
 * Created by PhpStorm.
 * User: SebastianN
 * Date: 09.02.17
 * Time: 16:17
 */

namespace RatePAY\Payment\Helper\Content\ShoppingBasket;

use Magento\Framework\App\Helper\Context;

class ShoppingBasket extends \Magento\Framework\App\Helper\AbstractHelper
{
    /**
     * @var Items
     */
    protected $rpContentBasketItemsHelper;

    /**
     * @var Shipping
     */
    protected $rpContentBasketShippingHelper;
    /**
     * @var Discount
     */
    protected $rpContentBasketDiscountHelper;

    /**
     * ShoppingBasket constructor.
     * @param Context $context
     * @param Items $rpContentBasketItemsHelper
     * @param Shipping $rpContentBasketShippingHelper
     * @param Discount $rpContentBasketDiscountHelper
     */
    public function __construct(
        Context $context,
        \RatePAY\Payment\Helper\Content\ShoppingBasket\Items $rpContentBasketItemsHelper,
        \RatePAY\Payment\Helper\Content\ShoppingBasket\Shipping $rpContentBasketShippingHelper,
        \RatePAY\Payment\Helper\Content\ShoppingBasket\Discount $rpContentBasketDiscountHelper
    ) {
        parent::__construct($context);

        $this->rpContentBasketItemsHelper = $rpContentBasketItemsHelper;
        $this->rpContentBasketShippingHelper = $rpContentBasketShippingHelper;
        $this->rpContentBasketDiscountHelper = $rpContentBasketDiscountHelper;
    }

    /**
     * Build Shopping Basket Block of Payment Request
     *
     * @param $quoteOrOrder
     * @param null $articleList
     * @param null $amount
     * @return array
     */
    public function setShoppingBasket($quoteOrOrder, $articleList = null, $amount = null)
    {
        $content = [];

        if (is_null($articleList)) {
            if ($quoteOrOrder->getAdjustmentPositive() > 0 || $quoteOrOrder->getAdjustmentNegative() > 0) {
                $content = [
                    'Items' => $this->rpContentBasketItemsHelper->setItems($quoteOrOrder)
                ];
            } else {
                $content = [
                    'Amount' => round($quoteOrOrder->getGrandTotal(), 2),
                    'Items' => $this->rpContentBasketItemsHelper->setItems($quoteOrOrder),
                ];
            }
            if ($quoteOrOrder->getShippingAmount() > 0) {
                $content['Shipping'] = $this->rpContentBasketShippingHelper->setShipping($quoteOrOrder);
            }
            if ($quoteOrOrder->getGiftCardsAmount() > 0) {
                $content['Discount'] = $this->rpContentBasketDiscountHelper->setDiscount($quoteOrOrder->getGiftCardsAmount(), 'GiftCard');
            }
        } elseif (count($articleList) > 0) {
            $content['Items'] = $articleList;
        }

        if (!is_null($amount)) {
            $content['Amount'] = $amount;
        }

        if ($quoteOrOrder->getOrderCurrencyCode() != 'EUR') {
            $content['Currency'] = $quoteOrOrder->getOrderCurrencyCode();
        }

        return $content;
    }
}
