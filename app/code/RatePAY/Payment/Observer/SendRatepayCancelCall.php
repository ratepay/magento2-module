<?php
/**
 * Created by PhpStorm.
 * User: SebastianN
 * Date: 13.07.17
 * Time: 10:03
 */

namespace RatePAY\Payment\Observer;

use \Psr\Log\LoggerInterface;
use Magento\Framework\Event\ObserverInterface;

class SendRatepayCancelCall implements ObserverInterface
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
     * SendRatepayCancelCall constructor.
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
        \Magento\Store\Model\StoreManagerInterface $storeManager)
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
        $order = $observer->getEvent()->getOrder();
        $paymentMethod = $order->getPayment()->getMethodInstance()->getCode();
        if(!$this->rpPaymentHelper->isRatepayPayment($paymentMethod)){
            return $this;
        }
        $this->sendRatepayCancelCall($order, $paymentMethod);
    }

    /**
     * @param $order
     * @param $paymentMethod
     * @return bool
     */
    public function sendRatepayCancelCall($order, $paymentMethod)
    {
        $sandbox = (bool)$this->rpDataHelper->getRpConfigData($paymentMethod, 'sandbox', $this->storeManager->getStore()->getId());
        $head = $this->rpLibraryModel->getRequestHead($order, 'PAYMENT_CHANGE');
        $content = $this->rpLibraryModel->getRequestContent($order, 'PAYMENT_CHANGE', [], 0);
        $cancellationRequest = $this->rpLibraryController->callPaymentChange($head, $content, 'cancellation', $sandbox);
        if (!$cancellationRequest->isSuccessful()){
            throw new $this->paymentException(__('Cancellation was not successsfull'));
        } else {
        return true;
        }
    }
}
