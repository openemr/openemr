<?php

namespace PubNub\Endpoints\Presence;

use PubNub\Endpoints\Endpoint;
use PubNub\Enums\PNHttpMethod;
use PubNub\Enums\PNOperationType;
use PubNub\Exceptions\PubNubValidationException;
use PubNub\Models\Consumer\Presence\PNWhereNowResult;
use PubNub\PubNub;
use PubNub\PubNubUtil;


class WhereNow extends Endpoint
{
    const PATH = "/v2/presence/sub-key/%s/uuid/%s";

    /** @var string */
    protected $uuid;

    /**
     * WhereNow constructor.
     * @param PubNub $pubnubInstance
     */
    public function __construct(PubNub $pubnubInstance)
    {
        parent::__construct($pubnubInstance);
        $this->uuid = $this->pubnub->getConfiguration()->getUuid();
    }

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

        if ($this->uuid === null || !is_string($this->uuid)) {
            throw new PubNubValidationException("uuid missing or not a string");
        }
    }

    /**
     * @return array
     */
    protected function customParams()
    {
        return [];
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
    public function buildPath()
    {
        return sprintf(WhereNow::PATH,
            $this->pubnub->getConfiguration()->getSubscribeKey(),
            PubNubUtil::urlEncode($this->uuid)
        );
    }

    /**
     * @return PNWhereNowResult
     */
    public function sync()
    {
        return parent::sync();
    }

    /**
     * @param array $result Decoded json
     * @return PNWhereNowResult
     */
    protected function createResponse($result)
    {
        return PNWhereNowResult::fromPayload(static::fetchPayload($result));
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
        return PNOperationType::PNWhereNowOperation;
    }

    /**
     * @return string
     */
    protected function getName()
    {
        return "WhereNow";
    }
}