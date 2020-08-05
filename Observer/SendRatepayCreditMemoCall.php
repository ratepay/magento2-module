<?php
/**
 * Created by PhpStorm.
 * User: SebastianN
 * Date: 17.07.17
 * Time: 15:21
 */

namespace RatePAY\Payment\Observer;


use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Exception\PaymentException;

class SendRatepayCreditMemoCall implements ObserverInterface
{
    /**
     * @var \RatePAY\Payment\Helper\Data
     */
    protected $rpDataHelper;

    /**
     * @var \RatePAY\Payment\Helper\Payment
     */
    protected $rpPaymentHelper;

    /**
     * @var \RatePAY\Payment\Model\LibraryModel
     */
    protected $rpLibraryModel;

    /**
     * @var \RatePAY\Payment\Controller\LibraryController
     */
    protected $rpLibraryController;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $storeManager;

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
     * SendRatepayCreditMemoCall constructor.
     * @param \RatePAY\Payment\Helper\Data $rpDataHelper
     * @param \RatePAY\Payment\Helper\Payment $rpPaymentHelper
     * @param \RatePAY\Payment\Model\LibraryModel $rpLibraryModel
     * @param \RatePAY\Payment\Controller\LibraryController $rpLibraryController
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \RatePAY\Payment\Model\ResourceModel\OrderAdjustment $orderAdjustment
     */
    function __construct(
        \RatePAY\Payment\Helper\Data $rpDataHelper,
        \RatePAY\Payment\Helper\Payment $rpPaymentHelper,
        \RatePAY\Payment\Model\LibraryModel $rpLibraryModel,
        \RatePAY\Payment\Controller\LibraryController $rpLibraryController,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \RatePAY\Payment\Model\ResourceModel\OrderAdjustment $orderAdjustment
    )
    {
        $this->rpDataHelper = $rpDataHelper;
        $this->rpPaymentHelper = $rpPaymentHelper;
        $this->rpLibraryModel = $rpLibraryModel;
        $this->rpLibraryController = $rpLibraryController;
        $this->storeManager = $storeManager;
        $this->orderAdjustment = $orderAdjustment;
    }

    /**
     * @param \Magento\Framework\Event\Observer $observer
     * @return $this
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
       $creditMemo = $observer->getEvent()->getCreditmemo();
       $order = $creditMemo->getOrder();
       $paymentMethod = $order->getPayment()->getMethod();
        if(!$this->rpPaymentHelper->isRatepayPayment($paymentMethod)){
            return $this;
        }

        if ($this->isReturnPreviousAdjustmentsCheckboxChecked() === false) {
            $this->addAdjustmentsToDb($order, $creditMemo);
        }

        $this->callRatepayReturn($order, $creditMemo, $paymentMethod);
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
            $this->orderAdjustment->addOrderAdjustment($order->getId(), 'positive', $this->artNumRefund, $creditMemo->getAdjustmentPositive(), $creditMemo->getBaseAdjustmentPositive());
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
    public function callRatepayReturn($order, $creditMemo, $paymentMethod)
    {
        $sandbox = (bool)$this->rpDataHelper->getRpConfigData($paymentMethod, 'sandbox', $this->storeManager->getStore()->getId());
        $head = $this->rpLibraryModel->getRequestHead($order, 'PAYMENT_CHANGE');
        $content = $this->rpLibraryModel->getRequestContent($creditMemo, "PAYMENT_CHANGE");

        if ($this->rpDataHelper->getRpConfigData($paymentMethod, 'status', $this->storeManager->getStore()->getId()) == 1) {
            throw new PaymentException(__('Processing failed'));
        }

        if ($this->hasPartialRefundBundle($creditMemo) === true) { // module doesnt support partial bundle refunds at the moment, bundle is transmitted as 1 product to API
            throw new PaymentException(__('Bundles can only be refunded completely'));
        }

        if ($creditMemo->getAdjustmentPositive() > 0 || $creditMemo->getAdjustmentNegative() > 0) {
            $this->callRatepayCredit($order, $creditMemo, $paymentMethod);
            $iQuantity = $this->getCreditMemoQuantity($creditMemo);
            if ($iQuantity > 0) {
                $returnRequest = $this->rpLibraryController->callPaymentChange($head, $content, 'return', $order, $sandbox);
                if (!$returnRequest->isSuccessful()) {
                    throw new PaymentException(__('Refund was not successfull'));
                }
            }
            return true;
        } else {
            $returnRequest = $this->rpLibraryController->callPaymentChange($head, $content, 'return', $order, $sandbox);
            if (!$returnRequest->isSuccessful()) {
                throw new PaymentException(__('Refund was not successfull'));
            } else {
                return true;
            }
        }
    }

    /**
     * @param $order
     * @param $creditMemo
     * @param $paymentMethod
     * @return bool
     */
    public function callRatepayCredit($order, $creditMemo, $paymentMethod)
    {
        $sandbox = (bool)$this->rpDataHelper->getRpConfigData($paymentMethod, 'sandbox', $this->storeManager->getStore()->getId());
        $head = $this->rpLibraryModel->getRequestHead($order, 'PAYMENT_CHANGE');

        if ($this->isReturnPreviousAdjustmentsCheckboxChecked() === true) {
            $operation = 'return';
            $content = $this->rpLibraryModel->getRequestContent($order, 'PAYMENT_CHANGE', null, null, null, $this->rpLibraryModel->addReturnAdjustments($order));
        } else {
            $operation = 'credit';
            $content = $this->rpLibraryModel->getRequestContent($order, 'PAYMENT_CHANGE', null, null, null, $this->rpLibraryModel->addAdjustments($creditMemo, $this->artNumRefund, $this->artNumFee));
        }

        $creditRequest = $this->rpLibraryController->callPaymentChange($head, $content, $operation, $order, $sandbox);

        if (!$creditRequest->isSuccessful()) {
            throw new PaymentException(__('Credit was not successfull'));
        } else {
            if ($this->isReturnPreviousAdjustmentsCheckboxChecked()) {
                $this->orderAdjustment->setAdjustmentsToReturned($order->getId());
            }
            return true;
        }
    }
}
