<?php

namespace RatePAY\Payment\Model;

use RatePAY\Payment\Model\Method\DE\Invoice;
use RatePAY\Payment\Model\Method\DE\Installment;
use RatePAY\Payment\Model\Method\AbstractMethod;

class RechnungConfigProvider implements \Magento\Checkout\Model\ConfigProviderInterface
{
    /**
     * @var \Magento\Payment\Helper\Data
     */
    protected $paymentHelper;

    /**
     * @var \Magento\Framework\Escaper
     */
    protected $escaper;

    /**
     * @var \RatePAY\Payment\Helper\Data
     */
    protected $rpDataHelper;

    /**
     * Checkout session
     *
     * @var \Magento\Checkout\Model\Session
     */
    protected $checkoutSession;

    /**
     * @param \Magento\Payment\Helper\Data $paymentHelper
     * @param \Magento\Framework\Escaper $escaper
     * @param \RatePAY\Payment\Helper\Data $rpDataHelper
     * @param \Magento\Checkout\Model\Session $checkoutSession
     */
    public function __construct(
        \Magento\Payment\Helper\Data $paymentHelper,
        \Magento\Framework\Escaper $escaper,
        \RatePAY\Payment\Helper\Data $rpDataHelper,
        \Magento\Checkout\Model\Session $checkoutSession
    ) {
        $this->escaper = $escaper;
        $this->paymentHelper = $paymentHelper;
        $this->rpDataHelper = $rpDataHelper;
        $this->checkoutSession = $checkoutSession;
    }

    /**
     * {@inheritdoc}
     */
    public function getConfig()
    {
        $config = array_merge_recursive([], $this->getInvoiceConfig());
        $config = array_merge_recursive($config, $this->getInstallmentConfig());
        return $config;
    }

    /**
     * @return array
     */
    protected function getInvoiceConfig()
    {
        return ($this->isPaymentMethodActive(Invoice::METHOD_CODE)) ? [
            'payment' => [
                'ratepay_de_invoice' => [
                    #'mailingAddress' => $this->getMailingAddress(),
                    #'payableTo' => $this->getPayableTo(),
                ],
            ],
        ] : [];
    }

    /**
     * @return array
     */
    protected function getInstallmentConfig()
    {
        return ($this->isPaymentMethodActive(Installment::METHOD_CODE)) ? [
            'payment' => [
                'ratepay_de_installment' => [
                    'allowedMonths' => $this->getAllowedMonths(Installment::METHOD_CODE),
                    'validPaymentFirstdays' => $this->getValidPaymentFirstdays()
                ],
            ],
        ] : [];
    }

    /**
     * @param string $sMethodCode
     * @return array
     */
    protected function getAllowedMonths($sMethodCode)
    {
        $method = $this->paymentHelper->getMethodInstance($sMethodCode);
        if ($method instanceof AbstractMethod) {
            $quoteAmount = $this->checkoutSession->getQuote()->getGrandTotal();
            return $method->getAllowedMonths($quoteAmount);
        }
        return [];
    }

    /**
     * @return string|array
     */
    protected function getValidPaymentFirstdays()
    {
        $validPaymentFirstdays = $this->rpDataHelper->getRpConfigData(Installment::METHOD_CODE, 'valid_payment_firstday');
        if(strpos($validPaymentFirstdays, ',') !== false) {
            $validPaymentFirstdays = explode(',', $validPaymentFirstdays);
        }
        return $validPaymentFirstdays;
    }

    /**
     * @param string $sMethodCode
     * @return bool
     */
    protected function isPaymentMethodActive($sMethodCode)
    {
        $method = $this->paymentHelper->getMethodInstance($sMethodCode);
        if ($method && $method->isActive()) {
            return true;
        }
        return false;
    }
}
