<?php
namespace Sigma\PaymentMethodsOverride\Model;

use Magento\Payment\Api\Data\PaymentMethodInterface;
use Magento\Payment\Model\MethodInterface;
use Magento\Payment\Api\Data\PaymentMethodInterfaceFactory;
use Magento\Payment\Helper\Data;
use Sigma\PaymentMethodsOverride\Model\CustomPaymentMethodRepository;
use UnexpectedValueException;

class PaymentMethodList extends \Magento\Payment\Model\PaymentMethodList
{
    /**
     * @var PaymentMethodInterfaceFactory
     */
    private $methodFactory;

    /**
     * @var Data
     */
    private $helper;

    /**
     * @var CustomPaymentMethodRepository
     */
    private $customPaymentMethodRepository;

    /**
     * @param PaymentMethodInterfaceFactory $methodFactory
     * @param Data $helper
     * @param CustomPaymentMethodRepository $customPaymentMethodRepository
     */
    public function __construct(
        PaymentMethodInterfaceFactory $methodFactory,
        Data $helper,
        CustomPaymentMethodRepository $customPaymentMethodRepository
    ) {
        $this->methodFactory = $methodFactory;
        $this->helper = $helper;
        $this->customPaymentMethodRepository = $customPaymentMethodRepository;
        parent::__construct($methodFactory, $helper); // Make sure to call parent constructor
    }

    /**
     * @inheritDoc
     */
    public function getList($storeId)
    {
        $methodsCodes = array_keys($this->helper->getPaymentMethods());
        $methodsInstances = array_map(
            function ($code) {
                try {
                    return $this->helper->getMethodInstance($code);
                } catch (UnexpectedValueException $e) {
                    return null;
                }
            },
            $methodsCodes
        );

        $methodsInstances = array_filter($methodsInstances, function ($method) {
            return $method && !($method instanceof \Magento\Payment\Model\Method\Substitution);
        });

        uasort(
            $methodsInstances,
            function (MethodInterface $a, MethodInterface $b) use ($storeId) {
                return (int)$a->getConfigData('sort_order', $storeId) - (int)$b->getConfigData('sort_order', $storeId);
            }
        );

        $methodList = array_map(
            function (MethodInterface $methodInstance) use ($storeId) {
                return $this->methodFactory->create([
                    'code' => (string)$methodInstance->getCode(),
                    'title' => (string)$methodInstance->getTitle(),
                    'storeId' => (int)$storeId,
                    'isActive' => (bool)$methodInstance->isActive($storeId)
                ]);
            },
            $methodsInstances
        );

        return array_values($methodList);
    }

    /**
     * @inheritDoc
     */
    public function getActiveList($storeId)
    {
        // Get the list of active payment methods
        $methodList = array_filter(
            $this->getList($storeId),
            function (PaymentMethodInterface $method) {
                return $method->getIsActive();
            }
        );

        // Fetch allowed payment methods from the custom configuration
        $allowedMethods = $this->customPaymentMethodRepository->getSelectedPaymentMethods();

        // Filter the method list to include only allowed methods
        $filteredMethodList = array_filter(
            $methodList,
            function (PaymentMethodInterface $method) use ($allowedMethods) {
                return in_array($method->getCode(), $allowedMethods);
            }
        );

        return array_values($filteredMethodList);
    }
}
