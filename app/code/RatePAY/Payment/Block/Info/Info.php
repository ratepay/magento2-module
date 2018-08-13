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
     *
     * @return $this
     */
    protected function _convertAdditionalData()
    {
        $this->_transactionId = $this->getInfo()->getAdditionalInformation('transactionId');
        $this->_descriptor = $this->getInfo()->getAdditionalInformation('descriptor');

        return $this;
    }
}
