<?php


namespace RatePAY\Payment\Model\BamsApi;


class DeleteBankAccount extends Base
{
    /**
     * Sends DeleteBankAccount request to BAMS api and returns saved bank accounts of the customer
     *
     * @param  string      $sCustomerNr
     * @param  string      $sProfileId
     * @param  string      $sBankAccountReference
     * @return array|bool
     */
    public function sendRequest($sCustomerNr, $sProfileId, $sBankAccountReference)
    {
        $aUrlParameters = [
            'partners' => $sProfileId,
            'consumer' => $sCustomerNr,
            'bank-accounts' => $sBankAccountReference
        ];

        $this->sendCurlRequest($this->getBamsApiUrl($aUrlParameters), 'DELETE');

        $iHttpResponseCode = $this->getHttpResponseCode();
        if ($iHttpResponseCode == 204) {
            return true;
        }
        return false;
    }
}
