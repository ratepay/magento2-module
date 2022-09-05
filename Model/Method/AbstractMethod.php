<?php
/**
 * Created by PhpStorm.
 * User: SebastianN
 * Date: 20.02.17
 * Time: 14:59
 */

namespace RatePAY\Payment\Model\Method;

use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Framework\App\Area;
use RatePAY\Payment\Controller\LibraryController;
use RatePAY\Payment\Helper\Validator;
use Magento\Framework\Exception\PaymentException;
use RatePAY\Payment\Model\Exception\DisablePaymentMethodException;
use RatePAY\Payment\Model\Handler\Cancel;
use RatePAY\Payment\Model\Handler\Capture;
use RatePAY\Payment\Model\Handler\Refund;
use RatePAY\Payment\Model\ResourceModel\HidePaymentType;
use Magento\Sales\Model\Order\Invoice;

abstract class AbstractMethod extends \Magento\Payment\Model\Method\AbstractMethod
{
    const BACKEND_SUFFIX = '_backend';

    /**
     * Payment method code
     *
     * @var string
     */
    protected $_code;

    /**
     * Availability option
     *
     * @var bool
     */
    protected $_isOffline = false;

    /**
     * @var bool
     */
    protected $_canAuthorize = false;

    /**
     * Determines if payment type can use refund mechanism
     *
     * @var bool
     */
    protected $_canRefund = true;

    /**
     * Determines if payment type can use capture mechanism
     *
     * @var bool
     */
    protected $_canCapture = true;

    /**
     * Determines if payment type can use partial captures
     *
     * @var bool
     */
    protected $_canCapturePartial = true;

    /**
     * Determines if payment type can use partial refunds
     *
     * @var bool
     */
    protected $_canRefundInvoicePartial = true;

    /**
     * Payment Method feature
     *
     * @var bool
     */
    protected $_canVoid = true;

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
     * @var \RatePAY\Payment\Helper\ProfileConfig
     */
    protected $profileConfig;

    /**
     * @var \Magento\Checkout\Model\Session
     */
    protected $checkoutSession;

    /**
     * @var \Magento\Backend\Model\Session\Quote
     */
    protected $backendCheckoutSession;

    /**
     * @var CustomerRepositoryInterface
     */
    protected $customerRepository;

    /**
     * @var \Magento\Customer\Model\Session
     */
    protected $customerSession;

    /**
     * Can be used to install a different block for backend orders
     *
     * @var string
     */
    protected $_adminFormBlockType = null;

    /**
     * Used to differentiate between frontend and backend payment methods
     *
     * @var bool
     */
    protected $isFrontendPaymentMethod = true;

    /**
     * Ratepay LibraryController
     *
     * @var \RatePAY\Payment\Controller\LibraryController
     */
    protected $libraryController;

    /**
     * HidePaymentType resource model
     *
     * @var \RatePAY\Payment\Model\ResourceModel\HidePaymentType
     */
    protected $hidePaymentType;

    /**
     * Ratepay capture handler
     *
     * @var Capture
     */
    protected $captureHandler;

    /**
     * Ratepay refund handler
     *
     * @var Refund
     */
    protected $refundHandler;

    /**
     * Ratepay cancel handler
     *
     * @var Cancel
     */
    protected $cancelHandler;

    /**
     * @var \RatePAY\Payment\Model\Entities\ProfileConfiguration|null
     */
    protected $profile = null;

    /**
     * @var \RatePAY\Payment\Model\Entities\ProfileConfiguration[]|null
     */
    protected $profiles = null;

