<?php

namespace Sigma\ProductSearch\Model\Resolver;

use Magento\Framework\GraphQl\Query\ResolverInterface;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;
use Magento\Catalog\Model\ResourceModel\Product\CollectionFactory;

class ProductSearch implements ResolverInterface
{
    /**
     * @var CollectionFactory
     */
    private $productCollectionFactory;

    /**
     * ProductSearch constructor.
     * @param CollectionFactory $productCollectionFactory
     */
    public function __construct(
        CollectionFactory $productCollectionFactory
    ) {
        $this->productCollectionFactory = $productCollectionFactory;
    }

    /**
     * @inheritDoc
     */
    public function resolve($field, $context, $info, $value = null, $args = null)
    {
        $query = $args['query'] ?? '';
        $filters = $args['filters'] ?? [];
        $minPrice = $filters['price']['min'] ?? null;
        $maxPrice = $filters['price']['max'] ?? null;
        $brand = $filters['brand'] ?? null;
        $sortDirection = $args['sort']['direction'] ?? null;

        $collection = $this->productCollectionFactory->create()
            ->addAttributeToSelect(['*', 'brand', 'popularity'])
            ->addAttributeToFilter('name', ['like' => '%' . $query . '%'])
            ->addFieldToFilter('price', ['gteq' => $minPrice])
            ->addFieldToFilter('price', ['lteq' => $maxPrice]);

        if ($brand) {
            $collection->addFieldToFilter('brand', ['eq' => $brand]);
        }

        $sortField = $args['sort']['field'] ?? null;
        if ($sortField) {
            $collection->setOrder($sortField, $sortDirection === 'DESC' ? 'DESC' : 'ASC');
        }

        $products = [];
        foreach ($collection as $product) {
            $products[] = [
                'name' => $product->getName(),
                'price' => $product->getPrice(),
                'sku' => $product->getSku(),
                'brand' => $product->getData('brand'),
                'popularity' => $product->getData('popularity')
            ];
        }

        return $products;
    }
}
