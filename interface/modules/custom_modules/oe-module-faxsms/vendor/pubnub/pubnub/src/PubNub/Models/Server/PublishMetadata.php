<?php

namespace PubNub\Models\Server;


class PublishMetadata
{
    /** @var  string */
    protected $publish_timetoken;

    /** @var  string */
    protected $region;

    /**
     * PublishMetadata constructor.
     * @param string|null $timetoken
     * @param string|null $region
     */
    public function __construct($timetoken = null, $region = null)
    {
        $this->publish_timetoken = $timetoken;
        $this->region = $region;
    }

    public static function fromJson($jsonInput)
    {
        return new static($jsonInput['t'], $jsonInput['r']);
    }

    public function getPublishTimetoken()
    {
        return $this->publish_timetoken;
    }
}