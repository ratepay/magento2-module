<?php

/**
 * Copyright (c) Ratepay GmbH
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
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
     * Get currently selected scope of configuration
     *
     * @return \Magento\Store\Model\Store
     */
    public function getCurrentStore()
    {
        $aRequestParams = $this->getRequest()->getParams();
        if (isset($aRequestParams['website'])) {
            return $this->_storeManager->getWebsite($aRequestParams['website']);
        }

        if (isset($aRequestParams['store'])) {
            return $this->_storeManager->getStore($aRequestParams['store']);
        }

        return $this->_storeManager->getStore();
    }

    /**
     * Returns current config scope
     *
     * @return string
     */
    public function getScope()
    {
        $aRequestParams = $this->getRequest()->getParams();
        if (isset($aRequestParams['website'])) {
            return 'websites';
        } elseif (isset($aRequestParams['store'])) {
            return 'stores';
        }
        return 'default';
    }

    /**
     * Returns current config scope id
     *
     * @return string
     */
    public function getScopeId()
    {
        $aRequestParams = $this->getRequest()->getParams();
        if (isset($aRequestParams['website'])) {
            return $aRequestParams['website'];
        } elseif (isset($aRequestParams['store'])) {
            return $aRequestParams['store'];
        }
        return 0;
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
    private function _getRpMethod($id)
    {
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
