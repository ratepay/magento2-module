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

namespace RatePAY\Payment\Observer;

use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Exception\PaymentException;

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
     * SendRatepayCancelCall constructor.
     *
     * @param \RatePAY\Payment\Helper\Data                  $rpDataHelper
     * @param \RatePAY\Payment\Helper\Payment               $rpPaymentHelper
     * @param \Ratepay\Payment\Model\LibraryModel           $rpLibraryModel
     * @param \RatePAY\Payment\Controller\LibraryController $rpLibraryController
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     */
    public function __construct(
        \RatePAY\Payment\Helper\Data $rpDataHelper,
        \RatePAY\Payment\Helper\Payment $rpPaymentHelper,
        \RatePAY\Payment\Model\LibraryModel $rpLibraryModel,
        \RatePAY\Payment\Controller\LibraryController $rpLibraryController,
        \Magento\Store\Model\StoreManagerInterface $storeManager)
    {
        $this->rpDataHelper = $rpDataHelper;
        $this->rpPaymentHelper = $rpPaymentHelper;
        $this->rpLibraryModel = $rpLibraryModel;
        $this->rpLibraryController = $rpLibraryController;
        $this->storeManager = $storeManager;
    }

    /**
     * @param \Magento\Framework\Event\Observer $observer
     *
     * @return $this
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        $order = $observer->getEvent()->getOrder();
        $paymentMethod = $order->getPayment()->getMethodInstance()->getCode();
        if (!$this->rpPaymentHelper->isRatepayPayment($paymentMethod)) {
            return $this;
        }
        $this->sendRatepayCancelCall($order, $paymentMethod);
    }

    /**
     * @param $order
     * @param $paymentMethod
     *
     * @return bool
     */
    public function sendRatepayCancelCall($order, $paymentMethod)
    {
        $sandbox = (bool) $this->rpDataHelper->getRpConfigData($paymentMethod, 'sandbox', $this->storeManager->getStore()->getId());
        $head = $this->rpLibraryModel->getRequestHead($order, 'PAYMENT_CHANGE');
        $content = $this->rpLibraryModel->getRequestContent($order, 'PAYMENT_CHANGE', [], 0);
        $cancellationRequest = $this->rpLibraryController->callPaymentChange($head, $content, 'cancellation', $sandbox);
        if (!$cancellationRequest->isSuccessful()){
            throw new PaymentException(__('Cancellation was not successsfull'));
        } else {
            return true;
        }
    }
}
