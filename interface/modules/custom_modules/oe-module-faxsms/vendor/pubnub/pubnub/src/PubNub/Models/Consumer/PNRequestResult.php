<?php

namespace PubNub\Models\Consumer;

class PNRequestResult
{
    protected $status;
    protected $data = null;
    protected $error = null;
    protected $service;

    public function __construct($status, $service, $data = null, $error = null)
    {
        $this->status = $status;
        $this->service = $service;
        $this->data = $data;
        $this->error = $error;
    }

    public function getStatus()
    {
        return $this->status;
    }

    public function getService()
    {
        return $this->service;
    }

    public function isError()
    {
        return (null !== $this->error);
    }

    public function getError()
    {
        return $this->error;
    }

    public function getMessage()
    {
        if (null !== $this->data) {
            return $this->data['message'];
        }
    }
}
