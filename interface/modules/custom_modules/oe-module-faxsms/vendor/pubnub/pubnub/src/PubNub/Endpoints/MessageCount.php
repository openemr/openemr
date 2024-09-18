<?php

namespace PubNub\Endpoints;

use PubNub\Enums\PNHttpMethod;
use PubNub\Enums\PNOperationType;
use PubNub\Exceptions\PubNubServerException;
use PubNub\Exceptions\PubNubValidationException;
use PubNub\Models\Consumer\History\PNMessageCountResult;
use PubNub\PubNubUtil;

class MessageCount extends Endpoint
{
    const PATH = "/v3/history/sub-key/%s/message-counts/%s";

    /** @var array */
    protected $channels = [];

    /** @var array */
    protected $channelsTimetoken = [];

    /**
     * @param string|array $ch
     * @return $this
     */
    public function channels($channels)
    {
        $this->channels = PubNubUtil::extendArray($this->channels, $channels);

        return $this;
    }

    /**
     * @param array $channelsTimetoken
     * @return $this
     */
    public function channelsTimetoken($channelsTimetoken)
    {
        $this->channelsTimetoken = PubNubUtil::extendArray($this->channelsTimetoken, $channelsTimetoken);

        return $this;
    }

    /**
     * @throws PubNubValidationException
     */
    protected function validateParams()
    {
        $this->validateSubscribeKey();

        if (!is_array($this->channels) || count($this->channels) === 0) {
            throw new PubNubValidationException("Channel missing");
        }

        if (count($this->channelsTimetoken) === 0) {
            throw new PubNubValidationException("Timetoken missing");
        }

        if (count($this->channelsTimetoken) > 1 && count($this->channels) !== count($this->channelsTimetoken)) {
            throw new PubNubValidationException("The number of channels and the number of timetokens do not match");
        }

    }

    /**
     * @param array $result Decoded json
     * @return PNMessageCountResult
     * @throws PubNubServerException
     */
    protected function createResponse($result)
    {
        if (!isset($result['channels'])) {
            $exception = (new PubNubServerException())
                ->setRawBody(json_encode($result));

            throw $exception;
        }

        return new PNMessageCountResult($result['channels']);
    }

    /**
     * @return int
     */
    protected function getOperationType()
    {
        return PNOperationType::PNMessageCountOperation;
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
            PubNubUtil::joinChannels($this->channels)
        );
    }

    /**
     * @return array
     */
    protected function customParams()
    {
        $params = [];

        if (count($this->channelsTimetoken) > 1) {
            $params['channelsTimetoken'] = PubNubUtil::joinItems($this->channelsTimetoken);
        } else {
            $params['timetoken'] = $this->channelsTimetoken[0];
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
        return PNHttpMethod::GET;
    }

    /**
     * @return string
     */
    protected function getName()
    {
        return "Message Count";
    }

    /**
     * @return PNMessageCountResult
     * @throws \PubNub\Exceptions\PubNubException
     */
    public function sync()
    {
        return parent::sync();
    }
}