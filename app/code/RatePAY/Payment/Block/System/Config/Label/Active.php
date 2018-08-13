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
     * Return element html.
     *
     * @param AbstractElement $element
     *
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

        return '<p style="font-weight:bold; color: '.$color.'">'.__($text).'</p>';
    }
}
