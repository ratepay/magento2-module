<?php

/**
 * Copyright (c) Ratepay GmbH
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace RatePAY\Payment\Helper;

use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Framework\App\Helper\Context;
use Magento\Framework\Exception\PaymentException;

class Validator extends \Magento\Framework\App\Helper\AbstractHelper
{
    /**
     * @var \Magento\Checkout\Model\Session
     */
    protected $checkoutSession;

    /**
     * @var CustomerRepositoryInterface
     */
    protected $customerRepository;

    /**
     * @var \Magento\Customer\Model\Session
     */
    protected $customerSession;

    /**
     * Validator constructor.
     * @param Context $context
     * @param \Magento\Checkout\Model\Session $checkoutSession
     * @param CustomerRepositoryInterface $customerRepository
     * @param \Magento\Customer\Model\Session $customerSession
     */
    public function __construct(
        Context $context,
        \Magento\Checkout\Model\Session $checkoutSession,
        CustomerRepositoryInterface $customerRepository,
        \Magento\Customer\Model\Session $customerSession
    ) {
        parent::__construct($context);
        $this->checkoutSession = $checkoutSession;
        $this->customerRepository = $customerRepository;
        $this->customerSession = $customerSession;
    }

    /**
     * @param $additionalData
     */
    public function validateDob($additionalData)
    {
        if (!$this->_isNumericBetter($additionalData->getRpDobDay()) ||
            !$this->_isNumericBetter($additionalData->getRpDobMonth()) ||
            !$this->_isNumericBetter($additionalData->getRpDobYear())) {
            throw new PaymentException(__("dob data invalid"));
        }

        $response = $this->_isValidAge($additionalData->getRpDobDay(), $additionalData->getRpDobMonth(), $additionalData->getRpDobYear());
        if ($response !== true) {
            throw new PaymentException(__($response));
        }

        if ($this->customerSession->isLoggedIn()) {
            $customer = $this->customerRepository->getById($this->customerSession->getCustomerId());
            $customer->setDob(
                sprintf(
                    "%s-%s-%s",
                    $additionalData->getRpDobYear(),
                    $additionalData->getRpDobMonth(),
                    $additionalData->getRpDobDay()
                )
            );
            $this->customerRepository->save($customer);
        } else {
            $this->checkoutSession->setRatepayDob(
                sprintf(
                    "%s-%s-%s",
                    $additionalData->getRpDobYear(),
                    $additionalData->getRpDobMonth(),
                    $additionalData->getRpDobDay()
                )
            );
        }
    }

    /**
     * @param $dobDay
     * @param $dobMonth
     * @param $dobYear
     * @return bool|string
     */
    private function _isValidAge($dobDay, $dobMonth, $dobYear)
    {
        $minAge = 18;
        $maxAge = 120;

        try {
            $dob = new \DateTime(trim($dobDay) . "-" . trim($dobMonth) . "-" . trim($dobYear));
        } catch (\Exception $e) {
            throw new PaymentException(__('dob data invalid'));
        }

        $today = new \DateTime(date("d-m-Y"));

        $interval = $dob->diff($today);
        $age = $interval->y;

        if ($age < $minAge) {
            return "dob too low";
        } elseif ($age > $maxAge) {
            return "dob too high";
        } else {
            return true;
        }
    }

    /**
     * Checks if value is set and numeric
     *
     * @param $str
     * @return bool
     */
    private function _isNumericBetter($str)
    {
        return (!empty($str) && is_numeric($str));
    }

    /**
     * @param $additionalData
     */
    public function validateIban($additionalData)
    {
        if (empty($additionalData->getRpIban())) {
            throw new PaymentException(__("iban data invalid"));
        }
    }

    /**
     * @param $additionalData
     */
    public function validatePhone($additionalData)
    {
        /*if (!$additionalData->getRpPhone()) {
            throw new $this->paymentException(__("phone data invalid"));
        }

        if (!$this->isValidPhone($additionalData->getRpPhone())) {
            throw new $this->paymentException(__("phone not valid"));
        }

        $this->checkoutSession->setRatepayPhone($additionalData->getRpPhone());*/
    }

    /**
     * @param $phone
     * @return bool
     */
    public function isValidPhone($phone)
    {
        $valid = "/^[\d\s\/\(\)-+]/";
        if (strlen(trim($phone)) >= 6 && preg_match($valid, trim($phone))) {
            return true;
        }
        return false;
    }
}
