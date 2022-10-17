<?php

/**
 * Copyright (c) Ratepay GmbH
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace RatePAY\Payment\Helper\Head;

use Magento\Framework\App\Helper\Context;

class External extends \Magento\Framework\App\Helper\AbstractHelper
{
    /**
     * External constructor.
     * @param Context $context
     */
    public function __construct(Context $context)
    {
        parent::__construct($context);
    }

    /**
     * Build External Block of Head Section
     *
     * @param $quoteOrOrder
     * @param /app/code/RatePAY/Payment/Model/Library/src/ModelBuilder $head
     * @return /app/code/RatePAY/Payment/Model/Library/src/ModelBuilder $headModel
     */
    public function setHeadExternal($quoteOrOrder, $headModel)
    {
        $headModel->setArray([
            'External' => [
                'MerchantConsumerId' => $quoteOrOrder->getCustomerId() ?? '', // Customer Id
                'OrderId' => $quoteOrOrder->getRealOrderId()
            ]
        ]);

        return $headModel;
    }
}
