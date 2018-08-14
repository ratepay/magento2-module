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

namespace RatePAY\Payment\Controller\Adminhtml\System\Config;

use Magento\Framework\App\Action\Context;

class ProfileRequest extends \Magento\Framework\App\Action\Action
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
     * @var \Magento\Config\Model\ResourceModel\Config
     */
    protected $_config;

    /**
     * @var \RatePAY\Payment\Model\LibraryModel
     */
    protected $_rpLibraryModel;

    /**
     * @var \RatePAY\Payment\Controller\LibraryController
     */
    protected $_rpLibraryController;

    /**
     * @var \RatePAY\Payment\Helper\Payment
     */
    protected $_rpPaymentHelper;

    /**
     * ProfileRequest constructor.
     *
     * @param Context                                          $context
     * @param \Magento\Framework\Controller\Result\JsonFactory $resultJsonFactory
     * @param \Magento\Checkout\Model\Session                  $checkoutSession
     * @param \Magento\Config\Model\ResourceModel\Config       $config
     * @param \RatePAY\Payment\Model\LibraryModel              $rpLibraryModel
     * @param \RatePAY\Payment\Controller\LibraryController    $rpLibraryController
     * @param \RatePAY\Payment\Helper\Payment                  $rpPaymentHelper
     */
    public function __construct(
        Context $context,
                                \Magento\Framework\Controller\Result\JsonFactory $resultJsonFactory,
                                \Magento\Checkout\Model\Session $checkoutSession,
                                \Magento\Config\Model\ResourceModel\Config $config,
                                \RatePAY\Payment\Model\LibraryModel $rpLibraryModel,
                                \RatePAY\Payment\Controller\LibraryController $rpLibraryController,
                                \RatePAY\Payment\Helper\Payment $rpPaymentHelper
    ) {
        parent::__construct($context);

        $this->_resultJsonFactory = $resultJsonFactory;
        $this->_checkoutSession = $checkoutSession;
        $this->_config = $config;
        $this->_rpLibraryModel = $rpLibraryModel;
        $this->_rpLibraryController = $rpLibraryController;
        $this->_rpPaymentHelper = $rpPaymentHelper;
    }

    /**
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

        if (!key_exists('profile_id', $params) ||
            !key_exists('security_code', $params)) {
            $response['message'] = 'credentials needed';

            return $result->setData($response);
        }

        $head = $this->getHead($params['profile_id'], $params['security_code']);
        $profileRequest = $this->_rpLibraryController->callProfileRequest($head, (bool) $params['sandbox']);
        $method = $params['method'];

        if ($profileRequest->isSuccessful()) {
            $prResult = $profileRequest->getResult();
            $method = $this->_getRpMethod($method);
            $product = $this->_rpPaymentHelper->convertMethodToProduct($this->_getRpMethodWithoutCountry($method));
            $country = $this->_getRpCountry($method);

            if (!is_array($prResult)) {
                $this->_config->saveConfig('payment/'.$method.'/status', 0, 'default', 0);

                return __('Request Failed');
            }

            $merchantConfig = $prResult['merchantConfig'];
            $installmentConfig = $prResult['installmentConfig'];

            if (strstr(strtolower($merchantConfig['country-code-billing']), $country) === false) {
                $response['status'] = 'failure';
                $response['message'] = __('Country not supported by credentials');

                return $result->setData($response);
            }

            if (strstr($method, 'ratepay_'.$country.'_installment0') && intval($installmentConfig['interestrate-max']) > 0) {
                $response['status'] = 'failure';
                $response['message'] = __('Interest Rate not supported by payment method');

                return $result->setData($response);
            }

            $this->_config->saveConfig('payment/'.$method.'/specificcountry_billing', $merchantConfig['country-code-billing'], 'default', 0);
            $this->_config->saveConfig('payment/'.$method.'/specificcountry_delivery', $merchantConfig['country-code-delivery'], 'default', 0);
            $this->_config->saveConfig('payment/'.$method.'/currency', $merchantConfig['currency'], 'default', 0);
            $this->_config->saveConfig('payment/'.$method.'/status', (($merchantConfig['merchant-status'] === 2) &&
                ($merchantConfig['activation-status-'.$product] !== 1) &&
                ($merchantConfig['eligibility-ratepay-'.$product] === 'yes')) ? $merchantConfig['activation-status-'.$product] : 1, 'default', 0);

            $this->_config->saveConfig('payment/'.$method.'/min_order_total', $merchantConfig['tx-limit-'.$product.'-min'], 'default', 0);
            $this->_config->saveConfig('payment/'.$method.'/max_order_total', $merchantConfig['tx-limit-'.$product.'-max'], 'default', 0);
            $this->_config->saveConfig('payment/'.$method.'/b2b', ($merchantConfig['b2b-'.$product] === 'yes') ? 1 : 0, 'default', 0);
            $this->_config->saveConfig('payment/'.$method.'/limit_max_b2b', ($merchantConfig['tx-limit-'.$product.'-max-b2b'] > 0) ? $merchantConfig['tx-limit-'.$product.'-max-b2b'] : $merchantConfig['tx-limit-'.$product.'-max'], 'default', 0);
            $this->_config->saveConfig('payment/'.$method.'/delivery_address', ($merchantConfig['delivery-address-'.$product] === 'yes') ? 1 : 0, 'default', 0);

            if (strstr($method, 'ratepay_'.$country.'_installment') || strstr($method, 'ratepay_'.$country.'_installment0')) {
                $this->_config->saveConfig('payment/'.$method.'/month_allowed', $installmentConfig['month-allowed'], 'default', 0);
                $this->_config->saveConfig('payment/'.$method.'/rate_min', $installmentConfig['rate-min-normal'], 'default', 0);
                $this->_config->saveConfig('payment/'.$method.'/service_charge', $installmentConfig['service-charge'], 'default', 0);
                $this->_config->saveConfig('payment/'.$method.'/interestrate_default', $installmentConfig['interestrate-default'], 'default', 0);
            }
            $this->_config->saveConfig('payment/ratepay_general/device_ident', ($merchantConfig['eligibility-device-fingerprint'] === 'yes') ? 1 : 0, 'default', 0); //device-fingerprint-snippet-id
            $this->_config->saveConfig('payment/ratepay_general/snipped_id', $merchantConfig['device-fingerprint-snippet-id'], 'default', 0);
            $response['status'] = 'success';
            $response['message'] = 'profile data saved';
        } else {
            $response['status'] = 'error';
            $response['message'] = 'profile data not saved';
        }

        return $result->setData($response);
    }

    /**
     * @param $profileId
     * @param $securityCode
     *
     * @return mixed|\RatePAY\ModelBuilder
     */
    public function getHead($profileId, $securityCode)
    {
        $quote = $this->_checkoutSession->getQuote();

        return $this->_rpLibraryModel->getRequestHead($quote, null, null, null, $profileId, $securityCode);
    }

    /**
     * Get RatePay payment method.
     *
     * @param $id
     *
     * @return string
     */
    private function _getRpMethod($id)
    {
        $pos = strpos($id, 'ratepay');

        return substr($id, $pos);
    }

    /**
     * Get RatePay payment method without country code.
     *
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

    /**
     *  Get country for RatePay payment method.
     *
     * @param $id
     *
     * @return string
     */
    private function _getRpCountry($id)
    {
        if (strstr($id, '_at_')) {
            return 'at';
        }
        if (strstr($id, '_ch_')) {
            return 'ch';
        }
        if (strstr($id, '_nl_')) {
            return 'nl';
        }
        if (strstr($id, '_be_')) {
            return 'be';
        }

        return 'de';
    }
}
