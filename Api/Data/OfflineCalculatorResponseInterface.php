<?php

/**
 * Copyright (c) Ratepay GmbH
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace RatePAY\Payment\Api\Data;

/**
 * Response interface for offline calculator reponse
 */
interface OfflineCalculatorResponseInterface
{
    /**
     * Returns if the request was a success
     *
     * @return bool
     */
    public function getSuccess();

    /**
     * Return monthly instalment rate
     *
     * @return string
     */
    public function getMonthlyInstalment();

    /**
     * Returns number of months for instalment
     *
     * @return string
     */
    public function getMonths();

    /**
     * Returns instalment text for frontend
     *
     * @return string
     */
    public function getText();
}
