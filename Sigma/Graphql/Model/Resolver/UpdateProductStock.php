<?php

namespace Sigma\Graphql\Model\Resolver;

use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\CatalogInventory\Api\StockRegistryInterface;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Query\Resolver\ContextInterface;
use Magento\Framework\GraphQl\Query\Resolver\Value;
use Magento\Framework\GraphQl\Query\ResolverInterface;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;
use Magento\Framework\Exception\InputException;
use Magento\Framework\Exception\LocalizedException;

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

    public function __construct(
        ProductRepositoryInterface $productRepository,
        StockRegistryInterface $stockRegistry
    ) {
        $this->productRepository = $productRepository;
        $this->stockRegistry = $stockRegistry;
    }
    public function resolve(Field $field, $context, ResolveInfo $info, array $value = null, array $args = null)
    {
        try {
            $writer = new \Zend_Log_Writer_Stream(BP . '/var/log/test.log');
            $logger = new \Zend_Log();
            $logger->addWriter($writer);
            $sku = $args['sku'];
            $logger->info($sku);
            $quantity = $args['quantity'];
            $logger->info($quantity);
            $product = $this->productRepository->get($sku);
            $stockItem = $this->stockRegistry->getStockItem($product->getId());
            $stockItem->setQty($quantity);
            $this->stockRegistry->updateStockItemBySku($sku, $stockItem);

//            $logger->info(print_r($stockItem));
            return [
                'sku' => $sku,
                'message' => "Updated stock successfully"
            ];
        } catch (\Exception $e) {
            throw new \Exception(__('Failed to update stock quantity: %1', $e->getMessage()));
        }
    }
}
