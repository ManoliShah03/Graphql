<?php
namespace Sigma\ProductCategory\Model\Resolver;

use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Query\ResolverInterface;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Catalog\Api\ProductAttributeRepositoryInterface;
use Magento\Catalog\Api\CategoryRepositoryInterface;

/**
 * Resolver for retrieving product details by SKU
 */
class ProductCategoryResolver implements ResolverInterface
{
    /**
     * @var ProductRepositoryInterface
     */
    protected $productRepository;

    /**
     * @var ProductAttributeRepositoryInterface
     */
    protected $attributeRepository;

    /**
     * @var CategoryRepositoryInterface
     */
    protected $categoryRepository;

    /**
     * ProductCategoryResolver constructor.
     *
     * @param ProductRepositoryInterface $productRepository
     * @param ProductAttributeRepositoryInterface $attributeRepository
     * @param CategoryRepositoryInterface $categoryRepository
     */
    public function __construct(
        ProductRepositoryInterface $productRepository,
        ProductAttributeRepositoryInterface $attributeRepository,
        CategoryRepositoryInterface $categoryRepository
    ) {
        $this->productRepository = $productRepository;
        $this->attributeRepository = $attributeRepository;
        $this->categoryRepository = $categoryRepository;
    }

    /**
     * Resolve product details by SKU
     *
     * @param Field $field
     * @param mixed|null $context
     * @param ResolveInfo $info
     * @param array|null $value
     * @param array|null $args
     * @return array
     */
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

            $categoryIds = $product->getCategoryIds();
            $categories = [];
            foreach ($categoryIds as $categoryId) {
                $category = $this->categoryRepository->get($categoryId);
                if ($category->getIsActive()) {
                    $categories[] = [
                        'id' => $category->getId(),
                        'name' => $category->getName(),
                    ];
                }
            }

            return [
                'sku' => $productsku,
                'label' => $optionLabel,
                'id' => $productid,
                'price' => $productprice,
                'color' => $defaultAttributeLabel,
                'categories' => $categories,
                'message' => "Enabled categories are:"
            ];
        } catch (\Magento\Framework\Exception\NoSuchEntityException $e) {
            // Handle the case when product with given SKU is not found
            return [
                'sku' => $sku,
                'message' => "The product with SKU {$sku} was not found. Verify the SKU and try again."
            ];
        }
    }
}
