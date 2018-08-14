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

namespace RatePAY\Payment\Helper\Content\Payment;

use Magento\Framework\App\Helper\Context;

class Payment extends \Magento\Framework\App\Helper\AbstractHelper
{
    /**
     * @var \Magento\Checkout\Model\Session
     */
    protected $_checkoutSession;

    /**
     * @var \RatePAY\Payment\Helper\Payment
     */
    protected $_rpPaymentHelper;

    /**
     * Payment constructor.
     *
     * @param Context $context
     */
    public function __construct(
        Context $context,
                                \Magento\Checkout\Model\Session $checkoutSession,
                                \RatePAY\Payment\Helper\Payment $rpPaymentHelper
    ) {
        parent::__construct($context);

        $this->_checkoutSession = $checkoutSession;
        $this->_rpPaymentHelper = $rpPaymentHelper;
    }

    /**
     * Build Payment Block of Payment Request.
     *
     * @param $quoteOrOrder
     * @param null|mixed $fixedPaymentMethod
     *
     * @return array
     */
    public function setPayment($quoteOrOrder, $fixedPaymentMethod = null)
    {
        $id = (is_null($fixedPaymentMethod) ? $quoteOrOrder->getPayment()->getMethod() : $fixedPaymentMethod);
        $id = $this->_getRpMethodWithoutCountry($id);
        $content = [
            'Method' => $this->_rpPaymentHelper->convertMethodToProduct($id), // "installment", "elv", "prepayment"
            'Amount' => round($quoteOrOrder->getBaseGrandTotal(), 2),
        ];
        if ($id === 'ratepay_installment') {
            $content['Amount'] = $this->_checkoutSession->getRatepayPaymentAmount();
            $content['InstallmentDetails'] = [
                'InstallmentNumber' => $this->_checkoutSession->getRatepayInstallmentNumber(),
                'InstallmentAmount' => $this->_checkoutSession->getRatepayInstallmentAmount(),
                'LastInstallmentAmount' => $this->_checkoutSession->getRatepayLastInstallmentAmount(),
                'InterestRate' => $this->_checkoutSession->getRatepayInterestRate(),
            ];
            $content['DebitPayType'] = 'BANK-TRANSFER';
        }

        return $content;
    }

    /**
     * Get RatePay payment method without country code.
     *
     * @param $id
     *
     * @return mixed
     */
    private function _getRpMethodWithoutCountry($id)
    {
        $id = str_replace('_de', '', $id);
        $id = str_replace('_at', '', $id);
        $id = str_replace('_ch', '', $id);
        $id = str_replace('_nl', '', $id);

        return str_replace('_be', '', $id);
    }
}
