<?php

namespace PubNub\Endpoints\Presence;

use PubNub\Endpoints\Endpoint;
use PubNub\Enums\PNHttpMethod;
use PubNub\Enums\PNOperationType;
use PubNub\Exceptions\PubNubValidationException;
use PubNub\PubNubUtil;


class Leave extends Endpoint
{
    const PATH = "/v2/presence/sub-key/%s/channel/%s/leave";

    /** @var string[] */
    protected $channels = [];

    /** @var string[] */
    protected $groups = [];

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
     * @param string|string[] $groups
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

        if (count($this->channels) === 0 && count($this->groups) === 0) {
            throw new PubNubValidationException("Channel or group missing");
        }
    }

    /**
     * @return array $params
     */
    protected function customParams()
    {
        $params = [];

        if (count($this->groups) > 0) {
            $params['channel-group'] = PubNubUtil::joinItems($this->groups);
        }

        return $params;
    }

    /**
     * @return null
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
        return sprintf(
            Leave::PATH,
            $this->pubnub->getConfiguration()->getSubscribeKey(),
            PubNubUtil::joinChannels($this->channels));
    }

    /**
     * @param array $result Decoded json
     * @return array
     */
    protected function createResponse($result)
    {
        return $result;
    }

    /**
     * @return bool
     */
    protected function isAuthRequired()
    {
        return true;
    }

    /**
     * @return int
     */
    protected function getRequestTimeout()
    {
        return $this->pubnub->getConfiguration()->getNonSubscribeRequestTimeout();
    }

    /**
     * @return int
     */
    protected function getConnectTimeout()
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
        return PNOperationType::PNUnsubscribeOperation;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return "Leave";
    }

}
