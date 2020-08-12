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
            try {
                $table = $this->databaseResource->getTableName('ratepay_api_log');
                $data = [
                    'order_id' => $order->getId(),
                    'order_increment_id' => $order->getIncrementId(),
                ];
                $where = ['transaction_id = ?' => $transactionId];
                $this->databaseResource->getConnection()->update($table, $data, $where);
            } catch (\Exception $exc) {
                // do nothing - if this section fails because of a DB deadlock or something else, a DB rollback will happen which will result in RatePay waiting for money for an order that doesnt exist
                error_log('RatePay Error: Was not able to update ratepay_api_log table with order_id = '.$order->getId().' and order_increment_id = '.$order->getIncrementId().' for transaction_id = '.$transactionId.' Original Exception: '.$exc->getMessage());
            }
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
