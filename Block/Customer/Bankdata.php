<?php

namespace RatePAY\Payment\Block\Customer;

class Bankdata extends \Magento\Framework\View\Element\Template
{
    /**
     * BAMS GetStoredBankAccounts request model
     *
     * @var \RatePAY\Payment\Model\BamsApi\GetStoredBankAccounts
     */
    protected $getStoredBankAccounts;

    /**
     * Magento customer session object
     *
     * @var \Magento\Customer\Model\Session
     */
    protected $customerSession;

    /**
     * Constructor
     *
     * @param \Magento\Framework\View\Element\Template\Context      $context
     * @param \RatePAY\Payment\Model\BamsApi\GetStoredBankAccounts  $getStoredBankAccounts
     * @param \Magento\Customer\Model\Session                       $customerSession
     * @param array                                                 $data
     */
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \RatePAY\Payment\Model\BamsApi\GetStoredBankAccounts $getStoredBankAccounts,
        \Magento\Customer\Model\Session $customerSession,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->getStoredBankAccounts = $getStoredBankAccounts;
        $this->customerSession = $customerSession;
    }

    /**
     * Generate action url
     *
     * @param  string $sHash
     * @param  string $sAction
     * @return string
     */
    public function getActionUrl($sHash, $sAction)
    {
        return $this->getUrl('ratepay/customer/'.$sAction, ['hash' => $sHash]);
    }

    /**
     * Retrieve bank data from Ratepay
     *
     * @return array|bool
     */
    public function getSavedBankData()
    {
        $iCustomerId = $this->customerSession->getCustomerId();
        $aBankAccounts = $this->getStoredBankAccounts->getBankDataForAllIbanProfiles($iCustomerId);
        if (empty($aBankAccounts)) {
            return false;
        }
        return $aBankAccounts;
    }
}