<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Sigma\PaymentMethodsOverride\Model\Resolver;

use Magento\QuoteGraphQl\Model\Resolver\AvailablePaymentMethods as BaseAvailablePaymentMethods;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;
use Magento\Quote\Api\Data\CartInterface;
use Magento\Quote\Api\ShippingMethodManagementInterface;
use Sigma\PaymentMethodsOverride\Model\CustomPaymentMethodRepository;
use Magento\Checkout\Api\PaymentInformationManagementInterface;

/**
 * Class PaymentMethods
 *
 * Overrides the available payment methods resolver to filter payment methods
 * based on custom selected payment methods.
 */
class PaymentMethods extends BaseAvailablePaymentMethods
{
    /**
     * @var CustomPaymentMethodRepository
     */
    private $customPaymentMethodRepository;

    /**
     * PaymentMethods constructor.
     *
     * @param PaymentInformationManagementInterface $informationManagement
     * @param ShippingMethodManagementInterface $informationShipping
     * @param CustomPaymentMethodRepository $customPaymentMethodRepository
     */
    public function __construct(
        PaymentInformationManagementInterface $informationManagement,
        ShippingMethodManagementInterface $informationShipping,
        CustomPaymentMethodRepository $customPaymentMethodRepository
    ) {
        parent::__construct($informationManagement, $informationShipping);
        $this->customPaymentMethodRepository = $customPaymentMethodRepository;
    }

    /**
     * Resolve available payment methods for the cart.
     *
     * @param Field $field
     * @param mixed $context
     * @param ResolveInfo $info
     * @param array|null $value
     * @param array|null $args
     * @return array
     * @throws LocalizedException
     */
    public function resolve(
        Field $field,
        $context,
        ResolveInfo $info,
        array $value = null,
        array $args = null
    ) {
        if (!isset($value['model'])) {
            throw new LocalizedException(__('"model" value should be specified'));
        }

        /** @var CartInterface $cart */
        $cart = $value['model'];
        $selectedPaymentMethods = $this->customPaymentMethodRepository->getSelectedPaymentMethods();

        // Fetch all enabled payment methods
        $availablePaymentMethods = parent::resolve($field, $context, $info, $value, $args);

        // Filter available payment methods based on selected payment methods
        $filteredPaymentMethods = [];
        foreach ($availablePaymentMethods as $paymentMethod) {
            if (in_array($paymentMethod['code'], $selectedPaymentMethods)) {
                $filteredPaymentMethods[] = $paymentMethod;
            }
        }

        return $filteredPaymentMethods;
    }
}
