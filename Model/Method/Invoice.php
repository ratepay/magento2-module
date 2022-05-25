<?php

namespace RatePAY\Payment\Model\Method;

class Invoice extends AbstractMethod
{
    const METHOD_CODE = 'ratepay_invoice';

    protected $_code = self::METHOD_CODE;

    /**
     * @var string
     */
    protected $_formBlockType = 'RatePAY\Payment\Block\Form\Dfp';

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
