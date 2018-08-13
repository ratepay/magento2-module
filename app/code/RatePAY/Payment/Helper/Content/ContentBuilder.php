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

namespace RatePAY\Payment\Helper\Content;

use Magento\Framework\App\Helper\Context;

class ContentBuilder extends \Magento\Framework\App\Helper\AbstractHelper
{
    /**
     * @var Customer\Customer
     */
    protected $rpContentCustomerHelper;

    /**
     * @var ShoppingBasket\ShoppingBasket
     */
    protected $rpContentBasketHelper;

    /**
     * @var Payment\Payment
     */
    protected $rpContentPaymentHelper;

    /**
     * ContentBuilder constructor.
     *
     * @param Context                       $context
     * @param Customer\Customer             $rpContentCustomerHelper
     * @param ShoppingBasket\ShoppingBasket $rpContentBasketHelper
     * @param Payment\Payment               $rpContentPaymentHelper
     */
    public function __construct(
        Context $context,
                                Customer\Customer $rpContentCustomerHelper,
                                ShoppingBasket\ShoppingBasket $rpContentBasketHelper,
                                Payment\Payment $rpContentPaymentHelper
    ) {
        parent::__construct($context);

        $this->rpContentCustomerHelper = $rpContentCustomerHelper;
        $this->rpContentBasketHelper = $rpContentBasketHelper;
        $this->rpContentPaymentHelper = $rpContentPaymentHelper;
    }

    /**
     * Collect all Data for Content Block of Payment Request and assembles them to one array.
     *
     * @param $quoteOrOrder
     * @param $operation
     * @param null $articleList
     * @param null $amount
     * @param null $fixedPaymentMethod
     *
     * @return array
     */
    public function setContent($quoteOrOrder, $operation, $articleList = null, $amount = null, $fixedPaymentMethod = null)
    {
        $contentArr = [];

        switch ($operation) {
            case 'CALCULATION_REQUEST':
                /*$contentArr = [
                    'InstallmentCalculation' => $this->getRequest($quoteOrOrder),
                ];*/
                break;
            case 'PAYMENT_REQUEST':
                $contentArr = [
                    'Customer' => $this->rpContentCustomerHelper->setCustomer($quoteOrOrder),
                    'ShoppingBasket' => $this->rpContentBasketHelper->setShoppingBasket($quoteOrOrder),
                    'Payment' => $this->rpContentPaymentHelper->setPayment($quoteOrOrder, $fixedPaymentMethod),
                ];

                break;
            case 'PAYMENT_CHANGE':
                $contentArr = [
                    'ShoppingBasket' => $this->rpContentBasketHelper->setShoppingBasket($quoteOrOrder, $articleList, $amount),
                ];

                break;
            case 'CONFIRMATION_DELIVER':
                $contentArr = [
                    'ShoppingBasket' => $this->rpContentBasketHelper->setShoppingBasket($quoteOrOrder),
                ];

                break;
        }

        return $contentArr;
    }
}
