<?php
declare(strict_types=1);

namespace Sigma\OrderHistory\Model\Resolver;

use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Exception\GraphQlAuthorizationException;
use Magento\Framework\GraphQl\Query\ResolverInterface;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;
use Magento\GraphQl\Model\Query\ContextInterface;
use Magento\Sales\Model\Order;
use Magento\Sales\Model\ResourceModel\Order\CollectionFactoryInterface;
use Magento\Sales\Model\ResourceModel\Order\Item\CollectionFactory as OrderItemCollectionFactory;

/**
 * Resolver for fetching customer orders with additional order item details.
 */
class Orders extends \Magento\SalesGraphQl\Model\Resolver\Orders implements ResolverInterface
{
    /**
     * @var CollectionFactoryInterface
     */
    private $collectionFactory;

    /**
     * @var OrderItemCollectionFactory
     */
    private $orderItemCollectionFactory;

    /**
     * Constructor.
     *
     * @param CollectionFactoryInterface $collectionFactory
     * @param OrderItemCollectionFactory $orderItemCollectionFactory
     */
    public function __construct(
        CollectionFactoryInterface $collectionFactory,
        OrderItemCollectionFactory $orderItemCollectionFactory
    ) {
        parent::__construct($collectionFactory, $orderItemCollectionFactory);
        $this->collectionFactory = $collectionFactory;
        $this->orderItemCollectionFactory = $orderItemCollectionFactory;
    }

    /**
     * Resolves customer orders with additional order item details.
     *
     * @param Field         $field
     * @param mixed         $context
     * @param ResolveInfo   $info
     * @param array|null    $value
     * @param array|null    $args
     * @return array
     * @throws GraphQlAuthorizationException
     */
    public function resolve(
        Field $field,
        $context,
        ResolveInfo $info,
        array $value = null,
        array $args = null
    ): array {
        /** @var ContextInterface $context */
        if (false === $context->getExtensionAttributes()->getIsCustomer()) {
            throw new GraphQlAuthorizationException(__('The current customer isn\'t authorized.'));
        }

        $items = [];
        $orders = $this->collectionFactory->create($context->getUserId());

        /** @var Order $order */
        foreach ($orders as $order) {
            $orderItems = $this->orderItemCollectionFactory->create()->addFieldToFilter('order_id', $order->getId());
            $orderItemsData = [];

            foreach ($orderItems as $orderItem) {
                $productName = $orderItem->getName();
                $quantity = $orderItem->getQtyOrdered();

                $orderItemsData[] = [
                    'product_name' => $productName,
                    'quantity' => $quantity
                ];
            }

            $items[] = [
                'id' => $order->getId(),
                'increment_id' => $order->getIncrementId(),
                'order_number' => $order->getIncrementId(),
                'created_at' => $order->getCreatedAt(),
                'grand_total' => $order->getGrandTotal(),
                'status' => $order->getStatus(),
                'model' => $order,
                'order_items' => $orderItemsData
            ];
        }
        return ['items' => $items];
    }
}
