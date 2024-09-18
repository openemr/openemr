<?php

namespace PubNub\Models\Consumer\PubSub;


class PNSignalResult
{
    /** @var  int */
    private $timetoken;

    /**
     * PNTimeResult constructor.
     *
     * @param int $timetoken
     */
    public function __construct($timetoken)
    {
        $this->timetoken = $timetoken;
    }

    /**
     * @return int
     */
    public function getTimetoken()
    {
        return $this->timetoken;
    }
}
