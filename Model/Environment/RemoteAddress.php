<?php

namespace RatePAY\Payment\Model\Environment;

use Magento\Framework\HTTP\PhpEnvironment\RemoteAddress as CoreRemoteAddress;

class RemoteAddress extends CoreRemoteAddress
{
    /**
     * Add X-Forwarded-For header to IP address determination
     * For determining the IP address when user is behind a proxy server
     *
     * @return void
     */
    public function addHttpXForwardedHeader()
    {
        if (array_key_exists('x-forwarded-for', $this->alternativeHeaders) === false) {
            $this->alternativeHeaders['x-forwarded-for'] = 'HTTP_X_FORWARDED_FOR';
        }
    }
}
