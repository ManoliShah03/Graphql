<?php

namespace Sigma\Graphql\Model\Resolver;

use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\CatalogInventory\Api\StockRegistryInterface;
use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Query\ResolverInterface;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;

class UpdateProductStock implements ResolverInterface
{
    /**
     * @var ProductRepositoryInterface
     */
    private $productRepository;

    /**
     * @var StockRegistryInterface
     */
    private $stockRegistry;

    /**
     * UpdateProductStock constructor.
     *
     * @param ProductRepositoryInterface $productRepository
     * @param StockRegistryInterface $stockRegistry
     */
    public function __construct(
        ProductRepositoryInterface $productRepository,
        StockRegistryInterface $stockRegistry
    ) {
        $this->productRepository = $productRepository;
        $this->stockRegistry = $stockRegistry;
    }

    /**
     * Resolver for updating product stock.
     *
     * @param Field $field
     * @param mixed $context
     * @param ResolveInfo $info
     * @param array|null $value
     * @param array|null $args
     * @return array
     */
    public function resolve(Field $field, $context, ResolveInfo $info, array $value = null, array $args = null)
    {
        try {
            $sku = $args['sku'];
            $quantity = $args['quantity'];

            if ($quantity < 0) {
                return [
                    'sku' => $sku,
                    'message' => "Negative values are not allowed for stock"
                ];
            }

            $product = $this->productRepository->get($sku);
            $stockItem = $this->stockRegistry->getStockItem($product->getId());

            $stockItem->setQty($quantity);
            $this->stockRegistry->updateStockItemBySku($sku, $stockItem);

            // Check if the stock update was successful
            $updatedStockItem = $this->stockRegistry->getStockItem($product->getId());

            return [
                'sku' => $sku,
                'message' => "Updated stock successfully"
            ];
        } catch (\Exception $e) {
            return [
                'sku' => $sku,
                'message' => "Failed to update stock quantity: The product that was requested doesn't exist.
                 Verify the product and try again"
            ];
        }
    }
}
