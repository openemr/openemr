<?php

namespace PubNub\Endpoints\Push;

use PubNub\Endpoints\Endpoint;
use PubNub\Enums\PNHttpMethod;
use PubNub\Enums\PNPushType;
use PubNub\Exceptions\PubNubValidationException;

abstract class PushEndpoint extends Endpoint
{
    protected const OPERATION_TYPE = null;
    protected const OPERATION_NAME = null;
    protected string $deviceId;
    protected string $pushType;
    protected string $environment = 'development';
    protected string $topic;

    /**
     * @param string $deviceId
     * @return $this
     */
    public function deviceId(string $deviceId): static
    {
        $this->deviceId = $deviceId;
        return $this;
    }

    /**
     * @param string $environment
     * @return $this
     */
    public function environment(string $environment): static
    {
        $this->environment = $environment;
        return $this;
    }

    /**
     * @param string  $topic
     * @return $this
     */
    public function topic(string $topic): static
    {
        $this->topic = $topic;
        return $this;
    }

    /**
     * @param string $pushType
     * @return $this
     */
    public function pushType(string $pushType): static
    {
        $this->pushType = $pushType;
        return $this;
    }

    protected function validatePushType()
    {
        if (!isset($this->pushType) || empty($this->pushType)) {
            throw new PubNubValidationException("Push Type is missing");
        }

        if ($this->pushType === PNPushType::GCM) {
            trigger_error("GCM is deprecated. Please use FCM instead.", E_USER_DEPRECATED);
        }

        if (!in_array($this->pushType, PNPushType::all())) {
            throw new PubNubValidationException("Invalid push type");
        }
    }

    /**
     * @throws PubNubValidationException
     */
    protected function validateDeviceId()
    {
        if (!isset($this->deviceId) || empty($this->deviceId)) {
            throw new PubNubValidationException("Device ID is missing for push operation");
        }
    }

    protected function validateTopic()
    {
        if (($this->pushType == PNPushType::APNS2) && (!isset($this->topic) || empty($this->topic))) {
            throw new PubNubValidationException("APNS2 topic is missing");
        }
    }

     /**
     * @throws PubNubValidationException
     */
    protected function validateParams()
    {
        $this->validateSubscribeKey();
        $this->validateDeviceId();
        $this->validatePushType();
        $this->validateTopic();
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
        return static::OPERATION_TYPE;
    }

    /**
     * @return string
     */
    protected function getName()
    {
        return static::OPERATION_NAME;
    }

    protected function getPushType(): string
    {
        return $this->pushType == PNPushType::FCM ? 'gcm' : $this->pushType;
    }
}
