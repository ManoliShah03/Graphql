<?php

namespace Sigma\PaymentMethodsOverride\Model\Config\Source;

use Magento\Framework\Option\ArrayInterface;
use Magento\Payment\Model\Config as PaymentConfig;

/**
 * Class PaymentMethods
 *
 * Provides a list of active payment methods as options.
 */
class PaymentMethods implements ArrayInterface
{
    /**
     * @var PaymentConfig
     */
    protected $paymentConfig;

    /**
     * PaymentMethods constructor.
     *
     * @param PaymentConfig $paymentConfig
     */
    public function __construct(
        PaymentConfig $paymentConfig
    ) {
        $this->paymentConfig = $paymentConfig;
    }

    /**
     * Retrieve active payment methods as options.
     *
     * @return array
     */
    public function toOptionArray(): array
    {
        $paymentMethods = [];
        $methods = $this->paymentConfig->getActiveMethods();
        foreach ($methods as $code => $paymentMethod) {
            $paymentMethods[] = [
                'value' => $code,
                'label' => $paymentMethod->getTitle(),
            ];
        }
        return $paymentMethods;
    }
}
