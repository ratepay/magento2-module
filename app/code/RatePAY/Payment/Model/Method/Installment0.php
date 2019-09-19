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
     * Can be used to install a different block for backend orders
     *
     * @var string
     */
    protected $_adminFormBlockType = 'RatePAY\Payment\Block\Form\Installment';

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
        foreach ($runtimes as $runtime) {
            if ($interestrateMonth > 0) { // otherwise division by zero error will happen
                $rateAmount = ceil($basketAmount * (($interestrateMonth * pow((1 + $interestrateMonth), $runtime)) / (pow((1 + $interestrateMonth), $runtime) - 1)));
            } else {
                $rateAmount = $basketAmount / $runtime;
            }

            if ($rateAmount >= $rateMinNormal) {
                $allowedRuntimes[] = $runtime;
            }
        }
        return $allowedRuntimes;
    }
}
