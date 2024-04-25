<?php
namespace Sigma\ProductCategory\Model\Resolver;

use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Query\ResolverInterface;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Catalog\Api\ProductAttributeRepositoryInterface;
use Magento\Catalog\Api\CategoryRepositoryInterface;

class ProductCategoryResolver implements ResolverInterface
{
    protected $productRepository;
    protected $attributeRepository;
    protected $categoryRepository;

    public function __construct(
        ProductRepositoryInterface $productRepository,
        ProductAttributeRepositoryInterface $attributeRepository,
        CategoryRepositoryInterface $categoryRepository
    ) {
        $this->productRepository = $productRepository;
        $this->attributeRepository = $attributeRepository;
        $this->categoryRepository = $categoryRepository;
    }

    public function resolve(Field $field, $context, ResolveInfo $info, array $value = null, array $args = null)

    {
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

            $writer = new \Zend_Log_Writer_Stream(BP . '/var/log/manoli.log');
            $logger = new \Zend_Log();
            $logger->addWriter($writer);

            $categoryIds = $product->getCategoryIds();
            $categories = [];
            foreach ($categoryIds as $categoryId) {
                $category = $this->categoryRepository->get($categoryId);
                $categories[] = [
                    'id' => $category->getId(),
                    'name' => $category->getName(),
                ];
            }

            return [
                'sku' => $productsku,
                'label' => $optionLabel,
                'id' => $productid,
                'price' => $productprice,
                'color' => $defaultAttributeLabel,
                'categories' => $categories,
            ];
        } catch (\Exception $e) {
            throw new \Exception(__('Failed to fetch custom attribute value: %1', $e->getMessage()));
        }
    }
}
