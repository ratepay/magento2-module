<?php
/**
 * Created by PhpStorm.
 * User: SebastianN
 * Date: 09.02.17
 * Time: 09:12
 */

namespace RatePAY\Payment\Helper\Head;


use Magento\Framework\App\Helper\Context;

class Head extends \Magento\Framework\App\Helper\AbstractHelper
{
    /**
     * @var \RatePAY\Payment\Helper\Data
     */
    protected $rpDataHelper;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $storeManager;

    /**
     * @var \Magento\Framework\HTTP\PhpEnvironment\RemoteAddress
     */
    protected $remoteAddress;

    /**
     * @var \Magento\Framework\App\ProductMetadataInterface
     */
    protected $productMetadata;

    /**
     * @var \Magento\Framework\Module\ModuleListInterface
     */
    protected $moduleList;

    /**
     * @var \Magento\Payment\Helper\Data
     */
    protected $paymentHelper;


    /**
     * Head constructor.
     * @param Context $context
     * @param \RatePAY\Payment\Helper\Data $rpHelper
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Magento\Framework\App\ProductMetadataInterface $productMetadata
     * @param \Magento\Framework\Module\ModuleListInterface $moduleList
     * @param \Magento\Payment\Helper\Data $paymentHelper
     */
    public function __construct(
        Context $context,
        \RatePAY\Payment\Helper\Data $rpHelper,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Framework\App\ProductMetadataInterface $productMetadata,
        \Magento\Framework\Module\ModuleListInterface $moduleList,
        \Magento\Payment\Helper\Data $paymentHelper
    ) {
        parent::__construct($context);

        $this->rpDataHelper = $rpHelper;
        $this->storeManager = $storeManager;
        $this->remoteAddress = $context->getRemoteAddress();
        $this->productMetadata = $productMetadata;
        $this->moduleList = $moduleList;
        $this->paymentHelper = $paymentHelper;
    }

    /**
     * Build basic head section
     *
     * @param $quoteOrOrder
     * @param RatePAY/Payment/Model/Library/src/ModelBuilder $headModel
     * @param null $fixedPaymentMethod
     * @param null $profileId
     * @param null $securityCode
     * @return /app/code/RatePAY/Payment/Model/Library/src/ModelBuilder
     */
    public function setHead($quoteOrOrder, $headModel, $fixedPaymentMethod = null, $profileId = null, $securityCode = null)
    {
        $storeCode = $quoteOrOrder->getStore()->getCode();

        $sMethodCode = (is_null($fixedPaymentMethod) ? $quoteOrOrder->getPayment()->getMethod() : $fixedPaymentMethod);
        if ($profileId === null || $securityCode === null) {
            $method = $this->paymentHelper->getMethodInstance($sMethodCode);
        }
        $profileId = (is_null($profileId) ? $method->getMatchingProfile(null, $storeCode)->getData('profile_id') : $profileId);
        $securityCode = (is_null($securityCode) ? $method->getMatchingProfile(null, $storeCode)->getSecurityCode() : $securityCode);

        $serverAddr = '';
        if ($_SERVER && isset($_SERVER['SERVER_ADDR'])) {
            $serverAddr = $_SERVER['SERVER_ADDR'];
        }

        $headModel->setArray([
            'SystemId' => $this->storeManager->getStore($quoteOrOrder->getStore()->getId())->getBaseUrl() . ' (' . $serverAddr . ')',
            'Credential' => [
                'ProfileId' => $profileId,
                'Securitycode' => $securityCode
            ],
            'Meta' => [
                'Systems' => [
                    'System' => [
                        'Name' => "Magento_" . $this->rpDataHelper->getEdition(),
                        'Version' => $this->productMetadata->getVersion() . '_' . $this->moduleList->getOne('RatePAY_Payment')['setup_version']
                    ]
                ]
            ]
        ]);

        return $headModel;
    }
}
