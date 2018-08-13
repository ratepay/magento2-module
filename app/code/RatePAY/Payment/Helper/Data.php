<?php

/**
 * RatePAY Payments - Magento 2
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 */

namespace RatePAY\Payment\Helper;

use Magento\Framework\App\Helper\Context;

class Data extends \Magento\Framework\App\Helper\AbstractHelper
{
    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    protected $_scopeConfig;

    /**
     * @var \Magento\Framework\App\Filesystem\DirectoryList
     */
    protected $directoryList;

    /**
     * Data constructor.
     *
     * @param Context $context
     */
    public function __construct(
        Context $context,
                                \Magento\Framework\App\Filesystem\DirectoryList $directoryList
    ) {
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
     *
     * @return mixed
     */
    public function getRpConfigData($method, $field, $storeId)
    {
        $dataset = $method;
        $path = 'payment/'.$dataset.'/'.$field;

        return $this->_scopeConfig->getValue($path, \Magento\Store\Model\ScopeInterface::SCOPE_STORE, $storeId);
    }

    /**
     * We have to diff the addresses, because same_as_billing is sometimes wrong.
     *
     * @param unknown_type $address
     */
    public function getImportantAddressData($address)
    {
        $result = [];
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
        if (file_exists($this->directoryList->getPath('base').'/LICENSE_EE.txt')) {
            $edition = 'EE';
        } elseif (file_exists($this->directoryList->getPath('base').'/LICENSE_PRO.html')) {
            $edition = 'PE';
        }

        return $edition;
    }
}