    /**
     * @var \RatePAY\Payment\Service\V1\InstallmentPlan
     */
    protected $installmentPlan;

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
     * @param \RatePAY\Payment\Helper\ProfileConfig $profileConfig
     * @param \Magento\Checkout\Model\Session $checkoutSession
     * @param CustomerRepositoryInterface $customerRepository
     * @param \Magento\Customer\Model\Session $customerSession
     * @param \RatePAY\Payment\Controller\LibraryController $libraryController
     * @param \RatePAY\Payment\Model\ResourceModel\HidePaymentType $hidePaymentType
     * @param \RatePAY\Payment\Model\Handler\Capture $captureHandler
     * @param \RatePAY\Payment\Model\Handler\Refund $refundHandler
     * @param \RatePAY\Payment\Model\Handler\Cancel $cancelHandler
     * @param \Magento\Backend\Model\Session\Quote $backendCheckoutSession
     * @param \RatePAY\Payment\Service\V1\InstallmentPlan $installmentPlan
     * @param \Magento\Framework\Model\ResourceModel\AbstractResource|null $resource
     * @param \Magento\Framework\Data\Collection\AbstractDb|null $resourceCollection
     * @param array $data
     */
    function __construct(
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
        \RatePAY\Payment\Helper\Validator $rpValidator,
        \RatePAY\Payment\Helper\ProfileConfig $profileConfig,
        \Magento\Checkout\Model\Session $checkoutSession,
        CustomerRepositoryInterface $customerRepository,
        \Magento\Customer\Model\Session $customerSession,
        \RatePAY\Payment\Controller\LibraryController $libraryController,
        \RatePAY\Payment\Model\ResourceModel\HidePaymentType $hidePaymentType,
        \RatePAY\Payment\Model\Handler\Capture $captureHandler,
        \RatePAY\Payment\Model\Handler\Refund $refundHandler,
        \RatePAY\Payment\Model\Handler\Cancel $cancelHandler,
        \Magento\Backend\Model\Session\Quote $backendCheckoutSession,
        \RatePAY\Payment\Service\V1\InstallmentPlan $installmentPlan,
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
        $this->profileConfig = $profileConfig;
        $this->checkoutSession = $checkoutSession;
        $this->customerRepository = $customerRepository;
        $this->customerSession = $customerSession;
        $this->libraryController = $libraryController;
        $this->hidePaymentType = $hidePaymentType;
        $this->captureHandler = $captureHandler;
        $this->refundHandler = $refundHandler;
        $this->cancelHandler = $cancelHandler;
        $this->backendCheckoutSession = $backendCheckoutSession;
        $this->installmentPlan = $installmentPlan;
    }

    /**
     * call of ratepay requests moved to controller (Request.php)
     *
     * Authorize the transaction by calling PAYMENT_INIT, PAYMENT_REQUEST.
     *
     * @param \Magento\Payment\Model\InfoInterface $payment
     * @param float $amount
     * @return $this
     * @throws \Magento\Framework\Exception\PaymentException
     */
    public function authorize(\Magento\Payment\Model\InfoInterface $payment, $amount)
    {
        $order = $this->getQuoteOrOrder();

        $head = $this->_rpLibraryModel->getRequestHead($order);
        $oProfileConfig = $this->getMatchingProfile(null, $order->getStore()->getCode());
        $sandbox = $oProfileConfig->getSandboxMode();
        $company = $order->getBillingAddress()->getCompany();

        if (!$oProfileConfig->getProductData("b2b_?", $this->getCode(), true) && !empty($company)) {
            throw new PaymentException(__('b2b not allowed'));
        }

        $billingAddress = $order->getBillingAddress();
        $shippingAddress = $order->getShippingAddress();
        $diff = array_diff($this->rpDataHelper->getImportantAddressData($shippingAddress), $this->rpDataHelper->getImportantAddressData($billingAddress));

        if (!$oProfileConfig->getProductData("delivery_address_?", $this->getCode(), true) && count($diff)) {
            throw new PaymentException(__('ala not allowed'));
        }

        $resultInit = $this->libraryController->callPaymentInit($head, $order, $sandbox);
        if ($resultInit->isSuccessful()) {
            $payment->setAdditionalInformation('transactionId', $resultInit->getTransactionId());
            $payment->setTransactionId($resultInit->getTransactionId());
            $payment->setIsTransactionClosed(0);
            $this->handlePrePaymentRequestTasks($order);
            $head = $this->_rpLibraryModel->getRequestHead($order, 'PAYMENT_REQUEST', $resultInit);
            $content = $this->_rpLibraryModel->getRequestContent($order, 'PAYMENT_REQUEST');
            $resultRequest = $this->libraryController->callPaymentRequest($head, $content, $order, $sandbox);
            if (!$resultRequest->isSuccessful()) {
                $message = $resultRequest->getCustomerMessage();
                if (!$resultRequest->isRetryAdmitted()) {
                    $this->customerSession->setRatePayDeviceIdentToken(null);
                    $this->handleError($resultRequest, $order);

                    $sMethodCode = $order->getPayment()->getMethod();
                    $this->addPaymentMethodToDisabledMethods($sMethodCode);
                    throw new DisablePaymentMethodException(__($message), $sMethodCode); // RatePAY Error Message
                } else {
                    $sReason = $resultRequest->getReasonMessage();
                    if (empty($message) && stripos($sReason, "IBAN") !== false && stripos($sReason, "invalid") !== false) {
                        $message = __($sReason);
                    }
                    throw new PaymentException(__($message)); // RatePAY Error Message
                }
            }
            $payment->setAdditionalInformation('descriptor', $resultRequest->getDescriptor());
            $this->customerSession->setRatePayDeviceIdentToken(null);
            $order->setRatepaySandboxUsed((int)$sandbox);
            $order->setRatepayProfileId($oProfileConfig->getData('profile_id'));
            return $this;
        } else {
            $message = $this->formatMessage($resultInit->getReasonMessage());
            $this->customerSession->setRatePayDeviceIdentToken(null);
            throw new PaymentException(__($message)); // RatePAY Error Message
        }
    }

