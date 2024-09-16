<?php

namespace PubNub\Endpoints;

use PubNub\Enums\PNOperationType;
use PubNub\Enums\PNHttpMethod;
use PubNub\Models\Consumer\PNTimeResult;

class Time extends Endpoint
{
    private const TIME_PATH = "/time/0";

    protected function validateParams()
    {
        // nothing to validate
    }

    /**
     * @return array
     */
    protected function customParams()
    {
        return $this->defaultParams();
    }

    /**
     * @return null
     */
    protected function buildData()
    {
        return null;
    }

    protected function buildPath()
    {
        return static::TIME_PATH;
    }

    /**
     * @return PNTimeResult
     */
    public function sync(): PNTimeResult
    {
        return parent::sync();
    }

    /**
     * @param array $result
     * @return PNTimeResult
     */
    protected function createResponse($result)
    {
        $timetoken = floatval($result[0]);

        $response = new PNTimeResult($timetoken);

        return $response;
    }

    /**
     * @return bool
     */
    protected function isAuthRequired()
    {
        return false;
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
     * @return string
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
        return PNOperationType::PNTimeOperation;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return "Time";
    }
}