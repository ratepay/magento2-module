<?php

namespace RatePAY\Payment\Plugin;

use Magento\Payment\Model\MethodList as OrigMethodList;
use Magento\Payment\Model\MethodInterface;

class MethodList
{
    /**
     * Checkout session
     *
     * @var \Magento\Checkout\Model\Session
     */
    protected $checkoutSession;

    /**
     * Constructor
     *
     * @param \Magento\Checkout\Model\Session                $checkoutSession
     */
    public function __construct(\Magento\Checkout\Model\Session $checkoutSession) {
        $this->checkoutSession = $checkoutSession;
    }

    /**
     * Remove disabled paymenttypes
     *
     * @param  array $aPaymentMethods
     * @return array
     */
    protected function removeDisabledPaymentMethods($aPaymentMethods)
    {
        $aDisabledMethods = $this->checkoutSession->getRatePayDisabledPaymentMethods();
        if (empty($aDisabledMethods)) {
            return $aPaymentMethods;
        }

        $aReturnMethods = $aPaymentMethods;
        for($i = 0; $i < count($aPaymentMethods); $i++) {
            $sCode = $aPaymentMethods[$i]->getCode();
            if (in_array($sCode, $aDisabledMethods)) {
                unset($aReturnMethods[$i]);
            }
        }
        return $aReturnMethods;
    }

    /**
     * Plugin for methot getAvailableMethods
     *
     * Used to filter out payment methods
     *
     * @param  OrigMethodList    $subject
     * @param  MethodInterface[] $aPaymentMethods
     * @return MethodInterface[]
     */
    public function afterGetAvailableMethods(OrigMethodList $subject, $aPaymentMethods)
    {
        $aPaymentMethods = $this->removeDisabledPaymentMethods($aPaymentMethods);

        return $aPaymentMethods;
    }
}