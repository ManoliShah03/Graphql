<?php
namespace Sigma\CustomAttribute\Model\Resolver;

use Magento\Framework\GraphQl\Query\ResolverInterface;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Catalog\Api\ProductAttributeRepositoryInterface;

class CustomAttributeResolver implements ResolverInterface
{
    protected $productRepository;
    protected $attributeRepository;

    public function __construct(
        ProductRepositoryInterface $productRepository,
        ProductAttributeRepositoryInterface $attributeRepository
    ) {
        $this->productRepository = $productRepository;
        $this->attributeRepository = $attributeRepository;
    }

    public function resolve(
        \Magento\Framework\GraphQl\Config\Element\Field $field,
        $context,
        ResolveInfo $info,
        array $value = null,
        array $args = null
    ) {
        $sku = $args['sku'];
        $attributeCode = 'materials';
        $defaultAttribute = 'color';
        try {
            $product = $this->productRepository->get($sku);

            $productsku = $product->getSku();
            $productid = $product->getId();
            $productprice = $product->getPrice();
            $customAttributeValue = $product->getData($attributeCode);
            $attribute = $this->attributeRepository->get($attributeCode);
            $optionLabel = $attribute->getSource()->getOptionText($customAttributeValue);

            $defaultAttributeValue = $product->getData($defaultAttribute);
            $defaultAttribute = $this->attributeRepository->get($defaultAttribute);
            $defaultAttributeLabel = $defaultAttribute->getSource()->getOptionText($defaultAttributeValue);
            return [
                'sku' => $productsku,
                'label' => $optionLabel,
                'id' => $productid,
                'price' => $productprice,
                'color' => $defaultAttributeLabel
            ];
        } catch (\Exception $e) {
            throw new \Exception(__('Failed to fetch custom attribute value: %1', $e->getMessage()));
        }
    }
}
