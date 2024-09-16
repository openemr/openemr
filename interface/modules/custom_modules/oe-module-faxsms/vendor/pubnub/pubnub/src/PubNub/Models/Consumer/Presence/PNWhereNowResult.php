<?php

namespace PubNub\Models\Consumer\Presence;

use PubNub\PubNubUtil;

class PNWhereNowResult
{
    /** @var  string[] */
    protected $channels;

    /**
     * PNWhereNowResult constructor.
     * @param string[] $channels
     */
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
        return sprintf("User is currently subscribed to %s", PubNubUtil::joinItems($this->channels));
    }

    /**
     * @param array $payload
     * @return PNWhereNowResult
     */
    public static function fromPayload(array $payload)
    {
        return new PNWhereNowResult($payload['channels']);
    }
}
