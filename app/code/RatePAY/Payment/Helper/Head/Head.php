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

    protected $moduleList;


    /**
     * Head constructor.
     * @param Context $context
     * @param \RatePAY\Payment\Helper\Data $rpHelper
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     */
    public function __construct(Context $context,
                                \RatePAY\Payment\Helper\Data $rpHelper,
                                \Magento\Store\Model\StoreManagerInterface $storeManager,
                                \Magento\Framework\App\ProductMetadataInterface $productMetadata,
                                \Magento\Framework\Module\ModuleListInterface $moduleList)
    {
        parent::__construct($context);

        $this->rpDataHelper = $rpHelper;
        $this->storeManager = $storeManager;
        $this->remoteAddress = $context->getRemoteAddress();
        $this->productMetadata = $productMetadata;
        $this->moduleList = $moduleList;
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
        $paymentMethod = (is_null($fixedPaymentMethod) ? $quoteOrOrder->getPayment()->getMethod() : $fixedPaymentMethod);
        $profileId = (is_null($profileId) ? $this->rpDataHelper->getRpConfigData($paymentMethod, 'profileId', $this->storeManager->getStore()->getId()) : $profileId);
        $securityCode = (is_null($securityCode) ? $this->rpDataHelper->getRpConfigData($paymentMethod, 'securityCode', $this->storeManager->getStore()->getId()) : $securityCode);

        $headModel->setArray([
            'SystemId' => $this->storeManager->getStore($this->storeManager->getStore()->getId())->getBaseUrl() . ' (' . $_SERVER['SERVER_ADDR'] . ')',
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
