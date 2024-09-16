<?php

namespace PubNub\Exceptions;


use PubNub\Builders\DTO\UnsubscribeOperation;
use PubNub\Managers\SubscriptionManager;

class PubNubUnsubscribeException extends \Exception
{
    /** @var  string[] */
    protected $channels = [];

    /** @var  string[] */
    protected $channelGroups = [];

    /** @var  bool */
    protected $all = true;

    /**
     * @return string[]
     */
    public function getChannels()
    {
        return $this->channels;
    }

    /**
     * @param string[] $channels
     * @return $this
     */
    public function setChannels(array $channels)
    {
        $this->channels = $channels;

        if (count($channels) > 0) {
            $this->all = false;
        }

        return $this;
    }

    /**
     * @return string[]
     */
    public function getChannelGroups()
    {
        return $this->channelGroups;
    }

    /**
     * @param string[] $channelGroups
     * @return $this
     */
    public function setChannelGroups(array $channelGroups)
    {
        $this->channelGroups = $channelGroups;

        if (count($channelGroups) > 0) {
            $this->all = false;
        }

        return $this;
    }

    /**
     * @param SubscriptionManager $subscriptionManager
     * @return UnsubscribeOperation
     */
    public function getUnsubscribeOperation(SubscriptionManager $subscriptionManager) {
        if ($this->all) {
            return (new UnsubscribeOperation())
                ->setChannels($subscriptionManager->subscriptionState->prepareChannelList(false))
                ->setChannelGroups($subscriptionManager->subscriptionState->prepareChannelGroupList(false));
        } else {
            return (new UnsubscribeOperation())
                ->setChannels($this->channels)
                ->setChannelGroups($this->channelGroups);
        }
    }
}