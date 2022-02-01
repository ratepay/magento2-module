<?php
/**
 * Created by PhpStorm.
 * User: SebastianN
 * Date: 02.03.17
 * Time: 10:47
 */

namespace RatePAY\Payment\Controller\Adminhtml\System\Config;

use Magento\Framework\App\Action\Context;
use RatePAY\Payment\Helper\Data;
use RatePAY\Payment\Model\Method\Invoice;

class Refresh extends \Magento\Framework\App\Action\Action
{
    /**
     * Result factory for get configuration script
     *
     * @var \Magento\Framework\Controller\Result\JsonFactory
     */
    protected $resultJsonFactory;

    /**
     * @var \RatePAY\Payment\Helper\ProfileConfig
     */
    protected $profileConfigHelper;

    /**
     * @var \RatePAY\Payment\Helper\Data
     */
    protected $rpDataHelper;

    /**
     * Constructor
     *
     * @param \Magento\Backend\App\Action\Context                   $context
     * @param \Magento\Framework\Controller\Result\JsonFactory      $resultJsonFactory
     * @param \RatePAY\Payment\Helper\ProfileConfig                 $profileConfigHelper
     * @param \RatePAY\Payment\Helper\Data                          $rpDataHelper
     */
    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Magento\Framework\Controller\Result\JsonFactory $resultJsonFactory,
        \RatePAY\Payment\Helper\ProfileConfig $profileConfigHelper,
        \RatePAY\Payment\Helper\Data $rpDataHelper
    ) {
        parent::__construct($context);
        $this->resultJsonFactory = $resultJsonFactory;
        $this->profileConfigHelper = $profileConfigHelper;
        $this->rpDataHelper = $rpDataHelper;
    }

    /**
     * Refresh ratepay profiles
     *
     * @return \Magento\Framework\Controller\Result\Json
     */
    public function execute()
    {
        $aData = ['success' => true];

        $sPseudoMethodCode = Invoice::METHOD_CODE;
        $blIsBackend = (bool)$this->rpDataHelper->getRequestParameter("isBackendConfig");
        if ($blIsBackend === true) {
            $sPseudoMethodCode = $sPseudoMethodCode.Invoice::BACKEND_SUFFIX;
        }

        try {
            $this->profileConfigHelper->refreshProfileConfigurations($sPseudoMethodCode);
        } catch (\Exception $exc) {
            $aData['success'] = false;
            $aData['errormessage'] = $exc->getMessage();
        }

        /** @var \Magento\Framework\Controller\Result\Json $resultJson */
        $resultJson = $this->resultJsonFactory->create();
        return $resultJson->setData($aData);
    }
}
