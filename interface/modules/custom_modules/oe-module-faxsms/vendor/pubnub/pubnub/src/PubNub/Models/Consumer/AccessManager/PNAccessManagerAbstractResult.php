<?php

namespace  PubNub\Models\Consumer\AccessManager;


use PubNub\PubNubUtil;

class PNAccessManagerAbstractResult
{
    /** @var  string */
    protected $level;

    /** @var  int */
    protected $ttl;

    /** @var  string */
    protected $subscribeKey;

    /** @var  array */
    protected $channels;

    /** @var  array */
    protected $channelGroups;

    /** @var  array */
    protected $users;

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

    /**
     * PNAccessManagerAbstractResult constructor.
     * @param string $level
     * @param string $subscribeKey
     * @param array $channels
     * @param array $channelGroups
     * @param array $users
     * @param int $ttl
     * @param bool $r
     * @param bool $w
     * @param bool $m
     * @param bool $d
     * @param bool $g
     * @param bool $u
     * @param bool $j
     */
    public function __construct($level, $subscribeKey, array $channels, array $channelGroups, array $users, $ttl, $r, $w, $m, $d, $g, $u, $j)
    {
        $this->level = $level;
        $this->subscribeKey = $subscribeKey;
        $this->channels = $channels;
        $this->channelGroups = $channelGroups;
        $this->users = $users;
        $this->ttl = $ttl;
        $this->readEnabled = $r;
        $this->writeEnabled = $w;
        $this->manageEnabled = $m;
        $this->deleteEnabled = $d;
        $this->getEnabled = $g;
        $this->updateEnabled = $u;
        $this->joinEnabled = $j;
    }

    /**
     * @param array $jsonInput
     * @return mixed
     */
    public static function fromJson($jsonInput)
    {
        $constructedChannels = [];
        $constructedGroups = [];
        $constructedUsers = [];
        list($r, $w, $m, $d, $g, $u, $j, $ttl) = PubNubUtil::fetchPamPermissionsFrom($jsonInput);

        if (array_key_exists('channel', $jsonInput)) {
            $channelName = $jsonInput['channel'];
            $constructedAuthKeys = [];

            foreach ($jsonInput['auths'] as $authKeyName => $value) {
                $constructedAuthKeys[$authKeyName] = PNAccessManagerKeyData::fromJson($value);
            }

            $constructedChannels[$channelName] = new PNAccessManagerChannelData(
                $channelName, $constructedAuthKeys, null, null, null, null, null, null, null, $ttl);
        }

        if (array_key_exists('channel-group', $jsonInput)) {
            if (is_string($jsonInput['channel-group'])) {
                $groupName = $jsonInput['channel-group'];
                $constructedAuthKeys = [];

                foreach ($jsonInput['auths'] as $authKeyName => $value) {
                    $constructedAuthKeys[$authKeyName] = PNAccessManagerKeyData::fromJson($value);
                }

                $constructedGroups[$groupName] = new PNAccessManagerChannelGroupData(
                    $groupName,
                    $constructedAuthKeys,
                    null,
                    null,
                    null,
                    null,
                    null,
                    null,
                    null,
                    $ttl
                );
            }

            if (is_array($jsonInput['channel-group'])) {
                foreach ($jsonInput['channel-group'] as $groupName => $value) {
                    $constructedGroups[$groupName] = PNAccessManagerChannelGroupData::fromJson($groupName, $value);
                }
            }
        }

        if (array_key_exists('channel-groups', $jsonInput)) {
            if (is_string($jsonInput['channel-groups'])) {
                $groupName = $jsonInput['channel-groups'];
                $constructedAuthKeys = [];

                foreach ($jsonInput['auths'] as $authKeyName => $value) {
                    $constructedAuthKeys[$authKeyName] = PNAccessManagerKeyData::fromJson($value);
                }

                $constructedGroups[$groupName] = new PNAccessManagerChannelGroupData(
                    $groupName,
                    $constructedAuthKeys,
                    null,
                    null,
                    null,
                    null,
                    null,
                    null,
                    null,
                    $ttl
                );
            }

            if (PubNubUtil::isAssoc($jsonInput['channel-groups'])) {
                foreach ($jsonInput['channel-groups'] as $groupName => $value) {
                    $constructedGroups[$groupName] = PNAccessManagerChannelGroupData::fromJson($groupName, $value);
                }
            }
        }

        if (array_key_exists('channels', $jsonInput)) {
            foreach ($jsonInput['channels'] as $channelName => $value) {
                $constructedChannels[$channelName] = PNAccessManagerChannelData::fromJson($channelName, $value);
            }
        }

        if (array_key_exists('uuids', $jsonInput)) {
            foreach ($jsonInput['uuids'] as $userName => $value) {
                $constructedUsers[$userName] = PNAccessManagerUserData::fromJson($userName, $value);
            }
        }

        return new PNAccessManagerAbstractResult(
            $jsonInput['level'],
            $jsonInput['subscribe_key'],
            $constructedChannels,
            $constructedGroups,
            $constructedUsers,
            $ttl,
            $r,
            $w,
            $m,
            $d,
            $g,
            $u,
            $j
        );
    }

    /**
     * @return string
     */
    public function getLevel()
    {
        return $this->level;
    }

    /**
     * @return int
     */
    public function getTtl()
    {
        return $this->ttl;
    }

    /**
     * @return string
     */
    public function getSubscribeKey()
    {
        return $this->subscribeKey;
    }

    /**
     * @return PNAccessManagerChannelData[]
     */
    public function getChannels()
    {
        return $this->channels;
    }

    /**
     * @return PNAccessManagerChannelGroupData[]
     */
    public function getChannelGroups()
    {
        return $this->channelGroups;
    }

    /**
     * @return PNAccessManagerChannelGroupData[]
     */
    public function getUsers()
    {
        return $this->users;
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
}
