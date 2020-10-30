<?php
/**
 * Created by PhpStorm.
 * User: SebastianN
 * Date: 09.02.17
 * Time: 14:24
 */

namespace RatePAY\Payment\Helper\Content\Customer;


use Magento\Framework\App\Helper\Context;
class Addresses extends \Magento\Framework\App\Helper\AbstractHelper
{
    /**
     * Addresses constructor.
     * @param Context $context
     */
    public function __construct(Context $context)
    {
        parent::__construct($context);
    }

    /**
     * Checks if string might be a house number
     *
     * @param  string $string
     * @return bool
     */
    protected function isHouseNumber($string)
    {
        if (preg_match("#^\\d+[ /\\-]?\\d*[a-zA-Z]?(?<![/-])$#", trim($string)) === 1) {
            return true;
        }
        return false;
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
            $billingAddress['Street'] = $street[0];
            if (isset($street[1])) {
                if ($this->isHouseNumber($street[1])) {
                    $billingAddress['StreetNumber'] = $street[1];
                } else {
                    if (!isset($street[2])) {
                        $billingAddress['StreetAdditional'] = $street[1];
                    } else {
                        $billingAddress['Street'] .= ' '.$street[1];
                    }
                }
            }
            if (isset($street[2])) {
                $billingAddress['StreetAdditional'] = $street[2];
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

        if(!empty($quoteOrOrder->getBillingAddress()->getCompany())){
            $content[0]['Address']['Company'] = $quoteOrOrder->getBillingAddress()->getCompany();
        }
        if(!empty($quoteOrOrder->getShippingAddress()->getCompany())){
            $content[1]['Address']['Company'] = $quoteOrOrder->getShippingAddress()->getCompany();
        }

        return $content;
    }
}