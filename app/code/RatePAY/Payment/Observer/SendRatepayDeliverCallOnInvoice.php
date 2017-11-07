<?php
/**
 * Created by PhpStorm.
 * User: SebastianN
 * Date: 26.06.17
 * Time: 17:25
 */

namespace RatePAY\Payment\Observer;

use Magento\Framework\Event\ObserverInterface;
use RatePAY\Payment\Controller\LibraryController;

class SendRatepayDeliverCallOnInvoice implements ObserverInterface
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
     * @var LibraryController
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
     * SendRatepayDeliverCallOnInvoice constructor.
     * @param \RatePAY\Payment\Model\LibraryModel $rpLibraryModel
     * @param \RatePAY\Payment\Helper\Data $rpDataHelper
     * @param \RatePAY\Payment\Helper\Payment $rpPaymentHelper
     * @param LibraryController $rpLibraryController
     * @param \Magento\Framework\Exception\PaymentException $paymentException
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     */
    public function __construct(
        \RatePAY\Payment\Model\LibraryModel $rpLibraryModel,
        \RatePAY\Payment\Helper\Data $rpDataHelper,
        \RatePAY\Payment\Helper\Payment $rpPaymentHelper,
        \RatePAY\Payment\Controller\LibraryController $rpLibraryController,
        \Magento\Framework\Exception\PaymentException $paymentException,
        \Magento\Store\Model\StoreManagerInterface $storeManager)
    {
        $this->rpLibraryModel = $rpLibraryModel;
        $this->rpDataHelper = $rpDataHelper;
        $this->rpPaymentHelper = $rpPaymentHelper;
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
        $inv = $observer->getEvent()->getData('invoice');
        $order = $observer->getEvent()->getData('invoice')->getOrder();
        $paymentMethod = $observer->getEvent()->getData('invoice')->getOrder()->getPayment()->getMethodInstance()->getCode();
        if(!$this->rpPaymentHelper->isRatepayPayment($paymentMethod)){
            return $this;
        }
        $this->sendRatepayDeliverCall($order, $inv, $paymentMethod);
    }

    /**
     * @param $order
     * @param $inv
     * @param $paymentMethod
     * @return bool
     */
    private function sendRatepayDeliverCall($order, $inv, $paymentMethod)
    {
        $sandbox = (bool)$this->rpDataHelper->getRpConfigData($paymentMethod, 'sandbox', $this->storeManager->getStore()->getId());
        $head = $this->rpLibraryModel->getRequestHead($order, 'CONFIRMATION_DELIVER');
        $content = $this->rpLibraryModel->getRequestContent($inv, 'CONFIRMATION_DELIVER');
        $resultConfirmationDeliver = $this->rpLibraryController->callConfirmationDeliver($head, $content, $sandbox);

        if(!$resultConfirmationDeliver->isSuccessful())
        {
            throw new $this->paymentException(__('Invoice not successful'));
        } else {
            return true;
        }
    }
}
