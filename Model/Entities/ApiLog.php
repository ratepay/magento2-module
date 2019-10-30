<?php

namespace RatePAY\Payment\Model\Entities;

use Magento\Framework\Model\AbstractModel;

/**
 * API Log entity model
 */
class ApiLog extends AbstractModel
{
    /**
     * Initialize resource model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('RatePAY\Payment\Model\ResourceModel\ApiLog');
    }

    /**
     * Returns raw xml from request
     *
     * @return array
     */
    public function getRequest()
    {
        return htmlentities($this->getData('request'));
    }

    /**
     * Returns raw xml from response
     *
     * @return array
     */
    public function getResponse()
    {
        return htmlentities($this->getData('response'));
    }
}
