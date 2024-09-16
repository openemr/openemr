<?php

namespace PubNub\Endpoints\Objects\UUID;

use PubNub\Endpoints\Endpoint;
use PubNub\Enums\PNHttpMethod;
use PubNub\Enums\PNOperationType;
use PubNub\Exceptions\PubNubValidationException;
use PubNub\Models\Consumer\Objects\UUID\PNSetUUIDMetadataResult;
use PubNub\PubNubUtil;


class SetUUIDMetadata extends Endpoint
{
    const PATH = "/v2/objects/%s/uuids/%s";

    /** @var string */
    protected $uuid;

    /** @var array */
    protected $meta;

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
     * @param array $meta
     * @return $this
     */
    public function meta($meta)
    {
        $this->meta = $meta;

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

        if (empty($this->meta)) {
            throw new PubNubValidationException("meta missing");
        }
    }

    /**
     * @return string
     * @throws PubNubBuildRequestException
     */
    protected function buildData()
    {
        return PubNubUtil::writeValueAsString($this->meta);
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
     * @return PNSetUUIDMetadataResult
     */
    protected function createResponse($result)
    {
        return PNSetUUIDMetadataResult::fromPayload($result);
    }

    /**
     * @return array
     */
    protected function customParams()
    {
        $params = $this->defaultParams();

        $params['include'] = 'custom';

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
        return PNHttpMethod::PATCH;
    }

    /**
     * @return int
     */
    protected function getOperationType()
    {
        return PNOperationType::PNSetUUIDMetadataOperation;
    }

    /**
     * @return string
     */
    protected function getName()
    {
        return "SetUUIDMetadata";
    }
}
