<?php
/**
 * Created by PhpStorm.
 * User: SebastianN
 * Date: 24.02.17
 * Time: 15:48
 */

namespace RatePAY\Payment\Model\Method;


class Installment0 extends AbstractMethod
{
    const METHOD_CODE = 'ratepay_installment0';

    protected $_code = self::METHOD_CODE;

    /**
     * @var string
     */
    protected $_infoBlockType = 'RatePAY\Payment\Block\Info\Info';

    /**
     * Generates allowed months
     *
     * @param double $basketAmount
     * @return array
     */
    public function getAllowedMonths($basketAmount)
    {
        return explode(",", $this->rpDataHelper->getRpConfigData($this->getCode(), 'month_allowed'));
    }
}
