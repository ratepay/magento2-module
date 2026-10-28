<?php

namespace RatePAY\Payment\Helper\Content\Secci;

use RatePAY\Payment\Helper\Data;

class Secci extends Data
{
    const SECCI_DELIVERY_METHOD_EMAIL = 'EMAIL';
    const SECCI_DELIVERY_METHOD_PDF = 'PDF';

    /**
     * Build Secci Block of Secci Request
     *
     * @param $quote
     * @param string $deliveryMethod
     * @param string|null $email Recipient email, falls back to the quote email if empty
     * @return array
     */
    public function setSecci($quote, $deliveryMethod, $email = null)
    {
        $content = [
            'DeliveryMethod' => strtoupper($deliveryMethod), // "EMAIL", "PDF"
            'CountryCode' => $quote->getBillingAddress()->getCountryId(),
        ];

        $language = strtoupper($this->getLocale($quote));
        if (in_array($language, ['DE', 'EN', 'NL'])) {
            $content['Language'] = $language;
        }

        if (strtoupper($deliveryMethod) == self::SECCI_DELIVERY_METHOD_EMAIL) {
            $content['Email'] = !empty($email) ? $email : $this->getEmailFromQuoteOrOrder($quote);
        } elseif (strtoupper($deliveryMethod) == self::SECCI_DELIVERY_METHOD_PDF) {
            $content['Action'] = 'DOWNLOAD';
        }

        return $content;
    }

    protected function getEmailFromQuoteOrOrder($quote)
    {
        return $quote->getCustomerEmail()
            ?: $quote->getBillingAddress()->getEmail()
            ?: $quote->getShippingAddress()->getEmail()
            ?: null;
    }
}