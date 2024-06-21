<?php

namespace Sigma\OrderTracking\Model\Resolver;

use Magento\Framework\GraphQl\Query\ResolverInterface;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;
use Magento\Sales\Api\OrderRepositoryInterface;
use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Query\Resolver\ContextInterface;

class OrderTracking implements ResolverInterface
{
    /**
     * @var OrderRepositoryInterface
     */
    protected $orderRepository;

    /**
     * OrderTracking constructor.
     * @param OrderRepositoryInterface $orderRepository
     */
    public function __construct(
        OrderRepositoryInterface $orderRepository
    ) {
        $this->orderRepository = $orderRepository;
    }

    /**
     * Resolver for fetching order tracking information.
     *
     * @param Field $field
     * @param ContextInterface $context
     * @param ResolveInfo $info
     * @param array|null $value
     * @param array|null $args
     * @return array|null
     */
    public function resolve(
        Field $field,
        $context,
        ResolveInfo $info,
        array $value = null,
        array $args = null
    ) {
        $orderId = $args['orderId'];

        try {
            $order = $this->orderRepository->get($orderId);

            $shipments = [];

            foreach ($order->getShipmentsCollection() as $shipment) {
                $shipmentId = $shipment->getId();
                $tracks = $shipment->getAllTracks();

                foreach ($tracks as $track) {
                    $shipments[] = [
                        'id' => sprintf('%09d', $shipmentId), 
                        'carrier' => $track->getTitle(),
                        'trackingNumber' => $track->getTrackNumber()
                    ];
                }
            }

            if (empty($shipments)) {
                return null;
            }

            return [
                'status' => $order->getStatus(),
                'shipments' => $shipments
            ];
        } catch (\Exception $e) {
            return null;
        }
    }
}
