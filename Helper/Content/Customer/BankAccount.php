<?php
/**
 * Created by PhpStorm.
 * User: SebastianN
 * Date: 10.03.17
 * Time: 10:43
 */

namespace RatePAY\Payment\Helper\Content\Customer;


use Magento\Framework\App\Helper\Context;

class BankAccount extends \Magento\Framework\App\Helper\AbstractHelper
{
    /**
     * Build Bank Account block of payment request in customer block
     *
     * @param $quoteOrOrder
     * @return array|false
     */
    public function getBankAccount($quoteOrOrder)
    {
        $return = false;

        $iban = $quoteOrOrder->getPayment()->getAdditionalInformation('rp_iban');
        $accountHolder = $quoteOrOrder->getPayment()->getAdditionalInformation('rp_accountholder');
        if (!empty($iban)) {
            $return =[
                'Owner' => $quoteOrOrder->getBillingAddress()->getFirstname() . ' ' . $quoteOrOrder->getBillingAddress()->getLastname(),
                //'BankName' =>
                //'BankAccountNumber' => '1234567891',
                //'BankCode' => '12345678',
                'Iban' => $iban,
                //'BicSwift' =>
            ];
            if (!empty($accountHolder)) {
                $return['Owner'] = $accountHolder;
            } elseif ($quoteOrOrder->getBillingAddress()->getCompany() != "" && stripos($quoteOrOrder->getPayment()->getMethod(), "directdebit") !== false) {
                $return['Owner'] = $quoteOrOrder->getBillingAddress()->getCompany();
            }
        }
        return $return;
    }
}