<?php

namespace Sigma\OrderComment\Plugin\Model\Checkout;

class PaymentInformationManagement
{
    /**
     * @var \Magento\Quote\Model\QuoteRepository
     */
    protected $quoteRepository;

    /**
     * PaymentInformationManagement constructor.
     *
     * @param \Magento\Quote\Model\QuoteRepository $quoteRepository
     */
    public function __construct(
        \Magento\Quote\Model\QuoteRepository $quoteRepository
    ) {
        $this->quoteRepository = $quoteRepository;
    }

    /**
     * Before saving payment information, set custom order comments to the quote.
     *
     * @param \Magento\Checkout\Model\PaymentInformationManagement $subject
     * @param mixed $cartId
     * @param \Magento\Quote\Api\Data\PaymentInterface $paymentMethod
     */
    public function beforeSavePaymentInformation(
        \Magento\Checkout\Model\PaymentInformationManagement $subject,
        $cartId,
        \Magento\Quote\Api\Data\PaymentInterface $paymentMethod
    ) {
        $commentsExtensionAttributes = $paymentMethod->getExtensionAttributes();
        if ($commentsExtensionAttributes->getComments()) {
            $comments = trim($commentsExtensionAttributes->getComments());
        } else {
            $comments = '';
        }
        $quote = $this->quoteRepository->getActive($cartId);
        $quote->setSigmaOrderComments($comments);
    }
}
