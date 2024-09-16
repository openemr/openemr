<?php

namespace PubNub\Models\Consumer\AccessManager;


use PubNub\PubNubUtil;

class PNAccessManagerKeyData
{
    /** @var  bool */
    protected $readEnabled;

    /** @var  bool */
    protected $writeEnabled;

    /** @var  bool */
    protected $manageEnabled;

    /** @var  bool */
    protected $deleteEnabled;

    /** @var  bool */
    protected $getEnabled;

    /** @var  bool */
    protected $updateEnabled;

    /** @var  bool */
    protected $joinEnabled;

    /** @var  int */
    protected $ttl;

    /**
     * PNAccessManagerKeyData constructor.
     * @param bool $readEnabled
     * @param bool $writeEnabled
     * @param bool $manageEnabled
     * @param bool $deleteEnabled
     * @param bool $getEnabled
     * @param bool $updateEnabled
     * @param bool $joinEnabled
     * @param int $ttl
     */
    final public function __construct($readEnabled, $writeEnabled, $manageEnabled, $deleteEnabled, $getEnabled, $updateEnabled, $joinEnabled, $ttl)
    {
        $this->readEnabled = $readEnabled;
        $this->writeEnabled = $writeEnabled;
        $this->manageEnabled = $manageEnabled;
        $this->deleteEnabled = $deleteEnabled;
        $this->getEnabled = $getEnabled;
        $this->updateEnabled = $updateEnabled;
        $this->joinEnabled = $joinEnabled;
        $this->ttl = $ttl;
    }

    public static function fromJson($jsonInput)
    {
        list($r, $w, $m, $d, $g, $u, $j, $ttl) = PubNubUtil::fetchPamPermissionsFrom($jsonInput);

        return new static($r, $w, $m, $d, $g, $u, $j, $ttl);
    }

    /**
     * @return bool
     */
    public function isReadEnabled()
    {
        return $this->readEnabled;
    }

    /**
     * @return bool
     */
    public function isWriteEnabled()
    {
        return $this->writeEnabled;
    }

    /**
     * @return bool
     */
    public function isManageEnabled()
    {
        return $this->manageEnabled;
    }

    /**
     * @return bool
     */
    public function isDeleteEnabled()
    {
        return $this->deleteEnabled;
    }

    /**
     * @return bool
     */
    public function isGetEnabled()
    {
        return $this->getEnabled;
    }

    /**
     * @return bool
     */
    public function isUpdateEnabled()
    {
        return $this->updateEnabled;
    }

    /**
     * @return bool
     */
    public function isJoinEnabled()
    {
        return $this->joinEnabled;
    }

    /**
     * @return int
     */
    public function getTtl()
    {
        return $this->ttl;
    }
}
