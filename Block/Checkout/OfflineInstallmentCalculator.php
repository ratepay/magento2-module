<?php

/**
 * Copyright (c) Ratepay GmbH
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace RatePAY\Payment\Block\Checkout;

use Magento\Catalog\Model\Product;
use Magento\Framework\View\Element\Template\Context;
use RatePAY\ModelBuilder;
use RatePAY\Service\OfflineInstallmentCalculation;

class OfflineInstallmentCalculator extends \Magento\Framework\View\Element\Template
{
    /**
     * @var Product
     */
    protected $_product = null;

    /**
     * @var \RatePAY\Payment\Helper\Data
     */
    protected $rpDataHelper;

    /**
     * @param \Magento\Framework\View\Element\Template\Context $context
     * @param \RatePAY\Payment\Helper\Data $rpHelper
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \RatePAY\Payment\Helper\Data $rpHelper,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->rpDataHelper = $rpHelper;
    }

    /**
     * Return currently used currency
     *
     * @return string
     */
    public function getCurrentCurrency()
    {
        return $this->_storeManager->getStore()->getCurrentCurrency()->getCode();
    }

    /**
     * Returns URL for REST API call
     *
     * @return string
     */
    public function getRestCallUrl()
    {
        return $this->_urlBuilder->getUrl('rest/'.$this->_storeManager->getStore()->getCode().'/V1/ratepay/offlineCalc');
    }

    /**
     * Check if calculator was activated in the configuration
     *
     * @return bool
     */
    public function isCalculatorActivated()
    {
        if (empty($this->rpDataHelper->getRpConfigDataByPath("ratepay/general/instalment_plan_billing_country"))) {
            return false;
        }

        if (empty($this->rpDataHelper->getRpConfigDataByPath("ratepay/general/instalment_plan_shipping_country"))) {
            return false;
        }

        return (bool)$this->rpDataHelper->getRpConfigDataByPath("ratepay/general/product_page_instalment_plan");
    }
}
