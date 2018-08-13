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

namespace RatePAY\Payment\Controller;

use RatePAY\Frontend\DeviceFingerprintBuilder;
use RatePAY\InstallmentBuilder;
use RatePAY\RequestBuilder;

class LibraryController
{
    /**
     * Call Ratepay Payment Init.
     *
     * @param $head
     * @param $sandbox
     *
     * @return mixed
     */
    public static function callPaymentInit($head, $sandbox)
    {
        // Initiation of generic RequestBuilder object.
        $rb = new RequestBuilder($sandbox); // true == Sandbox mode

        try {
            $paymentInit = $rb->callPaymentInit($head); // Initializes transaction
        } catch (\Exception $e) {
            echo $e->getMessage();
        }

        return $paymentInit;
    }

    /**
     * Call Ratepay Payment Request.
     *
     * @param $head
     * @param $content
     * @param $sandbox
     *
     * @return mixed
     */
    public static function callPaymentRequest($head, $content, $sandbox)
    {
        $rb = new RequestBuilder($sandbox); // Sandbox mode = true
        try {
            $paymentRequest = $rb->callPaymentRequest($head, $content);
        } catch (\Exception $e) {
            echo $e->getMessage();
        }

        return $paymentRequest;
    }

    /**
     * Get Ratepay Installment Configuration.
     *
     * @param $profileId
     * @param $securityCode
     * @param $sandbox
     * @param $orderAmount
     * @param null $template
     *
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
            echo $e->getMessage();
        }

        return $installmentConfiguration;
    }

    /**
     * Get RatePay installment plan.
     *
     * @param $profileId
     * @param $securityCode
     * @param $sandbox
     * @param $calculationAmount
     * @param $calculationType
     * @param $calculationValue
     * @param null $template
     *
     * @return string
     */
    public static function getInstallmentPlan($profileId, $securityCode, $sandbox, $calculationAmount, $calculationType, $calculationValue, $template = null)
    {
        $ib = new InstallmentBuilder($sandbox);
        $ib->setProfileId($profileId);
        $ib->setSecuritycode($securityCode);

        try {
            if (is_null($template)) {
                $installmentPlan = $ib->getInstallmentPlanAsJson($calculationAmount, $calculationType, $calculationValue);
            } else {
                $installmentPlan = $ib->getInstallmentPlanByTemplate($calculationAmount, $calculationType, $calculationValue, $template);
            }
        } catch (\Exception $e) {
            echo $e->getMessage();
        }

        return $installmentPlan;
    }

    /**
     * call RatePay profile request.
     *
     * @param $head
     * @param $sandbox
     *
     * @return mixed
     */
    public function callProfileRequest($head, $sandbox)
    {
        $rb = new RequestBuilder($sandbox);

        try {
            $profilerequest = $rb->callProfileRequest($head);
        } catch (\Exception $e) {
            echo $e->getMessage();
        }

        return $profilerequest;
    }

    /**
     * @param $head
     * @param $content
     * @param $sandbox
     *
     * @return mixed
     */
    public function callConfirmationDeliver($head, $content, $sandbox)
    {
        $rb = new RequestBuilder($sandbox);

        try {
            $confirmationDeliver = $rb->callConfirmationDeliver($head, $content);
        } catch (\Exception $e) {
            echo $e->getMessage();
        }

        return $confirmationDeliver;
    }

    /**
     * @param $head
     * @param $content
     * @param $operation
     * @param $sandbox
     *
     * @return mixed
     */
    public function callPaymentChange($head, $content, $operation, $sandbox)
    {
        $rb = new RequestBuilder($sandbox);

        try {
            $paymentChange = $rb->callPaymentChange($head, $content)->subtype($operation);
        } catch (\Exception $e) {
            echo $e->getMessage();
        }

        return $paymentChange;
    }

    public function getDfpCode($snippetId, $orderId)
    {
        return new DeviceFingerprintBuilder($snippetId, $orderId);
    }
}
