<?php

namespace PubNub\Builders\DTO;


class UnsubscribeOperation
{
    /** @var  string[] */
    protected $channels = [];

    /** @var  string[] */
    protected $channelGroups = [];

    /**
     * @return \string[]
     */
    public function getChannels()
    {
        return $this->channels;
    }

    /**
     * @param \string[] $channels
     * @return $this
     */
    public function setChannels(array $channels)
    {
        $this->channels = $channels;

        return $this;
    }

    /**
     * @return \string[]
     */
    public function getChannelGroups()
    {
        return $this->channelGroups;
    }

    /**
     * @param \string[] $channelGroups
     * @return $this
     */
    public function setChannelGroups(array $channelGroups)
    {
        $this->channelGroups = $channelGroups;

        return $this;
    }


}