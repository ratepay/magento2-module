<?php
/**
 * Created by PhpStorm.
 * User: SebastianN
 * Date: 08.02.17
 * Time: 11:34
 */

namespace RatePAY\Payment\Helper;

use Magento\Framework\App\Helper\Context;

class Data extends \Magento\Framework\App\Helper\AbstractHelper
{
    /**
     * @var \Magento\Framework\App\Filesystem\DirectoryList
     */
    protected $directoryList;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $storeManager;

    /**
     * @var \Magento\Framework\App\State\Proxy
     */
    protected $state;

    /**
     * Data constructor.
     *
     * @param Context $context
     * @param \Magento\Framework\App\Filesystem\DirectoryList $directoryList
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Magento\Framework\App\State\Proxy $state
     */
    public function __construct(
        Context $context,
        \Magento\Framework\App\Filesystem\DirectoryList $directoryList,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Framework\App\State\Proxy $state
    ) {
        parent::__construct($context);
        $this->directoryList = $directoryList;
        $this->storeManager = $storeManager;
        $this->state = $state;
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
    public function getRpConfigDataForQuote($method, $field, \Magento\Quote\Api\Data\CartInterface $quote = null)
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
        $result = array();
        $result['city'] = trim($address->getCity());
        $result['company'] = trim($address->getCompany());
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
        } else if (file_exists($this->directoryList->getPath('base') . '/LICENSE_PRO.html')) {
            $edition = 'PE';
        }
        return $edition;
    }
}
