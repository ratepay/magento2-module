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

class External extends \Magento\Framework\App\Helper\AbstractHelper
{
    /**
     * External constructor.
     *
     * @param Context $context
     */
    public function __construct(Context $context)
    {
        parent::__construct($context);
    }

    /**
     * Build External Block of Head Section.
     *
     * @param $quoteOrOrder
     * @param /app/code/RatePAY/Payment/Model/Library/src/ModelBuilder $head
     * @param mixed                                                    $headModel
     *
     * @return /app/code/RatePAY/Payment/Model/Library/src/ModelBuilder $headModel
     */
    public function setHeadExternal($quoteOrOrder, $headModel)
    {
        $headModel->setArray([
            'External' => [
                'MerchantConsumerId' => $quoteOrOrder->getCustomerId(), // Customer Id
                'OrderId' => $quoteOrOrder->getRealOrderId(),
            ],
        ]);

        return $headModel;
    }
}
