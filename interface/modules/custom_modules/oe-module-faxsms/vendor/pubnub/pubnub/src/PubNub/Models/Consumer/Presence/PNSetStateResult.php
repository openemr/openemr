<?php

namespace PubNub\Models\Consumer\Presence;


class PNSetStateResult
{
    /** @var  array */
    protected $state;

    /**
     * PNSetStateResult constructor.
     * @param $state
     */
    public function __construct($state)
    {
        $this->state = $state;
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
        return sprintf("New state %s successfully set", $this->state);
    }
}