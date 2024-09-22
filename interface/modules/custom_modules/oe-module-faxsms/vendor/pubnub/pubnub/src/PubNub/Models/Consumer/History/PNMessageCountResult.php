<?php

namespace PubNub\Models\Consumer\History;

use PubNub\PubNubCryptoCore;


class PNMessageCountResult
{
    /** @var  array */
    private $channels;

    /**
     * PNMessageCountResult constructor.
     * @param array $channels
     */
    public function __construct($channels)
    {
        $this->channels = $channels;
    }

    public function __toString()
    {
        return sprintf("Message count for channels: %s", $this->getChannels());
    }

    /**
     * @return array
     */
    public function getChannels()
    {
        return $this->channels;
    }
}