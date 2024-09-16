<?php

namespace PubNub\Builders;

use PubNub\Managers\SubscriptionManager;
use PubNub\PubNubUtil;


abstract class PubSubBuilder
{
    /** @var  string[] */
    protected $channelSubscriptions = [];

    /** @var  string[] */
    protected $channelGroupSubscriptions = [];

    /** @var  SubscriptionManager */
    protected $subscriptionManager;

    /**
     * PubSubBuilder constructor.
     * @param SubscriptionManager $subscriptionManager
     */
    public function __construct(SubscriptionManager $subscriptionManager)
    {
        $this->subscriptionManager = $subscriptionManager;
    }

    abstract public function execute();

    /**
     * @param string|string[] $channel
     * @return $this
     */
    public function channels($channel)
    {
        $this->channelSubscriptions = PubNubUtil::extendArray($this->channelSubscriptions, $channel);

        return $this;
    }

    /**
     * Alias for channels method
     *
     * @param string|string[] $channel
     * @return $this
     */
    public function channel($channel)
    {
        $this->channels($channel);

        return $this;
    }

    /**
     * @param string|string[] $channelGroup
     * @return $this
     */
    public function channelGroups($channelGroup)
    {
        $this->channelGroupSubscriptions = PubNubUtil::extendArray($this->channelGroupSubscriptions, $channelGroup);

        return $this;
    }

    /**
     * Alias for channelGroups method
     *
     * @param string|string[] $channelGroup
     * @return $this
     */
    public function channelGroup($channelGroup)
    {
        $this->channelGroups($channelGroup);

        return $this;
    }
}