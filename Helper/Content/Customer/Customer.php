<?php

/**
 * Copyright (c) Ratepay GmbH
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace RatePAY\Payment\Helper\Content\Customer;

use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Framework\App\Helper\Context;

class Customer extends \Magento\Framework\App\Helper\AbstractHelper
{
    /**
     * @var Addresses
     */
    protected $rpContentCustomerAddressesHelper;

    /**
     * @var Contacts
     */
    protected $rpContentCustomerContactsHelper;

    /**
     * @var BankAccount
     */
    protected $rpContentCustomerBankAccountHelper;

    /**
     * @var \Magento\Checkout\Model\Session
     */
    protected $checkoutSession;

    /**
     * @var CustomerRepositoryInterface
     */
    protected $customerRepository;

    /**
     * @var \Magento\Customer\Model\Session
     */
    protected $customerSession;

    /**
     * @var \Magento\Store\Api\Data\StoreInterface
     */
    protected $store;

    /**
     * @var \RatePAY\Payment\Model\Environment\RemoteAddress
     */
    protected $remoteAddress;

    /**
     * @var \RatePAY\Payment\Helper\Data
     */
    protected $rpDataHelper;

    /**
     * Customer constructor.
     * @param Context                                               $context
     * @param \RatePAY\Payment\Helper\Content\Customer\Addresses    $rpContentCustomerAddressesHelper,
     * @param \RatePAY\Payment\Helper\Content\Customer\Contacts     $rpContentCustomerContactsHelper,
     * @param \RatePAY\Payment\Helper\Content\Customer\BankAccount  $rpContentCustomerBankAccountHelper,
     * @param \Magento\Checkout\Model\Session                       $checkoutSession,
     * @param CustomerRepositoryInterface                           $customerRepository,
     * @param \Magento\Customer\Model\Session                       $customerSession,
     * @param \Magento\Framework\Locale\Resolver                    $resolver
     * @param \RatePAY\Payment\Model\Environment\RemoteAddress      $remoteAddress
     * @param \RatePAY\Payment\Helper\Data                          $rpDataHelper
     */
    public function __construct(
        Context $context,
        \RatePAY\Payment\Helper\Content\Customer\Addresses $rpContentCustomerAddressesHelper,
        \RatePAY\Payment\Helper\Content\Customer\Contacts $rpContentCustomerContactsHelper,
        \RatePAY\Payment\Helper\Content\Customer\BankAccount $rpContentCustomerBankAccountHelper,
        \Magento\Checkout\Model\Session $checkoutSession,
        CustomerRepositoryInterface $customerRepository,
        \Magento\Customer\Model\Session $customerSession,
        \Magento\Framework\Locale\Resolver $resolver,
        \RatePAY\Payment\Model\Environment\RemoteAddress $remoteAddress,
        \RatePAY\Payment\Helper\Data $rpDataHelper
    ) {
        parent::__construct($context);
        $this->rpContentCustomerAddressesHelper = $rpContentCustomerAddressesHelper;
        $this->rpContentCustomerContactsHelper = $rpContentCustomerContactsHelper;
        $this->rpContentCustomerBankAccountHelper = $rpContentCustomerBankAccountHelper;
        $this->checkoutSession = $checkoutSession;
        $this->customerRepository = $customerRepository;
        $this->customerSession = $customerSession;
        $this->store = $resolver;
        $this->remoteAddress = $remoteAddress;
        $this->rpDataHelper = $rpDataHelper;
    }

    /**
     * Build Customer Block of Payment Request
     *
     * @param $quoteOrOrder
     * @return array
     */
    public function setCustomer($quoteOrOrder)
    {
        if ($this->customerSession->isLoggedIn()) {
            $dob = $this->customerRepository->getById($this->customerSession->getCustomerId())->getDob();
        } else {
            $dob = $this->checkoutSession->getRatepayDob();
        }

        $locale = substr($this->store->getLocale(), 0, 2);
        if (empty($locale)) {
            $locale = substr($this->store->getDefaultLocale(), 0, 2);
        }

        $content = [
                'Gender' => "U",
                //'Salutation' => "Mrs.",
                //'Title' => "Dr.",
                'FirstName' => $quoteOrOrder->getBillingAddress()->getFirstname(),
                //'MiddleName' => "J.",
                'LastName' => $quoteOrOrder->getBillingAddress()->getLastname(),
                //'NameSuffix' => "Sen.",
                'DateOfBirth' => $dob,
                'Language' => $locale,
                //'Nationality' => "DE",
                'IpAddress' => $this->getRemoteAddress(),
                'Addresses'=> $this->rpContentCustomerAddressesHelper->setAddresses($quoteOrOrder),
                'Contacts' => $this->rpContentCustomerContactsHelper->setContacts($quoteOrOrder)
        ];

        $bankAccount = $this->rpContentCustomerBankAccountHelper->getBankAccount($quoteOrOrder);
        if (!empty($bankAccount)) {
            $content['BankAccount'] = $bankAccount;
        }
        if (!empty($quoteOrOrder->getPayment()->getAdditionalInformation('rp_company'))) {
            $content['CompanyName'] = $quoteOrOrder->getPayment()->getAdditionalInformation('rp_company');
            $content['VatId'] = $quoteOrOrder->getPayment()->getAdditionalInformation('rp_vatid');
        }
        return $content;
    }

    /**
     * Returns the remote address of the current customer
     *
     * @return string
     */
    protected function getRemoteAddress()
    {
        if ((bool)$this->rpDataHelper->getRpConfigDataByPath("ratepay/general/proxy_mode") === true) {
            $this->remoteAddress->addHttpXForwardedHeader();
        }
        return $this->remoteAddress->getRemoteAddress();
    }
}
