<?php
namespace Sigma\CustomAttribute\Model\Resolver;

use Magento\Framework\GraphQl\Query\ResolverInterface;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Catalog\Api\ProductAttributeRepositoryInterface;
use Magento\Catalog\Api\CategoryRepositoryInterface;
use Magento\Catalog\Model\ResourceModel\Product\CollectionFactory as ProductCollectionFactory;
use Magento\Framework\Exception\ResolverException;

/**
 * Resolver for fetching custom attributes of a product in GraphQL query.
 */
class CustomAttributeResolver implements ResolverInterface
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
     * @var ProductCollectionFactory
     */
    protected $productCollectionFactory;

    /**
     * CustomAttributeResolver constructor.
     *
     * @param ProductRepositoryInterface $productRepository
     * @param ProductAttributeRepositoryInterface $attributeRepository
     * @param CategoryRepositoryInterface $categoryRepository
     * @param ProductCollectionFactory $productCollectionFactory
     */
    public function __construct(
        ProductRepositoryInterface $productRepository,
        ProductAttributeRepositoryInterface $attributeRepository,
        CategoryRepositoryInterface $categoryRepository,
        ProductCollectionFactory $productCollectionFactory
    ) {
        $this->productRepository = $productRepository;
        $this->attributeRepository = $attributeRepository;
        $this->categoryRepository = $categoryRepository;
        $this->productCollectionFactory = $productCollectionFactory;
    }

    /**
     * Resolve custom attributes of a product.
     *
     * @param \Magento\Framework\GraphQl\Config\Element\Field $field
     * @param \Magento\Framework\GraphQl\ContextInterface $context
     * @param ResolveInfo $info
     * @param array|null $value
     * @param array|null $args
     * @return array
     * @throws ResolverException
     */
    public function resolve(
        \Magento\Framework\GraphQl\Config\Element\Field $field,
        $context,
        ResolveInfo $info,
        array $value = null,
        array $args = null
    ): array {
        $sku = $args['sku'];
        $attributeCodes = $args['attributeCode'];

        try {
            $product = $this->productRepository->get($sku);

            $productData = [
                'sku' => $product->getSku(),
                'label' => $product->getName(),
                'id' => $product->getId(),
                'price' => $product->getPrice()
            ];

            // Fetch the attribute labels and values dynamically
            $productAttributes = [];
            foreach ($attributeCodes as $code) {
                $attribute = $this->attributeRepository->get($code);
                $label = $attribute->getDefaultFrontendLabel();
                $value = null;

                // Fetching value for textarea and text attributes
                if ($attribute->getFrontendInput() === 'textarea' || $attribute->getFrontendInput() === 'text') {
                    $value = $product->getData($code);
                } else {
                    // For other attribute types
                    $value = $product->getAttributeText($code);
                }

                $productAttributes[] = [
                    'attributeCode' => $code,
                    'label' => $label,
                    'value' => $value
                ];
            }

            $customAttributeValue = $product->getData('custom_attribute_code');
            if ($customAttributeValue !== null) {
                $customAttributeLabel = 'Custom Attribute Label';
                $productAttributes[] = [
                    'attributeCode' => 'custom_attribute_code',
                    'label' => $customAttributeLabel,
                    'value' => $customAttributeValue
                ];
            }

            return [
                'sku' => $productData['sku'],
                'label' => $productData['label'],
                'id' => $productData['id'],
                'price' => $productData['price'],
                'productAttributes' => $productAttributes
            ];
        } catch (\Exception $e) {
            throw new ResolverException(__('Failed to fetch custom attribute value: %1', $e->getMessage()));
        }
    }
}
