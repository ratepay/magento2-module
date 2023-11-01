<?php

/**
 * Copyright (c) Ratepay GmbH
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace RatePAY\Payment\Service\V1\Data;

use RatePAY\Payment\Api\Data\DfpSentResponseInterface;

/**
 * Response object for installment plan
 */
class DfpSentResponse extends \Magento\Framework\Api\AbstractExtensibleObject implements DfpSentResponseInterface
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
}
