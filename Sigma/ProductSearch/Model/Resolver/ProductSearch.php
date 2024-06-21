<?php

namespace Sigma\ProductSearch\Model\Resolver;

use Magento\Framework\GraphQl\Query\ResolverInterface;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;
use Magento\Catalog\Model\ResourceModel\Product\CollectionFactory;
use Magento\Reports\Model\ResourceModel\Product\CollectionFactory as ReportCollectionFactory;

class ProductSearch implements ResolverInterface
{
    /**
     * @var CollectionFactory
     */
    private $productCollectionFactory;

    /**
     * @var ReportCollectionFactory
     */
    private $reportCollectionFactory;

    /**
     * ProductSearch constructor.
     * @param CollectionFactory $productCollectionFactory
     * @param ReportCollectionFactory $reportCollectionFactory
     */
    public function __construct(
        CollectionFactory $productCollectionFactory,
        ReportCollectionFactory $reportCollectionFactory
    ) {
        $this->productCollectionFactory = $productCollectionFactory;
        $this->reportCollectionFactory = $reportCollectionFactory;
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

        $reportCollection = $this->reportCollectionFactory->create()
            ->addViewsCount()
            ->setPageSize(5);

        $mostViewedProducts = [];
        foreach ($reportCollection as $item) {
            $mostViewedProducts[$item->getId()] = $item->getViews();
        }

        $collection = $this->productCollectionFactory->create()
            ->addAttributeToSelect(['*', 'brand', 'popularity'])
            ->addAttributeToFilter('entity_id', ['in' => array_keys($mostViewedProducts)])
            ->addAttributeToFilter('name', ['like' => '%' . $query . '%'])
            ->addFieldToFilter('price', ['gteq' => $minPrice])
            ->addFieldToFilter('price', ['lteq' => $maxPrice]);

        if ($brand) {
            $collection->addFieldToFilter('brand', ['eq' => $brand]);
        }

        $sortField = $args['sort']['field'] ?? null;
        if ($sortField) {
            $collection->setOrder($sortField, $sortDirection === 'DESC' ? 'DESC' : 'ASC');
        } else {
            $collection->getSelect()->order(
                'FIND_IN_SET(e.entity_id, ?)',
                implode(',', array_keys($mostViewedProducts))
            );
        }

        $products = [];
        foreach ($collection as $product) {
            $productId = $product->getId();
            $products[] = [
                'name' => $product->getName(),
                'price' => $product->getPrice(),
                'sku' => $product->getSku(),
                'brand' => $product->getData('brand'),
                'popularity' => isset($mostViewedProducts[$productId]) ? $mostViewedProducts[$productId] : 0
            ];
        }

        return $products;
    }
}
