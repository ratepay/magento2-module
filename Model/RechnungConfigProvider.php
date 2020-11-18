<?php

namespace RatePAY\Payment\Model;

use RatePAY\Payment\Model\Method\DE\Directdebit;
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
     * Customer session
     *
     * @var \Magento\Customer\Model\Session
     */
    protected $customerSession;

    /**
     * @var \RatePAY\Payment\Model\BamsApi\GetStoredBankAccounts
     */
    protected $getStoredBankAccounts;

    /**
     * @var \Magento\Framework\UrlInterface
     */
    protected $urlBuilder;

    /**
     * Array with all ratepay payment methods
     *
     * @var array
     */
    protected $allRatePayMethods = [
        \RatePAY\Payment\Model\Method\DE\Invoice::METHOD_CODE,
        \RatePAY\Payment\Model\Method\DE\Directdebit::METHOD_CODE,
        \RatePAY\Payment\Model\Method\DE\Installment::METHOD_CODE,
        \RatePAY\Payment\Model\Method\DE\Installment0::METHOD_CODE,
        \RatePAY\Payment\Model\Method\AT\Invoice::METHOD_CODE,
        \RatePAY\Payment\Model\Method\AT\Directdebit::METHOD_CODE,
        \RatePAY\Payment\Model\Method\AT\Installment::METHOD_CODE,
        \RatePAY\Payment\Model\Method\AT\Installment0::METHOD_CODE,
        \RatePAY\Payment\Model\Method\CH\Invoice::METHOD_CODE,
        \RatePAY\Payment\Model\Method\NL\Invoice::METHOD_CODE,
        \RatePAY\Payment\Model\Method\NL\Directdebit::METHOD_CODE,
        \RatePAY\Payment\Model\Method\BE\Invoice::METHOD_CODE,
        \RatePAY\Payment\Model\Method\BE\Directdebit::METHOD_CODE,
    ];

    /**
     * Array with all ratepay installment payment methods
     *
     * @var array
     */
    protected $installmentPaymentTypes = [
        \RatePAY\Payment\Model\Method\DE\Installment::METHOD_CODE,
        \RatePAY\Payment\Model\Method\DE\Installment0::METHOD_CODE,
        \RatePAY\Payment\Model\Method\AT\Installment::METHOD_CODE,
        \RatePAY\Payment\Model\Method\AT\Installment0::METHOD_CODE
    ];

    /**
     * Array with all ratepay debit payment methods
     *
     * @var array
     */
    protected $debitPaymentTypes = [
        \RatePAY\Payment\Model\Method\DE\Directdebit::METHOD_CODE,
        \RatePAY\Payment\Model\Method\AT\Directdebit::METHOD_CODE,
        \RatePAY\Payment\Model\Method\NL\Directdebit::METHOD_CODE,
        \RatePAY\Payment\Model\Method\BE\Directdebit::METHOD_CODE,
    ];

    /**
     * @param \Magento\Payment\Helper\Data $paymentHelper
     * @param \Magento\Framework\Escaper $escaper
     * @param \RatePAY\Payment\Helper\Data $rpDataHelper
     * @param \Magento\Checkout\Model\Session $checkoutSession
     * @param \Magento\Customer\Model\Session $customerSession
     * @param \RatePAY\Payment\Model\BamsApi\GetStoredBankAccounts $getStoredBankAccounts
     * @param \Magento\Framework\UrlInterface $urlBuilder
     */
    public function __construct(
        \Magento\Payment\Helper\Data $paymentHelper,
        \Magento\Framework\Escaper $escaper,
        \RatePAY\Payment\Helper\Data $rpDataHelper,
        \Magento\Checkout\Model\Session $checkoutSession,
        \Magento\Customer\Model\Session $customerSession,
        \RatePAY\Payment\Model\BamsApi\GetStoredBankAccounts $getStoredBankAccounts,
        \Magento\Framework\UrlInterface $urlBuilder
    ) {
        $this->escaper = $escaper;
        $this->paymentHelper = $paymentHelper;
        $this->rpDataHelper = $rpDataHelper;
        $this->checkoutSession = $checkoutSession;
        $this->customerSession = $customerSession;
        $this->getStoredBankAccounts = $getStoredBankAccounts;
        $this->urlBuilder = $urlBuilder;
    }

    /**
     * {@inheritdoc}
     */
    public function getConfig()
    {
        $config = array_merge_recursive([], $this->getInvoiceConfig());
        $config = array_merge_recursive($config, $this->getInstallmentConfig());
        $config = array_merge_recursive($config, $this->getB2BConfig());
        $config = array_merge_recursive($config, $this->getRatepayGeneralConfig());

        if ($this->isRememberIbanAllowed()) {
            $config = array_merge_recursive($config, $this->getDebitConfig($this->customerSession->getCustomerId()));
        }
        return $config;
    }

    /**
     * Checks whether remember Iban feature is allowed or not
     *
     * @return bool
     */
    protected function isRememberIbanAllowed()
    {
        $iCustomerId = $this->customerSession->getCustomerId();
        if ((bool)$this->rpDataHelper->getRpConfigDataByPath('ratepay/general/bams_enabled') === true && !empty($iCustomerId)) {
            return true;
        }
        return false;
    }

    /**
     * Add b2b config for given payment method
     *
     * @param  string $sMethodCode
     * @return array
     */
    protected function getSingleB2BConfig($sMethodCode)
    {
        return ($this->isPaymentMethodActive($sMethodCode)) ? [
            'payment' => [
                $sMethodCode => [
                    'b2bActive' => (bool)$this->rpDataHelper->getRpConfigData($sMethodCode, 'b2b'),
                    'differentShippingAddressAllowed' => $this->getIsDifferentShippingAddressAllowed($sMethodCode),
                    'rememberIbanEnabled' => $this->isRememberIbanAllowed(),
                ],
            ],
        ] : [];
    }

    /**
     * Add b2b config for all active payment methods
     *
     * @return array
     */
    protected function getB2BConfig()
    {
        $config = [];
        foreach ($this->allRatePayMethods as $paymentType) {
            $config = array_merge_recursive($config, $this->getSingleB2BConfig($paymentType));
        }
        return $config;
    }

    protected function getRatepayGeneralConfig()
    {
        return [
            'payment' => [
                'ratepay' => [
                    'manageBankdataUrl' => $this->urlBuilder->getUrl("ratepay/customer/bankdata"),
                ],
            ],
        ];
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
     * @param string $sMethodCode
     * @param int    $iCustomerId
     * @return array
     */
    protected function getSingleDebitConfig($sMethodCode, $iCustomerId)
    {
        return ($this->isPaymentMethodActive($sMethodCode)) ? [
            'payment' => [
                $sMethodCode => [
                    'savedBankData' => $this->getSavedBankData($sMethodCode, $iCustomerId),
                ],
            ],
        ] : [];
    }

    /**
     * @param string $sMethodCode
     * @param int    $iCustomerId
     * @return array|bool
     */
    protected function getSavedBankData($sMethodCode, $iCustomerId)
    {
        $sProfileId = $this->rpDataHelper->getRpConfigData($sMethodCode, 'profileId');
        $aBankData = $this->getStoredBankAccounts->sendRequest($iCustomerId, $sProfileId);
        if (!empty($aBankData)) {
            $aBankData = reset($aBankData); // Returns first element
            if (isset($aBankData['owner'])) {
                unset($aBankData['owner']);
                unset($aBankData['hash']);
                unset($aBankData['profile']);
                return $aBankData;
            }
        }
        return false;
    }

    /**
     * @param  string $sMethodCode
     * @return bool
     */
    protected function getIsDifferentShippingAddressAllowed($sMethodCode)
    {
        return (bool)$this->rpDataHelper->getRpConfigData($sMethodCode, 'delivery_address');
    }

    /**
     * @return array
     */
    protected function getInstallmentConfig()
    {
        $config = [];
        foreach ($this->installmentPaymentTypes as $installmentPaymentType) {
            $config = array_merge_recursive($config, $this->getSingleInstallmentConfig($installmentPaymentType));
        }
        return $config;
    }

    /**
     * @param int $iCustomerId
     * @return array
     */
    protected function getDebitConfig($iCustomerId)
    {
        $config = [];
        foreach ($this->debitPaymentTypes as $debitPaymentType) {
            $config = array_merge_recursive($config, $this->getSingleDebitConfig($debitPaymentType, $iCustomerId));
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
