<?php

/**
 * Copyright (c) Ratepay GmbH
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace RatePAY\Payment\Helper;

use Magento\Framework\App\Helper\Context;

class Data extends \Magento\Framework\App\Helper\AbstractHelper
{
    /**
     * Ratepay legal text URLs by language, 'en' is the default
     *
     * @var array
     */
    const LEGAL_URLS = [
        'de' => [
            'termsOfPayment' => 'https://www.ratepay.com/legal-payment-terms',
            'dataPrivacy' => 'https://www.ratepay.com/legal-payment-dataprivacy',
        ],
        'en' => [
            'termsOfPayment' => 'https://www.ratepay.com/en/ratepay-terms-of-payment',
            'dataPrivacy' => 'https://www.ratepay.com/en/ratepay-data-privacy-statement',
        ],
    ];

    /**
     * @var \Magento\Framework\App\Filesystem\DirectoryList
     */
    protected $directoryList;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $storeManager;

    /**
     * @var \Magento\Framework\App\State
     */
    protected $state;

    /**
     * @var \Magento\Framework\Locale\Resolver
     */
    protected $localeResolver;

    /**
     * Data constructor.
     *
     * @param Context $context
     * @param \Magento\Framework\App\Filesystem\DirectoryList $directoryList
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Magento\Framework\App\State $state
     * @param \Magento\Framework\Locale\Resolver $localeResolver
     */
    public function __construct(
        Context $context,
        \Magento\Framework\App\Filesystem\DirectoryList $directoryList,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Framework\App\State $state,
        \Magento\Framework\Locale\Resolver $localeResolver
    ) {
        parent::__construct($context);
        $this->directoryList = $directoryList;
        $this->storeManager = $storeManager;
        $this->state = $state;
        $this->localeResolver = $localeResolver;
    }

    /**
     * Get parameter from the request
     *
     * @param  string $sParameter
     * @return mixed
     */
    public function getRequestParameter($sParameter)
    {
        return $this->_getRequest()->getParam($sParameter);
    }

    /**
     * Trying to get the correct storeCode
     * Getting the correct current storeCode in Magento2 is horror
     *
     * @return array
     */
    protected function resolveCurrentStoreCode()
    {
        $scopeCode = \Magento\Store\Model\ScopeInterface::SCOPE_STORES;
        if ($this->state->getAreaCode() == \Magento\Framework\App\Area::AREA_ADMINHTML) {
            $storeCode = 0; // 0 = default config, which should be used when neither website nor store parameter are present, storeManager returns default STORE though, which would be wrong
            if (!empty($this->getRequestParameter('website'))) {
                $storeCode = $this->getRequestParameter('website');
                $scopeCode = \Magento\Store\Model\ScopeInterface::SCOPE_WEBSITES;
            }
            if (!empty($this->getRequestParameter('store'))) {
                $storeCode = $this->getRequestParameter('store');
            }
        } else {
            $storeCode = $this->storeManager->getStore()->getCode();
        }

        return [$storeCode, $scopeCode];
    }

    /**
     * @param string $path
     * @param string $storeCode
     * @param string $scopeCode
     * @return mixed
     */
    public function getRpConfigDataByPath($path, $storeCode = null, $scopeCode = \Magento\Store\Model\ScopeInterface::SCOPE_STORES)
    {
        if (!$storeCode) {
            list($storeCode, $scopeCode) = $this->resolveCurrentStoreCode();
        }
        return $this->scopeConfig->getValue($path, $scopeCode, $storeCode);
    }

    /**
     * @param string $method
     * @param string $field
     * @param string $storeCode
     * @return mixed
     */
    public function getRpConfigData($method, $field, $storeCode = null)
    {
        return $this->getRpConfigDataByPath('payment/'.$method.'/'.$field, $storeCode);
    }

    /**
     * @param string                                $method
     * @param string                                $field
     * @param \Magento\Quote\Api\Data\CartInterface $quote
     * @return mixed
     */
    public function getRpConfigDataForQuote($method, $field, ?\Magento\Quote\Api\Data\CartInterface $quote = null)
    {
        $storeCode = null;
        if ($quote !== null) {
            $storeCode = $quote->getStore()->getCode();
        }
        return $this->getRpConfigData($method, $field, $storeCode);
    }

    /**
     * We have to diff the addresses, because same_as_billing is sometimes wrong
     *
     * @param unknown_type $address
     * @return array
     */
    public function getImportantAddressData($address)
    {
        $result = [];
        $result['city'] = trim($address->getCity() ?? '');
        $result['company'] = trim($address->getCompany() ?? '');
        $result['prefix'] = $address->getPrefix();
        $result['gender'] = $address->getGender();
        $result['firstname'] = $address->getFirstname();
        $result['lastname'] = $address->getLastname();
        $result['street'] = $address->getStreetFull();
        $result['postcode'] = $address->getPostcode();
        $result['region'] = $address->getRegion();
        $result['region_id'] = $address->getRegionId();
        $result['country_id'] = $address->getCountryId();
        return $result;
    }

    /**
     * @return string
     */
    public function getEdition()
    {
        $edition = 'CE';
        if (file_exists($this->directoryList->getPath('base') . '/LICENSE_EE.txt')) {
            $edition = 'EE';
        } elseif (file_exists($this->directoryList->getPath('base') . '/LICENSE_PRO.html')) {
            $edition = 'PE';
        }
        return $edition;
    }

    /**
     * Returns the 2-letter language of the buyer
     * In the admin area the locale resolver returns the interface locale of the admin user,
     * so the locale of the store view the quote or order belongs to is used if given
     *
     * @param  \Magento\Quote\Api\Data\CartInterface|\Magento\Sales\Model\Order|null $quoteOrOrder
     * @return string
     */
    public function getLocale($quoteOrOrder = null)
    {
        $locale = null;
        if ($quoteOrOrder !== null && $quoteOrOrder->getStoreId()) {
            $locale = $this->scopeConfig->getValue(
                \Magento\Directory\Helper\Data::XML_PATH_DEFAULT_LOCALE,
                \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
                $quoteOrOrder->getStoreId()
            );
        }
        if (empty($locale)) {
            $locale = $this->localeResolver->getLocale();
        }

        $locale = substr((string)$locale, 0, 2);
        if (empty($locale)) {
            $locale = substr($this->localeResolver->getDefaultLocale(), 0, 2);
        }
        return $locale;
    }

    /**
     * Returns the Ratepay legal text URLs for the current language (e.g. de_DE, de_AT and de_CH all use 'de')
     *
     * @return array
     */
    public function getLegalUrls()
    {
        $language = strtolower((string)$this->getLocale());
        return self::LEGAL_URLS[$language] ?? self::LEGAL_URLS['en'];
    }
}
