<?php

namespace Sigma\PhysicalStore\Model\Resolver;

use Magento\Framework\GraphQl\Query\Resolver\ValueFactory;
use Magento\Framework\GraphQl\Query\ResolverInterface;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;
use Ideo\StoreLocator\Model\StoreFactory;
use Ideo\StoreLocator\Model\CategoryFactory;

class StoreLocatorResolver implements ResolverInterface
{
    /**
     * @var ValueFactory
     */
    private $valueFactory;

    /**
     * @var StoreFactory
     */
    private $storeFactory;

    /**
     * @var CategoryFactory
     */
    private $categoryFactory;

    /**
     * StoreLocatorResolver constructor.
     *
     * @param ValueFactory $valueFactory
     * @param StoreFactory $storeFactory
     * @param CategoryFactory $categoryFactory
     */
    public function __construct(
        ValueFactory $valueFactory,
        StoreFactory $storeFactory,
        CategoryFactory $categoryFactory
    ) {
        $this->valueFactory = $valueFactory;
        $this->storeFactory = $storeFactory;
        $this->categoryFactory = $categoryFactory;
    }

    /**
     * Resolve function for retrieving store locations based on latitude, longitude, and radius.
     *
     * @param \Magento\Framework\GraphQl\Config\Element\Field $field
     * @param mixed $context
     * @param ResolveInfo $info
     * @param array|null $value
     * @param array|null $args
     * @return array
     */
    public function resolve(
        \Magento\Framework\GraphQl\Config\Element\Field $field,
        $context,
        ResolveInfo $info,
        array $value = null,
        array $args = null
    ) {
        $latitude = (float)$args['latitude'];
        $longitude = (float)$args['longitude'];
        $radius = (int)$args['radius'];

        $stores = $this->getStoreLocatorData($latitude, $longitude, $radius);

        if (empty($stores)) {
            return [
                'message' => 'No stores found within the specified radius.',
                'stores' => []
            ];
        }

        return [
            'message' => 'Stores found within the specified radius.',
            'stores' => $stores
        ];
    }

    /**
     * Get store locations based on latitude, longitude, and radius.
     *
     * @param float $latitude
     * @param float $longitude
     * @param int $radius
     * @return array
     */
    private function getStoreLocatorData(float $latitude, float $longitude, int $radius)
    {
        $stores = [];

        foreach ($this->storeFactory->create()->getCollection() as $store) {
            $categoryId = $store->getCategoryId();
            $category = $this->categoryFactory->create()->load($categoryId);

            if ($category && $category->isActive() && $this->isStoreActive($store)) {
                $distance = $this->calculateDistance(
                    $latitude,
                    $longitude,
                    (float)$store->getLat(),
                    (float)$store->getLng()
                );

                if ($distance <= $radius) {
                    $stores[] = [
                        'storeName' => $store->getName(),
                        'address' => $store->getAddress() . ', ' . $store->getCity() . ', ' . $store->getCountry(),
                        'distance' => $distance
                    ];
                }
            }
        }

        return $stores;
    }

    /**
     * Check if the store is active.
     *
     * @param \Ideo\StoreLocator\Model\Store $store
     * @return bool
     */
    private function isStoreActive($store)
    {
        // Assuming the store model has an isActive method or property
        return $store->isActive();
    }

    /**
     * Calculate distance between two points on the Earth's surface.
     *
     * @param float $latitude1
     * @param float $longitude1
     * @param float $latitude2
     * @param float $longitude2
     * @return float
     */
    public function calculateDistance(float $latitude1, float $longitude1, float $latitude2, float $longitude2): float
    {
        $latFrom = deg2rad($latitude1);
        $lonFrom = deg2rad($longitude1);
        $latTo = deg2rad($latitude2);
        $lonTo = deg2rad($longitude2);

        $earthRadius = 6371;

        $latDelta = $latTo - $latFrom;
        $lonDelta = $lonTo - $lonFrom;

        $angle = 2 * asin(sqrt(pow(sin($latDelta / 2), 2) + cos($latFrom) * cos($latTo) * pow(sin($lonDelta / 2), 2)));

        return $angle * $earthRadius;
    }
}