    /**
     * Can be extended by derived payment models to add certain mechanics PRE payment request
     *
     * @param  \Magento\Sales\Model\Order $oOrder
     * @return void
     */
    protected function handlePrePaymentRequestTasks(\Magento\Sales\Model\Order $oOrder)
    {
        // Hook for extension in the derived payment models
    }

    /**
     * Needed for Multishipping mode
     * Installment plan has to be recalculated for each sub-order
     *
     * @param  \Magento\Sales\Model\Order $oOrder
     * @return void
     */
    protected function recalculateInstallmentPlan(\Magento\Sales\Model\Order $oOrder)
    {
        $oQuote = $this->checkoutSession->getQuote();
        if ($oQuote->getIsMultiShipping()) {
            $calculationType = $this->checkoutSession->getData('ratepayInstallmentCalcType_'.$this->getCode());
            $calculationValue = $this->checkoutSession->getData('ratepayInstallmentCalcValue_'.$this->getCode());

            try {
                $this->installmentPlan->getInstallmentPlanFromRatepay($calculationType, $calculationValue, $oOrder->getGrandTotal(), $this->getCode());
            } catch (\Exception $exc) {
                error_log($exc->getMessage());
                throw $exc;
            }
        }
    }

    /**
     * Capture payment abstract method
     *
     * @param  \Magento\Payment\Model\InfoInterface $payment
     * @param  float                                $amount
     * @return AbstractMethod
     */
    public function capture(\Magento\Payment\Model\InfoInterface $payment, $amount)
    {
        $parentReturn = parent::capture($payment, $amount);
        if ($payment->getParentTransactionId()) {
            $this->captureHandler->executeRatepayCapture($payment, $amount);
        }
        return $parentReturn;
    }

    /**
     * Refund payment abstract method
     *
     * @param  InfoInterface $payment
     * @param  float         $amount
     * @return AbstractMethod
     */
    public function refund(\Magento\Payment\Model\InfoInterface $payment, $amount)
    {
        $parentReturn = parent::refund($payment, $amount);
        $this->refundHandler->executeRatepayRefund($payment, $amount);
        return $parentReturn;
    }


    /**
     * Cancel payment abstract method
     *
     * @param \Magento\Framework\DataObject|InfoInterface $payment
     * @return $this
     * @api
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     * @deprecated 100.2.0
     */
    public function cancel(\Magento\Payment\Model\InfoInterface $payment)
    {
        $parentReturn = parent::cancel($payment);
        $this->cancelHandler->executeRatepayCancel($payment);
        return $parentReturn;
    }

    /**
     * Void payment abstract method
     *
     * @param \Magento\Framework\DataObject|InfoInterface $payment
     * @return $this
     * @throws \Magento\Framework\Exception\LocalizedException
     * @api
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     * @deprecated 100.2.0
     */
    public function void(\Magento\Payment\Model\InfoInterface $payment)
    {
        $parentReturn = parent::void($payment);
        $this->cancelHandler->executeRatepayCancel($payment);
        return $parentReturn;
    }

