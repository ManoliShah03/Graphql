<?php

namespace Sigma\StoreConfig\Model\Resolver;

use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Query\ResolverInterface;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;
use Sigma\StoreConfig\Helper\MessageQueueHelper;

class MessageQueueConfigResolver implements ResolverInterface
{
    /**
     * @var MessageQueueHelper
     */
    protected $messageQueueHelper;

    /**
     * MessageQueueConfigResolver constructor.
     * @param MessageQueueHelper $messageQueueHelper
     */
    public function __construct(
        MessageQueueHelper $messageQueueHelper
    ) {
        $this->messageQueueHelper = $messageQueueHelper;
    }

    /**
     * Resolve message queue configuration
     *
     * @param Field $field
     * @param mixed $context
     * @param ResolveInfo $info
     * @param array|null $value
     * @param array|null $args
     * @return array
     */
    public function resolve(Field $field, $context, ResolveInfo $info, array $value = null, array $args = null)
    {
        return [
            'successfulMessagesLifetime' => (int) $this->messageQueueHelper->getSuccessfulMessagesLifetime(),
            'retryInProgressAfter' => (int) $this->messageQueueHelper->getRetryInProgressAfter(),
            'failedMessagesLifetime' => (int) $this->messageQueueHelper->getFailedMessagesLifetime(),
            'newMessagesLifetime' => (int) $this->messageQueueHelper->getNewMessagesLifetime(),
        ];
    }
}
