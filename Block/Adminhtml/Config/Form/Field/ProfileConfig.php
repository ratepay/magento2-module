<?php

/**
 * Copyright (c) Ratepay GmbH
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace RatePAY\Payment\Block\Adminhtml\Config\Form\Field;

class ProfileConfig extends \Magento\Config\Block\System\Config\Form\Field\FieldArray\AbstractFieldArray
{
    /**
     * Element factory
     *
     * @var \Magento\Framework\Data\Form\Element\Factory
     */
    protected $elementFactory;

    /**
     * @var \Magento\Config\Model\Config\Source\Yesno
     */
    protected $yesNo;

    /**
     * Constructor
     *
     * @param \Magento\Backend\Block\Template\Context      $context
     * @param \Magento\Framework\Data\Form\Element\Factory $elementFactory
     * @param \Magento\Config\Model\Config\Source\Yesno    $yesNo
     * @param array                                        $data
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Framework\Data\Form\Element\Factory $elementFactory,
        \Magento\Config\Model\Config\Source\Yesno $yesNo,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->elementFactory = $elementFactory;
        $this->yesNo = $yesNo;
    }

    /**
     * Initialise form fields
     *
     * @return void
     */
    protected function _construct()
    {
        $this->addColumn('profileId', ['label' => __('ProfileID')]);
        $this->addColumn('securityCode', ['label' => __('Security Code')]);
        $this->addColumn('sandbox', ['label' => __('Sandbox active')]);
        $this->_addAfter = false;
        $this->_addButtonLabel = __('Add Profile');
        parent::_construct();
    }

    /**
     * Render array cell for prototypeJS template
     *
     * @param  string $columnName
     * @return string
     */
    public function renderCellTemplate($columnName)
    {
        if ($columnName != 'sandbox') {
            return parent::renderCellTemplate($columnName);
        }
        $aOptions = $this->yesNo->toOptionArray(); // add transction status action options to dropdown

        $oElement = $this->elementFactory->create('select');
        $oElement->setForm($this->getForm());
        $oElement->setName($this->_getCellInputElementName($columnName));
        $oElement->setHtmlId($this->_getCellInputElementId('<%- _id %>', $columnName));
        $oElement->setValues($aOptions);
        return str_replace("\n", '', $oElement->getElementHtml());
    }

    /**
     * Obtain existing data from form element
     *
     * Each row will be instance of \Magento\Framework\DataObject
     *
     * @return array
     */
    public function getArrayRows()
    {
        $result = [];
        /** @var \Magento\Framework\Data\Form\Element\AbstractElement */
        $element = $this->getElement();
        $aValue = $element->getValue(); // get values
        if (is_array($aValue) === false) { // no array given? -> value from config.xml
            $aValue = json_decode($aValue, true); // convert string to array
        }
        if ($aValue && is_array($aValue)) {
            foreach ($aValue as $rowId => $row) {
                $rowColumnValues = [];
                foreach ($row as $key => $value) {
                    $row[$key] = $value;
                    $rowColumnValues[$this->_getCellInputElementId($rowId, $key)] = $row[$key]; // add value the row
                }
                $row['_id'] = $rowId;
                $row['column_values'] = $rowColumnValues;
                $result[$rowId] = new \Magento\Framework\DataObject($row);
                $this->_prepareArrayRow($result[$rowId]);
            }
        }
        return $result;
    }
}
