<?php

/**
 * RatePAY Payments - Magento 2
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
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
     *
     * @param Context $context
     */
    public function __construct(
        Context $context,
                                \Magento\Customer\Model\Session $customerSession
    ) {
        parent::__construct($context);

        $this->customerSession = $customerSession;
    }

    /**
     * Build additional Block of Head Section.
     *
     * @param $resultInit
     * @param /app/code/RatePAY/Payment/Model/Library/src/ModelBuilder $headModel
     *
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
