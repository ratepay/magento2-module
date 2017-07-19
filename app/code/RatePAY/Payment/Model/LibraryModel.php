<?php
/**
 * Created by PhpStorm.
 * User: SebastianN
 * Date: 08.02.17
 * Time: 09:35
 */

namespace RatePAY\Payment\Model;

require_once __DIR__ . '/../Model/Library/vendor/autoload.php';

use RatePAY\ModelBuilder;
class LibraryModel
{
    /**
     * LibraryModel constructor.
     * @param \RatePAY\Payment\Helper\Head\Head $rpHeadHelper
     * @param \Ratepay\Payment\Helper\Head\Additional $rpHeadAdditionalHelper
     * @param \Ratepay\Payment\Helper\Head\External $rpHeadExternalHelper
     * @param \RatePAY\Payment\Helper\Content\ContentBuilder $rpContentBuilder
     */
    public function __construct(\RatePAY\Payment\Helper\Head\Head $rpHeadHelper,
                                \Ratepay\Payment\Helper\Head\Additional  $rpHeadAdditionalHelper,
                                \Ratepay\Payment\Helper\Head\External $rpHeadExternalHelper,
                                \RatePAY\Payment\Helper\Content\ContentBuilder $rpContentBuilder)
    {
        $this->rpHeadHelper = $rpHeadHelper;
        $this->rpHeadAdditionalHelper = $rpHeadAdditionalHelper;
        $this->rpHeadExternalHelper = $rpHeadExternalHelper;
        $this->rpContentBuilder = $rpContentBuilder;

    }

    /**
     * Build requests head section
     *
     * @param $quoteOrOrder
     * @param null $resultInit
     * @return /app/code/RatePAY/Payment/Model/Library/src/ModelBuilder mixed|ModelBuilder
     */
    public function getRequestHead($quoteOrOrder, $resultInit = null, $fixedPaymentMethod = null, $profileId = null, $securityCode = null)
    {
        $headModel = new ModelBuilder('Head');

        $headModel = $this->rpHeadHelper->setHead($quoteOrOrder, $headModel, $fixedPaymentMethod, $profileId, $securityCode);
        if (!$resultInit == null) {
            $headModel = $this->rpHeadAdditionalHelper->setHeadAdditional($resultInit, $headModel);
            $headModel = $this->rpHeadExternalHelper->setHeadExternal($quoteOrOrder, $headModel);
        }

        return $headModel;
    }

    /**
     * Build requests content section
     *
     * @param $quoteOrOrder
     * @return ModelBuilder
     */
    public function getRequestContent($quoteOrOrder, $fixedPaymentMethod = null)
    {
        $content = new ModelBuilder('Content');

        $contentArr = $this->rpContentBuilder->setContent($quoteOrOrder, $fixedPaymentMethod);
        try{
            $content->setArray($contentArr);
        } catch (\Exception $e){
            echo $e->getMessage();
        }

        return $content ;
    }
}
