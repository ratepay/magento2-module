<?php
/**
 * Created by PhpStorm.
 * User: SebastianN
 * Date: 02.03.17
 * Time: 15:39
 */

namespace RatePAY\Payment\Block\System\Config;

use Magento\Backend\Block\Template\Context;
use Magento\Config\Block\System\Config\Form\Field;
use Magento\Framework\Data\Form\Element\AbstractElement;;

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
     * @param array $data
     */
    public function __construct(
        Context $context,
        array $data = []
    ) {
        parent::__construct($context, $data);
    }

    /**
     * Remove scope label
     *
     * @param  AbstractElement $element
     * @return string
     */
    public function render(AbstractElement $element)
    {
        $element->unsScope()->unsCanUseWebsiteValue()->unsCanUseDefaultValue();
        return parent::render($element);
    }

    /**
     * Return element html
     *
     * @param  AbstractElement $element
     * @return string
     */
    protected function _getElementHtml(AbstractElement $element)
    {
        $this->_element = $element;
        return $this->_toHtml();
    }

    /**
     * Return ajax url for collect button
     *
     * @return string
     */
    public function getAjaxUrl()
    {
        return $this->getUrl('ratepay/system_config/profilerequest');
    }

    /**
     * Get RatePay payment method
     *
     * @param $id
     * @return string
     */
    private function _getRpMethod($id) {
        $pos = strpos($id, 'ratepay');
        $method = substr($id, $pos);
        if (stripos($id, '_backend') !== false) {
            $method = str_ireplace('_backend', '', $method).'_backend';
        }
        return $method;
    }

    /**
     * Returns container id
     *
     * @return string
     */
    public function getContainerId()
    {
        return $this->_element->getContainer()->getId();
    }

    /**
     * Generates method code from container id
     *
     * @return string
     */
    public function getPaymentMethod()
    {
        $id = $this->getContainerId();
        $id = $this->_getRpMethod($id);
        return $id;
    }

    /**
     * Generate collect button html
     *
     * @return string
     */
    public function getButtonHtml()
    {
        $button = $this->getLayout()->createBlock(
            'Magento\Backend\Block\Widget\Button'
        )->setData(
            [
                'id' => $this->_element->getContainer()->getId() . '_profilerequest_button',
                'label' => __('Get Config'),
            ]
        );

        return $button->toHtml();
    }
}
