<?php

namespace PubNub\Endpoints\ChannelGroups;

use PubNub\Endpoints\Endpoint;
use PubNub\Enums\PNHttpMethod;
use PubNub\Enums\PNOperationType;
use PubNub\Exceptions\PubNubValidationException;
use PubNub\Models\Consumer\ChannelGroup\PNChannelGroupsListChannelsResult;


class ListChannelsInChannelGroup extends Endpoint
{
    const PATH = "/v1/channel-registration/sub-key/%s/channel-group/%s";

    /** @var  string */
    protected $channelGroup;

    /**
     * @param string $channelGroup
     * @return $this
     */
    public function channelGroup($channelGroup)
    {
        $this->channelGroup = $channelGroup;

        return $this;
    }

    /**
     * @throws PubNubValidationException
     */
    protected function validateParams()
    {
        $this->validateSubscribeKey();

        if ($this->channelGroup === null || empty($this->channelGroup)) {
            throw new PubNubValidationException("Channel group missing");
        }
    }

    /**
     * @return array
     */
    protected function customParams()
    {
        $params = $this->defaultParams();

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
            self::PATH,
            $this->pubnub->getConfiguration()->getSubscribeKey(),
            $this->channelGroup
        );
    }

    /**
     * @return PNChannelGroupsListChannelsResult
     */
    public function sync()
    {
        return parent::sync();
    }

    /**
     * @param array $result Decoded json
     * @return PNChannelGroupsListChannelsResult
     */
    protected function createResponse($result)
    {
        return PNChannelGroupsListChannelsResult::fromPayload(static::fetchPayload($result));
    }

    /**
     * @return bool
     */
    protected function isAuthRequired()
    {
        return True;
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
        return PNOperationType::PNChannelsForGroupOperation;
    }

    /**
     * @return string
     */
    protected function getName()
    {
        return "ListChannelsInChannelGroup";
    }
}