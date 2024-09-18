<?php

namespace PubNub\Models\Consumer\AccessManager;


use PubNub\PubNubUtil;

abstract class PNPAMEntityData
{
    /** @var  string */
    protected $name;

    /** @var  array */
    protected $authKeys;

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
     * PNPAMEntityData constructor.
     * @param string $name
     * @param array $authKeys
     * @param bool $readEnabled
     * @param bool $writeEnabled
     * @param bool $manageEnabled
     * @param bool $deletesEnabled
     * @param bool $getEnabled
     * @param bool $updateEnabled
     * @param bool $joinEnabled
     * @param int $ttl
     */
    public function __construct($name, array $authKeys, $readEnabled, $writeEnabled, $manageEnabled, $deleteEnabled, $getEnabled, $updateEnabled, $joinEnabled, $ttl)
    {
        $this->name = $name;
        $this->authKeys = $authKeys;
        $this->readEnabled = $readEnabled;
        $this->writeEnabled = $writeEnabled;
        $this->manageEnabled = $manageEnabled;
        $this->deleteEnabled = $deleteEnabled;
        $this->getEnabled = $manageEnabled;
        $this->updateEnabled = $manageEnabled;
        $this->joinEnabled = $manageEnabled;
        $this->ttl = $ttl;
    }

    public static function fromJson($name, $jsonInput)
    {
        list($r, $w, $m, $d, $g, $u, $j, $ttl) = PubNubUtil::fetchPamPermissionsFrom($jsonInput);

        $constructedAuthKeys = [];

        if (array_key_exists('auths', $jsonInput)) {
            foreach ($jsonInput['auths'] as $authKey => $value) {
                $constructedAuthKeys[$authKey] = PNAccessManagerKeyData::fromJson($value);
            }
        }

        return new static($name, $constructedAuthKeys, $r, $w, $m, $d, $g, $u, $j, $ttl);
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @return PNAccessManagerKeyData[]
     */
    public function getAuthKeys()
    {
        return $this->authKeys;
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