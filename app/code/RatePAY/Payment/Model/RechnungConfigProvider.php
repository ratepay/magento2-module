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
     * @param string $sMethodCode
     * @return array
     */
    protected function getSingleInstallmentConfig($sMethodCode)
    {
        return ($this->isPaymentMethodActive($sMethodCode)) ? [
            'payment' => [
                $sMethodCode => [
                    'allowedMonths' => $this->getAllowedMonths($sMethodCode),
                    'validPaymentFirstdays' => $this->getValidPaymentFirstdays($sMethodCode)
                ],
            ],
        ] : [];
    }

    /**
     * @return array
     */
    protected function getInstallmentConfig()
    {
        $installmentPaymentTypes = [
            \RatePAY\Payment\Model\Method\DE\Installment::METHOD_CODE,
            \RatePAY\Payment\Model\Method\DE\Installment0::METHOD_CODE,
            \RatePAY\Payment\Model\Method\AT\Installment::METHOD_CODE,
            \RatePAY\Payment\Model\Method\AT\Installment0::METHOD_CODE
        ];
        $config = [];
        foreach ($installmentPaymentTypes as $installmentPaymentType) {
            $config = array_merge_recursive($config, $this->getSingleInstallmentConfig($installmentPaymentType));
        }
        return $config;
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
    protected function getValidPaymentFirstdays($sMethodCode)
    {
        $validPaymentFirstdays = $this->rpDataHelper->getRpConfigData($sMethodCode, 'valid_payment_firstday');
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
