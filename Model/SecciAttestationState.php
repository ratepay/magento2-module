<?php

/**
 * Copyright (c) Ratepay GmbH
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace RatePAY\Payment\Model;

use Magento\Quote\Api\Data\CartInterface;

/**
 * Remembers the attestation token of the last successfully created SECCI together with the payment method
 * it was created for, so the token can't be used for an order with another payment method or debit pay type
 */
class SecciAttestationState
{
    const SESSION_KEY = 'ratepay_secci_attestation_state';

    /**
     * Payment methods sending the DebitPayType with the SECCI request, see Helper\Content\Payment\Payment::setPayment()
     */
    const INSTALLMENT_METHODS = ['ratepay_installment', 'ratepay_installment0', 'ratepay_installment_backend', 'ratepay_installment0_backend'];

    /**
     * @var \Magento\Checkout\Model\Session
     */
    protected $checkoutSession;

    /**
     * @param \Magento\Checkout\Model\Session $checkoutSession
     */
    public function __construct(
        \Magento\Checkout\Model\Session $checkoutSession
    ) {
        $this->checkoutSession = $checkoutSession;
    }

    /**
     * The SECCI is created with DebitPayType DIRECT-DEBIT only if rp_directdebit is true
     *
     * @param  CartInterface|\Magento\Sales\Model\Order $quoteOrOrder
     * @param  string                                   $paymentMethod
     * @return bool|null
     */
    protected function getDirectDebit($quoteOrOrder, $paymentMethod)
    {
        if (!in_array($paymentMethod, self::INSTALLMENT_METHODS)) {
            return null;
        }
        return $quoteOrOrder->getPayment()->getAdditionalInformation('rp_directdebit') === true;
    }

    /**
     * Saves the attestation token after the SECCI was created successfully
     *
     * @param  CartInterface $quote
     * @param  string        $paymentMethod
     * @param  string|null   $token
     * @return void
     */
    public function commit(CartInterface $quote, $paymentMethod, $token)
    {
        if (empty($token)) {
            $this->clear();
            return;
        }

        $this->checkoutSession->setData(self::SESSION_KEY, [
            'token' => (string)$token,
            'quoteId' => (int)$quote->getId(),
            'paymentMethod' => (string)$paymentMethod,
            'directDebit' => $this->getDirectDebit($quote, $paymentMethod),
        ]);
    }

    /**
     * Checks if the SECCI was created for the quote, payment method and debit pay type of the order
     *
     * @param  \Magento\Sales\Model\Order $order
     * @param  string                     $paymentMethod
     * @return bool
     */
    public function isValidFor(\Magento\Sales\Model\Order $order, $paymentMethod)
    {
        $state = $this->checkoutSession->getData(self::SESSION_KEY);
        if (!is_array($state) || empty($state['token'])) {
            return false;
        }

        return $state['quoteId'] === (int)$order->getQuoteId()
            && $state['paymentMethod'] === (string)$paymentMethod
            && $state['directDebit'] === $this->getDirectDebit($order, $paymentMethod);
    }

    /**
     * @return string|null
     */
    public function getToken()
    {
        $state = $this->checkoutSession->getData(self::SESSION_KEY);
        return is_array($state) && !empty($state['token']) ? $state['token'] : null;
    }

    /**
     * @return void
     */
    public function clear()
    {
        $this->checkoutSession->unsetData(self::SESSION_KEY);
    }
}
