<?php

namespace PubNub\Models\Consumer\FileSharing;

class PNSendFileResult
{
    protected ?string $name;
    protected ?string $fileId;

    public function __construct($json)
    {
        $this->name = $json["data"]["name"];
        $this->fileId = $json["data"]["file_id"];
    }

    public function __toString()
    {
        return sprintf("Send file success with id: %s", $this->fileId);
    }

    public function getName()
    {
        return $this->name;
    }

    public function getFileId()
    {
        return $this->fileId;
    }
}
