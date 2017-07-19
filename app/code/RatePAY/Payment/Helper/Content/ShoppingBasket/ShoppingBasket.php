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
     * @return array
     */
    public function setShoppingBasket($quoteOrOrder)
    {
        $content = [];
        $content = [
                'Amount' => round($quoteOrOrder->getBaseGrandTotal(), 2),
                'Items' => $this->rpContentBasketItemsHelper->setItems($quoteOrOrder),
        ];
        if (!empty($quoteOrOrder->getShippingAmount())) {
            $content['Shipping'] = $this->rpContentBasketShippingHelper->setShipping($quoteOrOrder);
        }
        if (!is_null($amount)) {
            $content['Amount'] = $amount;
        }

        return $content;
    }
}
