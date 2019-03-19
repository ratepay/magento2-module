<?php

namespace RatePAY\Payment\Block\Checkout;

use Magento\Framework\View\Element\Template\Context;

class InstallmentPlan extends \Magento\Framework\View\Element\Template
{
    /**
     * Constructor
     *
     * @param Context $context
     * @param \Magento\Framework\View\Asset\Repository $assetRepo
     * @param array   $data
     */
    public function __construct(
        Context $context,
        \Magento\Framework\View\Asset\Repository $assetRepo,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->assetRepo = $assetRepo;
        $this->setTemplate('checkout/installment_plan.phtml');
    }

    /**
     * Return Url to given image
     *
     * @param string $imageName
     * @return string
     */
    public function getImageUrl($imageName)
    {
        $params = [
            'theme' => 'Magento/luma',
            'area' => 'frontend',
        ];
        $asset = $this->_assetRepo->createAsset('RatePAY_Payment::images/'.$imageName, $params);
        return $asset->getUrl();
    }
}
