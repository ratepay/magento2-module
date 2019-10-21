<?php

namespace RatePAY\Payment\Block\Adminhtml\Order\View\Tab;

use Magento\Framework\Phrase;

class ApiLog extends \Magento\Framework\View\Element\Text\ListText implements \Magento\Backend\Block\Widget\Tab\TabInterface
{
    /**
     * Get label for the tab
     *
     * @return Phrase
     */
    public function getTabLabel()
    {
        return __('RatePAY - API log');
    }

    /**
     * Get title for the tab
     *
     * @return Phrase
     */
    public function getTabTitle()
    {
        return __('RatePAY - API log');
    }

    /**
     * Return if the tab can be shown
     *
     * @return bool
     */
    public function canShowTab()
    {
        return true;
    }

    /**
     * Return if the tab is hidden
     *
     * @return bool
     */
    public function isHidden()
    {
        return false;
    }
}
