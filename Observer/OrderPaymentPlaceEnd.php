<?php

namespace RatePAY\Payment\Observer;

use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Event\Observer;

class OrderPaymentPlaceEnd implements ObserverInterface
{
    /**
     * @var \Magento\Customer\Model\Session
     */
    protected $customerSession;

    /**
     * @var \Magento\Checkout\Model\Session
     */
    protected $checkoutSession;

    /**
     * Array with variables to delete from the customer session
     *
     * @var array
     */
    protected $deleteFromCustomerSession = [
        'rate_pay_device_ident_token',
    ];

    /**
     * Array with variables to delete from the checkout session
     *
     * @var array
     */
    protected $deleteFromCheckoutSession = [
        'ratepayPaymentAmount',
        'ratepayInstallmentNumber',
        'ratepayInstallmentAmount',
        'ratepayLastInstallmentAmount',
        'ratepayInterestRate',
        'rate_pay_disabled_payment_methods',
        'ratepay_request',
    ];

    /**
     * Constructor.
     *
     * @param \Magento\Customer\Model\Session $customerSession
     * @param \Magento\Checkout\Model\Session $checkoutSession
     */
    public function __construct(
        \Magento\Customer\Model\Session $customerSession,
        \Magento\Checkout\Model\Session $checkoutSession
    ) {
        $this->customerSession = $customerSession;
        $this->checkoutSession = $checkoutSession;
    }

    /**
     * Removes session parameters
     *
     * @param \Magento\Framework\Session\SessionManager $oSession
     * @param array $deleteMap
     */
    protected function deleteFromSession($oSession, $deleteMap)
    {
        $aData = $oSession->getData();
        foreach ($aData as $sessionKey => $sessionValue) {
            foreach ($deleteMap as $deleteKey) {
                if (stripos($sessionKey, $deleteKey) !== false) {
                    $oSession->unsetData($sessionKey);
                    break;
                }
            }
        }
    }

    /**
     * Used to unset session flags after a finished order
     *
     * @param  Observer $observer
     * @return void
     */
    public function execute(Observer $observer)
    {
        $this->deleteFromSession($this->checkoutSession, $this->deleteFromCheckoutSession);
        $this->deleteFromSession($this->customerSession, $this->deleteFromCustomerSession);
    }
}
