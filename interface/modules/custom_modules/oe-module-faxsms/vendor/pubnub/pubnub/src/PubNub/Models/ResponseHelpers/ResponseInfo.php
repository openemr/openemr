<?php

namespace PubNub\Models\ResponseHelpers;

use WpOrg\Requests\Response;

class ResponseInfo
{
    /** @var  int */
    private $statusCode;

    /** @var  bool */
    private $tlsEnabled;

    /** @var  string */
    private $origin;

    /** @var  string */
    private $uuid;

    /** @var  string */
    private $authKey;

    /** @var  \Requests_Response */
    private $originalResponse;

    /**
     * ResponseInfo constructor.
     *
     * @param int $statusCode
     * @param bool $tlsEnabled
     * @param string $origin
     * @param string $uuid
     * @param string $authKey
     * @param \Requests_Response $originalResponse
     */
    public function __construct($statusCode, $tlsEnabled, $origin, $uuid, $authKey, Response $originalResponse)
    {
        $this->statusCode = $statusCode;
        $this->tlsEnabled = $tlsEnabled;
        $this->origin = $origin;
        $this->uuid = $uuid;
        $this->authKey = $authKey;
        $this->originalResponse = $originalResponse;
    }

    /**
     * @return int
     */
    public function getStatusCode()
    {
        return $this->statusCode;
    }

    /**
     * @return bool
     */
    public function isTlsEnabled()
    {
        return $this->tlsEnabled;
    }

    /**
     * @return string
     */
    public function getOrigin()
    {
        return $this->origin;
    }

    /**
     * @return string
     */
    public function getUuid()
    {
        return $this->uuid;
    }

    /**
     * @return string
     */
    public function getAuthKey()
    {
        return $this->authKey;
    }

    /**
     * @return \Requests_Response
     */
    public function getOriginalResponse()
    {
        return $this->originalResponse;
    }
}