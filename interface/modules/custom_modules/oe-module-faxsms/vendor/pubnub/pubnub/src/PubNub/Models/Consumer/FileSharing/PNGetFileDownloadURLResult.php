<?php

namespace PubNub\Models\Consumer\FileSharing;

class PNGetFileDownloadURLResult
{
    protected string $fileUrl;

    public function __construct($result)
    {
        $this->fileUrl = $result->headers->getAll()['location'][0];
    }

    public function __toString()
    {
        return "Get file URL success with URL: %s" % $this->fileUrl;
    }

    public function getFileUrl()
    {
        return $this->fileUrl;
    }
}
