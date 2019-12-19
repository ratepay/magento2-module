<?php

namespace RatePAY\Payment\Model;

use RatePAY\RequestBuilder;
use Magento\Framework\DataObject;

/**
 * Class SerializableRequest
 *
 * @package RatePAY\Payment\Model
 * @method string getTransactionId()
 * @method string getPaymentType()
 * @method string getPaymentSubtype()
 * @method string getResult()
 * @method string getResultCode()
 * @method string getStatusCode()
 * @method string getReason()
 * @method string getRequest()
 * @method string getResponse()
 */
class SerializableRequest extends DataObject
{
    /**
     * @param RequestBuilder $request
     * @param $order
     * @return string
     */
    protected function getTransactionIdFromRequest(RequestBuilder $request, $order = null)
    {
        $transactionId = null;
        if (!is_null($order)) {
            try {
                $transactionId = $request->getTransactionId();
            } catch(\Exception $exc) {
                // do nothing
            }
        }
        return $transactionId;
    }

    /**
     * Returns payment sub type from xml if existing
     *
     * @param $requestXMLElement
     * @return string|null
     */
    protected function getPaymentSubTypeFromRequest($requestXMLElement)
    {
        $paymentSubType = null;
        if (isset($requestXMLElement->head->operation->attributes()->subtype)) {
            $paymentSubType = strtoupper((string)$requestXMLElement->head->operation->attributes()->subtype);
        }
        return $paymentSubType;
    }

    /**
     * Returns formatted xml from SimpelXML object
     *
     * @param \SimpleXMLElement $xml
     * @return string
     */
    protected function getFormattedXml(\SimpleXMLElement $xml)
    {
        $dom = dom_import_simplexml($xml);
        if (!empty($dom)) {
            $dom = $dom->ownerDocument;
            $dom->formatOutput = true;
            return $dom->saveXML();
        }
        return 'XML error';
    }

    /**
     * Writes needed information to the cachable object
     *
     * @param RequestBuilder $request
     * @param $order
     * @return void
     */
    public function initData(RequestBuilder $request, $order = null)
    {
        $requestXMLElement = $request->getRequestXmlElement();
        $responseXMLElement = $request->getResponseXmlElement();

        $this->setTransactionId($this->getTransactionIdFromRequest($request, $order));
        $this->setPaymentType((string)$requestXMLElement->head->{'operation'});
        $this->setPaymentSubtype($this->getPaymentSubTypeFromRequest($requestXMLElement));
        $this->setResult($request->getResultMessage());
        $this->setResultCode($request->getResultCode());
        $this->setStatusCode((string)$responseXMLElement->head->processing->status->attributes()->code);
        $this->setReason($request->getReasonMessage());
        $this->setRequest($this->getFormattedXml($requestXMLElement));
        $this->setResponse($this->getFormattedXml($responseXMLElement));
    }
}
