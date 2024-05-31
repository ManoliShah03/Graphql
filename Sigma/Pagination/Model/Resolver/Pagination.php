<?php

namespace Sigma\Pagination\Model\Resolver;

use Magento\Framework\GraphQl\Query\ResolverInterface;
use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;
use Magento\Catalog\Model\ResourceModel\Product\CollectionFactory;
use Magento\Catalog\Model\CategoryRepository;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\GraphQl\Exception\GraphQlInputException;

/**
 * Resolver for product pagination GraphQL query.
 */
class Pagination implements ResolverInterface
{
    /**
     * @var CollectionFactory
     */
    protected $productCollectionFactory;

    /**
     * @var CategoryRepository
     */
    protected $categoryRepository;

    /**
     * Constructor.
     *
     * @param CollectionFactory $productCollectionFactory
     * @param CategoryRepository $categoryRepository
     */
    public function __construct(
        CollectionFactory $productCollectionFactory,
        CategoryRepository $categoryRepository
    ) {
        $this->productCollectionFactory = $productCollectionFactory;
        $this->categoryRepository = $categoryRepository;
    }

    /**
     * Resolve GraphQL query.
     *
     * @param Field $field
     * @param mixed $context
     * @param ResolveInfo $info
     * @param array|null $value
     * @param array|null $args
     * @return array
     * @throws GraphQlInputException
     */
    public function resolve(Field $field, $context, ResolveInfo $info, array $value = null, array $args = null)
    {
        $minPrice = isset($args['minPrice']) ? $args['minPrice'] : 0;
        $maxPrice = isset($args['maxPrice']) ? $args['maxPrice'] : PHP_INT_MAX;
        $pageSize = isset($args['pageSize']) ? $args['pageSize'] : 10;
        $categoryId = isset($args['categoryId']) ? $args['categoryId'] : null;

        try {
            if ($categoryId !== null) {
                // Load category by ID
                $category = $this->categoryRepository->get($categoryId);
                // Add category filter to product collection
                $collection = $this->productCollectionFactory->create();
                $collection->addAttributeToSelect(['name', 'sku', 'price'])
                    ->addFieldToFilter('price', ['from' => $minPrice])
                    ->addFieldToFilter('price', ['to' => $maxPrice])
                    ->addCategoriesFilter(['in' => $category->getAllChildren(true)])
                    ->setPageSize($pageSize);

            } else {
                // If no category ID provided, retrieve all products without category filter
                $collection = $this->productCollectionFactory->create();
                $collection->addAttributeToSelect(['name', 'sku', 'price'])
                    ->addFieldToFilter('price', ['from' => $minPrice])
                    ->addFieldToFilter('price', ['to' => $maxPrice])
                    ->setPageSize($pageSize);
            }

            $products = [];
            foreach ($collection as $product) {
                $products[] = [
                    'name' => $product->getName(),
                    'sku' => $product->getSku(),
                    'price' => $product->getPrice()
                ];
            }

            return [
                'items' => $products,
                'totalItems' => $collection->getSize()
            ];

        } catch (NoSuchEntityException $e) {
            // Handle case where category with given ID does not exist
            throw new GraphQlInputException(__('Invalid category ID provided.'));
        }
    }
}
