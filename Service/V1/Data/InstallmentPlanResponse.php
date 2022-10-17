<?php

/**
 * Copyright (c) Ratepay GmbH
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace RatePAY\Payment\Service\V1\Data;

use RatePAY\Payment\Api\Data\InstallmentPlanResponseInterface;

/**
 * Response object for installment plan
 */
class InstallmentPlanResponse extends \Magento\Framework\Api\AbstractExtensibleObject implements InstallmentPlanResponseInterface
{
    /**
     * Returns if the request was a success
     *
     * @return bool
     */
    public function getSuccess()
    {
        return $this->_get('success');
    }

    /**
     * Return json installment plan
     *
     * @return string
     */
    public function getInstallmentPlan()
    {
        return $this->_get('installmentPlan');
    }

    /**
     * Return installment plan html
     *
     * @return string
     */
    public function getInstallmentHtml()
    {
        return $this->_get('installmentHtml');
    }

    /**
     * Returns errormessage
     *
     * @return string
     */
    public function getErrormessage()
    {
        return $this->_get('errormessage');
    }
}
