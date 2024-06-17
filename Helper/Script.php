<?php

namespace RatePAY\Payment\Helper;

class Script extends \Magento\Framework\App\Helper\AbstractHelper
{
    /**
     * @var \Magento\Framework\App\ProductMetadata
     */
    protected $productMetadata;

    /**
     * Payment constructor.
     * @param \Magento\Framework\App\Helper\Context $context
     * @param \Magento\Framework\App\ProductMetadata $productMetadata

     */
    public function __construct(
        \Magento\Framework\App\Helper\Context $context,
        \Magento\Framework\App\ProductMetadata $productMetadata
    ) {
        parent::__construct($context);
        $this->productMetadata = $productMetadata;
    }

    /**
     * This handles inserting JavaScript which has to be done different sind Magento 2.4.7
     *
     * @param  string $script
     * @return string
     */
    public function insertScript($script)
    {
        if (version_compare($this->productMetadata->getVersion(), '2.4.7', '>=')) {
            $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
            $secureRenderer = $objectManager->create(\Magento\Framework\View\Helper\SecureHtmlRenderer::class);
            return $secureRenderer->renderTag('script', [], $script, false);
        }
        return "<script>".$script."</script>";
    }
}