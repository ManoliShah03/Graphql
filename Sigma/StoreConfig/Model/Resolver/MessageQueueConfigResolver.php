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
     *
     * @param MessageQueueHelper $messageQueueHelper
     */
    public function __construct(MessageQueueHelper $messageQueueHelper)
    {
        $this->messageQueueHelper = $messageQueueHelper;
    }

    /**
     * Resolve message queue configuration
     *
     * @param Field         $field
     * @param mixed         $context
     * @param ResolveInfo   $info
     * @param array|null    $value
     * @param array|null    $args
     * @return array
     */
    public function resolve(
        Field $field,
        $context,
        ResolveInfo $info,
        array $value = null,
        array $args = null
    ) {
        return [
            'successfulMessagesLifetime' => $this->getValueOrBlank($this->messageQueueHelper->
            getSuccessfulMessagesLifetime()),
            'retryInProgressAfter' => $this->getValueOrBlank($this->messageQueueHelper->getRetryInProgressAfter()),
            'failedMessagesLifetime' => $this->getValueOrBlank($this->messageQueueHelper->getFailedMessagesLifetime()),
            'newMessagesLifetime' => $this->getValueOrBlank($this->messageQueueHelper->getNewMessagesLifetime())
        ];
    }

    /**
     * Get value or return null if value is empty
     *
     * @param mixed $value
     * @return mixed
     */
    protected function getValueOrBlank($value)
    {
        return !empty($value) ? (int)$value :' ';
    }
}
