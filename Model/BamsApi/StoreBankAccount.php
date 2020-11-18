<?php


namespace RatePAY\Payment\Model\BamsApi;


class StoreBankAccount extends Base
{
    /**
     * Sends StoreBankAccount request to BAMS api and returns bank account reference
     *
     * @param  string      $sCustomerNr
     * @param  string      $sProfileId
     * @param  string      $sOwner
     * @param  string      $sIban
     * @param  string|bool $sBic
     * @return string|bool
     */
    public function sendRequest($sCustomerNr, $sProfileId, $sOwner, $sIban, $sBic = false)
    {
        $aRequest = [
            "owner" => $sOwner,
            "iban" => $sIban,
        ];
        if ($sBic !== false) {
            $aRequest['bic'] = $sBic;
        }

        $aUrlParameters = [
            'partners' => $sProfileId,
            'consumer' => $sCustomerNr,
            'bank-accounts' => null
        ];

        $aResponse = $this->sendCurlRequest($this->getBamsApiUrl($aUrlParameters), 'POST', $aRequest);
        if (isset($aResponse['bank_account_reference'])) {
            return $aResponse['bank_account_reference'];
        }
        return false;
    }
}
