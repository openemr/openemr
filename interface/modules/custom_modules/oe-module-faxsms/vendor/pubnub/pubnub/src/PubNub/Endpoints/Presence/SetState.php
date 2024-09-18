<?php

namespace PubNub\Endpoints\Presence;

use PubNub\Endpoints\Endpoint;
use PubNub\Enums\PNHttpMethod;
use PubNub\Enums\PNOperationType;
use PubNub\Exceptions\PubNubValidationException;
use PubNub\Models\Consumer\Presence\PNSetStateResult;
use PubNub\PubNubUtil;


class SetState extends Endpoint
{
    const PATH = "/v2/presence/sub-key/%s/channel/%s/uuid/%s/data";

    /** @var array */
    protected $state = [];

    /** @var string[] */
    protected $channels = [];

    /** @var string[] */
    protected $groups = [];

    /**
     * @param array $state
     * @return $this
     */
    public function state($state)
    {
        $this->state = $state;

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
     * @param string|[]string $groups
     * @return $this
     */
    public function channelGroups($groups)
    {
        $this->groups = PubNubUtil::extendArray($this->groups, $groups);

        return $this;
    }

    /**
     * @throws PubNubValidationException
     */
    protected function validateParams()
    {
        $this->validateSubscribeKey();
        $this->validateChannelGroups($this->channels, $this->groups);

        if (count($this->channels) === 0 && count($this->groups) === 0) {
            throw new PubNubValidationException("State setter for channel groups is not supported yet");
        }

        if ($this->state === null || !PubNubUtil::isAssoc($this->state)) {
            throw new PubNubValidationException("State missing or not a dict");
        }
    }

    /**
     * @return null
     */
    protected function buildData()
    {
        return null;
    }

    /**
     * @return array
     */
    protected function customParams()
    {
        $params = [];

        $params['state'] = PubNubUtil::writeValueAsString($this->state);

        if (count($this->groups) > 0) {
            $params['channel-group'] = PubNubUtil::joinItems($this->groups);
        }

        return $params;
    }

    /**
     * @return string
     */
    public function buildPath()
    {
        return sprintf(
            static::PATH,
            $this->pubnub->getConfiguration()->getSubscribeKey(),
            PubNubUtil::joinChannels($this->channels),
            $this->pubnub->getConfiguration()->getUuid()
        );
    }

    /**
     * @return array
     */
    public function buildParams()
    {
        return parent::buildParams();
    }

    /**
     * @return PNSetStateResult
     */
    public function sync()
    {
        return parent::sync();
    }

    /**
     * @param array $result Decoded json
     * @return PNSetStateResult|array
     */
    public function createResponse($result)
    {
        if (array_key_exists('status', $result) && $result['status'] === 200) {
            return new PNSetStateResult($result['payload']);
        } else {
            return $result;
        }
    }


    /**
     * @return bool
     */
    protected function isAuthRequired()
    {
        return true;
    }

    /**
     * @return string[]|string
     */
    public function getAffectedChannels()
    {
        return $this->channels;
    }

    /**
     * @return string[]|string
     */
    public function getAffectedChannelGroups()
    {
        return $this->groups;
    }

    /**
     * @return int
     */
    public function getRequestTimeout()
    {
        return $this->pubnub->getConfiguration()->getNonSubscribeRequestTimeout();
    }

    /**
     * @return int
     */
    public function getConnectTimeout()
    {
        return $this->pubnub->getConfiguration()->getConnectTimeout();
    }

    /**
     * @return string PNHttpMethod
     */
    protected function httpMethod()
    {
        return PNHttpMethod::GET;
    }

    /**
     * @return int
     */
    protected function getOperationType()
    {
        return PNOperationType::PNSetStateOperation;
    }

    /**
     * @return string
     */
    protected function getName()
    {
        return "SetState";
    }
}