<?php

namespace Sigma\CmsBlock\Model\Resolver;

use Magento\CmsGraphQl\Model\Resolver\Blocks;
use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;
use Magento\Cms\Model\BlockFactory;
use Magento\Framework\GraphQl\Exception\GraphQlInputException;

/**
 * Custom resolver for fetching CMS blocks with additional details.
 */
class CustomCmsBlocks extends Blocks
{
    /**
     * @var BlockFactory
     */
    protected $blockFactory;

    /**
     * CustomCmsBlocks constructor.
     *
     * @param BlockFactory $blockFactory
     */
    public function __construct(
        BlockFactory $blockFactory
    ) {
        $this->blockFactory = $blockFactory;
    }

    /**
     * Resolve the CMS blocks with additional details.
     *
     * @param Field|null $field
     * @param mixed|null $context
     * @param ResolveInfo|null $info
     * @param array|null $value
     * @param array|null $args
     * @return array
     * @throws GraphQlInputException
     */
    public function resolve(Field $field, $context, ResolveInfo $info, array $value = null, array $args = null)
    {
        try {
            $identifiers = $args['identifiers'];
            $result = ['items' => []];

            foreach ($identifiers as $identifier) {
                $staticBlock = $this->blockFactory->create()->load($identifier);

                if (!$staticBlock->getId()) {
                    throw new GraphQlInputException(__('CMS block with identifier "%1" does not exist.', $identifier));
                }

                $result['items'][] = [
                    'title' => $staticBlock->getTitle(),
                    'content' => $staticBlock->getContent(),
                    'identifier' => $staticBlock->getIdentifier(),
                    'is_active' => (bool) $staticBlock->getIsActive()
                ];
            }

            return $result;
        } catch (\Exception $e) {
            // Handle exceptions
            throw new GraphQlInputException(__('An error occurred while processing the request: %1', $e->getMessage()));
        }
    }
}
