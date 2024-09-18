<?php

namespace PubNub\Models\Consumer\Push;


class PNPushListProvisionsResult
{
    protected $channels;

    function __construct(array $channels)
    {
        $this->channels = $channels;
    }

    public function getChannels()
    {
        return $this->channels;
    }

    public function __toString()
    {
        return sprintf("List contains following channels: %s", join(",", $this->channels));
    }

    /**
     * @param array $json
     * @return PNPushListProvisionsResult
     */
    public static function fromJson($json)
    {
        return new PNPushListProvisionsResult($json);
    }
}
