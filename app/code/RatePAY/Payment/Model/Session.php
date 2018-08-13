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

namespace RatePAY\Payment\Model;

use Magento\Framework\Session\Config\ConfigInterface;
use Magento\Framework\Session\SaveHandlerInterface;
use Magento\Framework\Session\SidResolverInterface;
use Magento\Framework\Session\StorageInterface;
use Magento\Framework\Session\ValidatorInterface;

class Session extends \Magento\Framework\Session\SessionManager
{
    protected $_session;
    protected $_coreUrl = null;
    protected $_configShare;
    protected $_urlFactory;
    protected $_eventManager;
    protected $response;
    protected $_sessionManager;

    public function __construct(
        \Magento\Framework\App\Request\Http $request,
                                SidResolverInterface $sidResolver,
                                ConfigInterface $sessionConfig,
                                SaveHandlerInterface $saveHandler,
                                ValidatorInterface $validator,
                                StorageInterface $storage,
                                \Magento\Framework\Stdlib\CookieManagerInterface $cookieManager,
                                \Magento\Framework\Stdlib\Cookie\CookieMetadataFactory $cookieMetadataFactory,
                                \Magento\Framework\App\Http\Context $httpContext,
                                \Magento\Framework\App\State $appState,
                                \Magento\Framework\Session\Generic $session,
                                \Magento\Framework\Event\ManagerInterface $eventManager,
                                \Magento\Framework\App\Response\Http $response
    ) {
        parent::__construct(
            $request,
                            $sidResolver,
                            $sessionConfig,
                            $saveHandler,
                            $validator,
                            $storage,
                            $cookieManager,
                            $cookieMetadataFactory,
                            $appState
        );
        $this->_session = $session;
        $this->_eventManager = $eventManager;
        $this->response = $response;
        $this->_eventManager->dispatch(
            'ratepay_session_init',
            ['ratepay_session' => $this]
        );
    }
}
