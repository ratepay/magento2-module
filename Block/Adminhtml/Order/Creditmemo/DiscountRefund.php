<?php

/**
 * Copyright (c) Ratepay GmbH
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace RatePAY\Payment\Block\Adminhtml\Order\Creditmemo;

use Magento\Sales\Model\Order;

class DiscountRefund extends \Magento\Backend\Block\Template
{
    /**
     * Sales admin helper
     *
     * @var \Magento\Sales\Helper\Admin
     */
    protected $_salesAdminHelper;

    /**
     * @var Order
     */
    protected $_order;

    /**
     * @var \Magento\Framework\DataObject
     */
    protected $_source;

    /**
     * @var \RatePAY\Payment\Helper\Payment
     */
    protected $rpPaymentHelper;

    /**
     * @var \RatePAY\Payment\Helper\Data
     */
    protected $rpDataHelper;

    /**
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\Sales\Helper\Admin $salesAdminHelper
     * @param \RatePAY\Payment\Helper\Payment $rpPaymentHelper
     * @param \RatePAY\Payment\Helper\Data $rpDataHelper
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Sales\Helper\Admin $salesAdminHelper,
        \RatePAY\Payment\Helper\Payment $rpPaymentHelper,
        \RatePAY\Payment\Helper\Data $rpDataHelper,
        array $data = []
    ) {
        $this->_salesAdminHelper = $salesAdminHelper;
        $this->rpPaymentHelper = $rpPaymentHelper;
        $this->rpDataHelper = $rpDataHelper;
        parent::__construct($context, $data);
    }

    /**
     * @return $this
     */
    public function initTotals()
    {
        $parent = $this->getParentBlock();
        $this->_order = $parent->getOrder();

        $paymentMethod = $this->_order->getPayment()->getMethodInstance()->getCode();
        if ($this->rpPaymentHelper->isRatepayPayment($paymentMethod) && $this->getRefundAmount() != 0) {
            $this->_source = $parent->getSource();

            $total = new \Magento\Framework\DataObject([
                'code' => 'ratepayrefund',
                'block_name' => $this->getNameInLayout()
            ]);
            $parent->addTotalBefore($total, 'tax');
        }
        return $this;
    }

    /**
     * Return positive adjustment return value
     *
     * @return float|int
     */
    public function getPositiveAdjustmentReturn()
    {
        $dCorrectionAmount = $this->getRefundAmount();
        if ($dCorrectionAmount < 0) {
            return $dCorrectionAmount * -1;
        }
        return 0.0;
    }

    /**
     * Return negative adjustment return value
     *
     * @return float|int
     */
    public function getNegativeAdjustmentReturn()
    {
        $dCorrectionAmount = $this->getRefundAmount();
        if ($dCorrectionAmount > 0) {
            return $dCorrectionAmount;
        }
        return 0.0;
    }

    /**
     * Returns refundable amount
     *
     * @return float
     */
    public function getRefundAmount()
    {
        $oOrder = $this->getOrder();
        $dRefundAmount = $oOrder->getAdjustmentPositive() - $oOrder->getAdjustmentNegative();
        return $dRefundAmount;
    }

    /**
     * Returns refundable amount
     *
     * @return float
     */
    public function getRefundAmountBase()
    {
        $oOrder = $this->getOrder();
        $dRefundAmountBase = $oOrder->getAdjustmentPositiveBase() - $oOrder->getAdjustmentNegativeBase();
        return $dRefundAmountBase;
    }

    /**
     * @param float $amount
     * @param float $baseAmount
     * @return mixed
     */
    public function displayAmount($amount, $baseAmount)
    {
        return $this->_salesAdminHelper->displayPrices($this->getSource(), $this->getRefundAmountBase(), $this->getRefundAmount(), false, '<br />');
    }

    /**
     * @return bool
     */
    public function isCheckboxChecked()
    {
        $paramCreditmemo = $this->rpDataHelper->getRequestParameter('creditmemo');
        if (isset($paramCreditmemo['ratepay_return_adjustments']) && (bool)$paramCreditmemo['ratepay_return_adjustments'] === true) {
            return true;
        }
        return false;
    }

    /**
     * @return Order
     */
    public function getOrder()
    {
        return $this->_order;
    }

    /**
     * Get data (totals) source model
     *
     * @return \Magento\Framework\DataObject
     */
    public function getSource()
    {
        return $this->_source;
    }
}
