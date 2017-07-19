<?php
/**
 * Created by PhpStorm.
 * User: SebastianN
 * Date: 14.06.17
 * Time: 15:04
 */

namespace RatePAY\Payment\Plugin;

use Magento\Framework\Exception\PaymentException;
class RatepayErrorProcessor
{
    /**
     * @var \Magento\Checkout\Model\Session
     */
    protected $session;

    protected $cartManagement;

    public function __construct(
        \Magento\Checkout\Model\Session $session,
        \Magento\Quote\Api\CartManagementInterface $cartManagement
    )
    {
        $this->session = $session;
        $this->cartManagement = $cartManagement;
    }

    public function aroundSavePaymentInformationAndPlaceOrder(
        \Magento\Checkout\Model\PaymentInformationManagement $subject,
        \Closure $proceed,
        $cartId,
        \Magento\Quote\Api\Data\PaymentInterface $paymentMethod,
        \Magento\Quote\Api\Data\AddressInterface $billingAddress
    )
    {
        $subject->savePaymentInformation($cartId, $paymentMethod, $billingAddress);
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