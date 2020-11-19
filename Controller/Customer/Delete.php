<?php

namespace RatePAY\Payment\Controller\Customer;

class Delete extends \Magento\Framework\App\Action\Action
{
    /**
     * Customer session object
     *
     * @var \Magento\Customer\Model\Session
     */
    protected $customerSession;

    /**
     * BAMS GetStoredBankAccounts request model
     *
     * @var \RatePAY\Payment\Model\BamsApi\GetStoredBankAccounts
     */
    protected $getStoredBankAccounts;

    /**
     * BAMS DeleteBankAccount request model
     *
     * @var \RatePAY\Payment\Model\BamsApi\DeleteBankAccount
     */
    protected $deleteBankAccount;

    /**
     * Constructor
     *
     * @param \Magento\Framework\App\Action\Context                 $context
     * @param \Magento\Customer\Model\Session                       $customerSession
     * @param \RatePAY\Payment\Model\BamsApi\GetStoredBankAccounts  $getStoredBankAccounts
     * @param \RatePAY\Payment\Model\BamsApi\DeleteBankAccount      $deleteBankAccount
     */
    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Magento\Customer\Model\Session $customerSession,
        \RatePAY\Payment\Model\BamsApi\GetStoredBankAccounts $getStoredBankAccounts,
        \RatePAY\Payment\Model\BamsApi\DeleteBankAccount $deleteBankAccount
    ) {
        parent::__construct($context);
        $this->customerSession = $customerSession;
        $this->getStoredBankAccounts = $getStoredBankAccounts;
        $this->deleteBankAccount = $deleteBankAccount;
    }

    /**
     * Dispatch request
     *
     * @return \Magento\Framework\Controller\Result\Redirect
     */
    public function execute()
    {
        $sDeleteHash = $this->getRequest()->getParam('hash');

        $iCustomerId = $this->customerSession->getCustomerId();
        $aBankAccounts = $this->getStoredBankAccounts->getBankDataForAllIbanProfiles($iCustomerId);
        foreach ($aBankAccounts as $aBankData) {
            if ($aBankData['hash'] == $sDeleteHash) {
                $this->deleteBankAccount->sendRequest($iCustomerId, $aBankData['profile'], $aBankData['bank_account_reference']);
                break;
            }
        }
        return $this->resultRedirectFactory->create()->setPath('ratepay/customer/bankdata');
    }
}