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
     * Build Addresses Block of Payment Request
     *
     * @param $quoteOrOrder
     * @return array
     */
    public function setAddresses($quoteOrOrder)
    {
        $content = [
            [
                'Address' => [
                    'Type' => "billing",
                    //'Salutation' => "Mrs.",
                    //'FirstName' => "Alice",
                    //'LastName' => "Nobodyknows",
                    //'Company' => "Umbrella Corp.",
                    'Street' => $quoteOrOrder->getBillingAddress()->getData('street'),
                    //'StreetAdditional' => "SubLevel 27",
                    //'StreetNumber' => "12",
                    'ZipCode' => $quoteOrOrder->getBillingAddress()->getPostCode(),
                    'City' => $quoteOrOrder->getBillingAddress()->getCity(),
                    'CountryCode' => $quoteOrOrder->getBillingAddress()->getCountryId(),
                ]
            ], [
                'Address' => [
                    'Type' => "delivery",
                    //'Salutation' => "Mrs.",
                    'FirstName' => $quoteOrOrder->getShippingAddress()->getFirstname(),
                    'LastName' => $quoteOrOrder->getShippingAddress()->getLastname(),
                    //'Company' => "Umbrella Corp.",
                    'Street' => $quoteOrOrder->getShippingAddress()->getData('street'),
                    //'StreetAdditional' => "SubLevel 27",
                    //'StreetNumber' => "12",
                    'ZipCode' => $quoteOrOrder->getShippingAddress()->getPostCode(),
                    'City' => $quoteOrOrder->getShippingAddress()->getCity(),
                    'CountryCode' => $quoteOrOrder->getShippingAddress()->getCountryId(),
                ]
            ]
        ];
        return $content;
    }
}