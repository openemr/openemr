<?php

namespace PubNub\Endpoints\PubSub;

use PubNub\Builders\PubNubErrorBuilder;
use PubNub\Endpoints\Endpoint;
use PubNub\Enums\PNHttpMethod;
use PubNub\Enums\PNOperationType;
use PubNub\Exceptions\PubNubValidationException;
use PubNub\Models\Consumer\PNPublishResult;
use PubNub\Models\Consumer\PubSub\SubscribeEnvelope;
use PubNub\PubNubException;
use PubNub\PubNubUtil;

class Subscribe extends Endpoint
{
    public const PATH = "/v2/subscribe/%s/%s/0";

    /** @var  string[] */
    protected $channels = [];

    /** @var  string[] */
    protected $channelGroups = [];

    /** @var  string */
    protected $region;

    /** @var  string */
    protected $filterExpression;

    /** @var  int */
    protected $timetoken;

    /** @var  bool */
    protected $withPresence;

    /**
     * @param string|string[] $ch
     * @return $this
     */
    public function channels($ch)
    {
        $this->channels = PubNubUtil::extendArray($this->channels, $ch);

        return $this;
    }

    /**
     * @param string|string[] $cgs
     * @return $this
     */
    public function channelGroups($cgs)
    {
        $this->channelGroups = PubNubUtil::extendArray($this->channelGroups, $cgs);

        return $this;
    }

    /**
     * @param string $region
     * @return $this
     */
    public function setRegion($region)
    {
        $this->region = $region;

        return $this;
    }

    /**
     * @param string $filterExpression
     * @return $this
     */
    public function setFilterExpression($filterExpression)
    {
        $this->filterExpression = $filterExpression;

        return $this;
    }

    /**
     * @param int $timetoken
     * @return $this
     */
    public function setTimetoken($timetoken)
    {
        $this->timetoken = $timetoken;

        return $this;
    }

    /**
     * @param bool $withPresence
     * @return $this
     */
    public function setWithPresence($withPresence)
    {
        $this->withPresence = $withPresence;

        return $this;
    }

    /**
     * @throws PubNubValidationException
     */
    protected function validateParams()
    {
        if (count($this->channels) === 0 && count($this->channelGroups) === 0) {
            throw new PubNubValidationException("At least one channel or channel group should be specified");
        }

        $this->validateSubscribeKey();
        $this->validatePublishKey();
    }

    /**
     * @return array
     */
    protected function customParams()
    {
        $params = [];

        if (count($this->channelGroups) > 0) {
            $params['channel-group'] = PubNubUtil::joinChannels($this->channelGroups);
        }

        if (!is_null($this->filterExpression) && strlen($this->filterExpression) > 0) {
            $params['filter-expr'] = PubNubUtil::urlEncode($this->filterExpression);
        }

        if ($this->timetoken !== null) {
            $params['tt'] = (string) $this->timetoken;
        }

        if ($this->region !== null) {
            $params['tr'] = $this->region;
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
        $channels = PubNubUtil::joinChannels($this->channels);

        return sprintf(
            static::PATH,
            $this->pubnub->getConfiguration()->getSubscribeKey(),
            $channels
        );
    }

    /**
     * @return SubscribeEnvelope
     */
    public function sync()
    {
        return parent::sync();
    }

    /**
     * @param array $result Decoded json
     * @return SubscribeEnvelope
     */
    protected function createResponse($result)
    {
        return SubscribeEnvelope::fromJson($result);
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
        return $this->pubnub->getConfiguration()->getSubscribeTimeout();
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
        return PNOperationType::PNSubscribeOperation;
    }

    /**
     * @return string
     */
    protected function getName()
    {
        return "Subscribe";
    }
}
