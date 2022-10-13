<?php

/**
 * Copyright (c) Ratepay GmbH
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace RatePAY\Payment\Block\Customer;

class DataLink extends \Magento\Framework\View\Element\Html\Link\Current
{
    /**
     * @var \RatePAY\Payment\Helper\Data
     */
    protected $rpDataHelper;

    /**
     * Constructor
     *
     * @param \Magento\Framework\View\Element\Template\Context  $context
     * @param \Magento\Framework\App\DefaultPathInterface       $defaultPath
     * @param \RatePAY\Payment\Helper\Data                      $rpDataHelper,
     * @param array                                             $data
     */
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Magento\Framework\App\DefaultPathInterface $defaultPath,
        \RatePAY\Payment\Helper\Data $rpDataHelper,
        array $data = []
    )
    {
        parent::__construct($context, $defaultPath, $data);
        $this->rpDataHelper = $rpDataHelper;
    }

    /**
     * Render block HTML
     *
     * @return string
     */
    public function toHtml()
    {
        if ((bool)$this->rpDataHelper->getRpConfigDataByPath('ratepay/general/bams_enabled') === true) {
            return parent::toHtml();
        }
        return '';
    }
}
