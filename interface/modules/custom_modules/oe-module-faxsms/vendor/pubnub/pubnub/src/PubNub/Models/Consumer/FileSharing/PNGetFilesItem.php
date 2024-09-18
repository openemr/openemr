<?php

namespace PubNub\Models\Consumer\FileSharing;

class PNGetFilesItem
{
    protected $id;
    protected $name;
    protected $size;
    protected $creationTime;

    public function __construct($file)
    {
        $this->id = $file['id'];
        $this->name = $file['name'];
        $this->size = $file['size'];
        $this->creationTime = $file['created'];
    }

    public function __toString()
    {
        return sprintf(
            "File: %s with id: %s, size: %s, created at: %s",
            $this->name,
            $this->id,
            $this->size,
            $this->creationTime
        );
    }

    public function getId()
    {
        return $this->id;
    }

    public function getName()
    {
        return $this->name;
    }

    public function getSize()
    {
        return $this->size;
    }

    public function getCreationTime()
    {
        return $this->creationTime;
    }
}
