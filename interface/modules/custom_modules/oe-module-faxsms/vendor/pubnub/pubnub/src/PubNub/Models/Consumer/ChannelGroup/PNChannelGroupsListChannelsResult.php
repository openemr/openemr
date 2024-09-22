<?php

namespace PubNub\Models\Consumer\ChannelGroup;

class PNChannelGroupsListChannelsResult
{
    protected $channels;

    function __construct($channels)
    {
        $this->channels = $channels;
    }

    public function getChannels()
    {
        return $this->channels;
    }

    public function __toString()
    {
        return sprintf("Group contains following channels: %s", join(",", $this->channels));
    }

    /**
     * @param array $payload
     * @return PNChannelGroupsListChannelsResult
     */
    public static function fromPayload(array $payload)
    {
        return new PNChannelGroupsListChannelsResult($payload['channels']);
    }
}