<?php

namespace RatePAY\Payment\Controller\Checkout;

use Magento\Framework\App\Action\Context;

class InstallmentPlan extends \Magento\Framework\App\Action\Action
{
    /**
     * @var \Magento\Framework\Controller\Result\JsonFactory
     */
    protected $_resultJsonFactory;

    /**
     * @var \Magento\Checkout\Model\Session
     */
    protected $_checkoutSession;

    /**
     * @var \RatePAY\Payment\Model\LibraryModel
     */
    protected $_rpLibraryModel;

    /**
     * @var \RatePAY\Payment\Controller\LibraryController
     */
    protected $_rpLibraryController;

    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    protected $_scopeConfig;

    /**
     * InstallmentPlan constructor.
     * @param Context $context
     * @param \Magento\Framework\Controller\Result\JsonFactory $resultJsonFactory
     * @param \Magento\Checkout\Model\Session $checkoutSession
     * @param \RatePAY\Payment\Model\LibraryModel $rpLibraryModel
     * @param \RatePAY\Payment\Controller\LibraryController $rpLibraryController
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
     */
    public function __construct(
        Context $context,
        \Magento\Framework\Controller\Result\JsonFactory $resultJsonFactory,
        \Magento\Checkout\Model\Session $checkoutSession,
        \RatePAY\Payment\Model\LibraryModel $rpLibraryModel,
        \RatePAY\Payment\Controller\LibraryController $rpLibraryController,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
    ) {
        $this->_resultJsonFactory = $resultJsonFactory;
        $this->_checkoutSession = $checkoutSession;
        $this->_rpLibraryModel = $rpLibraryModel;
        $this->_rpLibraryController = $rpLibraryController;
        $this->_scopeConfig = $scopeConfig;
        parent::__construct($context);
    }

    /**
     * evaluate ajax request for installment plan
     *
     * @return \Magento\Framework\Controller\Result\Json
     */
    public function execute()
    {
        $response = [
            'status' => 'failure',
            'message' => ''
        ];

        $params = $this->getRequest()->getParams();

        $result = $this->_resultJsonFactory->create();

        if (!key_exists('order_amount', $params) ||
            !key_exists('calc_type', $params) ||
            !key_exists('calc_value', $params)) {
            $response['message'] = "calc data invalid";
            return $result->setData($response);
        }

        try {
            $installmentPlan = $this->getInstallmentPlan((float) $params['order_amount'], $params['calc_type'], (int) $params['calc_value']);
            if ($installmentPlan !== false) {
                $response['status'] = "success";
                $response['response'] = json_decode($installmentPlan);
            } else {
                $response['message'] = "quote not found";
            }
        } catch (\Exception $e) {
            $response['message'] = $e->getMessage();
        }
        return $result->setData($response);
    }

    /**
     * get installment plan
     *
     * @param $orderAmount
     * @param $calculationType
     * @param $calculationValue
     * @param null $template
     * @return mixed
     */
    private function getInstallmentPlan($orderAmount, $calculationType, $calculationValue, $template = null)
    {
        $quote = $this->_checkoutSession->getQuote();
        if (!$quote || !$quote->getId()) {
            return false;
        }

        $storeId = $quote->getStoreId();
        $scopeType = $quote->getStore()->getScopeType();
        $countryId = strtolower($quote->getBillingAddress()->getCountryId());
        $basePath = "payment/ratepay_" . $countryId . "_installment/";

        $profileId = $this->_scopeConfig->getValue($basePath . "profileId", $scopeType, $storeId);
        $securitycode = $this->_scopeConfig->getValue($basePath . "securityCode", $scopeType, $storeId);
        $sandbox = $this->_scopeConfig->getValue($basePath . "sandbox", $scopeType, $storeId);

        $configurationRequest = $this->_rpLibraryController->getInstallmentPlan($profileId, $securitycode, $sandbox, $orderAmount, $calculationType, $calculationValue, $template);

        // ToDo: failure handling

        $methodCode = $quote->getPayment()->getMethod();

        $installmentPlan = json_decode($configurationRequest, true);
        $this->_checkoutSession->setData('ratepayPaymentAmount_'.$methodCode, $installmentPlan['totalAmount']);
        $this->_checkoutSession->setData('ratepayInstallmentNumber_'.$methodCode, $installmentPlan['numberOfRatesFull']);
        $this->_checkoutSession->setData('ratepayInstallmentAmount_'.$methodCode, $installmentPlan['rate']);
        $this->_checkoutSession->setData('ratepayLastInstallmentAmount_'.$methodCode, $installmentPlan['lastRate']);
        $this->_checkoutSession->setData('ratepayInterestRate_'.$methodCode, $installmentPlan['interestRate']);

        return $configurationRequest;
    }
}
