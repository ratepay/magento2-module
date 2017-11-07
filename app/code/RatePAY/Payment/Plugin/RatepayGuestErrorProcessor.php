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
    protected $session;

    /**
     * @var \Magento\Quote\Api\CartManagementInterface
     */
    protected $cartManagement;

    /**
     * @param \Magento\Checkout\Model\Session $session
     * @param \Magento\Quote\Api\GuestBillingAddressManagementInterface $billingAddressManagement
     * @param \Magento\Quote\Api\GuestPaymentMethodManagementInterface $paymentMethodManagement
     * @param \Magento\Quote\Api\GuestCartManagementInterface $cartManagement
     * @codeCoverageIgnore
     */
    public function __construct(
        \Magento\Checkout\Model\Session $session,
        \Magento\Quote\Api\GuestBillingAddressManagementInterface $billingAddressManagement,
        \Magento\Quote\Api\GuestPaymentMethodManagementInterface $paymentMethodManagement,
        \Magento\Quote\Api\GuestCartManagementInterface $cartManagement
    ) {
        $this->billingAddressManagement = $billingAddressManagement;
        $this->paymentMethodManagement = $paymentMethodManagement;
        $this->session = $session;
        $this->cartManagement = $cartManagement;
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
        $subject->savePaymentInformation($cartId, $email, $paymentMethod, $billingAddress);
        try {
            $orderId = $this->cartManagement->placeOrder($cartId);
        } catch (\Exception $e) {
            throw new PaymentException(
                __($e->getMessage()),
                $e
            );
        }

        return $orderId;
    }
}
