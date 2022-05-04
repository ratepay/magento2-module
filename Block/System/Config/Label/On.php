<?php
/**
 * Created by PhpStorm.
 * User: SebastianN
 * Date: 06.03.17
 * Time: 17:36
 */

namespace RatePAY\Payment\Block\System\Config\Label;

use Magento\Backend\Block\Template\Context;
use Magento\Config\Block\System\Config\Form\Field;
use Magento\Framework\Data\Form\Element\AbstractElement;

class On extends Field
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
        $status = ((int) $elementData['value'] == 1);
        $text = ($status) ? 'Yes' : 'No';
        return __($text);
    }
}
