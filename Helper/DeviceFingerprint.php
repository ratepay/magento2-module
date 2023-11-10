<?php

/**
 * Copyright (c) Ratepay GmbH
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace RatePAY\Payment\Helper;

use Magento\Framework\App\Helper\Context;

class DeviceFingerprint extends Data
{
    /**
     * @var \Magento\Customer\Model\Session
     */
    protected $customerSession;

    /**
     * Data constructor.
     *
     * @param Context $context
     * @param \Magento\Framework\App\Filesystem\DirectoryList $directoryList
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Magento\Framework\App\State $state
     * @param \Magento\Customer\Model\Session $customerSession
     */
    public function __construct(
        Context $context,
        \Magento\Framework\App\Filesystem\DirectoryList $directoryList,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Framework\App\State $state,
        \Magento\Customer\Model\Session $customerSession
    ) {
        parent::__construct($context, $directoryList, $storeManager, $state);
        $this->customerSession = $customerSession;
    }

    /**
     * Read snippet_id from config
     *
     * @return string
     */
    public function getSnippetId()
    {
        $snippetId = $this->getRpConfigData('ratepay_general', 'snippet_id');
        if (empty($snippetId)) {
            $snippetId = "C9rKgOt";
        }
        return $snippetId;
    }

    /**
     * Returns device fingerprint token
     *
     * @return string
     */
    public function getToken()
    {
        $dfpSessionToken = $this->customerSession->getRatePayDeviceIdentToken();
        if (empty($dfpSessionToken)) {
            $dfpSessionToken = $this->createToken($this->customerSession->getSessionId());
            $this->customerSession->setRatePayDeviceIdentToken($dfpSessionToken);
        }
        return $dfpSessionToken;
    }

    /**
     * Creates unique token.
     *
     * @param $uniqueIdentifier
     */
    protected function createToken($uniqueIdentifier)
    {
        return hash('md5', $this->getSnippetId() . '_' . $uniqueIdentifier . '_' . microtime());
    }
}
