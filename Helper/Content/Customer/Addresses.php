<?php
/**
 * Created by PhpStorm.
 * User: SebastianN
 * Date: 09.02.17
 * Time: 14:24
 */

namespace RatePAY\Payment\Helper\Content\Customer;

use Magento\Framework\App\Helper\Context;
use RatePAY\Payment\Model\Source\StreetFieldUsage;

class Addresses extends \Magento\Framework\App\Helper\AbstractHelper
{
    /**
     * @var \RatePAY\Payment\Helper\Data
     */
    protected $rpDataHelper;

    /**
     * Addresses constructor.
     * @param Context $context
     * @param \RatePAY\Payment\Helper\Data $rpHelper
     */
    public function __construct(
        Context $context,
        \RatePAY\Payment\Helper\Data $rpHelper
    ) {
        parent::__construct($context);

        $this->rpDataHelper = $rpHelper;
    }

    /**
     * Adds street parameters to request
     *
     * @param  array$billingAddress
     * @param  array $street
     * @return array
     */
    protected function addStreetParams($billingAddress, $street)
    {
        if (is_array($street)) {
            $sStreet = array_shift($street); // extract first street line
            $billingAddress['Street'] = trim($sStreet);
            if (!empty($street)) {
                if ($this->rpDataHelper->getRpConfigDataByPath("ratepay/general/street_field_usage") == StreetFieldUsage::HOUSENR) {
                    $sHouseNr = array_shift($street); // extract second street line
                    $billingAddress['StreetNumber'] = trim($sHouseNr);
                }
                $sImplodeString = implode(" ", $street);
                if (!empty($sImplodeString)) {
                    $billingAddress['StreetAdditional'] = trim($sImplodeString);
                }
            }
        }
        return $billingAddress;
    }

    /**
     * Build Addresses Block of Payment Request
     *
     * @param $quoteOrOrder
     * @return array
     */
    public function setAddresses($quoteOrOrder)
    {
        $billingAddress = [
            'Type' => "billing",
            //'Salutation' => "Mrs.",
            'FirstName' => $quoteOrOrder->getBillingAddress()->getFirstname(),
            'LastName' => $quoteOrOrder->getBillingAddress()->getLastname(),
            'ZipCode' => $quoteOrOrder->getBillingAddress()->getPostCode(),
            'City' => $quoteOrOrder->getBillingAddress()->getCity(),
            'CountryCode' => $quoteOrOrder->getBillingAddress()->getCountryId(),
        ];
        $billingAddress = $this->addStreetParams($billingAddress, $quoteOrOrder->getBillingAddress()->getStreet());
        $deliveryAddress = [
            'Type' => "delivery",
            //'Salutation' => "Mrs.",
            'FirstName' => $quoteOrOrder->getShippingAddress()->getFirstname(),
            'LastName' => $quoteOrOrder->getShippingAddress()->getLastname(),
            'ZipCode' => $quoteOrOrder->getShippingAddress()->getPostCode(),
            'City' => $quoteOrOrder->getShippingAddress()->getCity(),
            'CountryCode' => $quoteOrOrder->getShippingAddress()->getCountryId(),
        ];
        $deliveryAddress = $this->addStreetParams($deliveryAddress, $quoteOrOrder->getShippingAddress()->getStreet());

        $content = [
            ['Address' => $billingAddress],
            ['Address' => $deliveryAddress],
        ];

        if (!empty($quoteOrOrder->getBillingAddress()->getCompany())) {
            $content[0]['Address']['Company'] = $quoteOrOrder->getBillingAddress()->getCompany();
        }
        if (!empty($quoteOrOrder->getShippingAddress()->getCompany())) {
            $content[1]['Address']['Company'] = $quoteOrOrder->getShippingAddress()->getCompany();
        }

        return $content;
    }
}
