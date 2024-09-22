<?php

namespace PubNub\Models\Consumer\FileSharing;

class PNPublishFileMessageResult
{
    protected $timestamp;

    public function __construct($json)
    {
        $this->timestamp = $json[2];
    }

    public function __toString()
    {
        return "Sending file notification success with timestamp: " . $this->timestamp;
    }

    public function getTimestamp()
    {
        return $this->timestamp;
    }
}
