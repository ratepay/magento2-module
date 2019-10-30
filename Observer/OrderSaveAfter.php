<?php

namespace RatePAY\Payment\Observer;

use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Event\Observer;
use Magento\Sales\Model\Order;

class OrderSaveAfter implements ObserverInterface
{
    /**
     * @var \Magento\Framework\App\ResourceConnection
     */
    protected $databaseResource;

    /**
     * Constructor.
     *
     * @param \Magento\Framework\App\ResourceConnection $resource
     */
    public function __construct(\Magento\Framework\App\ResourceConnection $resource)
    {
        $this->databaseResource = $resource;
    }

    /**
     * Update API log database entities with order id, since the order id was not yet available during creation
     *
     * @param Order $order
     * @return void
     */
    protected function updateOrderIdForApiLog(Order $order)
    {
        $transactionId = $order->getPayment()->getAdditionalInformation('transactionId');
        if (!empty($transactionId)) {
            $table = $this->databaseResource->getTableName('ratepay_api_log');
            $data = [
                'order_id' => $order->getId(),
                'order_increment_id' => $order->getIncrementId(),
            ];
            $where = ['transaction_id = ?' => $transactionId];
            $this->databaseResource->getConnection()->update($table, $data, $where);
        }
    }

    /**
     * Used to unset session flags after a finished order
     *
     * @param  Observer $observer
     * @return void
     */
    public function execute(Observer $observer)
    {
        $order = $observer->getOrder();
        if ($order && $order->getId()) {
            $this->updateOrderIdForApiLog($order);
        }
    }
}
