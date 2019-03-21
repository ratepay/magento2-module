<?php
/**
 * Created by PhpStorm.
 * User: SebastianN
 * Date: 24.02.17
 * Time: 15:48
 */

namespace RatePAY\Payment\Model\Method;


class Installment extends AbstractMethod
{
    const METHOD_CODE = 'ratepay_installment';

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
        $rateMinNormal = $this->rpDataHelper->getRpConfigData($this->getCode(), 'rate_min');
        $runtimes = explode(",", $this->rpDataHelper->getRpConfigData($this->getCode(), 'month_allowed'));
        $interestrateMonth = ((float)$this->rpDataHelper->getRpConfigData($this->getCode(), 'interestrate_default') / 12) / 100;

        $allowedRuntimes = [];

        if ($interestrateMonth > 0) { // otherwise division by zero error will happen
            foreach ($runtimes as $runtime) {
                $rateAmount = ceil($basketAmount * (($interestrateMonth * pow((1 + $interestrateMonth), $runtime)) / (pow((1 + $interestrateMonth), $runtime) - 1)));

                if ($rateAmount >= $rateMinNormal) {
                    $allowedRuntimes[] = $runtime;
                }
            }
        }
        
        return $allowedRuntimes;
    }
}
