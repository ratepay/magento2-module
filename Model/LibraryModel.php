<?php
/**
 * Created by PhpStorm.
 * User: SebastianN
 * Date: 08.02.17
 * Time: 09:35
 */

namespace RatePAY\Payment\Model;

use RatePAY\ModelBuilder;
use RatePAY\Payment\Model\Source\CreditmemoDiscountType;

class LibraryModel
{
    /**
     * @var Discount
     */
    protected $rpContentBasketDiscountHelper;

    /**
     * @var \RatePAY\Payment\Helper\Data
     */
    protected $rpDataHelper;

    /**
     * @var \RatePAY\Payment\Model\ResourceModel\OrderAdjustment
     */
    protected $orderAdjustment;

    /**
     * LibraryModel constructor.
     * @param \RatePAY\Payment\Helper\Head\Head $rpHeadHelper
     * @param \RatePAY\Payment\Helper\Head\Additional $rpHeadAdditionalHelper
     * @param \RatePAY\Payment\Helper\Head\External $rpHeadExternalHelper
     * @param \RatePAY\Payment\Helper\Content\ContentBuilder $rpContentBuilder
     * @param \RatePAY\Payment\Helper\Content\ShoppingBasket\Discount $rpContentBasketDiscountHelper
     * @param \RatePAY\Payment\Helper\Data $rpDataHelper
     * @param \RatePAY\Payment\Model\ResourceModel\OrderAdjustment $orderAdjustment
     */
    public function __construct(
        \RatePAY\Payment\Helper\Head\Head $rpHeadHelper,
        \RatePAY\Payment\Helper\Head\Additional  $rpHeadAdditionalHelper,
        \RatePAY\Payment\Helper\Head\External $rpHeadExternalHelper,
        \RatePAY\Payment\Helper\Content\ContentBuilder $rpContentBuilder,
        \RatePAY\Payment\Helper\Content\ShoppingBasket\Discount $rpContentBasketDiscountHelper,
        \RatePAY\Payment\Helper\Data $rpDataHelper,
        \RatePAY\Payment\Model\ResourceModel\OrderAdjustment $orderAdjustment
    ) {
        $this->rpHeadHelper = $rpHeadHelper;
        $this->rpHeadAdditionalHelper = $rpHeadAdditionalHelper;
        $this->rpHeadExternalHelper = $rpHeadExternalHelper;
        $this->rpContentBuilder = $rpContentBuilder;
        $this->rpContentBasketDiscountHelper = $rpContentBasketDiscountHelper;
        $this->rpDataHelper = $rpDataHelper;
        $this->orderAdjustment = $orderAdjustment;
    }

    /**
     * Add adjustment items to the article list
     *
     * @param $creditmemo
     * @param $artNumRefund
     * @param $artNumFee
     * @return array
     */
    public function addAdjustments($creditmemo, $artNumRefund, $artNumFee)
    {
        $content = [];

        if ($creditmemo->getAdjustmentPositive() > 0) {
            if ($this->rpDataHelper->getRpConfigData('ratepay_general', 'creditmemo_discount_type') == CreditmemoDiscountType::SPECIAL_ITEM) {
                $content['Discount'] = $this->rpContentBasketDiscountHelper->setDiscount((float) $creditmemo->getAdjustmentPositive() * -1, 'Adjustment Refund');
            } else {
                $content['Items'][] = ['Item' => $this->addAdjustment((float) $creditmemo->getAdjustmentPositive() * -1, 'Adjustment Refund', $artNumRefund)];
            }
        }

        if ($creditmemo->getAdjustmentNegative() > 0) {
            $content['Items'][] = ['Item' => $this->addAdjustment((float) $creditmemo->getAdjustmentNegative(), 'Adjustment Fee', $artNumFee)];
        }

        return $content;
    }

    /**
     * @param  array $adjustments
     * @return int|mixed
     */
    protected function getSpecialItemAdjustmentSum($adjustments)
    {
        $sum = 0;
        foreach ($adjustments as $adjustment) {
            if ($adjustment['adjustment_type'] == 'positive' && (bool)$adjustment['is_specialitem'] === true) {
                $sum += $adjustment['amount'];
            }
        }
        return $sum;
    }

