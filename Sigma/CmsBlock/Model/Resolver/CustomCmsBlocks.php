<?php

namespace Sigma\CmsBlock\Model\Resolver;

use Magento\CmsGraphQl\Model\Resolver\DataProvider\Block as DefaultBlockDataProvider;
use Magento\Framework\App\ResourceConnection;
use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Exception\GraphQlInputException;
use Magento\Framework\GraphQl\Query\ResolverInterface;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;

/**
 * Class CustomCmsBlocks
 * Resolver for retrieving custom CMS blocks
 */
class CustomCmsBlocks implements ResolverInterface
{
    /**
     * @var DefaultBlockDataProvider
     */
    private $dataProvider;

    /**
     * @var ResourceConnection
     */
    private $resourceConnection;

    /**
     * CustomCmsBlocks constructor.
     *
     * @param DefaultBlockDataProvider $dataProvider The default block data provider
     * @param ResourceConnection $resourceConnection The resource connection
     */
    public function __construct(
        DefaultBlockDataProvider $dataProvider,
        ResourceConnection $resourceConnection
    ) {
        $this->dataProvider = $dataProvider;
        $this->resourceConnection = $resourceConnection;
    }

    /**
     * Resolve custom CMS blocks.
     *
     * @param Field $field The field element
     * @param mixed $context The context
     * @param ResolveInfo $info The resolve info
     * @param array|null $value The value
     * @param array|null $args The arguments
     * @return array The resolved custom CMS blocks
     * @throws GraphQlInputException
     */
    public function resolve(Field $field, $context, ResolveInfo $info, array $value = null, array $args = null)
    {
        $storeId = (int)$context->getExtensionAttributes()->getStore()->getId();
        $blockIdentifiers = $this->getBlockIdentifiers($args);
        $blocksData = $this->getBlocksData($blockIdentifiers, $storeId);

        return [
            'items' => $blocksData,
        ];
    }

    /**
     * Get block identifiers.
     *
     * @param array $args The arguments
     * @return array The block identifiers
     * @throws GraphQlInputException
     */
    private function getBlockIdentifiers(array $args): array
    {
        if (!isset($args['identifiers']) || !is_array($args['identifiers']) || count($args['identifiers']) === 0) {
            throw new GraphQlInputException(__('"identifiers" of CMS blocks should be specified'));
        }

        return $args['identifiers'];
    }

    /**
     * Get blocks data.
     *
     * @param array $blockIdentifiers The block identifiers
     * @param int $storeId The store ID
     * @return array The block data
     */
    private function getBlocksData(array $blockIdentifiers, int $storeId): array
    {
        $connection = $this->resourceConnection->getConnection();
        $cmsBlockTable = $connection->getTableName('cms_block');

        $blocksData = [];
        foreach ($blockIdentifiers as $blockIdentifier) {
            $select = $connection->select()
                ->from($cmsBlockTable, ['is_active', 'title', 'content'])
                ->where('identifier = ?', $blockIdentifier);

            $blockInfo = $connection->fetchRow($select);

            if ($blockInfo) {
                $blocksData[$blockIdentifier] = [
                    'identifier' => $blockIdentifier,
                    'is_active' => (bool)$blockInfo['is_active'],
                    'title' => $blockInfo['title'] ?? 'Default Title',
                    'content' => $blockInfo['content'] ?? 'Default Content',
                ];
            }
        }

        return $blocksData;
    }
}
