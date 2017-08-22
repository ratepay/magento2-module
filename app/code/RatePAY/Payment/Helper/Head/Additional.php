<?php
/**
 * Created by PhpStorm.
 * User: SebastianN
 * Date: 09.02.17
 * Time: 11:58
 */

namespace RatePAY\Payment\Helper\Head;


use Magento\Framework\App\Helper\Context;

class Additional extends \Magento\Framework\App\Helper\AbstractHelper
{
    /**
     * @var \Magento\Customer\Model\Session
     */
    protected $customerSession;

    /**
     * Additional constructor.
     * @param Context $context
     */
    public function __construct(Context $context,
                                \Magento\Customer\Model\Session $customerSession)
    {
        parent::__construct($context);

        $this->customerSession = $customerSession;
    }

    /**
     * Build additional Block of Head Section
     *
     * @param $resultInit
     * @param /app/code/RatePAY/Payment/Model/Library/src/ModelBuilder $headModel
     * @return /app/code/RatePAY/Payment/Model/Library/src/ModelBuilder $headModel
     */
    public function setHeadAdditional($resultInit, $headModel)
    {
        $headModel->setTransactionId($resultInit->getTransactionId());
        $headModel->setCustomerDevice(
            $headModel->CustomerDevice()->setDeviceToken($this->customerSession->getRatePayDeviceIdentToken())
        );

        return $headModel;
    }
}
