<?php

namespace PubNub\Endpoints\Objects\UUID;

use PubNub\Endpoints\Endpoint;
use PubNub\Enums\PNHttpMethod;
use PubNub\Enums\PNOperationType;
use PubNub\Exceptions\PubNubValidationException;


class RemoveUUIDMetadata extends Endpoint
{
    const PATH = "/v2/objects/%s/uuids/%s";

    /** @var string */
    protected $uuid;

    /**
     * @param string $uuid
     * @return $this
     */
    public function uuid($uuid)
    {
        $this->uuid = $uuid;

        return $this;
    }

    /**
     * @throws PubNubValidationException
     */
    protected function validateParams()
    {
        $this->validateSubscribeKey();

        if (!is_string($this->uuid)) {
            throw new PubNubValidationException("uuid missing");
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
            $this->uuid
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
        return PNOperationType::PNRemoveUUIDMetadataOperation;
    }

    /**
     * @return string
     */
    protected function getName()
    {
        return "RemoveUUIDMetadata";
    }
}
