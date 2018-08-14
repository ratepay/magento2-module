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
     *
     * @param Context  $context
     * @param Items    $rpContentBasketItemsHelper
     * @param Shipping $rpContentBasketShippingHelper
     * @param Discount $rpContentBasketDiscountHelper
     */
    public function __construct(
        Context $context,
                                Items $rpContentBasketItemsHelper,
                                Shipping $rpContentBasketShippingHelper,
                                Discount $rpContentBasketDiscountHelper
    ) {
        parent::__construct($context);

        $this->rpContentBasketItemsHelper = $rpContentBasketItemsHelper;
        $this->rpContentBasketShippingHelper = $rpContentBasketShippingHelper;
        $this->rpContentBasketDiscountHelper = $rpContentBasketDiscountHelper;
    }

    /**
     * Build Shopping Basket Block of Payment Request.
     *
     * @param $quoteOrOrder
     * @param null $articleList
     * @param null $amount
     *
     * @return array
     */
    public function setShoppingBasket($quoteOrOrder, $articleList = null, $amount = null)
    {
        $content = [];

        if (is_null($articleList)) {
            if ($quoteOrOrder->getAdjustmentPositive() > 0 || $quoteOrOrder->getAdjustmentNegative() > 0) {
                $content = [
                    'Items' => $this->rpContentBasketItemsHelper->setItems($quoteOrOrder),
                ];
            } else {
                $content = [
                    'Amount' => round($quoteOrOrder->getBaseGrandTotal(), 2),
                    'Items' => $this->rpContentBasketItemsHelper->setItems($quoteOrOrder),
                ];
            }
            if ($quoteOrOrder->getShippingAmount() > 0) {
                $content['Shipping'] = $this->rpContentBasketShippingHelper->setShipping($quoteOrOrder);
            }
            if ($quoteOrOrder->getGiftCardsAmount() > 0) {
                $content['Discount'] = $this->rpContentBasketDiscountHelper->setDiscount($quoteOrOrder);
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
