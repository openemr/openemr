<?php

namespace PubNub\Models\Server;


class SubscribeMetadata
{
    /** @var  string */
    protected $timetoken;

    /** @var  string */
    protected $region;

    /**
     * SubscribeMetadata constructor.
     * @param string|null $timetoken
     * @param string|null $region
     */
    public function __construct($timetoken = null, $region = null)
    {
        $this->timetoken = $timetoken;
        $this->region = $region;
    }

    /**
     * @param array $jsonInput
     * @return SubscribeMetadata
     */
    public static function fromJson($jsonInput)
    {
        return new static($jsonInput['t'], $jsonInput['r']);
    }

    public function getTimetoken()
    {
        return $this->timetoken;
    }

    /**
     * @return string
     */
    public function getRegion()
    {
        return $this->region;
    }
}