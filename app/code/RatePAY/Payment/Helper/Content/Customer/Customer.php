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
     * Customer constructor.
     *
     * @param Context   $context
     * @param Addresses $rpContentCustomerAddressesHelper
     * @param Contacts  $rpContentCustomerContactsHelper
     */
    public function __construct(
        Context $context,
                                Addresses $rpContentCustomerAddressesHelper,
                                Contacts $rpContentCustomerContactsHelper,
                                BankAccount $rpContentCustomerBankAccountHelper,
                                \Magento\Checkout\Model\Session $checkoutSession,
                                CustomerRepositoryInterface $customerRepository,
                                \Magento\Customer\Model\Session $customerSession,
                                \Magento\Framework\Locale\Resolver $resolver
    ) {
        parent::__construct($context);
        $this->_remoteAddress = $context->getRemoteAddress();
        $this->rpContentCustomerAddressesHelper = $rpContentCustomerAddressesHelper;
        $this->rpContentCustomerContactsHelper = $rpContentCustomerContactsHelper;
        $this->rpContentCustomerBankAccountHelper = $rpContentCustomerBankAccountHelper;
        $this->checkoutSession = $checkoutSession;
        $this->customerRepository = $customerRepository;
        $this->customerSession = $customerSession;
        $this->store = $resolver;
    }

    /**
     * Build Customer Block of Payment Request.
     *
     * @param $quoteOrOrder
     *
     * @return array
     */
    public function setCustomer($quoteOrOrder)
    {
        $id = $quoteOrOrder->getPayment()->getMethod();
        $id = $this->_getRpMethodWithoutCountry($id);

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
            'Gender' => 'U',
            //'Salutation' => "Mrs.",
            //'Title' => "Dr.",
            'FirstName' => $quoteOrOrder->getBillingAddress()->getFirstname(),
            //'MiddleName' => "J.",
            'LastName' => $quoteOrOrder->getBillingAddress()->getLastname(),
            //'NameSuffix' => "Sen.",
            'DateOfBirth' => $dob,
            'Language' => $locale,
            //'Nationality' => "DE",
            'IpAddress' => $this->_remoteAddress->getRemoteAddress(),
            'Addresses' => $this->rpContentCustomerAddressesHelper->setAddresses($quoteOrOrder),
            'Contacts' => $this->rpContentCustomerContactsHelper->setContacts($quoteOrOrder),
        ];
        if ($id === 'ratepay_directdebit') {
            $content['BankAccount'] = $this->rpContentCustomerBankAccountHelper->setBankAccount($quoteOrOrder);
        }
        if (!empty($quoteOrOrder->getBillingAddress()->getCompany())) {
            $content['CompanyName'] = $quoteOrOrder->getBillingAddress()->getCompany();
        }

        return $content;
    }

    /**
     * @param $id
     *
     * @return mixed
     */
    private function _getRpMethodWithoutCountry($id)
    {
        $id = str_replace('_de', '', $id);
        $id = str_replace('_at', '', $id);
        $id = str_replace('_ch', '', $id);
        $id = str_replace('_nl', '', $id);

        return str_replace('_be', '', $id);
    }
}
