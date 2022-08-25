<?php

/**
 * Copyright (c) Ratepay GmbH
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace RatePAY\Payment\Helper\Head;

use Magento\Framework\App\Helper\Context;

class Head extends \Magento\Framework\App\Helper\AbstractHelper
{
    /**
     * @var \RatePAY\Payment\Helper\Data
     */
    protected $rpDataHelper;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $storeManager;

    /**
     * @var \Magento\Framework\App\ProductMetadataInterface
     */
    protected $productMetadata;

    /**
     * @var \Magento\Framework\Module\ModuleListInterface
     */
    protected $moduleList;

    /**
     * @var \Magento\Payment\Helper\Data
     */
    protected $paymentHelper;


    /**
     * Head constructor.
     * @param Context $context
     * @param \RatePAY\Payment\Helper\Data $rpHelper
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Magento\Framework\App\ProductMetadataInterface $productMetadata
     * @param \Magento\Framework\Module\ModuleListInterface $moduleList
     * @param \Magento\Payment\Helper\Data $paymentHelper
     */
    public function __construct(
        Context $context,
        \RatePAY\Payment\Helper\Data $rpHelper,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Framework\App\ProductMetadataInterface $productMetadata,
        \Magento\Framework\Module\ModuleListInterface $moduleList,
        \Magento\Payment\Helper\Data $paymentHelper
    ) {
        parent::__construct($context);

        $this->rpDataHelper = $rpHelper;
        $this->storeManager = $storeManager;
        $this->productMetadata = $productMetadata;
        $this->moduleList = $moduleList;
        $this->paymentHelper = $paymentHelper;
    }

    /**
     * Build basic head section
     *
     * @param $quoteOrOrder
     * @param RatePAY/Payment/Model/Library/src/ModelBuilder $headModel
     * @param null $fixedPaymentMethod
     * @param null $profileId
     * @param null $securityCode
     * @return /app/code/RatePAY/Payment/Model/Library/src/ModelBuilder
     */
    public function setHead($quoteOrOrder, $headModel, $fixedPaymentMethod = null, $profileId = null, $securityCode = null)
    {
        if ($profileId === null || $securityCode === null) {
            $sMethodCode = ((is_null($fixedPaymentMethod) && $quoteOrOrder) ? $quoteOrOrder->getPayment()->getMethod() : $fixedPaymentMethod);
            $method = $this->paymentHelper->getMethodInstance($sMethodCode);
            $storeCode = null;
            if ($quoteOrOrder) {
                $storeCode = $quoteOrOrder->getStore()->getCode();
            }
            $oProfile = $method->getMatchingProfile(null, $storeCode, $this->getGrandTotal($quoteOrOrder), $this->getBillingCountry($quoteOrOrder), $this->getShippingCountry($quoteOrOrder), $this->getCurrency($quoteOrOrder));
            $profileId = (is_null($profileId) ? $oProfile->getData('profile_id') : $profileId);
            $securityCode = (is_null($securityCode) ? $oProfile->getSecurityCode() : $securityCode);
        }

        $serverAddr = '';
        if ($_SERVER && isset($_SERVER['SERVER_ADDR'])) {
            $serverAddr = $_SERVER['SERVER_ADDR'];
        }

        $storeId = null;
        if ($quoteOrOrder) {
            $storeId = $quoteOrOrder->getStore()->getId();
        }

        $headModel->setArray([
            'SystemId' => $this->storeManager->getStore($storeId)->getBaseUrl() . ' (' . $serverAddr . ')',
            'Credential' => [
                'ProfileId' => $profileId,
                'Securitycode' => $securityCode
            ],
            'Meta' => [
                'Systems' => [
                    'System' => [
                        'Name' => "Magento_" . $this->rpDataHelper->getEdition(),
                        'Version' => $this->productMetadata->getVersion() . '_' . $this->moduleList->getOne('RatePAY_Payment')['setup_version']
                    ]
                ]
            ]
        ]);

        return $headModel;
    }

    protected function getGrandTotal($quoteOrOrder)
    {
        if ($quoteOrOrder && !empty($quoteOrOrder->getGrandTotal())) {
            return $quoteOrOrder->getGrandTotal();
        }
        return null;
    }

    protected function getBillingCountry($quoteOrOrder)
    {
        if ($quoteOrOrder && !empty($quoteOrOrder->getBillingAddress())) {
            return $quoteOrOrder->getBillingAddress()->getCountryId();
        }
        return null;
    }

    protected function getShippingCountry($quoteOrOrder)
    {
        if ($quoteOrOrder && !empty($quoteOrOrder->getShippingAddress())) {
            return $quoteOrOrder->getShippingAddress()->getCountryId();
        }
        return null;
    }

    /**
     * Try to read currency code from quote or order object
     *
     * @param $quoteOrOrder
     * @return string|null
     */
    protected function getCurrency($quoteOrOrder)
    {
        if ($quoteOrOrder && !empty($quoteOrOrder->getOrderCurrencyCode())) {
            return $quoteOrOrder->getOrderCurrencyCode();
        }
        if ($quoteOrOrder && !empty($quoteOrOrder->getQuoteCurrencyCode())) {
            return $quoteOrOrder->getQuoteCurrencyCode();
        }
        return null;
    }
}
