<?php

namespace Sigma\PaymentMethodsOverride\Model;

use Magento\Framework\App\Config\ScopeConfigInterface;

/**
 * Class CustomPaymentMethodRepository
 *
 * Repository for fetching custom selected payment methods from configuration.
 */
class CustomPaymentMethodRepository
{
    /**
     * @var string
     */
    public const XML_PATH_SELECTED_PAYMENT_METHODS = 'payment/custom/enabled_payment_methods';

    /**
     * @var ScopeConfigInterface
     */
    private $scopeConfig;

    /**
     * CustomPaymentMethodRepository constructor.
     *
     * @param ScopeConfigInterface $scopeConfig
     */
    public function __construct(ScopeConfigInterface $scopeConfig)
    {
        $this->scopeConfig = $scopeConfig;
    }

    /**
     * Get selected payment methods from custom configuration.
     *
     * @return array
     */
    public function getSelectedPaymentMethods(): array
    {
        $methods = $this->scopeConfig->getValue(
            self::XML_PATH_SELECTED_PAYMENT_METHODS,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
        return $methods ? explode(',', $methods) : [];
    }
}
