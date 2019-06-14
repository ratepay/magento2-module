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
     * Constructor.
     *
     * @param \Magento\Customer\Model\Session $customerSession
     */
    public function __construct(\Magento\Customer\Model\Session $customerSession) {
        $this->customerSession = $customerSession;
    }

    /**
     * Used to unset session flags after a finished order
     *
     * @param  Observer $observer
     * @return void
     */
    public function execute(Observer $observer)
    {
        $this->customerSession->unsRatePayDeviceIdentToken();
    }
}