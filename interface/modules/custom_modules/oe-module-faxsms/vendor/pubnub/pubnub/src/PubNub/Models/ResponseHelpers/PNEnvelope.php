<?php

namespace PubNub\Models\ResponseHelpers;


class PNEnvelope
{
    private $status;
    private $result;

    /**
     * Envelope constructor.
     *
     * @param PNStatus $status
     * @param $result
     */
    public function __construct($result, $status)
    {
        $this->status = $status;
        $this->result = $result;
    }

    /**
     * @return PNStatus
     */
    public function getStatus()
    {
        return $this->status;
    }

    /**
     * @return mixed
     */
    public function getResult()
    {
        return $this->result;
    }

    /**
     * @return bool
     */
    public function isError()
    {
        return $this->status->isError();
    }
}
