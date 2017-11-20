<?php
/**
 * Created by PhpStorm.
 * User: SebastianN
 * Date: 20.06.17
 * Time: 11:03
 */

namespace RatePAY\Payment\Helper;


use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Framework\App\Helper\Context;

class Validator extends \Magento\Framework\App\Helper\AbstractHelper
{
    /**
     * @var \Magento\Checkout\Model\Session
     */
    protected $checkoutSession;

    /**
     * @var \Magento\Framework\Exception\PaymentException
     */
    protected $paymentException;

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
     * @param \Magento\Framework\Exception\PaymentException $paymentException
     * @param \Magento\Checkout\Model\Session $checkoutSession
     * @param CustomerRepositoryInterface $customerRepository
     * @param \Magento\Customer\Model\Session $customerSession
     */
    public function __construct(Context $context,
                                \Magento\Framework\Exception\PaymentException $paymentException,
                                \Magento\Checkout\Model\Session $checkoutSession,
                                CustomerRepositoryInterface $customerRepository,
                                \Magento\Customer\Model\Session $customerSession)
    {
        parent::__construct($context);

        $this->paymentException = $paymentException;
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
            throw new $this->paymentException(__("dob data invalid"));
        }

        $response = $this->_isValidAge($additionalData->getRpDobDay(), $additionalData->getRpDobMonth(), $additionalData->getRpDobYear());
        if ($response !== true) {
            throw new $this->paymentException(__($response));
        }

        if($this->customerSession->isLoggedIn()){
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
                sprintf("%s-%s-%s",
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

        try{
            $dob = new \DateTime($dobDay . "-" . $dobMonth . "-" . $dobYear);
        } catch (\Exception $e){
            throw new $this->paymentException(__('dob data invalid'));
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
    private function _isNumericBetter($str) {
        return (!empty($str) && is_numeric($str));
    }

    /**
     * @param $additionalData
     */
    public function validateIban($additionalData)
    {
        if (empty($additionalData->getRpIban())) {
            throw new $this->paymentException(__("iban data invalid"));
        }

        $this->checkoutSession->setRatepayIban($additionalData->getRpIban());
    }

    /**
     * @param $additionalData
     */
    public function validatePhone($additionalData)
    {
        if (!$additionalData->getRpPhone()) {
            throw new $this->paymentException(__("phone data invalid"));
        }

        if (!$this->isValidPhone($additionalData->getRpPhone())) {
            throw new $this->paymentException(__("phone not valid"));
        }

        $this->checkoutSession->setRatepayPhone($additionalData->getRpPhone());
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
