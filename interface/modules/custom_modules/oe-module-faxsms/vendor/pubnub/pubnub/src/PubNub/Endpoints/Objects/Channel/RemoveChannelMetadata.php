<?php

namespace PubNub\Endpoints\Objects\Channel;

use PubNub\Endpoints\Endpoint;
use PubNub\Enums\PNHttpMethod;
use PubNub\Enums\PNOperationType;
use PubNub\Exceptions\PubNubValidationException;


class RemoveChannelMetadata extends Endpoint
{
    const PATH = "/v2/objects/%s/channels/%s";

    /** @var string */
    protected $channel;

    /**
     * @param string $ch
     * @return $this
     */
    public function channel($ch)
    {
        $this->channel = $ch;

        return $this;
    }

    /**
     * @throws PubNubValidationException
     */
    protected function validateParams()
    {
        $this->validateSubscribeKey();

        if (!is_string($this->channel)) {
            throw new PubNubValidationException("channel missing");
        }
    }

    /**
     * @return string
     * @throws PubNubBuildRequestException
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
            $this->channel
        );
    }

    /**
     * @param array $result Decoded json
     * @return bool
     */
    protected function createResponse($result)
    {
        return array_key_exists("data", $result);
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
        return PNHttpMethod::DELETE;
    }

    /**
     * @return int
     */
    protected function getOperationType()
    {
        return PNOperationType::PNRemoveChannelMetadataOperation;
    }

    /**
     * @return string
     */
    protected function getName()
    {
        return "RemoveChannelMetadata";
    }
}
