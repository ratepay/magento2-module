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
     * @var \Magento\Quote\Api\GuestPaymentMethodManagementInterface
     */
    protected $paymentMethodManagement;

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
     * @var \Psr\Log\LoggerInterface
     */
    private $logger;

    /**
     * @param \Magento\Checkout\Model\Session $checkoutSession
     * @param \Magento\Quote\Api\GuestBillingAddressManagementInterface $billingAddressManagement
     * @param \Magento\Quote\Api\GuestPaymentMethodManagementInterface $paymentMethodManagement
     * @param \Magento\Quote\Api\GuestCartManagementInterface $cartManagement
     * @param \Magento\Framework\App\ProductMetadata $productMetadata
     * @param \RatePAY\Payment\Model\ResourceModel\ApiLog $apiLog
     * @param \Psr\Log\LoggerInterface $logger
     * @codeCoverageIgnore
     */
    public function __construct(
        \Magento\Checkout\Model\Session $checkoutSession,
        \Magento\Quote\Api\GuestBillingAddressManagementInterface $billingAddressManagement,
        \Magento\Quote\Api\GuestPaymentMethodManagementInterface $paymentMethodManagement,
        \Magento\Quote\Api\GuestCartManagementInterface $cartManagement,
        \Magento\Framework\App\ProductMetadata $productMetadata,
        \RatePAY\Payment\Model\ResourceModel\ApiLog $apiLog,
        \Psr\Log\LoggerInterface $logger
    ) {
        $this->billingAddressManagement = $billingAddressManagement;
        $this->paymentMethodManagement = $paymentMethodManagement;
        $this->checkoutSession = $checkoutSession;
        $this->cartManagement = $cartManagement;
        $this->productMetadata = $productMetadata;
        $this->apiLog = $apiLog;
        $this->logger = $logger;
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
    ) {
        if (strpos($paymentMethod->getMethod(), 'ratepay_') !== false) {
            if (version_compare($this->productMetadata->getVersion(), '2.1.0', '>=') &&
                version_compare($this->productMetadata->getVersion(), '2.2.0', '<')
            ) { // Problem only exists in Magento 2.1.X
                $subject->savePaymentInformation($cartId, $email, $paymentMethod, $billingAddress);
                try {
                    $orderId = $this->cartManagement->placeOrder($cartId);
                } catch (\Exception $e) {
                    throw new PaymentException(__($e->getMessage()), $e);
                }

                return $orderId;
            }

            if (version_compare($this->productMetadata->getVersion(), '2.4.6', '>=')) {
                // Fixes a problem where Magento forces the user back to the shipping address form when a payment error occured since Magento 2.4.6
                $subject->savePaymentInformation($cartId, $email, $paymentMethod, $billingAddress);
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
                    $this->handleApiLogEntry();
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
        try {
            $return = $proceed($cartId, $email, $paymentMethod, $billingAddress);
        } catch (\Exception $e) {
            $this->handleApiLogEntry();
            throw $e;
        }
        return $return;
    }

    /**
     * API log entry is lost in rollback when an error occured
     * This writes it again after rollback
     *
     * @return void
     */
    protected function handleApiLogEntry()
    {
        $request = $this->checkoutSession->getRatepayRequest();
        if (!empty($request)) {
            // Rewrite the log-entry after it was rolled back in the db-transaction
            $this->apiLog->addApiLogEntry($request);
        }
        $this->checkoutSession->unsRatepayRequest();
    }
}
