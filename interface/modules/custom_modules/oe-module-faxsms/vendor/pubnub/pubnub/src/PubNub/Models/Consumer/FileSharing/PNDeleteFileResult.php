<?php

namespace PubNub\Models\Consumer\FileSharing;

class PNDeleteFileResult
{
    protected $status;

    public function __construct($json)
    {
        $this->status = $json['status'];
    }

    public function __toString()
    {
        return "File delete success with status: " . $this->status;
    }

    public function getStatus()
    {
        return $this->status;
    }
}
