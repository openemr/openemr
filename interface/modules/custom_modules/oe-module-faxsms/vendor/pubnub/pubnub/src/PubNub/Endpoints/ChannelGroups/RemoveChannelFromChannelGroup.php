<?php

namespace PubNub\Endpoints\ChannelGroups;

use PubNub\Endpoints\Endpoint;
use PubNub\Enums\PNHttpMethod;
use PubNub\Enums\PNOperationType;
use PubNub\Exceptions\PubNubValidationException;
use PubNub\Models\Consumer\ChannelGroup\PNChannelGroupsRemoveChannelResult;
use PubNub\PubNubUtil;


class RemoveChannelFromChannelGroup extends Endpoint
{
    const PATH = "/v1/channel-registration/sub-key/%s/channel-group/%s";

    /** @var  string */
    protected $channelGroup;

    /** @var string[] */
    protected $channels = [];

    /**
     * @param string|string[] $ch
     * @return $this
     */
    public function channels($ch)
    {
        $this->channels = PubNubUtil::extendArray($this->channels, $ch);

        return $this;
    }

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

        if (count($this->channels) === 0) {
            throw new PubNubValidationException("Channels missing");
        }

        if (strlen((string)$this->channelGroup) === 0) {
            throw new PubNubValidationException("Channel group missing");
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
     * @return string
     */
    protected function buildPath()
    {
        return sprintf(
            static::PATH,
            $this->pubnub->getConfiguration()->getSubscribeKey(),
            $this->channelGroup
        );
    }

    /**
     * @return array
     */
    protected function customParams()
    {
        $params = $this->defaultParams();

        $params['remove'] = PubNubUtil::joinItems($this->channels);

        return $params;
    }

    /**
     * @return PNChannelGroupsRemoveChannelResult
     */
    public function sync()
    {
        return parent::sync();
    }

    /**
     * @param array $result Decoded json
     * @return PNChannelGroupsRemoveChannelResult
     */
    protected function createResponse($result)
    {
        return new PNChannelGroupsRemoveChannelResult();
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
        return PNOperationType::PNRemoveChannelsFromGroupOperation;
    }

    /**
     * @return string
     */
    protected function getName()
    {
        return "RemoveChannelFromChannelGroup";
    }
}