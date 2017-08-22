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
     * @var string
     */
    protected $_infoBlockType = 'RatePAY\Payment\Block\Info\Info';

}

