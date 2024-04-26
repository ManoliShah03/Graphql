<?php

namespace Sigma\DisabledCategory\Model\Resolver;

use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Query\ResolverInterface;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;
use Magento\Catalog\Model\CategoryFactory;
use Magento\Framework\Exception\LocalizedException;

class DisabledCategoriesResolver implements ResolverInterface
{
    /**
     * @var CategoryFactory
     */
    protected $categoryFactory;

    /**
     * DisabledCategoriesResolver constructor.
     * @param CategoryFactory $categoryFactory
     */
    public function __construct(
        CategoryFactory $categoryFactory
    ) {
        $this->categoryFactory = $categoryFactory;
    }

    /**
     * @inheritDoc
     */
    public function resolve(Field $field, $context, ResolveInfo $info, array $value = null, array $args = null)
    {
        try {
            $categories = $this->categoryFactory->create()->getCollection()
                ->addFieldToFilter('is_active', 0)
                ->addFieldToFilter('include_in_menu', 0)
                ->addAttributeToSelect(['name', 'description', 'image']);

            $result = [];
            foreach ($categories as $category) {
                $result[] = [
                    'id' => $category->getId(),
                    'name' => $category->getName(),
                    'description' => $category->getDescription(),
                    'imageUrl' => $category->getImageUrl()
                ];
            }
            return $result;
        } catch (\Exception $e) {
            throw new \Exception(__('Failed to fetch custom attribute value: %1', $e->getMessage()));
        }
    }
}
