<?php

/**
 * Copyright (c) Ratepay GmbH
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace RatePAY\Payment\Plugin;

use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Exception\LocalizedException;
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
     * @var \Psr\Log\LoggerInterface
     */
    private $logger;

    /**
     * RatepayErrorProcessor constructor.
     * @param \Magento\Checkout\Model\Session $session
     * @param \Magento\Quote\Api\CartManagementInterface $cartManagement
     * @param \Magento\Framework\App\ProductMetadata $productMetadata
     * @param LoggerInterface $logger
     */
    public function __construct(
        \Magento\Checkout\Model\Session $session,
        \Magento\Quote\Api\CartManagementInterface $cartManagement,
        \Magento\Framework\App\ProductMetadata $productMetadata,
        \Psr\Log\LoggerInterface $logger
    ) {
        $this->session = $session;
        $this->cartManagement = $cartManagement;
        $this->productMetadata = $productMetadata;
        $this->logger = $logger;
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
    ) {
        if (strpos($paymentMethod->getMethod(), 'ratepay_') !== false) {
            if (version_compare($this->productMetadata->getVersion(), '2.1.0', '>=') && version_compare($this->productMetadata->getVersion(), '2.2.0', '<')) {
                // Fixes a problem where Magento did not allow to pass through specific error messages in Magento 2.1.X
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

            if (version_compare($this->productMetadata->getVersion(), '2.4.6', '>=')) {
                // Fixes a problem where Magento forces the user back to the shipping address form when a payment error occured since Magento 2.4.6
                $subject->savePaymentInformation($cartId, $paymentMethod, $billingAddress);
                try {
                    $orderId = $this->cartManagement->placeOrder($cartId);
                } catch (DisablePaymentMethodException $e) {
                    throw $e;
                } catch (LocalizedException $e) {
                    $this->logger->critical(
                        'Placing an order with quote_id ' . $cartId . ' is failed: ' . $e->getMessage()
                    );
                    throw $e;
                } catch (\Exception $e) {
                    $this->logger->critical($e);
                    throw new CouldNotSaveException(
                        __('A server error stopped your order from being placed. Please try to place your order again.'),
                        $e
                    );
                }
                return $orderId;
            }
        }

        // run core method
        return $proceed($cartId, $paymentMethod, $billingAddress);
    }
}
