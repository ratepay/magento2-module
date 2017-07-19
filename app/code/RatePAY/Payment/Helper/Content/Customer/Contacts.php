<?php
/**
 * Created by PhpStorm.
 * User: SebastianN
 * Date: 09.02.17
 * Time: 16:00
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
     * @param Context $context
     */
    public function __construct(Context $context,
                                \Magento\Checkout\Model\Session $checkoutSession)
    {
        parent::__construct($context);
        $this->_checkoutSession = $checkoutSession;
    }

    /**
     * Build Contacts Block of Payment Request
     *
     * @param $quoteOrOrder
     * @return array
     */
    public function setContacts($quoteOrOrder)
    {
        $content = [
            'Email' => $quoteOrOrder->getCustomerEmail(),
            //'Mobile' => "0123 4567890",
            //'Fax' => "012 3456777",
        ];
        if(!empty($quoteOrOrder->getBillingAddress()->getTelephone())){
            $content['Phone']['DirectDial'] = $quoteOrOrder->getBillingAddress()->getTelephone();
        } else{
            $content['Phone']['DirectDial'] = $this->_checkoutSession->getRatepayPhone();
        }
        return $content;
    }
}
