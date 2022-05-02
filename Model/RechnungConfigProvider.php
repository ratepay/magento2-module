<?php

namespace RatePAY\Payment\Model;

use RatePAY\Payment\Model\Method\Directdebit;
use RatePAY\Payment\Model\Method\Installment;
use RatePAY\Payment\Model\Method\AbstractMethod;
use RatePAY\Payment\Model\Method\Invoice;

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
     * @var AbstractMethod[]
     */
    protected $aMethodInstances;

    /**
     * Array with all ratepay payment methods
     *
     * @var array
     */
    protected $allRatePayMethods = [
        \RatePAY\Payment\Model\Method\Invoice::METHOD_CODE,
        \RatePAY\Payment\Model\Method\Directdebit::METHOD_CODE,
        \RatePAY\Payment\Model\Method\Installment::METHOD_CODE,
        \RatePAY\Payment\Model\Method\Installment0::METHOD_CODE,
    ];

    /**
     * Array with all ratepay installment payment methods
     *
     * @var array
     */
    protected $installmentPaymentTypes = [
        \RatePAY\Payment\Model\Method\Installment::METHOD_CODE,
        \RatePAY\Payment\Model\Method\Installment0::METHOD_CODE,
    ];

    /**
     * Array with all ratepay iban payment methods
     *
     * @var array
     */
    protected $rememberIbanPaymentTypes = [
        \RatePAY\Payment\Model\Method\Directdebit::METHOD_CODE,
        \RatePAY\Payment\Model\Method\Installment::METHOD_CODE,
        \RatePAY\Payment\Model\Method\Installment0::METHOD_CODE,
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
        $config = array_merge_recursive([], $this->getInstallmentConfig());
        $config = array_merge_recursive($config, $this->getB2BConfig());
        $config = array_merge_recursive($config, $this->getRatepaySandboxConfig());
        $config = array_merge_recursive($config, $this->getRatepayGeneralConfig());

        if ($this->isRememberIbanAllowed()) {
            $config = array_merge_recursive($config, $this->getRememberIbanConfig($this->customerSession->getCustomerId()));
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
     * @param  string $sMethodCode
     * @return AbstractMethod
     */
    protected function getMethod($sMethodCode)
    {
        if (!isset($this->aMethodInstances[$sMethodCode])) {
            $this->aMethodInstances[$sMethodCode] = $this->paymentHelper->getMethodInstance($sMethodCode);
        }
        return $this->aMethodInstances[$sMethodCode];
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
                    'b2bActive' => (bool)$this->getMethod($sMethodCode)->getMatchingProfile()->getProductData("b2b_?", $sMethodCode, true),
                    'differentShippingAddressAllowed' => $this->getIsDifferentShippingAddressAllowed($sMethodCode),
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
                    'rememberIbanEnabled' => $this->isRememberIbanAllowed(),
                ],
            ],
        ];
    }

    /**
     * @return array
     */
    protected function getRatepaySandboxConfig()
    {
        $config = [];
        foreach ($this->allRatePayMethods as $paymentType) {
            $config = array_merge_recursive($config, $this->getSingleSandboxConfig($paymentType));
        }
        return $config;
    }

    protected function getSingleSandboxConfig($sMethodCode)
    {
        return ($this->isPaymentMethodActive($sMethodCode)) ? [
            'payment' => [
                $sMethodCode => [
                    'sandboxMode' => $this->getMethod($sMethodCode)->getMatchingProfile()->getSandboxMode(),
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
                    'validPaymentFirstdays' => $this->getValidPaymentFirstdays($sMethodCode),
                    'defaultPaymentFirstday' => $this->getMethod($sMethodCode)->getMatchingProfile()->getData("payment_firstday"),
                ],
            ],
        ] : [];
    }

    /**
     * @param string $sMethodCode
     * @param int    $iCustomerId
     * @return array
     */
    protected function getSingleRememberIbanConfig($sMethodCode, $iCustomerId)
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
        $sProfileId = $this->getMethod($sMethodCode)->getMatchingProfile()->getData("profile_id");
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
        return (bool)$this->getMethod($sMethodCode)->getMatchingProfile()->getProductData("delivery_address_?", $sMethodCode, true);
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
    protected function getRememberIbanConfig($iCustomerId)
    {
        $config = [];
        foreach ($this->rememberIbanPaymentTypes as $debitPaymentType) {
            $config = array_merge_recursive($config, $this->getSingleRememberIbanConfig($debitPaymentType, $iCustomerId));
        }
        return $config;
    }

    /**
     * @param string $sMethodCode
     * @return array
     */
    protected function getAllowedMonths($sMethodCode)
    {
        $method = $this->getMethod($sMethodCode);
        if ($method instanceof AbstractMethod) {
            $quoteAmount = $this->checkoutSession->getQuote()->getGrandTotal();
            return $method->getAllowedMonths($quoteAmount);
        }
        return [];
    }

    /**
     * @param  string
     * @return string|array
     */
    protected function getValidPaymentFirstdays($sMethodCode)
    {
        $validPaymentFirstdays = $this->getMethod($sMethodCode)->getMatchingProfile()->getData("valid_payment_firstdays");
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
        $method = $this->getMethod($sMethodCode);
        if ($method && $method->isActive() && !empty($method->getMatchingProfile())) {
            return true;
        }
        return false;
    }
}
