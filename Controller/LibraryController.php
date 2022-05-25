<?php
/**
 * Created by PhpStorm.
 * User: SebastianN
 * Date: 07.02.17
 * Time: 14:23
 */

namespace RatePAY\Payment\Controller;

use RatePAY\Payment\Model\ResourceModel\ApiLog;
use RatePAY\RequestBuilder;
use RatePAY\Frontend\InstallmentBuilder;
use RatePAY\Frontend\DeviceFingerprintBuilder;
use RatePAY\Payment\Model\SerializableRequestFactory;

class LibraryController
{
    /**
     * @var ApiLog
     */
    protected $apiLog;

    /**
     * @var \Magento\Checkout\Model\Session
     */
    protected $checkoutSession;

    /**
     * @var SerializableRequestFactory
     */
    protected $serializableRequestFactory;

    /**
     * LibraryController constructor.
     *
     * @param ApiLog $apiLog
     * @param \Magento\Checkout\Model\Session $checkoutSession
     * @param SerializableRequestFactory $serializableRequestFactory
     */
    public function __construct(ApiLog $apiLog, \Magento\Checkout\Model\Session $checkoutSession, SerializableRequestFactory $serializableRequestFactory)
    {
        $this->apiLog = $apiLog;
        $this->checkoutSession = $checkoutSession;
        $this->serializableRequestFactory = $serializableRequestFactory;
    }

    /**
     * Log request to database
     *
     * @param $request
     * @param $order
     * @param $addToSession
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function log($request, $order = null, $addToSession = false)
    {
        $serializableRequest = $this->serializableRequestFactory->create();
        $serializableRequest->initData($request, $order);

        if ($addToSession === true) {
            $this->checkoutSession->setRatepayRequest($serializableRequest);
        }

        $this->apiLog->addApiLogEntry($serializableRequest, $order);
    }

    /**
     * Call Ratepay Payment Init
     *
     * @param $head
     * @param $order
     * @param $sandbox
     * @return mixed
     * @throws \Exception
     */
    public function callPaymentInit($head, $order, $sandbox)
    {
        // Initiation of generic RequestBuilder object.
        $request = new RequestBuilder($sandbox); // true == Sandbox mode
        $exception = false;

        try {
            $request->callPaymentInit($head); // Initializes transaction
        } catch (\Exception $e) {
            $exception = $e;
        }
        $this->log($request, $order);

        if ($exception !== false) {
            throw $exception;
        }
        return $request;
    }

    /**
     * Call Ratepay Payment Request
     *
     * @param $head
     * @param $content
     * @param $order
     * @param $sandbox
     * @return mixed
     * @throws \Exception
     */
    public function callPaymentRequest($head, $content, $order, $sandbox)
    {
        $request = new RequestBuilder($sandbox); // Sandbox mode = true
        $exception = false;

        try {
            $request->callPaymentRequest($head, $content);
        } catch (\Exception $e) {
            $exception = $e;
        }
        $this->log($request, $order, true);

        if ($exception !== false) {
            throw $exception;
        }
        return $request;
    }

    /**
     * Get Ratepay Installment Configuration
     *
     * @param $profileId
     * @param $securityCode
     * @param $sandbox
     * @param $orderAmount
     * @param null $template
     * @return string
     */
    public static function getInstallmentConfiguration($profileId, $securityCode, $sandbox, $orderAmount, $template = null)
    {
        $ib = new InstallmentBuilder($sandbox);
        $ib->setProfileId($profileId);
        $ib->setSecuritycode($securityCode);

        try {
            if (is_null($template)) {
                $installmentConfiguration = $ib->getInstallmentConfigAsJson($orderAmount);
            } else {
                $installmentConfiguration = $ib->getInstallmentConfigByTemplate($orderAmount, $template);
            }
        } catch (\Exception $e) {
            error_log($e->getMessage());
        }

        return $installmentConfiguration;
    }

    /**
     * Get RatePay installment plan
     *
     * @param $profileId
     * @param $securityCode
     * @param $sandbox
     * @param $calculationAmount
     * @param $calculationType
     * @param $calculationValue
     * @param null $template
     * @return string
     * @throws \Exception
     */
    public static function getInstallmentPlan($profileId, $securityCode, $sandbox, $calculationAmount, $calculationType, $calculationValue, $template = null)
    {
        $ib = new InstallmentBuilder($sandbox);
        $ib->setProfileId($profileId);
        $ib->setSecuritycode($securityCode);

        $installmentPlan = '';
        try {
            if (is_null($template)) {
                $installmentPlan = $ib->getInstallmentPlanAsJson($calculationAmount, $calculationType, $calculationValue);
            } else {
                $installmentPlan = $ib->getInstallmentPlanByTemplate($calculationAmount, $calculationType, $calculationValue, $template);
            }
        } catch (\Exception $e) {
            throw $e;
        }

        return $installmentPlan;
    }

    /**
     * call RatePay profile request
     *
     * @param $head
     * @param $sandbox
     * @return RequestBuilder
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function callProfileRequest($head, $sandbox)
    {
        $request = new RequestBuilder($sandbox);
        $exception = false;

        try {
            $request->callProfileRequest($head);
        } catch (\Exception $e) {
            $exception = $e;
        }
        $this->log($request);

        if ($exception !== false) {
            throw $exception;
        }
        return $request;
    }

    /**
     * @param $head
     * @param $content
     * @param $order
     * @param $sandbox
     * @return RequestBuilder
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function callConfirmationDeliver($head, $content, $order, $sandbox)
    {
        $request = new RequestBuilder($sandbox);
        $exception = false;

        try {
            $request->callConfirmationDeliver($head, $content);
        } catch (\Exception $e) {
            $exception = $e;
        }
        $this->log($request, $order);

        if ($exception !== false) {
            throw $exception;
        }
        return $request;
    }

    /**
     * @param $head
     * @param $content
     * @param $operation
     * @param $order
     * @param $sandbox
     * @return mixed
     * @throws \Exception
     */
    public function callPaymentChange($head, $content, $operation, $order, $sandbox)
    {
        $request = new RequestBuilder($sandbox);
        $exception = false;

        try {
            $request->callPaymentChange($head, $content)->subtype($operation);
        } catch (\Exception $e) {
            $exception = $e;
        }

        $this->log($request, $order, true);

        if ($exception !== false) {
            throw $exception;
        }
        return $request;
    }

    /**
     * @param $snippetId
     * @param $orderId
     * @return DeviceFingerprintBuilder
     * @throws \RatePAY\Exception\FrontendException
     */
    public function getDfpCode($snippetId, $orderId)
    {
        $dfp = new DeviceFingerprintBuilder($snippetId, $orderId);

        return $dfp;
    }
}
