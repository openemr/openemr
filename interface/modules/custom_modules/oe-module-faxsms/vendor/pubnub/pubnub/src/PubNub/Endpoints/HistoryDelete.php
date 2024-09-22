<?php

namespace PubNub\Endpoints;

use PubNub\Enums\PNHttpMethod;
use PubNub\Enums\PNOperationType;
use PubNub\Exceptions\PubNubValidationException;
use PubNub\Models\Consumer\History\PNHistoryDeleteResult;
use PubNub\PubNubUtil;

class HistoryDelete extends Endpoint
{
    const PATH = "/v3/history/sub-key/%s/channel/%s";

    /** @var string */
    protected $channel;

    /** @var int */
    protected $start;

    /** @var int */
    protected $end;

    /**
     * @param string $channel
     * @return $this
     */
    public function channel($channel)
    {
        $this->channel = $channel;

        return $this;
    }

    /**
     * @param int $start
     * @return $this
     */
    public function start($start)
    {
        $this->start = $start;

        return $this;
    }

    /**
     * @param int $end
     * @return $this
     */
    public function end($end)
    {
        $this->end = $end;

        return $this;
    }

    /**
     * @throws PubNubValidationException
     */
    protected function validateParams()
    {
        $this->validateSubscribeKey();

        if ($this->channel === null || strlen($this->channel) === 0) {
            throw new PubNubValidationException("Channel missing");
        }
    }

    /**
     * @param array $result Decoded json
     * @return mixed
     */
    protected function createResponse($result)
    {
        return new PNHistoryDeleteResult();
    }

    /**
     * @return int
     */
    protected function getOperationType()
    {
        return PNOperationType::PNHistoryOperation;
    }

    /**
     * @return bool
     */
    protected function isAuthRequired()
    {
        return true;
    }

    /**
     * @return null|string
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
            static::PATH, $this->pubnub->getConfiguration()->getSubscribeKey(),
            PubNubUtil::urlEncode($this->channel)
        );
    }

    /**
     * @return array
     */
    protected function customParams()
    {
        $params = [];

        if ($this->start !== null) {
            $params['start'] = (string) $this->start;
        }

        if ($this->end !== null) {
            $params['end'] = (string) $this->end;
        }

        return $params;
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
     * @return string
     */
    protected function getName()
    {
        return "History delete";
    }

    /**
     * @return PNHistoryDeleteResult
     * @throws \PubNub\Exceptions\PubNubException
     */
    public function sync()
    {
        return parent::sync();
    }
}