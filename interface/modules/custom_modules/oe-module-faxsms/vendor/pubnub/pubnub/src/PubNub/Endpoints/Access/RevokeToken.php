<?php

namespace PubNub\Endpoints\Access;

use PubNub\Endpoints\Endpoint;
use PubNub\Exceptions\PubNubValidationException;
use PubNub\Enums\PNHttpMethod;
use PubNub\Enums\PNOperationType;
use PubNub\Models\Consumer\PNRequestResult;
use PubNub\PubNubUtil;

class RevokeToken extends Endpoint
{
    const PATH = "/v3/pam/%s/grant/%s";

    protected $token;

    /** @var bool */
    protected $sortParams = true;

    public function token($value)
    {
        $this->token = $value;
        return $this;
    }

    /**
     * @throws PubNubValidationException
     */
    public function validateParams()
    {
        $this->validateSubscribeKey();
        $this->validateSecretKey();

        if ($this->token === null || empty($this->token)) {
            throw new PubNubValidationException("Token is not set");
        }
    }

    /**
     * @return array
     */
    public function customParams()
    {
        return [];
    }

    protected function customHeaders()
    {
        return [ 'Content-Type' => 'application/json' ];
    }

    /**
     * @return null
     */
    public function buildData()
    {
        return null;
    }

    /**
     * @return string
     */
    public function buildPath()
    {
        $encodedToken = PubNubUtil::tokenEncode($this->token);
        return sprintf(
            static::PATH,
            $this->pubnub->getConfiguration()->getSubscribeKey(),
            $encodedToken
        );
    }

    /**
     * @return PNRequestResult
     */
    public function sync()
    {
        return parent::sync();
    }

    /**
     * @param string $token
     * @return PNRequestResult
     */
    public function createResponse($response)
    {
        return new PNRequestResult(
            $response['status'],
            $response['service'],
            $response['data'] ?? null,
            $response['error'] ?? null
        );
    }

    /**
     * @return bool
     */
    public function isAuthRequired()
    {
        return false;
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
     * @return string
     */
    public function httpMethod()
    {
        return PNHttpMethod::DELETE;
    }

    /**
     * @return int
     */
    public function getOperationType()
    {
        return PNOperationType::PNAccessManagerRevokeToken;
    }
}
