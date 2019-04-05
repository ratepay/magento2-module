<?php
/**
 * Created by PhpStorm.
 * User: SebastianN
 * Date: 09.02.17
 * Time: 13:33
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
     * @param Context $context
     * @param Addresses $rpContentCustomerAddressesHelper
     * @param Contacts $rpContentCustomerContactsHelper
     */
    public function __construct(Context $context,
                                \RatePAY\Payment\Helper\Content\Customer\Addresses $rpContentCustomerAddressesHelper,
                                \RatePAY\Payment\Helper\Content\Customer\Contacts $rpContentCustomerContactsHelper,
                                \RatePAY\Payment\Helper\Content\Customer\BankAccount $rpContentCustomerBankAccountHelper,
                                \Magento\Checkout\Model\Session $checkoutSession,
                                CustomerRepositoryInterface $customerRepository,
                                \Magento\Customer\Model\Session $customerSession,
                                \Magento\Framework\Locale\Resolver $resolver)
    {
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
     * Build Customer Block of Payment Request
     *
     * @param $quoteOrOrder
     * @return array
     */
    public function setCustomer($quoteOrOrder)
    {
        $id = $quoteOrOrder->getPayment()->getMethod();
        $id = $this->_getRpMethodWithoutCountry($id);

        if($this->customerSession->isLoggedIn()) {
            $dob = $this->customerRepository->getById($this->customerSession->getCustomerId())->getDob();
        } else {
            $dob = $this->checkoutSession->getRatepayDob();
        }

        $locale = substr($this->store->getLocale(),0,2);
        if (empty($locale)) {
            $locale = substr($this->store->getDefaultLocale(),0,2);
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
                'IpAddress' => $this->_remoteAddress->getRemoteAddress(),
                'Addresses'=> $this->rpContentCustomerAddressesHelper->setAddresses($quoteOrOrder),
                'Contacts' => $this->rpContentCustomerContactsHelper->setContacts($quoteOrOrder)

        ];

        $bankAccount = $this->rpContentCustomerBankAccountHelper->getBankAccount($quoteOrOrder);
        if(!empty($bankAccount)){
            $content['BankAccount'] = $bankAccount;
        }
        if (!empty($quoteOrOrder->getBillingAddress()->getCompany())) {
            $content['CompanyName'] = $quoteOrOrder->getBillingAddress()->getCompany();
            $content['VatId'] = $quoteOrOrder->getBillingAddress()->getVatId();
        }

        return $content;
    }

    /**
     * @param $id
     * @return mixed
     */
    private function _getRpMethodWithoutCountry($id) {
        $id = str_replace('_de', '', $id);
        $id = str_replace('_at', '', $id);
        $id = str_replace('_ch', '', $id);
        $id = str_replace('_nl', '', $id);
        $id = str_replace('_be', '', $id);

        return $id;
    }
}
