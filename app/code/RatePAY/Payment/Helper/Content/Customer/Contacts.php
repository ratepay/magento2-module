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

class Contacts extends \Magento\Framework\App\Helper\AbstractHelper
{
    /**
     * @var \Magento\Checkout\Model\Session
     */
    protected $_checkoutSession;

    /**
     * Contacts constructor.
     *
     * @param Context $context
     */
    public function __construct(
        Context $context,
                                \Magento\Checkout\Model\Session $checkoutSession
    ) {
        parent::__construct($context);
        $this->_checkoutSession = $checkoutSession;
    }

    /**
     * Build Contacts Block of Payment Request.
     *
     * @param $quoteOrOrder
     *
     * @return array
     */
    public function setContacts($quoteOrOrder)
    {
        $content = [
            'Email' => $quoteOrOrder->getCustomerEmail(),
        ];

        /*if (!empty($this->_checkoutSession->getRatepayPhone())) {
            $content['Phone']['DirectDial'] = $this->_checkoutSession->getRatepayPhone();
        } else {
            $content['Phone']['DirectDial'] = $quoteOrOrder->getBillingAddress()->getTelephone();
        }*/

        if (!empty($this->_checkoutSession->getRatepayPhone())) {
            $content['Phone']['DirectDial'] = $this->_checkoutSession->getRatepayPhone();
        } elseif (!empty($quoteOrOrder->getBillingAddress()->getTelephone())) {
            $content['Phone']['DirectDial'] = $quoteOrOrder->getBillingAddress()->getTelephone();
        } else { // Mock of RatePAY phone number in case of missing customer phone number
            $content['Phone']['AreaCode'] = '030';
            $content['Phone']['DirectDial'] = '33988560';
        }

        return $content;
    }
}
