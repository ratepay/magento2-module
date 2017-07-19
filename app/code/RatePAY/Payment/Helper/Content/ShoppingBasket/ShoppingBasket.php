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
     * ShoppingBasket constructor.
     * @param Context $context
     * @param Items $rpContentBasketItemsHelper
     * @param Shipping $rpContentBasketShippingHelper
     */
    public function __construct(Context $context,
                                \RatePAY\Payment\Helper\Content\ShoppingBasket\Items $rpContentBasketItemsHelper,
                                \RatePAY\Payment\Helper\Content\ShoppingBasket\Shipping $rpContentBasketShippingHelper)
    {
        parent::__construct($context);

        $this->rpContentBasketItemsHelper = $rpContentBasketItemsHelper;
        $this->rpContentBasketShippingHelper = $rpContentBasketShippingHelper;
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
                    'Amount' => round($quoteOrOrder->getBaseGrandTotal(), 2),
                    'Items' => $this->rpContentBasketItemsHelper->setItems($quoteOrOrder),
                ];
            }
            if (!empty($quoteOrOrder->getShippingAmount())) {
                $content['Shipping'] = $this->rpContentBasketShippingHelper->setShipping($quoteOrOrder);
            }
        } elseif (count($articleList) > 0) {
            $content['Items'] = $articleList;
        }

        if (!is_null($amount)) {
            $content['Amount'] = $amount;
        }

        return $content;
    }
}
