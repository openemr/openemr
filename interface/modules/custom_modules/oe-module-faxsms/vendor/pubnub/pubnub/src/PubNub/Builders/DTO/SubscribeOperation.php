<?php

namespace PubNub\Builders\DTO;


class SubscribeOperation
{
    /** @var  array */
    private $channels;

    /** @var  array */
    private $channelGroups;

    /** @var  bool */
    private $presenceEnabled;

    /** @var  int */
    private $timetoken;

    /**
     * SubscribeOperation constructor.
     * @param array $channels
     * @param array $channelGroups
     * @param bool $presenceEnabled
     * @param int $timetoken
     */
    public function __construct(array $channels, array $channelGroups, $presenceEnabled, $timetoken)
    {
        $this->channels = $channels;
        $this->channelGroups = $channelGroups;
        $this->presenceEnabled = $presenceEnabled;
        $this->timetoken = $timetoken;
    }

    /**
     * @return array
     */
    public function getChannels()
    {
        return $this->channels;
    }

    /**
     * @return array
     */
    public function getChannelGroups()
    {
        return $this->channelGroups;
    }

    /**
     * @return bool
     */
    public function isPresenceEnabled()
    {
        return $this->presenceEnabled;
    }

    /**
     * @return int
     */
    public function getTimetoken()
    {
        return $this->timetoken;
    }
}
