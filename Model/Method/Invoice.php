<?php

/**
 * Copyright (c) Ratepay GmbH
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace RatePAY\Payment\Model\Method;

class Invoice extends AbstractMethod
{
    const METHOD_CODE = 'ratepay_invoice';

    protected $_code = self::METHOD_CODE;

    /**
     * Can be used to install a different block for backend orders
     *
     * @var string
     */
    protected $_adminFormBlockType = 'RatePAY\Payment\Block\Form\Invoice';

    /**
     * @var string
     */
    protected $_infoBlockType = 'RatePAY\Payment\Block\Info\Info';
}
