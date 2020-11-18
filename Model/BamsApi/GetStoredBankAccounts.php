<?php


namespace RatePAY\Payment\Model\BamsApi;


class GetStoredBankAccounts extends Base
{
    /**
     * Method codes of all directdebit methods
     *
     * @var array
     */
    protected $aDebitMethods = [
        'ratepay_de_directdebit',
        'ratepay_at_directdebit',
        'ratepay_nl_directdebit',
        'ratepay_be_directdebit',
    ];

    /**
     * Generates hash for iban
     *
     * @param  array $aResponse
     * @return string
     */
    protected function getBankDataHash($aResponse)
    {
        return md5($aResponse['bank_account_reference'].$aResponse['owner'].$aResponse['iban']);
    }

    /**
     * Adds hash to each bank data entry
     *
     * @param  array  $aReponse
     * @param  string $sProfileId
     * @return array
     */
    protected function addDataToResponse($aReponse, $sProfileId)
    {

        foreach ($aReponse as $iKey => $aBankData) {
            if (isset($aBankData['bank_account_reference'])) {
                $aReponse[$iKey]['hash'] = $this->getBankDataHash($aBankData);
                $aReponse[$iKey]['profile'] = $sProfileId;
            }
        }
        return $aReponse;
    }

    /**
     * Collects all debit profile ids
     *
     * @return array
     */
    protected function getAvailableDebitProfiles()
    {
        $aReturn = [];
        foreach ($this->aDebitMethods as $sMethodCode) {
            $aReturn[] = $this->rpDataHelper->getRpConfigData($sMethodCode, 'profileId');
        }
        return $aReturn;
    }

    /**
     * Requests bank data for all debit profiles
     *
     * @param  int $iCustomerNr
     * @return array
     */
    public function getBankDataForAllDebitProfiles($iCustomerNr)
    {
        $aDebitProfiles = $this->getAvailableDebitProfiles();
        $aReturn = [];
        foreach ($aDebitProfiles as $sDebitProfile) {
            $aBankAccounts = $this->sendRequest($iCustomerNr, $sDebitProfile);
            if (!empty($aBankAccounts)) {
                $aReturn = array_merge($aReturn, $aBankAccounts);
            }
        }
        return $aReturn;
    }

    /**
     * Sends GetStoredBankAccounts request to BAMS api and returns saved bank accounts of the customer
     *
     * @param  string      $sCustomerNr
     * @param  string      $sProfileId
     * @return array|bool
     */
    public function sendRequest($sCustomerNr, $sProfileId)
    {
        $aUrlParameters = [
            'partners' => $sProfileId,
            'consumer' => $sCustomerNr,
            'bank-accounts' => null
        ];

        $aResponse = $this->sendCurlRequest($this->getBamsApiUrl($aUrlParameters), 'GET');
        if (is_array($aResponse) && !isset($aResponse['error'])) {
            $aResponse = $this->addDataToResponse($aResponse, $sProfileId);
            return $aResponse;
        }
        return false;
    }
}
