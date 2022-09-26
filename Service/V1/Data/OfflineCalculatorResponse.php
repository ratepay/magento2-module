<?php

/**
 * Copyright (c) Ratepay GmbH
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace RatePAY\Payment\Service\V1\Data;

use RatePAY\Payment\Api\Data\OfflineCalculatorResponseInterface;

/**
 * Response object for offline calculator response
 */
class OfflineCalculatorResponse extends \Magento\Framework\Api\AbstractExtensibleObject implements OfflineCalculatorResponseInterface
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
     * Return monthly instalment rate
     *
     * @return string
     */
    public function getMonthlyInstalment()
    {
        return $this->_get('monthlyInstalment');
    }

    /**
     * Returns number of months for instalment
     *
     * @return string
     */
    public function getMonths()
    {
        return $this->_get('months');
    }

    /**
     * Returns instalment text for frontend
     *
     * @return string
     */
    public function getText()
    {
        return $this->_get('text');
    }
}
