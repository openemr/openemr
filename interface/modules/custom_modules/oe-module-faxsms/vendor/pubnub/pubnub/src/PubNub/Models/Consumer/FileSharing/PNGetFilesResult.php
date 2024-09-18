<?php

namespace PubNub\Models\Consumer\FileSharing;

class PNGetFilesResult
{
    protected array $data;
    protected int $count;
    protected array $files;
    protected $next;
    protected $prev;

    public function __construct($result)
    {
        $this->data = $result['data'];
        $this->count = (int)$result['count'];
        $this->next = $result['next'] ?? null;
        $this->prev = $result['prev'] ?? null;
        if ($this->count === 0) {
            $this->files = [];
        } else {
            foreach ($this->data as $file) {
                $this->files[] = new PNGetFilesItem($file);
            }
        }
    }

    public function __toString()
    {
        return "Get file success with data: " . $this->data;
    }

    public function getData()
    {
        return $this->data;
    }

    public function getCount()
    {
        return $this->count;
    }

    public function getNext()
    {
        return $this->next;
    }

    public function getPrev()
    {
        return $this->prev;
    }

    public function getFiles()
    {
        return $this->files;
    }
}
