<?php

/**
 * Copyright (c) Ratepay GmbH
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace RatePAY\Payment\Service\V1\Data;

use RatePAY\Payment\Api\Data\SecciResponseInterface;

/**
 * Response object for secci trigger calls
 */
class SecciResponse extends \Magento\Framework\Api\AbstractExtensibleObject implements SecciResponseInterface
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
     * Returns filename of the secci pdf file
     *
     * @return string
     */
    public function getFilename()
    {
        return $this->_get('filename');
    }

    /**
     * Returns the secci pdf file as string
     *
     * @return string
     */
    public function getPdfString()
    {
        return $this->_get('pdfstring');
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
