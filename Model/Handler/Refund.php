<?php

namespace RatePAY\Payment\Model\Handler;

use RatePAY\Payment\Model\Source\CreditmemoDiscountType;
use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Exception\PaymentException;

class Refund
{
    /**
     * @var \RatePAY\Payment\Helper\Data
     */
    protected $rpDataHelper;

    /**
     * @var \RatePAY\Payment\Model\LibraryModel
     */
    protected $rpLibraryModel;

    /**
     * @var \RatePAY\Payment\Controller\LibraryController
     */
    protected $rpLibraryController;

    /**
     * @var \RatePAY\Payment\Model\ResourceModel\OrderAdjustment
     */
    protected $orderAdjustment;

    /**
     * @var string
     */
    protected $artNumRefund;

    /**
     * @var string
     */
    protected $artNumFee;

    /**
     * @var \RatePAY\Payment\Helper\ProfileConfig
     */
    protected $profileConfigHelper;

    /**
     * SendRatepayCreditMemoCall constructor.
     * @param \RatePAY\Payment\Helper\Data $rpDataHelper
     * @param \RatePAY\Payment\Model\LibraryModel $rpLibraryModel
     * @param \RatePAY\Payment\Controller\LibraryController $rpLibraryController
     * @param \RatePAY\Payment\Model\ResourceModel\OrderAdjustment $orderAdjustment
     * @param \RatePAY\Payment\Helper\ProfileConfig $profileConfigHelper
     */
    function __construct(
        \RatePAY\Payment\Helper\Data $rpDataHelper,
        \RatePAY\Payment\Model\LibraryModel $rpLibraryModel,
        \RatePAY\Payment\Controller\LibraryController $rpLibraryController,
        \RatePAY\Payment\Model\ResourceModel\OrderAdjustment $orderAdjustment,
        \RatePAY\Payment\Helper\ProfileConfig $profileConfigHelper
    )
    {
        $this->rpDataHelper = $rpDataHelper;
        $this->rpLibraryModel = $rpLibraryModel;
        $this->rpLibraryController = $rpLibraryController;
        $this->orderAdjustment = $orderAdjustment;
        $this->profileConfigHelper = $profileConfigHelper;
    }

    /**
     * @param  \Magento\Payment\Model\InfoInterface $payment
     * @param  float                                $amount
     * @return bool
     */
    public function executeRatepayRefund(\Magento\Payment\Model\InfoInterface $payment, $amount)
    {
        $creditMemo = $payment->getCreditmemo();
        $order = $payment->getOrder();
        $paymentMethod = $payment->getMethod();
        if($creditMemo->getDoTransaction() === false && (bool)$this->rpDataHelper->getRpConfigDataByPath("ratepay/general/true_offline_mode", $order->getStore()->getCode()) === true) {
            return;
        }

        if ($this->isReturnPreviousAdjustmentsCheckboxChecked() === false) {
            $this->addAdjustmentsToDb($order, $creditMemo);
        }

        return $this->callRatepayReturn($order, $creditMemo, $payment->getMethodInstance());
    }

    /**
     * Adds order adjustments to database
     *
     * @param $order
     * @param $creditMemo
     */
    protected function addAdjustmentsToDb($order, $creditMemo)
    {
        if ($creditMemo->getAdjustmentPositive() > 0) {
            $this->artNumRefund = 'adj-ref'.$this->orderAdjustment->getNextArticleNumberCounter($order->getId(), 'positive');
            $is_specialitem = false;
            if ($this->rpDataHelper->getRpConfigData('ratepay_general', 'creditmemo_discount_type') == CreditmemoDiscountType::SPECIAL_ITEM) {
                $is_specialitem = true;
            }
            $this->orderAdjustment->addOrderAdjustment($order->getId(), 'positive', $this->artNumRefund, $creditMemo->getAdjustmentPositive(), $creditMemo->getBaseAdjustmentPositive(), $is_specialitem);
        }
        if ($creditMemo->getAdjustmentNegative() > 0) {
            $this->artNumFee = 'adj-fee'.$this->orderAdjustment->getNextArticleNumberCounter($order->getId(), 'negative');
            $this->orderAdjustment->addOrderAdjustment($order->getId(), 'negative', $this->artNumFee, $creditMemo->getAdjustmentNegative(), $creditMemo->getBaseAdjustmentNegative());
        }
    }

    /**
     * @return bool
     */
    protected function isReturnPreviousAdjustmentsCheckboxChecked()
    {
        $paramCreditmemo = $this->rpDataHelper->getRequestParameter('creditmemo');
        if (isset($paramCreditmemo['ratepay_return_adjustments']) && (bool)$paramCreditmemo['ratepay_return_adjustments'] === true) {
            return true;
        }
        return false;
    }

