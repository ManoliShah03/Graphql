<?php

namespace Sigma\DisabledCategory\Model\Resolver;

use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Query\ResolverInterface;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;
use Magento\Catalog\Model\CategoryFactory;
use Magento\Framework\Exception\LocalizedException;
use Magento\Store\Model\StoreManagerInterface;

class DisabledCategoriesResolver implements ResolverInterface
{
    /**
     * @var CategoryFactory
     */
    protected $categoryFactory;

    /**
     * @var StoreManagerInterface
     */
    protected $storeManager;

    /**
     * DisabledCategoriesResolver constructor.
     * @param CategoryFactory $categoryFactory
     * @param StoreManagerInterface $storeManager
     */
    public function __construct(
        CategoryFactory $categoryFactory,
        StoreManagerInterface $storeManager
    ) {
        $this->categoryFactory = $categoryFactory;
        $this->storeManager = $storeManager;
    }

    /**
     * @inheritDoc
     */
    public function resolve(Field $field, $context, ResolveInfo $info, array $value = null, array $args = null)
    {
        try {
            $baseUrl = $this->storeManager->getStore()->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_MEDIA);

            $categories = $this->categoryFactory->create()->getCollection()
                ->addFieldToFilter('is_active', 0) // Filter disabled categories
                ->addFieldToFilter('include_in_menu', 0) // Filter categories not included in the menu
                ->addAttributeToSelect(['name', 'description', 'image']);

            $result = [];
            foreach ($categories as $category) {
                $imageUrl = null;
                $image = $category->getImage();
                if ($image) {
                    $imageUrl = $baseUrl . $image;
                }

                $result[] = [
                    'id' => $category->getId(),
                    'name' => $category->getName(),
                    'description' => $category->getDescription(),
                    'imageUrl' => $imageUrl,
                ];
            }
            return $result;
        } catch (\Exception $e) {
            throw new \Exception(__('Failed to fetch custom attribute value: %1', $e->getMessage()));
        }
    }
}
