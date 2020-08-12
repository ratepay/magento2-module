<?php
/**
 * Created by PhpStorm.
 * User: SebastianN
 * Date: 10.02.17
 * Time: 09:00
 */

namespace RatePAY\Payment\Helper\Content;


use Magento\Framework\App\Helper\Context;
use Magento\Sales\Model\Order\Invoice;

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
     * @param $operation
     * @param null $articleList
     * @param null $amount
     * @param null $fixedPaymentMethod
     * @param array $content
     * @return array
     */
    public function setContent($quoteOrOrder, $operation, $articleList = null, $amount = null, $fixedPaymentMethod = null, $content = null)
    {
        $contentArr = [];

        switch($operation) {
            case 'CALCULATION_REQUEST' :
                /*$contentArr = [
                    'InstallmentCalculation' => $this->getRequest($quoteOrOrder),
                ];*/
                break;
            case 'PAYMENT_REQUEST' :
                $contentArr = [
                    'Customer' => $this->rpContentCustomerHelper->setCustomer($quoteOrOrder),
                    'ShoppingBasket' => $this->rpContentBasketHelper->setShoppingBasket($quoteOrOrder),
                    'Payment' => $this->rpContentPaymentHelper->setPayment($quoteOrOrder, $fixedPaymentMethod)
                ];
                break;
            case "PAYMENT_CHANGE" :
                if ($content === null) {
                    $content = $this->rpContentBasketHelper->setShoppingBasket($quoteOrOrder, $articleList, $amount);
                }
                $contentArr = [
                    'ShoppingBasket' => $content
                ];
                break;
            case "CONFIRMATION_DELIVER" :
                $contentArr = [
                    'ShoppingBasket' => $this->rpContentBasketHelper->setShoppingBasket($quoteOrOrder)
                ];
                if ($quoteOrOrder instanceof Invoice && !empty($quoteOrOrder->getIncrementId())) {
                    $contentArr['Invoicing'] = ['InvoiceId' => $quoteOrOrder->getIncrementId()];
                }
                break;
        }

        return $contentArr;
    }
}
