<?php

namespace RatePAY\Payment\Model\ResourceModel;

use \RatePAY\RequestBuilder;

/**
 * Resource model for ratepay_api_log table
 */
class ApiLog extends \Magento\Framework\Model\ResourceModel\Db\AbstractDb
{
    /**
     * @var \RatePAY\Payment\Helper\Payment
     */
    protected $paymentHelper;

    /**
     * ApiLog constructor.
     *
     * @param \RatePAY\Payment\Helper\Payment $paymentHelper
     */
    public function __construct(
        \Magento\Framework\Model\ResourceModel\Db\Context $context,
        \RatePAY\Payment\Helper\Payment $paymentHelper,
        $connectionName = null
    ) {
        parent::__construct($context, $connectionName);
        $this->paymentHelper = $paymentHelper;
    }

    /**
     * Initialize connection and table
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('ratepay_api_log', 'entity_id');
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
     * @param RequestBuilder $request
     * @param $order
     * @return string
     */
    protected function getTransactionId(RequestBuilder $request, $order = null)
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
     * @param RequestBuilder $request
     * @param $order
     * @return $this
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function addApiLogEntry(RequestBuilder $request, $order = null)
    {
        $requestXMLElement = $request->getRequestXmlElement();

        $this->getConnection()->insert(
            $this->getMainTable(),
            [
                'order_id' => !is_null($order) ? $order->getId() : null,
                'order_increment_id' => !is_null($order) ? $order->getIncrementId() : null,
                'transaction_id' => $this->getTransactionId($request, $order),
                'name' => !is_null($order) ? $order->getBillingAddress()->getFirstname()." ".$order->getBillingAddress()->getLastname() : null,
                'payment_method' => !is_null($order) ? strtoupper($this->paymentHelper->convertMethodToProduct($order->getPayment()->getMethod())) : null,
                'payment_type' => $requestXMLElement->head->{'operation'},
                'payment_subtype' => isset($requestXMLElement->head->operation->attributes()->subtype) ? strtoupper((string) $requestXMLElement->head->operation->attributes()->subtype) :null,
                'result' => $request->getResultMessage(),
                'request' => $this->getFormattedXml($requestXMLElement),
                'response' => $this->getFormattedXml($request->getResponseXmlElement()),
                'result_code' => $request->getResultCode(),
                'reason' => $request->getReasonMessage(),
            ]
        );
        return $this;
    }
}
