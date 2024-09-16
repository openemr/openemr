<?php

namespace PubNub\Models\ResponseHelpers;


use PubNub\Enums\PNOperationType;
use PubNub\Enums\PNStatusCategory;
use PubNub\Exceptions\PubNubException;

class PNStatus
{
    /** @var  PubNubException */
    private $exception;

    /** @var  int PNStatusCategory */
    private $category;

    /** @var  int */
    private $statusCode;

    /** @var  int PNOperationType */
    private $operation;

    /** @var  bool */
    private $tlsEnabled;

    /** @var  string */
    private $uuid;

    /** @var  string */
    private $authKey;

    /** @var  string */
    private $origin;

    /** @var  \Requests_Response */
    private $originalResponse;

    /** @var  null|array */
    private $affectedChannels;

    /** @var  null|array */
    private $affectedChannelGroups;

    /** @var  null|array */
    private $affectedUsers;

    /**
     * @return bool
     */
    public function isError()
    {
        return $this->exception !== null;
    }

    /**
     * @return PubNubException
     */
    public function getException()
    {
        return $this->exception;
    }

    /**
     * @param PubNubException $exception
     * @return $this
     */
    public function setException($exception)
    {
        $this->exception = $exception;
        $this->exception->setStatus($this);

        return $this;
    }

    /**
     * @return int PNStatusCategory
     */
    public function getCategory()
    {
        return $this->category;
    }

    /**
     * @param int $category PNStatusCategory
     * @return $this
     */
    public function setCategory($category)
    {
        $this->category = $category;

        return $this;
    }

    /**
     * @return int
     */
    public function getStatusCode()
    {
        return $this->statusCode;
    }

    /**
     * @param int $statusCode
     * @return $this
     */
    public function setStatusCode($statusCode)
    {
        $this->statusCode = $statusCode;

        return $this;
    }

    /**
     * @return int PNOperationType
     */
    public function getOperation()
    {
        return $this->operation;
    }

    /**
     * @param int $operation PNOperationType
     * @return $this
     */
    public function setOperation($operation)
    {
        $this->operation = $operation;

        return $this;
    }

    /**
     * @return bool
     */
    public function isTlsEnabled()
    {
        return $this->tlsEnabled;
    }

    /**
     * @param bool $tlsEnabled
     * @return $this
     */
    public function setTlsEnabled($tlsEnabled)
    {
        $this->tlsEnabled = $tlsEnabled;

        return $this;
    }

    /**
     * @return string
     */
    public function getUuid()
    {
        return $this->uuid;
    }

    /**
     * @param string $uuid
     * @return $this
     */
    public function setUuid($uuid)
    {
        $this->uuid = $uuid;

        return $this;
    }

    /**
     * @return string
     */
    public function getAuthKey()
    {
        return $this->authKey;
    }

    /**
     * @param string $authKey
     * @return $this
     */
    public function setAuthKey($authKey)
    {
        $this->authKey = $authKey;

        return $this;
    }

    /**
     * @return string
     */
    public function getOrigin()
    {
        return $this->origin;
    }

    /**
     * @param string $origin
     * @return $this
     */
    public function setOrigin($origin)
    {
        $this->origin = $origin;

        return $this;
    }

    /**
     * @return \Requests_Response
     */
    public function getOriginalResponse()
    {
        return $this->originalResponse;
    }

    /**
     * @param \Requests_Response $originalResponse
     * @return $this
     */
    public function setOriginalResponse($originalResponse)
    {
        $this->originalResponse = $originalResponse;

        return $this;
    }

    /**
     * @return array|null
     */
    public function getAffectedChannels()
    {
        return $this->affectedChannels;
    }

    /**
     * @param array|null $affectedChannels
     * @return $this
     */
    public function setAffectedChannels($affectedChannels)
    {
        $this->affectedChannels = $affectedChannels;

        return $this;
    }

    /**
     * @return array|null
     */
    public function getAffectedChannelGroups()
    {
        return $this->affectedChannelGroups;
    }

    /**
     * @param array|null $affectedChannelGroups
     * @return $this
     */
    public function setAffectedChannelGroups($affectedChannelGroups)
    {
        $this->affectedChannelGroups = $affectedChannelGroups;

        return $this;
    }

    /**
     * @return array|null
     */
    public function getAffectedUsers()
    {
        return $this->affectedUsers;
    }

    /**
     * @param array|null $affectedUsers
     * @return $this
     */
    public function setAffectedUsers($affectedUsers)
    {
        $this->affectedUsers = $affectedUsers;

        return $this;
    }
}