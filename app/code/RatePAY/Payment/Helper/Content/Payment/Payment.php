<?php
/**
 * Created by PhpStorm.
 * User: SebastianN
 * Date: 09.02.17
 * Time: 16:35
 */

namespace RatePAY\Payment\Helper\Content\Payment;


use Magento\Framework\App\Helper\Context;

class Payment extends \Magento\Framework\App\Helper\AbstractHelper
{
    /**
     * @var \Magento\Checkout\Model\Session\Proxy
     */
    protected $checkoutSession;

    /**
     * @var \RatePAY\Payment\Helper\Payment
     */
    protected $_rpPaymentHelper;

    /**
     * @var \Magento\Framework\App\State
     */
    protected $appState;

    /**
     * Payment constructor.
     * @param Context $context
     * @param \Magento\Checkout\Model\Session $checkoutSession
     * @param \RatePAY\Payment\Helper\Payment $rpPaymentHelper
     */
    public function __construct(
        Context $context,
        \Magento\Checkout\Model\Session\Proxy $checkoutSession,
        \RatePAY\Payment\Helper\Payment $rpPaymentHelper
    ) {
        parent::__construct($context);

        $this->checkoutSession = $checkoutSession;
        $this->_rpPaymentHelper = $rpPaymentHelper;
    }

    /**
     * Build Payment Block of Payment Request
     *
     * @param $quoteOrOrder
     * @return array
     */
    public function setPayment($quoteOrOrder, $fixedPaymentMethod = null)
    {
        $id = (is_null($fixedPaymentMethod) ? $quoteOrOrder->getPayment()->getMethod() : $fixedPaymentMethod);
        $id = $this->_getRpMethodWithoutCountry($id);
        $content = [
                'Method' => $this->_rpPaymentHelper->convertMethodToProduct($id), // "installment", "elv", "prepayment"
                'Amount' => round($quoteOrOrder->getGrandTotal(), 2)
        ];
        if (in_array($id, ['ratepay_installment', 'ratepay_installment0'])) {
            $content['Amount'] = $this->checkoutSession->getRatepayPaymentAmount();
            $content['InstallmentDetails'] = [
                'InstallmentNumber' => $this->checkoutSession->getRatepayInstallmentNumber(),
                'InstallmentAmount' => $this->checkoutSession->getRatepayInstallmentAmount(),
                'LastInstallmentAmount' => $this->checkoutSession->getRatepayLastInstallmentAmount(),
                'InterestRate' => $this->checkoutSession->getRatepayInterestRate()
            ];
            $content['DebitPayType'] = 'BANK-TRANSFER';
        }
        return $content;
    }

    /**
     * Get RatePay payment method without country code
     *
     * @param $id
     * @return mixed
     */
    private function _getRpMethodWithoutCountry($id) {
        $id = str_replace('_de', '', $id);
        $id = str_replace('_at', '', $id);
        $id = str_replace('_ch', '', $id);
        $id = str_replace('_nl', '', $id);
        $id = str_replace('_be', '', $id);

        return $id;
    }
}
