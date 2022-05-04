<?php


namespace RatePAY\Payment\Model\ResourceModel;

class OrderAdjustment extends \Magento\Framework\Model\ResourceModel\Db\AbstractDb
{
    /**
     * Initialize connection and table
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('ratepay_order_adjustments', 'entity_id');
    }

    /**
     * Add new order adjustment
     *
     * @param  int    $orderId
     * @param  string $type
     * @param  string $article_number
     * @param  float  $adjustment
     * @param  float  $adjustment_base
     * @param  bool   $is_specialitem
     * @return $this
     */
    public function addOrderAdjustment($orderId, $type, $article_number, $adjustment, $adjustment_base, $is_specialitem = false)
    {
        $this->getConnection()->insert(
            $this->getMainTable(),
            [
                'order_id' => $orderId,
                'adjustment_type' => $type,
                'article_number' => $article_number,
                'amount' => $adjustment,
                'base_amount' => $adjustment_base,
                'is_specialitem' => (int)$is_specialitem
            ]
        );
        return $this;
    }

    /**
     * Get order adjustments for given order id
     *
     * @param  int $orderId
     * @return array
     */
    public function getOrderAdjustments($orderId)
    {
        $oSelect = $this->getConnection()->select()
            ->from($this->getMainTable(), ['adjustment_type', 'article_number', 'amount', 'base_amount', 'is_specialitem'])
            ->where("order_id = :orderId")
            ->where("is_returned = 0");

        $aParams = ['orderId' => $orderId];

        return $this->getConnection()->fetchAll($oSelect, $aParams);
    }

    /**
     * Get order adjustments for given order id by type
     *
     * @param  int     $orderId
     * @param  string  $type
     * @return array
     */
    public function getOrderAdjustmentsByType($orderId, $type)
    {
        $oSelect = $this->getConnection()->select()
            ->from($this->getMainTable(), ['adjustment_type', 'article_number', 'amount', 'base_amount', 'is_specialitem'])
            ->where("order_id = :orderId")
            ->where("adjustment_type = :adjustmentType");

        $aParams = [
            'orderId' => $orderId,
            'adjustmentType' => $type,
        ];

        return $this->getConnection()->fetchAll($oSelect, $aParams);
    }

    /**
     * @param  string $orderId
     * @param  string $type
     * @return int
     */
    public function getNextArticleNumberCounter($orderId, $type)
    {
        $adjustments = $this->getOrderAdjustmentsByType($orderId, $type);
        return count($adjustments) + 1;
    }

    /**
     * @param  int $orderId
     * @return void
     */
    public function setAdjustmentsToReturned($orderId)
    {
        $data = ['is_returned' => 1];
        $where = ['order_id = ?' => $orderId];
        $this->getConnection()->update($this->getMainTable(), $data, $where);
    }
}