    /**
     * @param object $resultRequest
     * @param \Magento\Sales\Model\Order $order
     * @return void
     */
    protected function handleError($resultRequest, $order)
    {
        $hideResultCodes = [703, 720, 721];
        if (in_array($resultRequest->getReasonCode(), $hideResultCodes) && !empty($order->getCustomerId())) {
            $this->hidePaymentType->addHiddenPaymentType($this->getCode(), $order->getCustomerId());
        }
    }

    /**
     * Adds current payment method to the array with disabled payment methods
     *
     * @param string $sPaymentMethod
     * @return void
     */
    protected function addPaymentMethodToDisabledMethods($sPaymentMethod)
    {
        $aDisabledMethods = $this->checkoutSession->getRatePayDisabledPaymentMethods();
        if (empty($aDisabledMethods)) {
            $aDisabledMethods = [];
        }
        $aDisabledMethods[] = $sPaymentMethod;
        $this->checkoutSession->setRatePayDisabledPaymentMethods($aDisabledMethods);
    }

    protected function isBackend()
    {
        if ($this->_appState->getAreaCode() == Area::AREA_ADMINHTML) {
            return true;
        }
        return false;
    }

    protected function isBackendMethod()
    {
        if (stripos($this->getCode(), self::BACKEND_SUFFIX) !== false) {
            return true;
        }
        return false;
    }

    /**
     * @param  \Magento\Quote\Api\Data\CartInterface|null $oQuote
     * @param  string|null $sStoreCode
     * @param  double $dGrandTotal
     * @param  string $sBillingCountryId
     * @param  string $sShippingCountryId
     * @param  string $currency
     * @param  int $installmentRuntime
     * @return \RatePAY\Payment\Model\Entities\ProfileConfiguration|false
     */
    public function getMatchingProfile(\Magento\Quote\Api\Data\CartInterface $oQuote = null, $sStoreCode = null, $dGrandTotal = null, $sBillingCountryId = null, $sShippingCountryId = null, $currency = null, $installmentRuntime = null)
    {
        if ($this->profile === null) {
            if ($oQuote === null) {
                if ($this->isBackendMethod() === false) {
                    $oQuote = $this->checkoutSession->getQuote();
                } else {
                    $oQuote = $this->backendCheckoutSession->getQuote();
                }
            }
            if ($sStoreCode === null) {
                $sStoreCode = $oQuote->getStore()->getCode();
            }

            $this->profile = $this->profileConfig->getMatchingProfile($oQuote, $this->getCode(), $sStoreCode, $dGrandTotal, $sBillingCountryId, $sShippingCountryId, $currency);
        }
        return $this->profile;
    }

    /**
     * @param  \Magento\Quote\Api\Data\CartInterface|null $oQuote
     * @param  string|null $sStoreCode
     * @param  double $dGrandTotal
     * @param  string $sBillingCountryId
     * @param  string $sShippingCountryId
     * @param  string $sCurrency
     * @return \RatePAY\Payment\Model\Entities\ProfileConfiguration[]|false
     */
    public function getMatchingProfiles(\Magento\Quote\Api\Data\CartInterface $oQuote = null, $sStoreCode = null, $dGrandTotal = null, $sBillingCountryId = null, $sShippingCountryId = null, $sCurrency = null)
    {
        if ($this->profiles === null) {
            if ($oQuote === null) {
                if ($this->isBackendMethod() === false) {
                    $oQuote = $this->checkoutSession->getQuote();
                } else {
                    $oQuote = $this->backendCheckoutSession->getQuote();
                }
            }
            if ($sStoreCode === null) {
                $sStoreCode = $oQuote->getStore()->getCode();
            }

            $this->profiles = $this->profileConfig->getAllMatchingProfiles($oQuote, $this->getCode(), $sStoreCode, $dGrandTotal, $sBillingCountryId, $sShippingCountryId, $sCurrency);
        }
        return $this->profiles;
    }

