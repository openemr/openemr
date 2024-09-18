<?php

namespace PubNub\Endpoints\Access;

use PubNub\Endpoints\Endpoint;
use PubNub\Enums\PNHttpMethod;
use PubNub\Enums\PNOperationType;
use PubNub\Exceptions\PubNubValidationException;
use PubNub\Models\Consumer\AccessManager\PNAccessManagerAuditResult;
use PubNub\PubNubUtil;


class Audit extends Endpoint
{
    const PATH = "/v1/auth/audit/sub-key/%s";

    /** @var string[] */
    protected $authKeys = [];

    /** @var string[] */
    protected $channels = [];

    /** @var string[]  */
    protected $groups = [];

    /** @var  bool */
    protected $read;

    /** @var  bool */
    protected $write;

    /** @var  bool */
    protected $manage;

    /** @var  int */
    protected $ttl;

    /**
     * @param string|string[] $authKeys
     * @return $this
     */
    public function authKeys($authKeys)
    {
        $this->authKeys = PubNubUtil::extendArray($this->authKeys, $authKeys);

        return $this;
    }

    /**
     * @param string|string[] $channels
     * @return $this
     */
    public function channels($channels)
    {
        $this->channels = PubNubUtil::extendArray($this->channels, $channels);

        return $this;
    }

    /**
     * @param string|string[] $channelsGroups
     * @return $this
     */
    public function channelGroups($channelsGroups)
    {
        $this->groups = PubNubUtil::extendArray($this->groups, $channelsGroups);

        return $this;
    }

    /**
     * @throws PubNubValidationException
     */
    protected function validateParams()
    {
        $this->validateSubscribeKey();
        $this->validateSecretKey();
    }

    /**
     * @return string PNHttpMethod
     */
    protected function httpMethod()
    {
        return PNHttpMethod::GET;
    }

    /**
     * @return PNAccessManagerAuditResult
     */
    public function sync()
    {
        return parent::sync();
    }

    /**
     * @param array $result Decoded json
     * @return PNAccessManagerAuditResult
     */
    protected function createResponse($result)
    {
        return PNAccessManagerAuditResult::fromJson($result['payload']);
    }

    /**
     * @return int
     */
    protected function getOperationType()
    {
        return PNOperationType::PNAccessManagerAudit;
    }

    /**
     * @return bool
     */
    protected function isAuthRequired()
    {
        return false;
    }

    /**
     * @return null|string
     */
    protected function buildData()
    {
        return null;
    }

    /**
     * @return string
     */
    protected function buildPath()
    {
        return sprintf(static::PATH, $this->pubnub->getConfiguration()->getSubscribeKey());
    }

    /**
     * @return array
     */
    protected function customParams()
    {
        $params = [];

        if (count($this->authKeys) > 0) {
            $params['auth'] = PubNubUtil::joinItems($this->authKeys);
        }

        if (count($this->channels) > 0) {
            $params['channel'] = PubNubUtil::joinItems($this->channels);
        }

        if (count($this->groups) > 0) {
            $params['channel-group'] = PubNubUtil::joinItems($this->groups);
        }

        return $params;
    }

    protected function getAffectedChannels()
    {
        return $this->channels;
    }

    protected function getAffectedChannelGroups()
    {
        return $this->groups;
    }

    protected function getRequestTimeout()
    {
        return $this->pubnub->getConfiguration()->getNonSubscribeRequestTimeout();
    }

    public function getConnectTimeout()
    {
        return $this->pubnub->getConfiguration()->getConnectTimeout();
    }

    public function getName()
    {
        return "Audit";
    }
}
