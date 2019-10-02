<?php
/**
 * Created by PhpStorm.
 * User: SebastianN
 * Date: 14.02.17
 * Time: 16:36
 */

namespace RatePAY\Payment\Model\Session;


class Storage extends \Magento\Framework\Session\Storage
{
    public function __construct(\Magento\Store\Model\StoreManagerInterface $storeManager ,$namespace = 'ratepay', array $data = [])
    {
        parent::__construct($namespace, $data);
    }
}