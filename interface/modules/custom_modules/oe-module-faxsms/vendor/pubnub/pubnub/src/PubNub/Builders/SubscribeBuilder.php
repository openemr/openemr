<?php

namespace PubNub\Builders;


use PubNub\Builders\DTO\SubscribeOperation;

class SubscribeBuilder extends PubSubBuilder
{
    /** @var  bool */
    protected $presenceEnabled = false;

    /** @var  int */
    protected $timetoken;

    /**
     * @return $this
     */
    public function withPresence()
    {
        $this->presenceEnabled = true;

        return $this;
    }

    /**
     * @param int $timetoken
     * @return $this
     */
    public function withTimetoken($timetoken)
    {
        $this->timetoken = $timetoken;

        return $this;
    }

    public function execute()
    {
        $subscribeOperation = new SubscribeOperation(
            $this->channelSubscriptions,
            $this->channelGroupSubscriptions,
            $this->presenceEnabled,
            $this->timetoken
        );

        $this->subscriptionManager->adaptSubscribeBuilder($subscribeOperation);
        $this->subscriptionManager->start();
    }
}
