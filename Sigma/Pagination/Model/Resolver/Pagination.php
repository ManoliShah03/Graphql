<?php

namespace Sigma\Pagination\Model\Resolver;

use Magento\Framework\GraphQl\Query\ResolverInterface;
use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;
use Magento\Catalog\Model\ResourceModel\Product\CollectionFactory;
use Magento\Catalog\Api\CategoryRepositoryInterface;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\GraphQl\Exception\GraphQlInputException;
use Magento\Catalog\Model\Product\Visibility;
use Magento\Catalog\Model\ProductFactory;
use Magento\Framework\Pricing\PriceCurrencyInterface;

/**
 * Resolver for product pagination.
 */
class Pagination implements ResolverInterface
{
    /**
     * @var CollectionFactory
     */
    protected $productCollectionFactory;

    /**
     * @var CategoryRepositoryInterface
     */
    protected $categoryRepository;

    /**
     * @var ProductFactory
     */
    protected $productFactory;

    /**
     * @var PriceCurrencyInterface
     */
    protected $priceCurrency;

    /**
     * Pagination constructor.
     *
     * @param CollectionFactory           $productCollectionFactory  Product collection factory
     * @param CategoryRepositoryInterface $categoryRepository       Category repository interface
     * @param ProductFactory              $productFactory           Product factory
     * @param PriceCurrencyInterface      $priceCurrency            Price currency interface
     */
    public function __construct(
        CollectionFactory $productCollectionFactory,
        CategoryRepositoryInterface $categoryRepository,
        ProductFactory $productFactory,
        PriceCurrencyInterface $priceCurrency
    ) {
        $this->productCollectionFactory = $productCollectionFactory;
        $this->categoryRepository = $categoryRepository;
        $this->productFactory = $productFactory;
        $this->priceCurrency = $priceCurrency;
    }

    /**
     * Resolve function for product pagination.
     *
     * @param Field         $field    GraphQL field details
     * @param mixed         $context  Execution context
     * @param ResolveInfo   $info     GraphQL resolve information
     * @param array|null    $value    Current resolved value (if any)
     * @param array|null    $args     Resolve arguments
     *
     * @return array        Pagination result
     * @throws GraphQlInputException If category ID is invalid
     */
    public function resolve(Field $field, $context, ResolveInfo $info, array $value = null, array $args = null)
    {
        $minPrice = isset($args['minPrice']) ? $args['minPrice'] : 0;
        $maxPrice = isset($args['maxPrice']) ? $args['maxPrice'] : PHP_INT_MAX;
        $pageSize = isset($args['pageSize']) ? $args['pageSize'] : 10;
        $categoryId = isset($args['categoryId']) ? $args['categoryId'] : null;

        try {
            $collection = $this->productCollectionFactory->create();
            $collection->addAttributeToSelect(['name', 'sku'])
                ->setVisibility([Visibility::VISIBILITY_BOTH, Visibility::VISIBILITY_IN_CATALOG])
                ->addTaxPercents()
                ->addUrlRewrite();

            if ($categoryId !== null) {
                $category = $this->categoryRepository->get($categoryId);
                $collection->addCategoriesFilter(['in' => $category->getAllChildren(true)]);
            }

            $filteredProductIds = [];
            foreach ($collection as $product) {
                $product = $this->productFactory->create()->setStoreId($context->getExtensionAttributes()->
                getStore()->getId())->load($product->getId());
                $finalPrice = $this->priceCurrency->convertAndRound($product->getFinalPrice());

                if ($finalPrice >= $minPrice && $finalPrice <= $maxPrice) {
                    $filteredProductIds[] = $product->getId();
                }
            }

            $filteredCollection = $this->productCollectionFactory->create();
            $filteredCollection->addAttributeToSelect(['name', 'sku'])
                ->setVisibility([Visibility::VISIBILITY_BOTH, Visibility::VISIBILITY_IN_CATALOG])
                ->addTaxPercents()
                ->addUrlRewrite()
                ->addIdFilter($filteredProductIds);

            $filteredCollection->setPageSize($pageSize);

            $products = [];
            foreach ($filteredCollection as $product) {
                $product = $this->productFactory->create()->setStoreId($context->getExtensionAttributes()->
                getStore()->getId())->load($product->getId());
                $finalPrice = $this->priceCurrency->convertAndRound($product->getFinalPrice());

                $products[] = [
                    'name' => $product->getName(),
                    'sku' => $product->getSku(),
                    'price' => floatval($finalPrice)
                ];
            }

            return [
                'items' => $products,
                'totalItems' => count($filteredProductIds)
            ];

        } catch (NoSuchEntityException $e) {
            throw new GraphQlInputException(__('Invalid category ID provided.'));
        }
    }
}