    /**
     * Check if payment method is available
     *
     * 1) If quote is not null
     * 2) If a session variable is set, which indicates that the customer was declined by RatePAY within the PAYMENT_REQUEST
     * 3) If the basket amount is less then min order total amount or more than max order total amount
     * 4) If shipping address doesnt equals billing address
     * 5) If b2b is not allowed and billing address contains an company name
     *
     * @param \Magento\Quote\Api\Data\CartInterface|null $quote
     * @return bool
     */
    public function isAvailable(\Magento\Quote\Api\Data\CartInterface $quote = null)
    {
        if (is_null($quote)) {
            return false;
        }

        if ($this->isBackend() === true && $this->isBackendMethod() === false) {
            return false;
        } elseif ($this->isBackend() === false && $this->isBackendMethod() === true) {
            return false;
        }

        if (parent::isAvailable($quote) == false) {
            return false;
        }

        if (!$this->rpDataHelper->getRpConfigDataForQuote($this->_code, 'active', $quote)) {
            return false;
        }

        $oProfile = $this->getMatchingProfile($quote);
        if ($oProfile === false) {
            return false;
        }
        return true;
    }

    /**
     * Retrieve information from payment configuration
     *
     * @param string $field
     * @param int|string|null|\Magento\Store\Model\Store $storeId
     *
     * @return mixed
     * @deprecated 100.2.0
     */
    public function getConfigData($field, $storeId = null)
    {
        if ($field == "min_order_total") {
            $oProfile = $this->getMatchingProfile();
            return $oProfile ? $oProfile->getProductData("tx_limit_?_min", $this->getCode(), true) : null;
        } elseif ($field == "max_order_total") {
            $oProfile = $this->getMatchingProfile();
            return $oProfile ? $oProfile->getProductData("tx_limit_?_max", $this->getCode(), true) : null;
        }
        return parent::getConfigData($field, $storeId);
    }

    /**
     * To check billing country is allowed for the payment method
     *
     * @param                                       $country
     * @param \Magento\Quote\Api\Data\CartInterface $quote
     * @return bool
     */
    public function canUseForCountryDelivery($country, \Magento\Quote\Api\Data\CartInterface $quote = null)
    {
        $availableCountries = explode(',', $this->getMatchingProfile()->getData("country_code_delivery"));
        if (!in_array($country, $availableCountries)) {
            return false;
        }
        return true;
    }

    /**
     * @param  object $additionalData
     * @param  string $methodCode
     * @return void
     */
    protected function handleInstallmentSessionParams($additionalData, $methodCode)
    {
        if ($additionalData->getData('rp_'.$methodCode.'_totalamount')) {
            $this->checkoutSession->setData('ratepayPaymentAmount_'.$methodCode, $additionalData->getData('rp_'.$methodCode.'_totalamount'));
            $this->checkoutSession->setData('ratepayInstallmentNumber_'.$methodCode, $additionalData->getData('rp_'.$methodCode.'_numberofratesfull'));
            $this->checkoutSession->setData('ratepayInstallmentAmount_'.$methodCode, $additionalData->getData('rp_'.$methodCode.'_rate'));
            $this->checkoutSession->setData('ratepayLastInstallmentAmount_'.$methodCode, $additionalData->getData('rp_'.$methodCode.'_lastrate'));
            $this->checkoutSession->setData('ratepayInterestRate_'.$methodCode, $additionalData->getData('rp_'.$methodCode.'_interestrate'));
        }
    }

