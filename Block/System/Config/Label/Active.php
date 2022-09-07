<?php

/**
 * Copyright (c) Ratepay GmbH
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace RatePAY\Payment\Block\System\Config\Label;

use Magento\Backend\Block\Template\Context;
use Magento\Config\Block\System\Config\Form\Field;
use Magento\Framework\Data\Form\Element\AbstractElement;

class Active extends Field
{
    protected $_element = null;

    public function __construct(
        Context $context,
        array $data = []
    ) {
        parent::__construct(
            $context,
            $data
        );
    }

    /**
     * Return element html
     *
     * @param  AbstractElement $element
     * @return string
     */
    protected function _getElementHtml(AbstractElement $element)
    {
        $elementData = $element->getData();
        $status = ((int) $elementData['value']);
        switch ($status) {
            case 1:
                $text = 'Inactive';
                $color = 'Red';
                break;
            case 2:
                $text = 'Active';
                $color = 'Green';
                break;
            case 3:
                $text = 'Phased out';
                $color = 'Orange';
                break;
            case null:
                $text = '';
                $color = 'White';
                break;
        }
        return '<p style="font-weight:bold; color: ' . $color . '">' . __($text) . '</p>';
    }
}
