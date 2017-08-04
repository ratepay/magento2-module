<?php
/**
 * Created by PhpStorm.
 * User: SebastianN
 * Date: 17.07.17
 * Time: 15:21
 */

namespace RatePAY\Payment\Observer;


use Magento\Framework\Event\ObserverInterface;

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
     * @var \Ratepay\Payment\Model\LibraryModel
     */
    protected $rpLibraryModel;

    /**
     * @var \RatePAY\Payment\Controller\LibraryController
     */
    protected $rpLibraryController;

    /**
     * @var \Magento\Framework\Exception\PaymentException
     */
    protected $paymentException;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $storeManager;

    /**
     * SendRatepayCreditMemoCall constructor.
     * @param \RatePAY\Payment\Helper\Data $rpDataHelper
     * @param \RatePAY\Payment\Helper\Payment $rpPaymentHelper
     * @param \Ratepay\Payment\Model\LibraryModel $rpLibraryModel
     * @param \RatePAY\Payment\Controller\LibraryController $rpLibraryController
     * @param \Magento\Framework\Exception\PaymentException $paymentException
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     */
    function __construct(
        \RatePAY\Payment\Helper\Data $rpDataHelper,
        \RatePAY\Payment\Helper\Payment $rpPaymentHelper,
        \Ratepay\Payment\Model\LibraryModel $rpLibraryModel,
        \RatePAY\Payment\Controller\LibraryController $rpLibraryController,
        \Magento\Framework\Exception\PaymentException $paymentException,
        \Magento\Store\Model\StoreManagerInterface $storeManager
    )
    {
        $this->rpDataHelper = $rpDataHelper;
        $this->rpPaymentHelper = $rpPaymentHelper;
        $this->rpLibraryModel = $rpLibraryModel;
        $this->rpLibraryController = $rpLibraryController;
        $this->paymentException = $paymentException;
        $this->storeManager = $storeManager;
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
        $this->callRatepayReturn($order, $creditMemo, $paymentMethod);
    }

    /**
     * @param $order
     * @param $creditMemo
     * @param $paymentMethod
     * @return bool
     */
    public function callRatepayReturn($order, $creditMemo, $paymentMethod)
    {
        $sandbox = (bool)$this->rpDataHelper->getRpConfigData($order, $paymentMethod, 'sandbox', $this->storeManager->getStore()->getId());
        $head = $this->rpLibraryModel->getRequestHead($order, 'PAYMENT_CHANGE');
        $content = $this->rpLibraryModel->getRequestContent($creditMemo, "PAYMENT_CHANGE");

        if ($this->rpDataHelper->getRpConfigData($order, $paymentMethod, 'status', $this->storeManager->getStore()->getId()) == 1) {
            throw new $this->paymentException(__('Processing failed'));
        }

        if ($creditMemo->getAdjustmentPositive() > 0 || $creditMemo->getAdjustmentNegative() > 0) {
            $this->callRatepayCredit($order, $creditMemo, $paymentMethod);
            $returnRequest = $this->rpLibraryController->callPaymentChange($head, $content, 'return', $sandbox);
            if (!$returnRequest->isSuccessful()) {
                throw new $this->paymentException(__('Refund was not successfull'));
            } else {
                return true;
            }
        } else {
            $returnRequest = $this->rpLibraryController->callPaymentChange($head, $content, 'return', $sandbox);
            if (!$returnRequest->isSuccessful()) {
                throw new $this->paymentException(__('Refund was not successfull'));
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
        $sandbox = (bool)$this->rpDataHelper->getRpConfigData($order, $paymentMethod, 'sandbox', $this->storeManager->getStore()->getId());
        $head = $this->rpLibraryModel->getRequestHead($order, 'PAYMENT_CHANGE');
        $content = $this->rpLibraryModel->getRequestContent($order, 'PAYMENT_CHANGE', $this->rpLibraryModel->addAdjustments($creditMemo));

        $creditRequest = $this->rpLibraryController->callPaymentChange($head, $content, 'credit', $sandbox);

        if (!$creditRequest->isSuccessful()) {
            throw new $this->paymentException(__('Credit was not successfull'));
        } else {
            return true;
        }
    }
}
