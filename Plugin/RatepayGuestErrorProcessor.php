<?php
/**
 * Created by PhpStorm.
 * User: SebastianN
 * Date: 14.06.17
 * Time: 15:04
 */

namespace RatePAY\Payment\Plugin;

use Magento\Framework\Exception\PaymentException;

class RatepayGuestErrorProcessor
{
    /**
     * @var \Magento\Quote\Api\GuestBillingAddressManagementInterface
     */
    protected $billingAddressManagement;

    /**
     * @var \Magento\Checkout\Api\PaymentInformationManagementInterface
     */
    protected $paymentInformationManagement;

    /**
     * @var \Magento\Checkout\Model\Session
     */
    protected $checkoutSession;

    /**
     * @var \Magento\Quote\Api\CartManagementInterface
     */
    protected $cartManagement;

    /**
     * @var \Magento\Framework\App\ProductMetadata
     */
    protected $productMetadata;

    /**
     * @var \RatePAY\Payment\Model\ResourceModel\ApiLog
     */
    protected $apiLog;

    /**
     * @param \Magento\Checkout\Model\Session $checkoutSession
     * @param \Magento\Quote\Api\GuestBillingAddressManagementInterface $billingAddressManagement
     * @param \Magento\Quote\Api\GuestPaymentMethodManagementInterface $paymentMethodManagement
     * @param \Magento\Quote\Api\GuestCartManagementInterface $cartManagement
     * @param \Magento\Framework\App\ProductMetadata $productMetadata
     * @param \RatePAY\Payment\Model\ResourceModel\ApiLog $apiLog
     * @codeCoverageIgnore
     */
    public function __construct(
        \Magento\Checkout\Model\Session $checkoutSession,
        \Magento\Quote\Api\GuestBillingAddressManagementInterface $billingAddressManagement,
        \Magento\Quote\Api\GuestPaymentMethodManagementInterface $paymentMethodManagement,
        \Magento\Quote\Api\GuestCartManagementInterface $cartManagement,
        \Magento\Framework\App\ProductMetadata $productMetadata,
        \RatePAY\Payment\Model\ResourceModel\ApiLog $apiLog
    ) {
        $this->billingAddressManagement = $billingAddressManagement;
        $this->paymentMethodManagement = $paymentMethodManagement;
        $this->checkoutSession = $checkoutSession;
        $this->cartManagement = $cartManagement;
        $this->productMetadata = $productMetadata;
        $this->apiLog = $apiLog;
    }

    /**
     * @param \Magento\Checkout\Model\GuestPaymentInformationManagement $subject
     * @param \Closure $proceed
     * @param $cartId
     * @param $email
     * @param \Magento\Quote\Api\Data\PaymentInterface $paymentMethod
     * @param \Magento\Quote\Api\Data\AddressInterface $billingAddress
     * @return int
     * @throws PaymentException
     */
    public function aroundSavePaymentInformationAndPlaceOrder(
        \Magento\Checkout\Model\GuestPaymentInformationManagement $subject,
        \Closure $proceed,
        $cartId,
        $email,
        \Magento\Quote\Api\Data\PaymentInterface $paymentMethod,
        \Magento\Quote\Api\Data\AddressInterface $billingAddress
    )
    {
        if (version_compare($this->productMetadata->getVersion(), '2.1.0', '>=') &&
            version_compare($this->productMetadata->getVersion(), '2.2.0', '<') &&
            strpos($paymentMethod->getMethod(), 'ratepay_') !== false
        ) { // Problem only exists in Magento 2.1.X
            $subject->savePaymentInformation($cartId, $email, $paymentMethod, $billingAddress);
            try {
                $orderId = $this->cartManagement->placeOrder($cartId);
            } catch (\Exception $e) {
                throw new PaymentException(__($e->getMessage()), $e);
            }

            return $orderId;
        }
        // run core method
        try {
            $return = $proceed($cartId, $email, $paymentMethod, $billingAddress);
        } catch (\Exception $e) {
            $request = $this->checkoutSession->getRatepayRequest();
            if (!empty($request)) {
                // Rewrite the log-entry after it was rolled back in the db-transaction
                $this->apiLog->addApiLogEntry($request);
            }
            $this->checkoutSession->unsRatepayRequest();
            throw $e;
        }
        return $return;
    }
}
