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

namespace RatePAY\Payment\Model\Method;

use Magento\Customer\Api\CustomerRepositoryInterface;
use RatePAY\Payment\Controller\LibraryController;
use RatePAY\Payment\Helper\Validator;
use Magento\Framework\Exception\PaymentException;

abstract class AbstractMethod extends \Magento\Payment\Model\Method\AbstractMethod
{
    /**
     * Payment method code.
     *
     * @var string
     */
    protected $_code;

    /**
     * Availability option.
     *
     * @var bool
     */
    protected $_isOffline = true;

    /**
     * @var bool
     */
    protected $_canAuthorize = false;

    /**
     * @var \RatePAY\Payment\Model\LibraryModel
     */
    protected $_rpLibraryModel;

    /**
     * @var \RatePAY\Payment\Model\Session
     */
    protected $rpSession;

    /**
     * @var \RatePAY\Payment\Helper\Data
     */
    protected $rpDataHelper;

    /**
     * @var \RatePAY\Payment\Helper\Validator
     */
    protected $rpValidator;

    /**
     * @var \Magento\Checkout\Model\Session
     */
    protected $checkoutSession;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $storeManager;

    /**
     * @var CustomerRepositoryInterface
     */
    protected $customerRepository;

    /**
     * @var \Magento\Customer\Model\Session
     */
    protected $customerSession;

    /**
     * AbstractMethod constructor.
     * @param \Magento\Framework\Model\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Framework\Api\ExtensionAttributesFactory $extensionFactory
     * @param \Magento\Framework\Api\AttributeValueFactory $customAttributeFactory
     * @param \Magento\Payment\Helper\Data $paymentData
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
     * @param \Magento\Payment\Model\Method\Logger $logger
     * @param \RatePAY\Payment\Model\LibraryModel $rpLibraryModel
     * @param \RatePAY\Payment\Model\Session $rpSession
     * @param \RatePAY\Payment\Helper\Data $rpDataHelper
     * @param Validator $rpValidator
     * @param \Magento\Checkout\Model\Session $checkoutSession
     * @param CustomerRepositoryInterface $customerRepository
     * @param \Magento\Customer\Model\Session $customerSession
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Magento\Framework\Model\ResourceModel\AbstractResource|null $resource
     * @param \Magento\Framework\Data\Collection\AbstractDb|null $resourceCollection
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\Model\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Api\ExtensionAttributesFactory $extensionFactory,
        \Magento\Framework\Api\AttributeValueFactory $customAttributeFactory,
        \Magento\Payment\Helper\Data $paymentData,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Payment\Model\Method\Logger $logger,
        \RatePAY\Payment\Model\LibraryModel $rpLibraryModel,
        \RatePAY\Payment\Model\Session $rpSession,
        \RatePAY\Payment\Helper\Data $rpDataHelper,
        Validator $rpValidator,
        \Magento\Checkout\Model\Session $checkoutSession,
        CustomerRepositoryInterface $customerRepository,
        \Magento\Customer\Model\Session $customerSession,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Framework\Model\ResourceModel\AbstractResource $resource = null,
        \Magento\Framework\Data\Collection\AbstractDb $resourceCollection = null,
        array $data = []
    ) {
        parent::__construct(
            $context,
            $registry,
            $extensionFactory,
            $customAttributeFactory,
            $paymentData,
            $scopeConfig,
            $logger,
            $resource,
            $resourceCollection,
            $data
        );

        $this->_rpLibraryModel = $rpLibraryModel;
        $this->rpSession = $rpSession;
        $this->rpDataHelper = $rpDataHelper;
        $this->rpValidator = $rpValidator;
        $this->storeManager = $storeManager;
        $this->checkoutSession = $checkoutSession;
        $this->customerRepository = $customerRepository;
        $this->customerSession = $customerSession;
    }

    /**
     * call of ratepay requests moved to controller (Request.php).
     *
     * Authorize the transaction by calling PAYMENT_INIT, PAYMENT_REQUEST.
     *
     * @param \Magento\Payment\Model\InfoInterface $payment
     * @param float                                $amount
     *
     * @throws \Magento\Framework\Exception\PaymentException
     */
    public function authorize(\Magento\Payment\Model\InfoInterface $payment, $amount)
    {
        $order = $this->getQuoteOrOrder();
        $head = $this->_rpLibraryModel->getRequestHead($order);
        $sandbox = (bool) $this->rpDataHelper->getRpConfigData($this->_code, 'sandbox', $this->storeManager->getStore()->getId());
        $company = $order->getBillingAddress()->getCompany();
        if (!$this->rpDataHelper->getRpConfigData($this->_code, 'b2b', $this->storeManager->getStore()->getId()) && !empty($company)) {
            throw new PaymentException(__('b2b not allowed'));
        }

        $billingAddress = $order->getBillingAddress();
        $shippingAddress = $order->getShippingAddress();
        $diff = array_diff($this->rpDataHelper->getImportantAddressData($shippingAddress), $this->rpDataHelper->getImportantAddressData($billingAddress));

        if (!$this->rpDataHelper->getRpConfigData($this->_code, 'delivery_address', $this->storeManager->getStore()->getId()) && count($diff)) {
            throw new PaymentException(__('ala not allowed'));
        }

        $resultInit = LibraryController::callPaymentInit($head, $sandbox);
        if ($resultInit->isSuccessful()) {
            $payment->setAdditionalInformation('transactionId', $resultInit->getTransactionId());
            $head = $this->_rpLibraryModel->getRequestHead($order, 'PAYMENT_REQUEST', $resultInit);
            $content = $this->_rpLibraryModel->getRequestContent($order, 'PAYMENT_REQUEST');
            $resultRequest = LibraryController::callPaymentRequest($head, $content, $sandbox);
            if (!$resultRequest->isSuccessful()) {
                if (!$resultRequest->isRetryAdmitted()) {
                    $this->checkoutSession->setRatepayMethodHide(true);
                    $message = $this->formatMessage($resultRequest->getCustomerMessage());
                    $this->customerSession->setRatePayDeviceIdentToken(null);
                    throw new PaymentException(__($message)); // RatePAY Error Message
                } else {
                    $message = $this->formatMessage($resultRequest->getCustomerMessage());
                    throw new PaymentException(__($message)); // RatePAY Error Message
                }
            }
            $payment->setAdditionalInformation('descriptor', $resultRequest->getDescriptor());
            $this->checkoutSession->setRatepayMethodHide(false);
            $this->customerSession->setRatePayDeviceIdentToken(null);

            return $this;
        } else {
            $message = $this->formatMessage($resultInit->getReasonMessage());
            $this->customerSession->setRatePayDeviceIdentToken(null);
            throw new PaymentException(__($message)); // RatePAY Error Message
        }
    }

