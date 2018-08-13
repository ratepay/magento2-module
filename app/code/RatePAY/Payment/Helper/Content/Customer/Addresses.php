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

namespace RatePAY\Payment\Helper\Content\Customer;

use Magento\Framework\App\Helper\Context;

class Addresses extends \Magento\Framework\App\Helper\AbstractHelper
{
    /**
     * Addresses constructor.
     *
     * @param Context $context
     */
    public function __construct(Context $context)
    {
        parent::__construct($context);
    }

    /**
     * Build Addresses Block of Payment Request.
     *
     * @param $quoteOrOrder
     *
     * @return array
     */
    public function setAddresses($quoteOrOrder)
    {
        $content = [
            [
                'Address' => [
                    'Type' => 'billing',
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
                ],
            ], [
                'Address' => [
                    'Type' => 'delivery',
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
                ],
            ],
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
