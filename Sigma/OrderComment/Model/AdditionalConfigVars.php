<?php

namespace Sigma\OrderComment\Model;

use Magento\Checkout\Model\ConfigProviderInterface;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Store\Model\ScopeInterface;

class AdditionalConfigVars implements ConfigProviderInterface
{
    /**
     * @var ScopeConfigInterface
     */
    protected $scopeConfig;

    /**
     * Path to the configuration for enabling Sigma order comments.
     */
    protected const PATH_SIGMA_ORDER_COMMENT = 'sigma_order_comments/general/enable';

    /**
     * AdditionalConfigVars constructor.
     *
     * @param ScopeConfigInterface $scopeConfig
     */
    public function __construct(
        ScopeConfigInterface $scopeConfig
    ) {
        $this->scopeConfig = $scopeConfig;
    }

    /**
     * Retrieve configuration.
     *
     * @return array
     */
    public function getConfig()
    {
        $storeScope = ScopeInterface::SCOPE_STORE;
        $enabledComments = $this->scopeConfig->getValue(self::PATH_SIGMA_ORDER_COMMENT, $storeScope);
        $additionalVariables['enabled_comments'] = (bool) $enabledComments;

        return $additionalVariables;
    }
}
