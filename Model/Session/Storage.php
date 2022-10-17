<?php

/**
 * Copyright (c) Ratepay GmbH
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace RatePAY\Payment\Model\Session;

class Storage extends \Magento\Framework\Session\Storage
{
    public function __construct(\Magento\Store\Model\StoreManagerInterface $storeManager, $namespace = 'ratepay', array $data = [])
    {
        parent::__construct($namespace, $data);
    }
}
