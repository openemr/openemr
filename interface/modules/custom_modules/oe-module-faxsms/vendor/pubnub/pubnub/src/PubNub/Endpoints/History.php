<?php

namespace PubNub\Endpoints;

use PubNub\Enums\PNOperationType;
use PubNub\Enums\PNHttpMethod;
use PubNub\Exceptions\PubNubValidationException;
use PubNub\Models\Consumer\History\PNHistoryResult;
use PubNub\PubNubUtil;


class History extends Endpoint
{
    const PATH = "/v2/history/sub-key/%s/channel/%s";
    const MAX_COUNT = 100;

    /** @var string */
    protected $channel;

    /** @var int */
    protected $start;

    /** @var int */
    protected $end;

    /** @var bool */
    protected $reverse;

    /** @var int */
    protected $count;

    /** @var bool */
    protected $includeTimetoken;

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
     * @param bool $reverse
     * @return $this
     */
    public function reverse($reverse)
    {
        $this->reverse = $reverse;

        return $this;
    }

    /**
     * @param int $count
     * @return $this
     */
    public function count($count)
    {
        $this->count = $count;

        return $this;
    }

    /**
     * @param bool $includeTimetoken
     * @return $this
     */
    public function includeTimetoken($includeTimetoken)
    {
        $this->includeTimetoken = $includeTimetoken;

        return $this;
    }

    /**
     * @throws PubNubValidationException
     */
    public function validateParams()
    {
        $this->validateSubscribeKey();

        if ($this->channel === null || strlen($this->channel) === 0) {
            throw new PubNubValidationException("Channel missing");
        }
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

        if ($this->count !== null && $this->count > 0 && $this->count <= static::MAX_COUNT) {
            $params['count'] = (string) $this->count;
        } else {
            $params['count'] = '100';
        }

        if ($this->reverse !== null) {
            $this->reverse ? $params['reverse'] = "true" : $params['reverse'] = "false";
        }

        if ($this->includeTimetoken !== null) {
            $this->includeTimetoken ? $params['include_token'] = "true" : $params['include_token'] = "false";
        }

        return $params;
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
    protected function buildPath()
    {
        return sprintf(
            static::PATH, $this->pubnub->getConfiguration()->getSubscribeKey(),
            PubNubUtil::urlEncode($this->channel)
        );
    }

    /**
     * @return PNHistoryResult
     */
    public function sync()
    {
        return parent::sync();
    }

    /**
     * @param array $result Decoded json
     * @return PNHistoryResult
     */
    protected function createResponse($result)
    {
        try {
            return PNHistoryResult::fromJson(
                $result,
                $this->pubnub->getConfiguration()->getCryptoSafe(),
                $this->includeTimetoken,
                $this->pubnub->getConfiguration()->getCipherKey()
            );
        } catch (PubNubValidationException $e) {
            return PNHistoryResult::fromJson(
                $result,
                null,
                $this->includeTimetoken,
                null
            );
        }
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
        return PNOperationType::PNHistoryOperation;
    }

    /**
     * @return string name
     */
    public function getName()
    {
        return "History";
    }
}