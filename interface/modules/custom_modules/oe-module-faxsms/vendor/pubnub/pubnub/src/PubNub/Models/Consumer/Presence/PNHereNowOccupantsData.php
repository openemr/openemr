<?php

namespace PubNub\Models\Consumer\Presence;

class PNHereNowOccupantsData
{
    /** @var  string */
    protected $uuid;

    /** @var  array */
    protected $state;

    public function __construct($uuid, $state)
    {
        $this->uuid = $uuid;
        $this->state = $state;
    }

    /**
     * @return string
     */
    public function getUuid()
    {
        return $this->uuid;
    }

    /**
     * @return array
     */
    public function getState()
    {
        return $this->state;
    }

    public function __toString()
    {
        return sprintf("HereNow Occupants Data for '%s': %s", $this->uuid, $this->state);
    }
}