    /**
     * Collect the quantity sum of all items
     *
     * @param $oCreditmemo
     * @return int
     */
    protected function getCreditMemoQuantity($oCreditmemo)
    {
        $iQuantity = 0;
        $aItems = $oCreditmemo->getItems();
        if (is_array($aItems) && !empty($aItems)) {
            foreach ($aItems as $oItem) {
                $iQuantity += $oItem->getQty();
            }
        }
        return $iQuantity;
    }

    /**
     * Check if creditmemo consists of partial bundle refunds
     *
     * @param $creditMemo
     * @return bool
     */
    protected function hasPartialRefundBundle($creditMemo)
    {
        $bundleRefundArray = array();
        foreach ($creditMemo->getItems() as $creditMemoItem) {
            $orderItem = $creditMemoItem->getOrderItem();
            $parentItem = $orderItem->getParentItem();
            if ($parentItem !== null && $parentItem->getProductType() == \Magento\Catalog\Model\Product\Type::TYPE_BUNDLE) {
                if (isset($bundleRefundArray[$parentItem->getId()]) && $bundleRefundArray[$parentItem->getId()] != (float)$creditMemoItem->getQty()) {
                    return true; // all items must have the same amount!
                }
                $bundleRefundArray[$parentItem->getId()] = (float)$creditMemoItem->getQty(); // add amount to array
            }
        }
        return false;
    }

    /**
     * @param $order
     * @param $creditMemo
     * @param $paymentMethod
     * @return bool
     */
    public function callRatepayReturn($order, $creditMemo, $methodInstance)
    {
        if (!$order->getRatepayProfileId()) {
            throw new PaymentException(__('Processing failed'));
        }

        if ($this->hasPartialRefundBundle($creditMemo) === true) { // module doesnt support partial bundle refunds at the moment, bundle is transmitted as 1 product to API
            throw new PaymentException(__('Bundles can only be refunded completely'));
        }

        $blReturnProducts = true;
        if ($this->isReturnPreviousAdjustmentsCheckboxChecked() === false && ($creditMemo->getAdjustmentPositive() > 0 || $creditMemo->getAdjustmentNegative() > 0)) {
            $this->callRatepayCredit($order, $creditMemo, $methodInstance);
            if ($this->getCreditMemoQuantity($creditMemo) <= 0) {
                $blReturnProducts = false;
            }
        }

        if ($blReturnProducts === true) {
            $sProfileId = null;
            $sSecurityCode = null;
            $blSandbox = false;
            if ($order->getRatepayProfileId()) {
                $sProfileId = $order->getRatepayProfileId();
                $sSecurityCode = $this->profileConfigHelper->getSecurityCodeForProfileId($sProfileId, $methodInstance->getCode());
                $blSandbox = $this->profileConfigHelper->getSandboxModeForProfileId($sProfileId, $methodInstance->getCode());
            }

            $head = $this->rpLibraryModel->getRequestHead($order, 'PAYMENT_CHANGE', null, null, $sProfileId, $sSecurityCode);

            $adjustments = null;
            if ($this->isReturnPreviousAdjustmentsCheckboxChecked() === true) {
                $adjustments = $this->rpLibraryModel->addReturnAdjustments($order);
            }
            $content = $this->rpLibraryModel->getRequestContent($creditMemo, "PAYMENT_CHANGE", null, null, null, null, $adjustments);

            $returnRequest = $this->rpLibraryController->callPaymentChange($head, $content, 'return', $order, $blSandbox);
            if (!$returnRequest->isSuccessful()) {
                throw new PaymentException(__('Refund was not successfull'));
            }

            if ($this->isReturnPreviousAdjustmentsCheckboxChecked() === true) {
                $this->orderAdjustment->setAdjustmentsToReturned($order->getId());
            }
        }
        return true;
    }

    /**
     * @param $order
     * @param $creditMemo
     * @param $methodInstance
     * @return bool
     */
    public function callRatepayCredit($order, $creditMemo, $methodInstance)
    {
        $sProfileId = null;
        $sSecurityCode = null;
        $blSandbox = false;
        if ($order->getRatepayProfileId()) {
            $sProfileId = $order->getRatepayProfileId();
            $sSecurityCode = $this->profileConfigHelper->getSecurityCodeForProfileId($sProfileId, $methodInstance->getCode());
            $blSandbox = $this->profileConfigHelper->getSandboxModeForProfileId($sProfileId, $methodInstance->getCode());
        }

        $head = $this->rpLibraryModel->getRequestHead($order, 'PAYMENT_CHANGE', null, null, $sProfileId, $sSecurityCode);
        $content = $this->rpLibraryModel->getRequestContent($order, 'PAYMENT_CHANGE', null, null, null, $this->rpLibraryModel->addAdjustments($creditMemo, $this->artNumRefund, $this->artNumFee));

        $creditRequest = $this->rpLibraryController->callPaymentChange($head, $content, 'credit', $order, $blSandbox);
        if (!$creditRequest->isSuccessful()) {
            throw new PaymentException(__('Credit was not successfull'));
        }
        return true;
    }
}
