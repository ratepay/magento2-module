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
     * @var \Magento\Checkout\Model\Session
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
        \Magento\Checkout\Model\Session $checkoutSession,
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
        $methodCode = $quoteOrOrder->getPayment()->getMethod();
        $id = (is_null($fixedPaymentMethod) ? $methodCode : $fixedPaymentMethod);
        $id = $this->_rpPaymentHelper->getRpMethodWithoutCountry($id);
        $content = [
            'Method' => $this->_rpPaymentHelper->convertMethodToProduct($id), // "installment", "elv", "prepayment"
            'Amount' => round($quoteOrOrder->getGrandTotal(), 2)
        ];
        if (in_array($id, ['ratepay_installment', 'ratepay_installment0', 'ratepay_installment_backend', 'ratepay_installment0_backend'])) {
            $content['Amount'] = $this->checkoutSession->getData('ratepayPaymentAmount_'.$methodCode);
            $content['InstallmentDetails'] = [
                'InstallmentNumber' => $this->checkoutSession->getData('ratepayInstallmentNumber_'.$methodCode),
                'InstallmentAmount' => $this->checkoutSession->getData('ratepayInstallmentAmount_'.$methodCode),
                'LastInstallmentAmount' => $this->checkoutSession->getData('ratepayLastInstallmentAmount_'.$methodCode),
                'InterestRate' => $this->checkoutSession->getData('ratepayInterestRate_'.$methodCode)
            ];
            if ($quoteOrOrder->getPayment()->getAdditionalInformation('rp_directdebit') === true) {
                $content['DebitPayType'] = 'DIRECT-DEBIT';
            } else {
                $content['DebitPayType'] = 'BANK-TRANSFER';
            }
        }
        return $content;
    }
}
