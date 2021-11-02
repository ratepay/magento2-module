<?php

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
