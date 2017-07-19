<?php
/**
 * Created by PhpStorm.
 * User: SebastianN
 * Date: 07.02.17
 * Time: 14:23
 */

namespace RatePAY\Payment\Controller;

require_once __DIR__ . '/../Model/Library/vendor/autoload.php';

use RatePAY\RequestBuilder;
use RatePAY\InstallmentBuilder;

class LibraryController
{
    /**
     * Call Ratepay Payment Init
     *
     * @param $head
     * @param $sandbox
     * @return mixed
     */
    public static function callPaymentInit($head, $sandbox)
    {
        // Initiation of generic RequestBuilder object.
        $rb = new RequestBuilder($sandbox); // true == Sandbox mode

        $paymentInit = $rb->callPaymentInit($head); // Initializes transaction
        return $paymentInit;
    }

    /**
     * Call Ratepay Payment Request
     *
     * @param $head
     * @param $content
     * @param $sandbox
     * @return mixed
     */
    public static function callPaymentRequest($head, $content, $sandbox)
    {
        $rb = new RequestBuilder($sandbox); // Sandbox mode = true
        try {
            $paymentRequest = $rb->callPaymentRequest($head, $content);
        } catch(\Exception $e) {
            echo $e->getMessage();
        }

        return $paymentRequest;
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
        } catch(\Exception $e) {
            echo $e->getMessage();
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
        } catch(\Exception $e) {
            echo $e->getMessage();
        }

        return $installmentPlan;
    }

    /**
     * call RatePay profile request
     *
     * @param $head
     * @param $sandbox
     * @return mixed
     */
    public function callProfileRequest($head, $sandbox)
    {
        $rb = new RequestBuilder($sandbox);

        try{
            $profilerequest = $rb->callProfileRequest($head);
        } catch (\Exception $e){
            echo $e->getMessage();
        }

        return $profilerequest;
    }

    /**
     * @param $head
     * @param $content
     * @param $sandbox
     * @return mixed
     */
    public function  callConfirmationDeliver($head, $content, $sandbox)
    {
        $rb = new RequestBuilder($sandbox);

        try {
            $confirmationDeliver = $rb->callConfirmationDeliver($head, $content);
        } catch (\Exception $e) {
            echo $e->getMessage();
        }

        return $confirmationDeliver;
    }
