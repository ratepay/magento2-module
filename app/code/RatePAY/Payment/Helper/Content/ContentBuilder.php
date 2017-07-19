<?php
/**
 * Created by PhpStorm.
 * User: SebastianN
 * Date: 10.02.17
 * Time: 09:00
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
     * @param Context $context
     * @param Customer\Customer $rpContentCustomerHelper
     * @param ShoppingBasket\ShoppingBasket $rpContentBasketHelper
     * @param Payment\Payment $rpContentPaymentHelper
     */
    public function __construct(Context $context,
                                \RatePAY\Payment\Helper\Content\Customer\Customer $rpContentCustomerHelper,
                                \RatePAY\Payment\Helper\Content\ShoppingBasket\ShoppingBasket $rpContentBasketHelper,
                                \RatePAY\Payment\Helper\Content\Payment\Payment $rpContentPaymentHelper)
    {
        parent::__construct($context);

        $this->rpContentCustomerHelper = $rpContentCustomerHelper;
        $this->rpContentBasketHelper = $rpContentBasketHelper;
        $this->rpContentPaymentHelper = $rpContentPaymentHelper;
    }

    /**
     * Collect all Data for Content Block of Payment Request and assembles them to one array
     *
     * @param $quoteOrOrder
     * @return array
     */
    public function setContent($quoteOrOrder, $fixedPaymentMethod = null)
    {
        $contentArr = [
          'Customer' => $this->rpContentCustomerHelper->setCustomer($quoteOrOrder),
          'ShoppingBasket' => $this->rpContentBasketHelper->setShoppingBasket($quoteOrOrder),
          'Payment' => $this->rpContentPaymentHelper->setPayment($quoteOrOrder, $fixedPaymentMethod)
        ];

        return $contentArr;
    }
}
