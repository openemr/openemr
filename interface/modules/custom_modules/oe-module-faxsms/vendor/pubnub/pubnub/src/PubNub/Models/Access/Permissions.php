<?php

namespace PubNub\Models\Access;

class Permissions
{
    private $name;
    private $read = false;
    private $write = false;
    private $manage = false;
    private $delete = false;
    private $create = false;
    private $get = false;
    private $update = false;
    private $join = false;

    public function __construct($name, $rights = 0)
    {
        $this->name = $name;
        if ($rights) {
            $this->read = (bool)($rights & 1);
            $this->write = (bool)($rights >> 1 & 1);
            $this->manage = (bool)($rights >> 2 & 1);
            $this->delete = (bool)($rights >> 3 & 1);
            $this->create = (bool)($rights >> 4 & 1);
            $this->get = (bool)($rights >> 5 & 1);
            $this->update = (bool)($rights >> 6 & 1);
            $this->join = (bool)($rights >> 7 & 1);
        }
    }

    public function setRead($read = true)
    {

        $this->read = $read;
        return $this;
    }

    public function setWrite($write = true)
    {
        $this->write = $write;
        return $this;
    }

    public function setManage($manage = true)
    {
        $this->manage = $manage;
        return $this;
    }

    public function setDelete($delete = true)
    {
        $this->delete = $delete;
        return $this;
    }

    public function setCreate($create = true)
    {
        $this->create = $create;
        return $this;
    }

    public function setGet($get = true)
    {
        $this->get = $get;
        return $this;
    }

    public function setUpdate($update = true)
    {
        $this->update = $update;
        return $this;
    }

    public function setJoin($join = true)
    {
        $this->join = $join;
        return $this;
    }

    /**
     * Returns acces rights in integer form. The values for specific rights are:
     *   READ = 1;
     *   WRITE = 2;
     *   MANAGE = 4;
     *   DELETE = 8;
     *   CREATE = 16;
     *   GET = 32;
     *   UPDATE = 64;
     *   JOIN = 128;
     *
     * return int
     */
    public function getRights()
    {
        $result = (int)$this->read
            | (int)$this->write << 1
            | (int)$this->manage << 2
            | (int)$this->delete << 3
            | (int)$this->create << 4
            | (int)$this->get << 5
            | (int)$this->update << 6
            | (int)$this->join << 7;
        return $result;
    }

    public function getName()
    {
        return $this->name;
    }

    public function hasRead()
    {
        return $this->read;
    }

    public function hasWrite()
    {
        return $this->write;
    }

    public function hasManage()
    {
        return $this->manage;
    }

    public function hasDelete()
    {
        return $this->delete;
    }

    public function hasCreate()
    {
        return $this->create;
    }

    public function hasGet()
    {
        return $this->get;
    }

    public function hasUpdate()
    {
        return $this->update;
    }

    public function hasJoin()
    {
        return $this->join;
    }

    public function toArray()
    {
        return [
            $this->name => [
                'read' => $this->read,
                'write' => $this->write,
                'manage' => $this->manage,
                'delete' => $this->delete,
                'create' => $this->create,
                'get' => $this->get,
                'update' => $this->update,
                'join' => $this->join,
            ],
        ];
    }
}
