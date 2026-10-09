<?php

/**
 * Copyright (c) Ratepay GmbH
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace RatePAY\Payment\Api\Data;

/**
 * Response interface for secci trigger calls
 */
interface SecciResponseInterface
{
    /**
     * Returns if the request was a success
     *
     * @return bool
     */
    public function getSuccess();

    /**
     * Returns filename of the secci pdf file
     *
     * @return string
     */
    public function getFilename();

    /**
     * Returns the secci pdf file as string
     *
     * @return string
     */
    public function getPdfString();

    /**
     * Returns errormessage
     *
     * @return string
     */
    public function getErrormessage();
}