    /**
     * Check if payment method is available.
     *
     * 1) If quote is not null
     * 2) If a session variable is set, which indicates that the customer was declined by RatePAY within the PAYMENT_REQUEST
     * 3) If the basket amount is less then min order total amount or more than max order total amount
     * 4) If shipping address doesnt equals billing address
     * 5) If b2b is not allowed and billing address contains an company name
     *
     * @param null|\Magento\Quote\Api\Data\CartInterface $quote
     *
     * @return bool
     */
    public function isAvailable(\Magento\Quote\Api\Data\CartInterface $quote = null)
    {
        if (is_null($quote)) {
            return false;
        }

        if (parent::isAvailable($quote) === false) {
            return false;
        }

        $ratepayMethodHide = $this->checkoutSession->getRatepayMethodHide();
        if ($ratepayMethodHide === true) {
            return false;
        }

        if (!$this->rpDataHelper->getRpConfigData($this->_code, 'active', $this->storeManager->getStore()->getId())) {
            return false;
        }

        $totalAmount = $quote->getGrandTotal();
        $minAmount = $this->rpDataHelper->getRpConfigData($this->_code, 'min_order_total', $this->storeManager->getStore()->getId());
        $maxAmount = $this->rpDataHelper->getRpConfigData($this->_code, 'max_order_total', $this->storeManager->getStore()->getId());

        if ($totalAmount < $minAmount || $totalAmount > $maxAmount) {
            return false;
        }

        $shippingAddress = $quote->getShippingAddress();
        if (!$this->canUseForCountryDelivery($shippingAddress->getCountryId())) {
            return false;
        }

        return true;
    }

    /**
     * To check billing country is allowed for the payment method.
     *
     * @param $country
     *
     * @return bool
     */
    public function canUseForCountryDelivery($country)
    {
        $availableCountries = explode(',', $this->rpDataHelper->getRpConfigData($this->_code, 'specificcountry_delivery', $this->storeManager->getStore()->getId()));
        if (!in_array($country, $availableCountries, true)) {
            return false;
        }

        return true;
    }

    /**
     * @param \Magento\Framework\DataObject $data
     *
     * @return $this
     */
    public function assignData(\Magento\Framework\DataObject $data)
    {
        $order = $this->getQuoteOrOrder();

        if (!$data instanceof \Magento\Framework\DataObject) {
            $data = new \Magento\Framework\DataObject($data);
        }

        $additionalData = $data->getData(\Magento\Quote\Api\Data\PaymentInterface::KEY_ADDITIONAL_DATA);
        if (!is_object($additionalData)) {
            $additionalData = new \Magento\Framework\DataObject($additionalData ?: []);
        }
        if (!$this->customerSession->isLoggedIn()) {
            $this->rpValidator->validateDob($additionalData);
        } else {
            if ($this->customerRepository->getById($this->customerSession->getCustomerId())->getDob() === null) {
                $this->rpValidator->validateDob($additionalData);
            }
        }

        if (!$order->getBillingAddress()->getTelephone()) {
            $this->rpValidator->validatePhone($additionalData);
        }

        if ($this->getQuoteOrOrder()->getPayment()->getMethod() === 'ratepay_de_directdebit' ||
            $this->getQuoteOrOrder()->getPayment()->getMethod() === 'ratepay_at_directdebit' ||
            $this->getQuoteOrOrder()->getPayment()->getMethod() === 'ratepay_nl_directdebit' ||
            $this->getQuoteOrOrder()->getPayment()->getMethod() === 'ratepay_be_directdebit') {
            $this->rpValidator->validateIban($additionalData);
        }

        return $this;
    }

    /**
     * @param $message
     * @param $order
     *
     * @return string
     */
    public function formatMessage($message)
    {
        if (empty($message)) {
            $message = __('Automated Data Procedure Error');
        }

        if (strpos($message, 'zusaetzliche-geschaeftsbedingungen-und-datenschutzhinweis') !== false) {
            $message = $message."\n\n".$this->rpDataHelper->getRpConfigData($this->_code, 'privacy_policy', $this->storeManager->getStore()->getId());
        }

        return strip_tags($message);
    }

    /**
     * @return \Magento\Sales\Model\Order
     */
    public function getQuoteOrOrder()
    {
        $paymentInfo = $this->getInfoInstance();

        if ($paymentInfo instanceof \Magento\Sales\Model\Order\Payment) {
            $quoteOrOrder = $paymentInfo->getOrder();
        } else {
            $quoteOrOrder = $paymentInfo->getQuote();
        }

        return $quoteOrOrder;
    }
}
