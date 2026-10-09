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
 * Remembers the SECCI sent for a backend order in the admin session,
 * so the order can only be placed if the cart didn't change since the SECCI was sent
 */
class BackendSecciState
{
    const SESSION_KEY = 'ratepay_backend_secci_state';

    /**
     * @var \Magento\Backend\Model\Session\Quote
     */
    protected $backendCheckoutSession;

    /**
     * @param \Magento\Backend\Model\Session\Quote $backendCheckoutSession
     */
    public function __construct(
        \Magento\Backend\Model\Session\Quote $backendCheckoutSession
    ) {
        $this->backendCheckoutSession = $backendCheckoutSession;
    }

    /**
     * @param  CartInterface|\Magento\Sales\Model\Order $quoteOrOrder
     * @param  string                                   $paymentMethod
     * @return array
     */
    protected function getCurrentState($quoteOrOrder, $paymentMethod)
    {
        if ($quoteOrOrder instanceof \Magento\Sales\Model\Order) {
            $quoteId = $quoteOrOrder->getQuoteId();
            $currency = $quoteOrOrder->getOrderCurrencyCode();
        } else {
            $quoteId = $quoteOrOrder->getId();
            $currency = $quoteOrOrder->getQuoteCurrencyCode();
        }

        return [
            'quoteId' => (int)$quoteId,
            'grandTotal' => (int)round((float)$quoteOrOrder->getGrandTotal() * 100),
            'currency' => (string)$currency,
            'paymentMethod' => (string)$paymentMethod,
        ];
    }

    /**
     * Saves the current cart state after the SECCI was sent successfully
     *
     * @param  CartInterface $quote
     * @param  string        $paymentMethod
     * @param  string        $email
     * @param  string|null   $documentId
     * @return void
     */
    public function commit(CartInterface $quote, $paymentMethod, $email, $documentId = null)
    {
        $state = $this->getCurrentState($quote, $paymentMethod);
        $state['email'] = $email;
        $state['documentId'] = $documentId;

        $this->backendCheckoutSession->setData(self::SESSION_KEY, $state);
    }

    /**
     * Checks if the current cart state still matches the state at the time the SECCI was sent
     *
     * @param  CartInterface|\Magento\Sales\Model\Order $quoteOrOrder
     * @param  string                                   $paymentMethod
     * @return bool
     */
    public function isValidFor($quoteOrOrder, $paymentMethod)
    {
        $state = $this->backendCheckoutSession->getData(self::SESSION_KEY);
        if (!is_array($state)) {
            return false;
        }

        unset($state['email'], $state['documentId']);
        return $state === $this->getCurrentState($quoteOrOrder, $paymentMethod);
    }

    /**
     * Returns the recipient email if the SECCI is still valid for the given cart state
     *
     * @param  CartInterface $quote
     * @param  string        $paymentMethod
     * @return string|null
     */
    public function getSentEmail(CartInterface $quote, $paymentMethod)
    {
        if ($this->isValidFor($quote, $paymentMethod) === false) {
            return null;
        }
        return $this->backendCheckoutSession->getData(self::SESSION_KEY)['email'] ?? null;
    }

    /**
     * Returns the document id of the sent SECCI if the SECCI is still valid for the given cart state
     *
     * @param  CartInterface $quote
     * @param  string        $paymentMethod
     * @return string|null
     */
    public function getSentDocumentId(CartInterface $quote, $paymentMethod)
    {
        if ($this->isValidFor($quote, $paymentMethod) === false) {
            return null;
        }
        return $this->backendCheckoutSession->getData(self::SESSION_KEY)['documentId'] ?? null;
    }

    /**
     * @return void
     */
    public function clear()
    {
        $this->backendCheckoutSession->unsetData(self::SESSION_KEY);
    }
}
