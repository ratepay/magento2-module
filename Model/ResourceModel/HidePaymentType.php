<?php


namespace RatePAY\Payment\Model\ResourceModel;

class HidePaymentType extends \Magento\Framework\Model\ResourceModel\Db\AbstractDb
{
    /**
     * Time to hide a payment type in hours
     *
     * @var int
     */
    protected $hideDuration = 48;

    /**
     * Initialize connection and table
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('ratepay_hide_payment_type', 'entity_id');
    }

    /**
     * Generate date string of the end of the hiding period
     *
     * @param  int $hourDuration
     * @return string
     */
    public function getHideEndDate($hourDuration)
    {
        return date('Y-m-d H:i:s', (time() + (60 * 60 * $hourDuration)));
    }

    /**
     * Add new hidden payment type
     *
     * @param  string $paymentType
     * @param  int    $customerId
     * @return $this
     */
    public function addHiddenPaymentType($paymentType, $customerId)
    {
        $this->getConnection()->insert(
            $this->getMainTable(),
            [
                'customer_id' => $customerId,
                'payment_type' => $paymentType,
                'to_date' => $this->getHideEndDate($this->hideDuration)
            ]
        );
        return $this;
    }

    /**
     * Get hidden payment types for the given customer
     *
     * @param  int $customerId
     * @return array
     */
    public function getHiddenPaymentTypes($customerId)
    {
        $oSelect = $this->getConnection()->select()
            ->from($this->getMainTable(), ['payment_type', 'to_date'])
            ->where("customer_id = :customerId")
            ->where("to_date > :toDate")
            ->order('to_date ASC');

        $aParams = [
            'customerId' => $customerId,
            'toDate' => date('Y-m-d H:i:s')
        ];

        $aResult = $this->getConnection()->fetchAll($oSelect, $aParams);

        $aReturn = [];
        foreach ($aResult as $aItem) {
            $aReturn[] = $aItem['payment_type'];
        }
        return $aReturn;
    }
}
