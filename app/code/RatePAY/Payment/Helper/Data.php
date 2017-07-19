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
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    protected $_scopeConfig;

    protected $directoryList;

    /**
     * Data constructor.
     * @param Context $context
     */
    public function __construct(Context $context,
                                \Magento\Framework\App\Filesystem\DirectoryList $directoryList)
    {
        parent::__construct($context);
        $this->_scopeConfig = $context->getScopeConfig();
        $this->directoryList = $directoryList;
    }

    /**
     * @param $quoteOrOrder
     * @param $method
     * @param $field
     * @param $storeId
     * @param bool $advanced
     * @param bool $noCountry
     * @return mixed
     */
    public function getRpConfigData($quoteOrOrder, $method, $field, $storeId, $advanced = false, $noCountry = false)
    {
        $dataset = $method;
        $path = 'payment/'. $dataset . '/' . $field;
        $result = $this->_scopeConfig->getValue($path,\Magento\Store\Model\ScopeInterface::SCOPE_STORE, $storeId);
        return $result;
    }

    /**
     * We have to diff the addresses, because same_as_billing is sometimes wrong
     *
     * @param unknown_type $address
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
