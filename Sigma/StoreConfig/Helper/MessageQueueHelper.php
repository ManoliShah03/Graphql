<?php

namespace Sigma\StoreConfig\Helper;

use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\App\Helper\Context;
use Magento\Framework\App\Config\ScopeConfigInterface;

class MessageQueueHelper extends AbstractHelper
{
    public const XML_PATH_SUCCESSFUL_MESSAGES_LIFETIME = 'system/mysqlmq/successful_messages_lifetime';
    public const XML_PATH_RETRY_INPROGRESS_AFTER = 'system/mysqlmq/retry_inprogress_after';
    public const XML_PATH_FAILED_MESSAGES_LIFETIME = 'system/mysqlmq/failed_messages_lifetime';
    public const XML_PATH_NEW_MESSAGES_LIFETIME = 'system/mysqlmq/new_messages_lifetime';

    /**
     * @var ScopeConfigInterface
     */
    protected $scopeConfig;

    /**
     * MessageQueueHelper constructor.
     * @param Context $context
     * @param ScopeConfigInterface $scopeConfig
     */
    public function __construct(
        Context $context,
        ScopeConfigInterface $scopeConfig
    ) {
        parent::__construct($context);
        $this->scopeConfig = $scopeConfig;
    }

    /**
     * Get successful messages lifetime
     *
     * @return mixed
     */
    public function getSuccessfulMessagesLifetime()
    {
        return $this->scopeConfig->getValue(self::XML_PATH_SUCCESSFUL_MESSAGES_LIFETIME);
    }

    /**
     * Get retry in progress after
     *
     * @return mixed
     */
    public function getRetryInProgressAfter()
    {
        return $this->scopeConfig->getValue(self::XML_PATH_RETRY_INPROGRESS_AFTER);
    }

    /**
     * Get failed messages lifetime
     *
     * @return mixed
     */
    public function getFailedMessagesLifetime()
    {
        return $this->scopeConfig->getValue(self::XML_PATH_FAILED_MESSAGES_LIFETIME);
    }

    /**
     * Get new messages lifetime
     *
     * @return mixed
     */
    public function getNewMessagesLifetime()
    {
        return $this->scopeConfig->getValue(self::XML_PATH_NEW_MESSAGES_LIFETIME);
    }
}
