<?php

namespace RatePAY\Payment\Plugin;

use Magento\Sales\Model\Service\CreditmemoService as CreditmemoServiceOriginal;
use Magento\Sales\Api\Data\CreditmemoInterface;
use Magento\Framework\Exception\LocalizedException;

class CreditmemoService
{
    /**
     * Checkout session model
     *
     * @var \Magento\Checkout\Model\Session
     */
    protected $checkoutSession;

    /**
     * API-log resource model
     *
     * @var \RatePAY\Payment\Model\ResourceModel\ApiLog
     */
    protected $apiLog;

    /**
     * Constructor
     *
     * @param \Magento\Checkout\Model\Session $checkoutSession
     * @param \RatePAY\Payment\Model\ResourceModel\ApiLog $apiLog
     */
    public function __construct(
        \Magento\Checkout\Model\Session $checkoutSession,
        \RatePAY\Payment\Model\ResourceModel\ApiLog $apiLog
    ) {
        $this->checkoutSession = $checkoutSession;
        $this->apiLog = $apiLog;
    }

    /**
     * @param  CreditmemoServiceOriginal $subject
     * @param  callable                  $proceed
     * @param  CreditmemoInterface       $creditmemo
     * @param  bool                      $offlineRequested
     * @return CreditmemoInterface
     * @throws LocalizedException|\Exception
     */
    public function aroundRefund(
        CreditmemoServiceOriginal $subject,
        callable $proceed,
        CreditmemoInterface $creditmemo,
        $offlineRequested = false
    ) {
        try {
            $return = $proceed($creditmemo, $offlineRequested);
        } catch (\Exception $ex) {
            $request = $this->checkoutSession->getRatepayRequest();
            if (!empty($request)) {
                // Rewrite the log-entry after it was rolled back in the db-transaction
                $this->apiLog->addApiLogEntry($request, $creditmemo->getOrder());
            }
            $this->checkoutSession->unsRatepayRequest();
            throw $ex;
        }
        $this->checkoutSession->unsRatepayRequest();
        return $return;
    }
}
