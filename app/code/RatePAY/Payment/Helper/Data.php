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
     * Data constructor.
     *
     * @param Context $context
     * @param \Magento\Framework\App\Filesystem\DirectoryList $directoryList
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     */
    public function __construct(
        Context $context,
        \Magento\Framework\App\Filesystem\DirectoryList $directoryList,
        \Magento\Store\Model\StoreManagerInterface $storeManager
    ) {
        parent::__construct($context);
        $this->directoryList = $directoryList;
        $this->storeManager = $storeManager;
    }

    /**
     * @param string $method
     * @param string $field
     * @param string $storeCode
     * @return mixed
     */
    public function getRpConfigData($method, $field, $storeCode = null)
    {
        if (!$storeCode) {
            $storeCode = $this->storeManager->getStore()->getCode();
        }
        $path = 'payment/'.$method.'/'.$field;
        $result = $this->scopeConfig->getValue($path,\Magento\Store\Model\ScopeInterface::SCOPE_STORES, $storeCode);
        return $result;
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
