<?php

/**
 * RatePAY Payments - Magento 2
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 */

namespace RatePAY\Payment\Controller\Checkout;

use Magento\Framework\App\Action\Context;

class B2b extends \Magento\Framework\App\Action\Action
{
    /**
     * @var \Magento\Framework\Controller\Result\JsonFactory
     */
    protected $_resultJsonFactory;

    /**
     * @var \Magento\Checkout\Model\Session
     */
    protected $_checkoutSession;

    /**
     * B2b constructor.
     *
     * @param Context                                          $context
     * @param \Magento\Framework\Controller\Result\JsonFactory $resultJsonFactory
     * @param \Magento\Checkout\Model\Session                  $checkoutSession
     */
    public function __construct(
        Context $context,
        \Magento\Framework\Controller\Result\JsonFactory $resultJsonFactory,
        \Magento\Checkout\Model\Session $checkoutSession
    ) {
        $this->_resultJsonFactory = $resultJsonFactory;
        $this->_checkoutSession = $checkoutSession;
        parent::__construct($context);
    }

    /**
     *  evaluate ajax request for B2B.
     *
     * @return $this
     */
    public function execute()
    {
        $response = [
            'status' => 'failure',
            'message' => '',
        ];

        $params = $this->getRequest()->getParams();

        $result = $this->_resultJsonFactory->create();

        if (!key_exists('vat_id', $params)) {
            $response['message'] = 'vat id invalid';

            return $result->setData($response);
        }

        if (!$this->_isValidVatId($params['vat_id'])) {
            $response['message'] = 'vat id invalid';

            return $result->setData($response);
        }

        $this->_checkoutSession->setRatepayVatId($params['vat_id']);

        $response['status'] = 'success';
        $response['message'] = 'vat id saved';

        return $result->setData($response);
    }

    /**
     *  validate vat id.
     *
     * @param $vatId
     *
     * @return bool
     */
    private function _isValidVatId($vatId)
    {
        $vatId = trim($vatId);
        $vatId = strtoupper($vatId);
        $countryPrefix = substr($vatId, 0, 2);

        switch ($countryPrefix) {
            case 'DE':
                $regex = '<^((DE)?[0-9]{9})$>';

                break;
            case 'AT':
                $regex = '<^((AT)?U[0-9]{8})$>';

                break;
            case 'NL':
                $regex = '<^((NL)?[0-9]{9}?(B)[0-9]{2})$>';

                break;
            case 'CH':
                $regex = '<^((CHE)?[0-9]{9}(MWST))$>';

                break;
            default:
                return false;
        }

        if (preg_match($regex, trim($vatId))) {
            return true;
        }

        return false;
    }
}
