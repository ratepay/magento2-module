<?php


namespace RatePAY\Payment\Model\BamsApi;


abstract class Base
{
    /**
     * Authentication URL for BAMS api
     *
     * @var string
     */
    protected $sOauthUrl = "https://oauth.ratepay.com/oauth/token";

    /**
     * Production URL for BAMS api
     *
     * @var string
     */
    protected $sBamsProductionUrl = "https://api.ratepay.com/shop/consumer/v1";

    /**
     * Sandbox URL for BAMS api
     *
     * @var string
     */
    protected $sBamsSandboxUrl = "https://api-integration.ratepay.com/shop/consumer/v1";

    /**
     * @var \RatePAY\Payment\Helper\Data
     */
    protected $rpDataHelper;

    /**
     * Authentication token
     *
     * @var array|null
     */
    protected $aAuthToken = null;

    /**
     * HTTP response code
     *
     * @var int|null
     */
    protected $iHttpResponseCode = null;

    /**
     * BamsApi base constructor.

     * @param \RatePAY\Payment\Helper\Data $rpDataHelper
     */
    public function __construct(
        \RatePAY\Payment\Helper\Data $rpDataHelper
    ) {
        $this->rpDataHelper = $rpDataHelper;
    }

    /**
     * Generates url parameter string
     *
     * @param array|bool $aUrlParameters
     * @return string
     */
    protected function getUrlParameterString($aUrlParameters = false)
    {
        $sUrlParams = '';
        if ($aUrlParameters !== false) {
            foreach ($aUrlParameters as $sParamName => $sParamValue) {
                $sUrlParams .= '/'.$sParamName;
                if (!empty($sParamValue)) {
                    $sUrlParams .= '/'.$sParamValue;
                }
            }
        }
        return $sUrlParams;
    }

    /**
     * Returns BAMS api url based on configuration
     *
     * @param  array|false $aUrlParameters
     * @return string
     */
    protected function getBamsApiUrl($aUrlParameters = false)
    {
        $sUrlParams = $this->getUrlParameterString($aUrlParameters);
        if ((bool)$this->rpDataHelper->getRpConfigDataByPath("ratepay/general/bams_sandbox") === true) {
            return $this->sBamsSandboxUrl.$sUrlParams;
        }
        return $this->sBamsProductionUrl.$sUrlParams;
    }

    /**
     * Returns HTTP response code
     *
     * @return int|null
     */
    public function getHttpResponseCode()
    {
        return $this->iHttpResponseCode;
    }

    /**
     * Generates a auth token by requesting it from the oauth service
     * Returns token if already received
     *
     * @return array|false
     */
    protected function getAuthToken()
    {
        if ($this->aAuthToken === null) {
            $aRequest = [
                'client_id' => $this->rpDataHelper->getRpConfigDataByPath("ratepay/general/bams_client_id"),
                'client_secret' => $this->rpDataHelper->getRpConfigDataByPath("ratepay/general/bams_client_secret"),
                'audience' => $this->getBamsApiUrl(),
                'grant_type' => 'client_credentials'
            ];
            $aResponse = $this->sendCurlRequest($this->sOauthUrl, 'POST', $aRequest, false);

            if (!isset($aResponse['access_token'])) {
                return false;
            }

            $this->aAuthToken = $aResponse;
        }
        return $this->aAuthToken;
    }

    /**
     * Sends json post request to given url
     *
     * @param  string $sUrl
     * @param  string $sRequestType
     * @param  array  $aPostBody
     * @param  bool   $blNeedsAuthToken
     * @return array|mixed
     */
    protected function sendCurlRequest($sUrl, $sRequestType, $aPostBody = false, $blNeedsAuthToken = true)
    {
        $aHeaders = [];
        if ($blNeedsAuthToken === true) {
            $aAuthToken = $this->getAuthToken();
            if ($aAuthToken !== false) {
                $aHeaders[] = 'Authorization: Bearer '.$aAuthToken['access_token'];
            }
        }

        $oCurl = curl_init($sUrl);
        if ($aPostBody !== false) {
            $aHeaders[] = 'Content-Type: application/json';
            curl_setopt($oCurl, CURLOPT_POST, 1);
            curl_setopt($oCurl, CURLOPT_POSTFIELDS, json_encode($aPostBody));
        }
        curl_setopt($oCurl, CURLOPT_CUSTOMREQUEST, $sRequestType);
        curl_setopt($oCurl, CURLOPT_SSL_VERIFYHOST, 0);
        curl_setopt($oCurl, CURLOPT_SSL_VERIFYPEER, 0);
        curl_setopt($oCurl, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($oCurl, CURLOPT_HTTPHEADER, $aHeaders);
        $sResult = curl_exec($oCurl);

        $this->iHttpResponseCode = curl_getinfo($oCurl, CURLINFO_HTTP_CODE);
        curl_close($oCurl);

        if (!empty($sResult)) {
            $aResponse = json_decode($sResult, true);
            return $aResponse;
        }
        return [];
    }
}