    /**
     * Add adjustment items to the article list
     *
     * @param $order
     * @return array
     */
    public function addReturnAdjustments($order)
    {
        $adjustments = $this->orderAdjustment->getOrderAdjustments($order->getId());

        $content = [];

        $positiveAdjustmentSum = $this->getSpecialItemAdjustmentSum($adjustments);
        if ($positiveAdjustmentSum > 0) {
            $content['Discount'] = $this->rpContentBasketDiscountHelper->setDiscount((float)$positiveAdjustmentSum * -1, 'Adjustment Refund');
        }

        foreach ($adjustments as $adjustment) {
            if ($adjustment['adjustment_type'] == 'positive' && (bool)$adjustment['is_specialitem'] === false) {
                $content['Items'][] = ['Item' => $this->addAdjustment((float) $adjustment['amount'] * -1, 'Adjustment Refund', $adjustment['article_number'])];
            } elseif($adjustment['adjustment_type'] == 'negative') {
                $content['Items'][] = ['Item' => $this->addAdjustment((float) $adjustment['amount'], 'Adjustment Fee', $adjustment['article_number'])];
            }
        }

        return $content;
    }

    /**
     * Add merchant credit to artcile list
     *
     * @param $amount
     * @param $description
     * @param $articleNumber
     * @return array
     */
    public function addAdjustment($amount, $description, $articleNumber)
    {
        $tempVoucherItem = [];
        $tempVoucherItem['Description'] = $description;
        $tempVoucherItem['ArticleNumber'] = $articleNumber;
        $tempVoucherItem['Quantity'] = 1;
        $tempVoucherItem['UnitPriceGross'] = $amount;
        $tempVoucherItem['TaxRate'] = 0;

        return $tempVoucherItem;
    }

    /**
     * Build requests head section
     *
     * @param $quoteOrOrder
     * @param null $resultInit
     * @return /app/code/RatePAY/Payment/Model/Library/src/ModelBuilder mixed|ModelBuilder
     */
    public function getRequestHead($quoteOrOrder, $operation = null, $resultInit = null, $fixedPaymentMethod = null, $profileId = null, $securityCode = null)
    {
        $headModel = new ModelBuilder('Head');

        $headModel = $this->rpHeadHelper->setHead($quoteOrOrder, $headModel, $fixedPaymentMethod, $profileId, $securityCode);
        switch($operation){
            case 'CALCULATION_REQUEST' :
                break;

            case 'PAYMENT_REQUEST' :
                $this->rpHeadAdditionalHelper->setHeadAdditional($resultInit, $headModel);
                /*$headModel->setTransactionId($resultInit->getTransactionId());
                $headModel->setCustomerDevice(
                    $headModel->CustomerDevice()->setDeviceToken($this->customerSession->getRatepayDeviceIdentToken())
                );*/
                $headModel = $this->rpHeadExternalHelper->setHeadExternal($quoteOrOrder, $headModel);
                break;

            case "PAYMENT_CHANGE" :
                $headModel->setTransactionId($quoteOrOrder->getPayment()->getAdditionalInformation('transactionId'));
                break;

            case "CONFIRMATION_DELIVER" :
                $headModel->setTransactionId($quoteOrOrder->getPayment()->getAdditionalInformation('transactionId'));
                break;
        }

        return $headModel;
    }

    /**
     * Build requests content section
     *
     * @param $quoteOrOrder
     * @return ModelBuilder
     */
    public function getRequestContent($quoteOrOrder, $operation, $articleList = null, $amount = null, $fixedPaymentMethod = null)
    {
        $content = new ModelBuilder('Content');

        $contentArr = $this->rpContentBuilder->setContent($quoteOrOrder, $operation, $articleList, $amount, $fixedPaymentMethod);
        try{
            $content->setArray($contentArr);
        } catch (\Exception $e){
            echo $e->getMessage();
        }

        return $content ;
    }
}