    /**
     * @param \Magento\Framework\DataObject $data
     * @return $this
     */
    public function assignData(\Magento\Framework\DataObject $data)
    {
        $order = $this->getQuoteOrOrder();
        $infoInstance = $order->getPayment();

        // clear the data first, to refresh it later in this method
        $infoInstance->unsAdditionalInformation('rp_company');
        $infoInstance->unsAdditionalInformation('rp_vatid');
        $infoInstance->unsAdditionalInformation('rp_iban_reference');
        $infoInstance->unsAdditionalInformation('rp_accountholder');
        $infoInstance->unsAdditionalInformation('rp_iban');

        if (!$data instanceof \Magento\Framework\DataObject) {
            $data = new \Magento\Framework\DataObject($data);
        }

        $additionalData = $data->getData(\Magento\Quote\Api\Data\PaymentInterface::KEY_ADDITIONAL_DATA);
        if (!is_object($additionalData)) {
            $additionalData = new \Magento\Framework\DataObject($additionalData ?: []);
        }

        if ($additionalData->getRpDobDay() === null && $additionalData->getRpDobMonth() === null && $additionalData->getRpDobYear() === null) {
            // this doesn't seem to be a assignData call after order is created - so skip following validations
            return $this;
        }

        $company = $order->getBillingAddress()->getCompany();
        if (!empty($additionalData->getRpCompany())) {
            $company = $additionalData->getRpCompany();
        }

        if (empty($company)) {
            if (!$this->customerSession->isLoggedIn()) {
                $this->rpValidator->validateDob($additionalData);
            } elseif ($this->customerRepository->getById($this->customerSession->getCustomerId())->getDob() == null) {
                $this->rpValidator->validateDob($additionalData);
            }
        } else {
            $infoInstance->setAdditionalInformation('rp_company', $company);
        }

        if (!$order->getBillingAddress()->getTelephone()) {
            $this->rpValidator->validatePhone($additionalData);
        }

        $methodCode = $infoInstance->getMethod();

        $sIban = $additionalData->getRpIban();
        if ($this instanceof Directdebit || !empty($sIban) || $additionalData->getRpDirectdebit() == "1") { // getRpIban used for installments
            $this->rpValidator->validateIban($additionalData);
            $infoInstance->setAdditionalInformation('rp_iban', $sIban);
        }

        if ($additionalData->getRpDirectdebit() !== null) {
            $infoInstance->setAdditionalInformation('rp_directdebit', filter_var($additionalData->getRpDirectdebit(), FILTER_VALIDATE_BOOLEAN));
        }

        if (!empty($additionalData->getRpAccountholder())) {
            $infoInstance->setAdditionalInformation('rp_accountholder', $additionalData->getRpAccountholder());
        }

        $installmentMethods = [
            'ratepay_installment',
            'ratepay_installment0',
            'ratepay_installment_backend',
            'ratepay_installment0_backend',
        ];
        if (in_array($methodCode, $installmentMethods)) {
            $this->handleInstallmentSessionParams($additionalData, $methodCode);
        }

        if (!empty($additionalData->getRpVatid())) {
            $infoInstance->setAdditionalInformation('rp_vatid', $additionalData->getRpVatid());
        }

        return $this;
    }

    /**
     * Returns profile id for current payment method
     *
     * @param string|null $storeCode
     * @return string
     */
    protected function getProfileId($storeCode = null)
    {
        return $this->getMatchingProfile()->getData("profile_id");
    }

    /**
     * @param $message
     * @param $order
     * @return string
     */
    public function formatMessage($message)
    {
        if (empty($message)) {
            $message = __('Automated Data Procedure Error');
        }
        return strip_tags($message);
    }

    public function getIsB2BModeEnabled($oQuote)
    {
        $dGrandTotal = $oQuote->getGrandTotal();
        $blB2BEnabled = (bool)$this->getMatchingProfile($oQuote)->getProductData("b2b_?", $this->getCode(), true);
        $dB2BMax = $this->getMatchingProfile($oQuote)->getProductData("tx_limit_?_max_b2b", $this->getCode(), true);
        if ($blB2BEnabled === true && ($dGrandTotal === null || ($dGrandTotal <= $dB2BMax))) {
            return true;
        }
        return false;
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

    /**
     * Generates allowed months
     *
     * @param double $basketAmount
     * @return array
     */
    public function getAllowedMonths($basketAmount)
    {
        return [];
    }

    /**
     * Returns allowed runtimes for given profile
     *
     * @param  double                                               $basketAmount
     * @param  \RatePAY\Payment\Model\Entities\ProfileConfiguration $oProfile
     * @return array
     */
    public function getAllowedMonthsForProfile($basketAmount, $oProfile)
    {
        return [];
    }

    /**
     * Retrieve block type for method form generation
     *
     * @return string
     */
    public function getFormBlockType()
    {
        if ($this->_appState->getAreaCode() == \Magento\Framework\App\Area::AREA_ADMINHTML && $this->_adminFormBlockType !== null) {
            return $this->_adminFormBlockType;
        }
        return $this->_formBlockType;
    }
}
