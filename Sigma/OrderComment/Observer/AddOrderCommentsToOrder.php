<?php

namespace Sigma\OrderComment\Observer;

class AddOrderCommentsToOrder implements \Magento\Framework\Event\ObserverInterface
{
    /**
     * Execute observer to add order comments to order.
     *
     * @param \Magento\Framework\Event\Observer $observer
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        $order = $observer->getEvent()->getOrder();
        $quote = $observer->getEvent()->getQuote();

        $order->setData('sigma_order_comments', $quote->getSigmaOrderComments());
    }
}
