<?php
/**
 * Created by PhpStorm.
 * User: SebastianN
 * Date: 14.06.17
 * Time: 15:04
 */

namespace RatePAY\Payment\Plugin;

use Magento\Framework\Exception\PaymentException;
use RatePAY\Payment\Model\Exception\DisablePaymentMethodException;

class RatepayErrorProcessor
{
    /**
     * @var \Magento\Checkout\Model\Session
     */
    protected $session;

    /**
     * @var \Magento\Quote\Api\CartManagementInterface
     */
    protected $cartManagement;

    /**
     * @var \Magento\Framework\App\ProductMetadata
     */
    protected $productMetadata;

    /**
     * RatepayErrorProcessor constructor.
     * @param \Magento\Checkout\Model\Session $session
     * @param \Magento\Quote\Api\CartManagementInterface $cartManagement
     * @param \Magento\Framework\App\ProductMetadata $productMetadata
     */
    public function __construct(
        \Magento\Checkout\Model\Session $session,
        \Magento\Quote\Api\CartManagementInterface $cartManagement,
        \Magento\Framework\App\ProductMetadata $productMetadata
    )
    {
        $this->session = $session;
        $this->cartManagement = $cartManagement;
        $this->productMetadata = $productMetadata;
    }

    /**
     * @param \Magento\Checkout\Model\PaymentInformationManagement $subject
     * @param \Closure $proceed
     * @param $cartId
     * @param \Magento\Quote\Api\Data\PaymentInterface $paymentMethod
     * @param \Magento\Quote\Api\Data\AddressInterface $billingAddress
     * @return int
     * @throws DisablePaymentMethodException
     * @throws PaymentException
     */
    public function aroundSavePaymentInformationAndPlaceOrder(
        \Magento\Checkout\Model\PaymentInformationManagement $subject,
        \Closure $proceed,
        $cartId,
        \Magento\Quote\Api\Data\PaymentInterface $paymentMethod,
        \Magento\Quote\Api\Data\AddressInterface $billingAddress
    )
    {
        if (version_compare($this->productMetadata->getVersion(), '2.1.0', '>=') &&
            version_compare($this->productMetadata->getVersion(), '2.2.0', '<') &&
            strpos($paymentMethod->getMethod(), 'ratepay_') !== false
        ) { // Problem only exists in Magento 2.1.X
            $subject->savePaymentInformation($cartId, $paymentMethod, $billingAddress);
            try {
                $orderId = $this->cartManagement->placeOrder($cartId);
            } catch (DisablePaymentMethodException $e) {
                throw $e;
            } catch (\Exception $e) {
                throw new PaymentException(__($e->getMessage()), $e);
            }

            return $orderId;
        }
        // run core method
        return $proceed($cartId, $paymentMethod, $billingAddress);
    }
}
