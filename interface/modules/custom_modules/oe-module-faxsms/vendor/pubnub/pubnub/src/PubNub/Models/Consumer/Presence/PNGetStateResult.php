<?php

namespace PubNub\Models\Consumer\Presence;


class PNGetStateResult
{
    protected $channels;

    public function __construct($channels)
    {
        $this->channels = $channels;
    }

    /**
     * @return string[]
     */
    public function getChannels()
    {
        return $this->channels;
    }

    public function __toString()
    {
        return sprintf("Current state is %s", $this->channels);
    }
}