<?php

/**
 * Copyright (c) Ratepay GmbH
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace RatePAY\Payment\Plugin;

use Magento\Framework\View\Element\Message\Renderer\EscapeRenderer as OrigEscapeRenderer;
use Magento\Framework\Message\MessageInterface;

class EscapeRenderer
{
    /**
     * Renders complex message
     *
     * @param OrigEscapeRenderer $subject
     * @param \Closure $proceed
     * @param MessageInterface $message
     * @param array $initializationData
     * @return string
     */
    public function aroundRender(OrigEscapeRenderer $subject, \Closure $proceed, MessageInterface $message, array $initializationData)
    {
        $text = $message->getText();
        if (stripos($text, "Ratepay") !== false && stripos($text, "href") !== false) {
            return $text;
        }
        return $proceed($message, $initializationData);
    }
}
