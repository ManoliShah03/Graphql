<?php
namespace Sigma\AddSimpleProduct\Model\Resolver;

use Magento\Framework\GraphQl\Query\ResolverInterface;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;
use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Catalog\Model\ProductFactory;
use Magento\Framework\UrlInterface;
use Magento\Framework\Exception\LocalizedException;
use \Magento\Catalog\Api\Data\ProductTierPriceExtensionFactory;
use \Magento\Catalog\Api\Data\ProductTierPriceInterfaceFactory;
use Magento\Catalog\Api\SpecialPriceInterface;
use Magento\Catalog\Api\Data\SpecialPriceInterfaceFactory;
use Magento\Framework\Stdlib\DateTime\TimezoneInterface;
use Magento\Framework\GraphQl\Exception\GraphQlInputException;

class AddSimpleProduct implements ResolverInterface
{
    /**
     * @var SpecialPriceInterface
     */
    private $specialPrice;

    /**
     * @var SpecialPriceInterfaceFactory
     */
    private $specialPriceFactory;

    /**
     * @var TimezoneInterface
     */
    protected $timezone;

    /**
     * @var ProductFactory
     */
    private $productFactory;

    /**
     * @var ProductRepositoryInterface
     */
    private $productRepository;

    /**
     * @var UrlInterface
     */
    private $urlBuilder;

    /**
     * @var ProductTierPriceInterfaceFactory
     */
    protected $tierPriceInterface;

    /**
     * @var ProductTierPriceExtensionFactory
     */
    public $tierPriceExtensionAttributesFactory;

    /**
     * AddSimpleProduct constructor.
     * @param ProductFactory $productFactory
     * @param ProductRepositoryInterface $productRepository
     * @param UrlInterface $urlBuilder
     * @param ProductTierPriceExtensionFactory $tierPriceExtensionAttributesFactory
     * @param ProductTierPriceInterfaceFactory $tierPriceInterface
     * @param SpecialPriceInterface $specialPrice
     * @param SpecialPriceInterfaceFactory $specialPriceFactory
     * @param TimezoneInterface $timezone
     */
    public function __construct(
        ProductFactory $productFactory,
        ProductRepositoryInterface $productRepository,
        UrlInterface $urlBuilder,
        ProductTierPriceExtensionFactory $tierPriceExtensionAttributesFactory,
        ProductTierPriceInterfaceFactory $tierPriceInterface,
        SpecialPriceInterface $specialPrice,
        SpecialPriceInterfaceFactory $specialPriceFactory,
        TimezoneInterface $timezone
    ) {
        $this->productFactory = $productFactory;
        $this->productRepository = $productRepository;
        $this->urlBuilder = $urlBuilder;
        $this->tierPriceExtensionAttributesFactory = $tierPriceExtensionAttributesFactory;
        $this->tierPriceInterface = $tierPriceInterface;
        $this->specialPrice = $specialPrice;
        $this->specialPriceFactory = $specialPriceFactory;
        $this->timezone = $timezone;
    }

    /**
     * Create new product
     *
     * @param Field $field
     * @param \Magento\Framework\GraphQl\Query\Resolver\ContextInterface $context
     * @param ResolveInfo $info
     * @param array|null $value
     * @param array|null $args
     * @return array
     * @throws GraphQlInputException
     * @throws LocalizedException
     */
    public function resolve(
        Field $field,
        $context,
        ResolveInfo $info,
        array $value = null,
        array $args = null
    ) {
        $inputArgs = $args['input'];

        $name = $inputArgs['name'];
        $sku = $inputArgs['sku'];
        $status = $inputArgs['status'];
        $visibility = $inputArgs['visibility'];
        $price = $inputArgs['price'];
        $stockData = $args['input']['stock_data'];

        if ($this->productExistsBySku($sku)) {
            throw new GraphQlInputException(__("Product with SKU '$sku' already exists."));
        }

        try {
            // Create a simple product
            $product = $this->productFactory->create();
            $product->setName($name);
            $product->setTypeId('simple');
            $product->setAttributeSetId(4);
            $product->setSku($sku);
            $product->setWebsiteIds([1]);
            $product->setStatus($status);
            $product->setVisibility($visibility);
            $product->setPrice($price);
            $product->setStockData([
                'is_in_stock' => $stockData['is_in_stock'],
                'qty' => $stockData['qty'],
            ]);

            $product->setUrlKey($name . $sku);

            if (!empty($args['input']['special_price'])) {
                $specialprice = $args['input']['special_price'];
                $Format = 'dd/mm/yy';

                $from = \DateTime::createFromFormat($Format, $specialprice['special_from_date']);
                $to = \DateTime::createFromFormat($Format, $specialprice['special_to_date']);

                $product->setCustomAttributes([
                    'special_from_date' => $from,
                    'special_to_date' => $to,
                    'special_price' => $specialprice['special_price'],
                ]);
            }

            if (!empty($inputArgs['tier_prices'])) {
                $tierPrices = $inputArgs['tier_prices'];

                $data = [];

                foreach ($tierPrices as $tierPriceData) {
                    $tierPrice = $this->tierPriceInterface->create();
                    $tierPrice->setCustomerGroupId($tierPriceData['customer_group_id']);
                    $tierPrice->setQty($tierPriceData['qty']);
                    $tierPrice->setValue($tierPriceData['value']);
                    $data[] = $tierPrice;
                }

                $product->setTierPrices($data);
            }
            $product->save();
            $url = $this->urlBuilder->getUrl($product->getUrlKey()) . ".html";

            return [
                'product_id' => $product->getId(),
                'name' => $product->getName(),
                'sku' => $product->getSku(),
                'qty' => $stockData['qty'],
                'url' => $url,
            ];
        } catch (LocalizedException $e) {
            throw new LocalizedException(__("Error creating product: " . $e->getMessage()));
        }
    }

    /**
     * Check sku
     *
     * @param string $sku
     * @return bool
     */
    private function productExistsBySku($sku)
    {
        try {
            $product = $this->productRepository->get($sku);
            return true;
        } catch (\Magento\Framework\Exception\NoSuchEntityException $e) {
            return false;
        }
    }
}
