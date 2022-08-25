<?php

/**
 * Copyright (c) Ratepay GmbH
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace RatePAY\Payment\Model\ResourceModel;

use RatePAY\Payment\Model\SerializableRequest;

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
     * Mask the value of a given tag in the xml
     *
     * @param string $xmlString
     * @param string $tagName
     * @return string
     */
    protected function maskTagValue($xmlString, $tagName)
    {
        preg_match('#<'.$tagName.'>(\w*)<\/'.$tagName.'>#', $xmlString, $matches);
        $masked = '';
        for ($i = 0; $i < strlen($matches[1]); $i++) {
            $masked .= '*';
        }
        $replaceWith = '<'.$tagName.'>'.$masked.'</'.$tagName.'>';
        return str_replace($matches[0], $replaceWith, $xmlString);
    }

    /**
     * Masks certain values in the xml string
     *
     * @param string $xmlString
     * @return string
     */
    protected function maskXml($xmlString)
    {
        $xmlString = $this->maskTagValue($xmlString, 'securitycode');
        return $xmlString;
    }

    /**
     * @param SerializableRequest $request
     * @param $order
     * @return $this
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function addApiLogEntry(SerializableRequest $request, $order = null)
    {
        $this->getConnection()->insert(
            $this->getMainTable(),
            [
                'order_id' => !is_null($order) ? $order->getId() : null,
                'order_increment_id' => !is_null($order) ? $order->getIncrementId() : null,
                'transaction_id' => $request->getTransactionId(),
                'name' => !is_null($order) ? $order->getBillingAddress()->getFirstname()." ".$order->getBillingAddress()->getLastname() : null,
                'payment_method' => !is_null($order) ? strtoupper($this->paymentHelper->convertMethodToProduct($order->getPayment()->getMethod())) : null,
                'payment_type' => $request->getPaymentType(),
                'payment_subtype' => $request->getPaymentSubtype(),
                'result' => $request->getResult(),
                'request' => $this->maskXml($request->getRequest()),
                'response' => $request->getResponse(),
                'result_code' => $request->getResultCode(),
                'status_code' => $request->getStatusCode(),
                'reason' => $request->getReason(),
            ]
        );
        return $this;
    }
}
