<?php

namespace RatePAY\Payment\Block\Info;

class Info extends \Magento\Payment\Block\Info
{
    protected $_transactionId;

    protected $_descriptor;

    protected $_template = 'RatePAY_Payment::info/info.phtml';

    /**
     * Enter description here...
     *
     * @return string
     */
    public function getTransactionId()
    {
        if ($this->_transactionId === null) {
            $this->_convertAdditionalData();
        }
        return $this->_transactionId;
    }

    /**
     * Enter description here...
     *
     * @return string
     */
    public function getDescriptor()
    {
        if ($this->_descriptor === null) {
            $this->_convertAdditionalData();
        }
        return $this->_descriptor;
    }

    /**
     * @deprecated
     * @return $this
     */
    protected function _convertAdditionalData()
    {
        $this->_transactionId = $this->getInfo()->getAdditionalInformation('transactionId');
        $this->_descriptor = $this->getInfo()->getAdditionalInformation('descriptor');
        return $this;
    }
}
