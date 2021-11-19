<?php

namespace RatePAY\Payment\Block\Adminhtml\Config\Fieldset;

/**
 * Ratepay payment logo fieldset
 */
class Payment extends \Magento\Config\Block\System\Config\Form\Fieldset
{
    /**
     * @var \Magento\Framework\Module\ModuleListInterface
     */
    protected $moduleList;

    /**
     * @param \Magento\Backend\Block\Context $context
     * @param \Magento\Backend\Model\Auth\Session $authSession
     * @param \Magento\Framework\View\Helper\Js $jsHelper
     * @param \Magento\Framework\Module\ModuleListInterface $moduleList,
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Context $context,
        \Magento\Backend\Model\Auth\Session $authSession,
        \Magento\Framework\View\Helper\Js $jsHelper,
        \Magento\Framework\Module\ModuleListInterface $moduleList,
        array $data = []
    ) {
        parent::__construct($context, $authSession, $jsHelper, $data);
        $this->moduleList = $moduleList;

    }

    /**
     * Add custom css class
     *
     * @param \Magento\Framework\Data\Form\Element\AbstractElement $element
     * @return string
     */
    protected function _getFrontendClass($element)
    {
        return parent::_getFrontendClass($element) . ' with-button enabled';
    }

    /**
     * Return header title part of html for payment solution
     *
     * @param \Magento\Framework\Data\Form\Element\AbstractElement $element
     * @return string
     * @SuppressWarnings(PHPMD.NPathComplexity)
     */
    protected function _getHeaderTitleHtml($element)
    {
        $htmlId = $element->getHtmlId();

        $html  = '<div class="config-heading" >';
        $html .= '    <div class="button-container">';
        $html .= '        <button type="button" class="button action-configure" id="'.$htmlId.'-head" onclick="ratepayToggleSolution.call(this, \''.$htmlId."', '".$this->getUrl('adminhtml/*/state').'\'); return false;">';
        $html .= '            <span class="state-closed">' . __('Configure') . '</span>';
        $html .= '            <span class="state-opened">' . __('Close') . '</span>';
        $html .= '        </button>';
        $html .= '    </div>';
        $html .= '    <div class="heading">';
        $html .= '        <strong>' . $element->getLegend() . '</strong>';
        $html .= '        <span class="heading-intro">';
        $html .= '            <div class="ratepay-logo"></div>';
        $html .= '            <div class="payment-text">' . $element->getComment() . '</div>';
        $html .= '        </span>';
        $html .= '        <div class="config-alt"></div>';
        $html .= '    </div>';
        $html .= '    <div id="ratepay_version_number">'.__('Module version').': '.$this->moduleList->getOne('RatePAY_Payment')['setup_version'].'</div>';
        $html .= '</div>';

        return $html;
    }

    /**
     * Return header comment part of html for payment solution
     *
     * @param \Magento\Framework\Data\Form\Element\AbstractElement $element
     * @return string
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    protected function _getHeaderCommentHtml($element)
    {
        return '';
    }

    /**
     * Get collapsed state on-load
     *
     * @param \Magento\Framework\Data\Form\Element\AbstractElement $element
     * @return false
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    protected function _isCollapseState($element)
    {
        return false;
    }

    /**
     * Return extra Js.
     *
     * @param \Magento\Framework\Data\Form\Element\AbstractElement $element
     * @return string
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    protected function _getExtraJs($element)
    {
        $script = "require(['jquery', 'prototype'], function(jQuery){
            window.ratepayToggleSolution = function (id, url) {
                var doScroll = false;
                Fieldset.toggleCollapse(id, url);
                jQuery('#ratepay_version_number').toggle();
                if ($(this).hasClassName(\"open\")) {
                    \$$(\".with-button button.button\").each(function(anotherButton) {
                        if (anotherButton != this && $(anotherButton).hasClassName(\"open\")) {
                            $(anotherButton).click();
                            doScroll = true;
                        }
                    }.bind(this));
                }
                if (doScroll) {
                    var pos = Element.cumulativeOffset($(this));
                    window.scrollTo(pos[0], pos[1] - 45);
                }
            }
        });";

        return $this->_jsHelper->getScript($script);
    }
}
