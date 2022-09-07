<?php

/**
 * Copyright (c) Ratepay GmbH
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace RatePAY\Payment\Api\Data;

/**
 * Response interface for installment plan
 */
interface InstallmentPlanResponseInterface
{
    /**
     * Returns if the request was a success
     *
     * @return bool
     */
    public function getSuccess();

    /**
     * Return json installment plan
     *
     * @return string
     */
    public function getInstallmentPlan();

    /**
     * Return installment plan html
     *
     * @return string
     */
    public function getInstallmentHtml();

    /**
     * Returns errormessage
     *
     * @return string
     */
    public function getErrormessage();
}
