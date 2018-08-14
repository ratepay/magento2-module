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

namespace RatePAY\Payment\Block\System\Config;

use Magento\Backend\Block\Template\Context;
use Magento\Config\Block\System\Config\Form\Field;
use Magento\Framework\Data\Form\Element\AbstractElement;

class ProfileRequest extends Field
{
    /**
     * @var null
     */
    protected $_element = null;

    /**
     * @var string
     */
    protected $_template = 'RatePAY_Payment::system/config/profilerequest.phtml';

    /**
     * @param Context $context
     * @param array   $data
     */
    public function __construct(
        Context $context,
        array $data = []
    ) {
        parent::__construct($context, $data);
    }

    /**
     * Remove scope label.
     *
     * @param AbstractElement $element
     *
     * @return string
     */
    public function render(AbstractElement $element)
    {
        $element->unsScope()->unsCanUseWebsiteValue()->unsCanUseDefaultValue();

        return parent::render($element);
    }

    /**
     * Return ajax url for collect button.
     *
     * @return string
     */
    public function getAjaxUrl()
    {
        return $this->getUrl('ratepay/system_config/profilerequest');
    }

    public function getPaymentMethod()
    {
        return $this->_element->getContainer()->getId();
    }

    /**
     * Generate collect button html.
     *
     * @return string
     */
    public function getButtonHtml()
    {
        $button = $this->getLayout()->createBlock(
            'Magento\Backend\Block\Widget\Button'
        )->setData(
            [
                'id' => $this->_element->getContainer()->getId().'_profilerequest_button',
                'label' => __('Get Config'),
            ]
        );

        return $button->toHtml();
    }

    /**
     * Return element html.
     *
     * @param AbstractElement $element
     *
     * @return string
     */
    protected function _getElementHtml(AbstractElement $element)
    {
        $this->_element = $element;

        return $this->_toHtml();
    }
}
