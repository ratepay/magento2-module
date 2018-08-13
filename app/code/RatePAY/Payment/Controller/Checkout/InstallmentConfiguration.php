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

namespace RatePAY\Payment\Controller\Checkout;

use Magento\Framework\App\Action\Context;

class InstallmentConfiguration extends \Magento\Framework\App\Action\Action
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
     * InstallmentConfiguration constructor.
     *
     * @param Context                                            $context
     * @param \Magento\Framework\Controller\Result\JsonFactory   $resultJsonFactory
     * @param \Magento\Checkout\Model\Session                    $checkoutSession
     * @param \RatePAY\Payment\Model\LibraryModel                $rpLibraryModel
     * @param \RatePAY\Payment\Controller\LibraryController      $rpLibraryController
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
     * evaluate ajax request for installment configuration.
     *
     * @return $this
     */
    public function execute()
    {
        $response = [
            'status' => 'failure',
            'message' => '',
        ];

        $params = $this->getRequest()->getParams();

        $result = $this->_resultJsonFactory->create();

        if (!key_exists('order-amount', $params)) {
            $response['message'] = 'order amount invalid';

            return $result->setData($response);
        }

        $installmentConfiguration = $this->getInstallmentConfiguration((float) $params['order-amount']);

        /*if (!is_array($requestResponse)) {
            $response['message'] = "country-code invalid";
            return $result->setData($response);
        }*/

        $response['status'] = 'success';
        $response['response'] = json_decode($installmentConfiguration);

        return $result->setData($response);
    }

    /**
     * get installment configuration.
     *
     * @param $orderAmount
     * @param null $template
     *
     * @return mixed
     */
    private function getInstallmentConfiguration($orderAmount, $template = null)
    {
        $quote = $this->_checkoutSession->getQuote();

        $storeId = $quote->getStoreId();
        $scopeType = $quote->getStore()->getScopeType();
        $countryId = strtolower($quote->getBillingAddress()->getCountryId());
        $basePath = 'payment/ratepay_'.$countryId.'_installment/';

        $profileId = $this->_scopeConfig->getValue($basePath.'profileId', $scopeType, $storeId);
        $securitycode = $this->_scopeConfig->getValue($basePath.'securityCode', $scopeType, $storeId);
        $sandbox = $this->_scopeConfig->getValue($basePath.'sandbox', $scopeType, $storeId);

        return $this->_rpLibraryController->getInstallmentConfiguration($profileId, $securitycode, $sandbox, $orderAmount, $template);
        // ToDo: failure handling
    }
}
