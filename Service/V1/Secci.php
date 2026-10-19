<?php

namespace RatePAY\Payment\Service\V1;

use Magento\Framework\Exception\AuthorizationException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Quote\Api\CartRepositoryInterface;
use Magento\Quote\Model\MaskedQuoteIdToQuoteIdInterface;
use Magento\Quote\Api\Data\CartInterface;
use Magento\Authorization\Model\UserContextInterface;
use Magento\Framework\Validator\EmailAddress as EmailAddressValidator;
use RatePAY\Payment\Api\Data\SecciResponseInterfaceFactory;
use RatePAY\Payment\Api\SecciInterface;
use RatePAY\Payment\Helper\Secci as SecciHelper;

class Secci implements SecciInterface
{
    /**
     * Payment methods using the useDirectDebit param
     */
    const INSTALLMENT_METHODS = ['ratepay_installment', 'ratepay_installment0'];

    /**
     * @var CartRepositoryInterface
     */
    protected $cartRepository;

    /**
     * @var MaskedQuoteIdToQuoteIdInterface
     */
    protected $maskedQuoteIdToQuoteId;

    /**
     * @var UserContextInterface
     */
    protected $userContext;

    /**
     * @var SecciResponseInterfaceFactory
     */
    protected $responseFactory;

    /**
     * @var SecciHelper
     */
    protected $secciHelper;

    /**
     * @var EmailAddressValidator
     */
    protected $emailValidator;

    /**
     * Constructor.
     *
     * @param CartRepositoryInterface $cartRepository
     * @param MaskedQuoteIdToQuoteIdInterface $maskedQuoteIdToQuoteId
     * @param UserContextInterface $userContext
     * @param SecciResponseInterfaceFactory $responseFactory
     * @param SecciHelper $secciHelper
     * @param EmailAddressValidator $emailValidator
     */
    public function __construct(
        CartRepositoryInterface $cartRepository,
        MaskedQuoteIdToQuoteIdInterface $maskedQuoteIdToQuoteId,
        UserContextInterface $userContext,
        SecciResponseInterfaceFactory $responseFactory,
        SecciHelper $secciHelper,
        EmailAddressValidator $emailValidator
    ) {
        $this->cartRepository = $cartRepository;
        $this->maskedQuoteIdToQuoteId = $maskedQuoteIdToQuoteId;
        $this->userContext = $userContext;
        $this->responseFactory = $responseFactory;
        $this->secciHelper = $secciHelper;
        $this->emailValidator = $emailValidator;
    }

    /**
     * Trigger a RatePay SECCI request
     *
     * @param string $cartId
     * @param string $method
     * @param string $paymentMethod
     * @param bool $useDirectDebit Only used for installments, settlement by direct debit or bank transfer
     * @return \RatePAY\Payment\Service\V1\Data\SecciResponse
     */
    public function triggerSecci($cartId, $method, $paymentMethod, $useDirectDebit = null)
    {
        $quote = $this->cartRepository->getActive((int)$cartId);

        if ((int)$quote->getCustomerId() !== (int)$this->userContext->getUserId()) {
            throw new AuthorizationException(__('Not allowed.'));
        }

        return $this->process($quote, (string)$method, (string)$paymentMethod, null, $useDirectDebit);
    }

    /**
     * Trigger a RatePay SECCI request
     *
     * @param string $cartId
     * @param string $method
     * @param string $paymentMethod
     * @param string $email Guest email, the guest email isn't reliably saved in the quote yet
     * @param bool $useDirectDebit Only used for installments, settlement by direct debit or bank transfer
     * @return \RatePAY\Payment\Service\V1\Data\SecciResponse
     */
    public function triggerGuestSecci($cartId, $method, $paymentMethod, $email = null, $useDirectDebit = null)
    {
        try {
            $quoteId = $this->maskedQuoteIdToQuoteId->execute((string)$cartId);
        } catch (NoSuchEntityException $e) {
            throw new NoSuchEntityException(__('Cart not found.'));
        }

        $quote = $this->cartRepository->getActive($quoteId);

        if ($quote->getCustomerId()) {
            throw new AuthorizationException(__('Not allowed.'));
        }

        // Invalid email addresses are ignored, the email is then taken from the quote
        if (!empty($email) && !$this->emailValidator->isValid($email)) {
            $email = null;
        }

        return $this->process($quote, (string)$method, (string)$paymentMethod, !empty($email) ? (string)$email : null, $useDirectDebit);
    }

    private function process(CartInterface $quote, string $deliveryMethod, string $paymentMethod, ?string $email = null, $useDirectDebit = null)
    {
        if (empty($paymentMethod)) {
            $paymentMethod = !empty($quote->getPayment()->getMethod()) ? $quote->getPayment()->getMethod() : null;
        }

        // The DebitPayType of the SECCI request is taken from the payment, see Helper\Content\Payment\Payment::setPayment()
        // The payment isn't saved here, the value is assigned again with the payment data when the order is placed
        if ($useDirectDebit !== null && in_array($paymentMethod, self::INSTALLMENT_METHODS)) {
            $quote->getPayment()->setAdditionalInformation('rp_directdebit', filter_var($useDirectDebit, FILTER_VALIDATE_BOOLEAN));
        }

        $response = $this->responseFactory->create();
        try {
            $oSecciRequest = $this->secciHelper->sendSecciRequest($quote, $deliveryMethod, $paymentMethod, $email);

            $response->setData('success', $oSecciRequest->isSuccessful());

            if ($oSecciRequest->isSuccessful() === false) {
                #$response->setData('errormessage', $oSecciRequest->getReasonMessage());
                // Ratepay wants a user-friendly error message to be shown here
                $response->setData('errormessage', __('Standard European Consumer Credit Information (SECCI) is temporarily unavailable. Please try again.'));
            } else {
                if (strtoupper($deliveryMethod) === \RatePAY\Payment\Helper\Content\Secci\Secci::SECCI_DELIVERY_METHOD_PDF) {
                    $response->setData('filename', $oSecciRequest->getDocumentId());
                    $response->setData('pdfstring', base64_encode($oSecciRequest->getDocument()));
                }
            }
        } catch (\Exception $e) {
            $response->setData('success', false);
            $response->setData('errormessage', __('Standard European Consumer Credit Information (SECCI) is temporarily unavailable. Please try again.'));
        }

        return $response;
    }
}
