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

namespace RatePAY\Payment\Helper\Content\Customer;

use Magento\Framework\App\Helper\Context;

class BankAccount extends \Magento\Framework\App\Helper\AbstractHelper
{
    /**
     * @var \Magento\Checkout\Model\Session
     */
    protected $_checkoutSession;

    /**
     * BankAccount constructor.
     *
     * @param Context                         $context
     * @param \Magento\Checkout\Model\Session $checkoutSession
     */
    public function __construct(
        Context $context,
                                \Magento\Checkout\Model\Session $checkoutSession
    ) {
        parent::__construct($context);

        $this->_checkoutSession = $checkoutSession;
    }

    /**
     * Build Bank Account block of payment request in customer block.
     *
     * @param $quoteOrOrder
     *
     * @return array
     */
    public function setBankAccount($quoteOrOrder)
    {
        return [
            'Owner' => $quoteOrOrder->getBillingAddress()->getFirstname().' '.$quoteOrOrder->getBillingAddress()->getLastname(),
            //'BankName' =>
            //'BankAccountNumber' => '1234567891',
            //'BankCode' => '12345678',
            'Iban' => $this->_checkoutSession->getRatepayIban(),
            //'BicSwift' =>
        ];
    }
}
