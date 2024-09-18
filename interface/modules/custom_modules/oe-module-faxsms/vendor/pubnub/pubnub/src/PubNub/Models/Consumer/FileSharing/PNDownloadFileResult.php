<?php

namespace PubNub\Models\Consumer\FileSharing;

class PNDownloadFileResult
{
    protected $fileContent;

    public function __construct($result)
    {
        $this->fileContent = $result;
    }

    public function __toString()
    {
        return "Download file success with file content: " . $this->fileContent;
    }

    public function getFileContent()
    {
        return $this->fileContent;
    }
}
