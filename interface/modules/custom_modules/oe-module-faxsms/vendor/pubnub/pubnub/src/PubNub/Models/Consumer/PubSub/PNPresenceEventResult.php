<?php

namespace PubNub\Models\Consumer\PubSub;

class PNPresenceEventResult
{
    private $event;
    private $uuid;
    private $timestamp;
    private $occupancy;
    private $state;
    private $channel;
    private $subscription;
    private $timetoken;
    private $userMetadata;

    function __construct(
        $event,
        $uuid,
        $timestamp,
        $occupancy,
        $subscription,
        $channel,
        $timetoken,
        $state,
        $userMetadata = null)
    {
        $this->event = $event;
        $this->uuid = $uuid;
        $this->timestamp = $timestamp;
        $this->occupancy = $occupancy;
        $this->subscription = $subscription;
        $this->channel = $channel;
        $this->timetoken = $timetoken;
        $this->state = $state;
        $this->userMetadata = $userMetadata;
    }

    public function getEvent()
    {
        return $this->event;
    }

    public function getUuid()
    {
        return $this->uuid;
    }

    public function getTimestamp()
    {
        return $this->timestamp;
    }

    public function getOccupancy()
    {
        return $this->occupancy;
    }

    public function getSubscription()
    {
        return $this->subscription;
    }

    public function getChannel()
    {
        return $this->channel;
    }

    public function getTimetoken()
    {
        return $this->timetoken;
    }

    public function getState()
    {
        return $this->state;
    }

    public function getUserMetadata()
    {
        return $this->userMetadata;
    }
}