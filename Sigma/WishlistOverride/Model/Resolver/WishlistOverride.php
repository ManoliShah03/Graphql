<?php

namespace Sigma\WishlistOverride\Model\Resolver;

use Magento\Catalog\Model\Product;
use Magento\CatalogGraphQl\Model\ProductDataProvider;
use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Query\ResolverInterface;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;
use Magento\Store\Model\StoreManagerInterface;

/**
 * Fetches the Product data according to the GraphQL schema
 */
class WishlistOverride implements ResolverInterface
{
    /**
     * @var ProductDataProvider
     */
    private $productDataProvider;

    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    /**
     * @param ProductDataProvider $productDataProvider
     * @param StoreManagerInterface $storeManager
     */
    public function __construct(
        ProductDataProvider $productDataProvider,
        StoreManagerInterface $storeManager
    ) {
        $this->productDataProvider = $productDataProvider;
        $this->storeManager = $storeManager;
    }

    /**
     * @inheritdoc
     */
    public function resolve(
        Field $field,
        $context,
        ResolveInfo $info,
        array $value = null,
        array $args = null
    ) {

        /** @var Product $product */
        $product = $value['model'];

        $productId = (int) $product->getId();
        $productData = $this->productDataProvider->getProductDataById((int) $product->getId());

        $productUrl = $this->storeManager->getStore()->getBaseUrl() . $product->getUrlKey() . '.html';

        $productData['product_id'] = $productId;
        $productData['product_url'] = $productUrl;

        return $productData;
    }
}
