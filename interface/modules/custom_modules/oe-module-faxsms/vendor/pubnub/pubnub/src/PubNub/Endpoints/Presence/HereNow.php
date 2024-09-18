<?php

namespace PubNub\Endpoints\Presence;

use PubNub\Endpoints\Endpoint;
use PubNub\Enums\PNHttpMethod;
use PubNub\Enums\PNOperationType;
use PubNub\Exceptions\PubNubValidationException;
use PubNub\Models\Consumer\Presence\PNHereNowResult;
use PubNub\PubNubUtil;


class HereNow extends Endpoint
{
    const PATH = "/v2/presence/sub-key/%s/channel/%s";
    const GLOBAL_PATH = "/v2/presence/sub-key/%s";

    /**  @var string[] */
    protected $channels = [];

    /**  @var string[] */
    protected $groups = [];

    /**  @var bool */
    protected $includeState = false;

    /**  @var bool */
    protected $includeUuids = true;

    /**
     * @param string|string[] $channels
     * @return $this
     */
    public function channels($channels)
    {
        $this->channels = PubNubUtil::extendArray($this->channels, $channels);

        return $this;
    }

    /**
     * @param string|string[] $channelGroups
     * @return $this
     */
    public function channelGroups($channelGroups)
    {
        $this->groups = PubNubUtil::extendArray($this->groups, $channelGroups);

        return $this;
    }

    /**
     * @param bool $shouldIncludeState
     * @return $this
     */
    public function includeState($shouldIncludeState)
    {
        $this->includeState = $shouldIncludeState;

        return $this;
    }

    /**
     * @param bool $includeUuids
     * @return $this
     */
    public function includeUuids($includeUuids)
    {
        $this->includeUuids = $includeUuids;

        return $this;
    }

    /**
     * @throws PubNubValidationException
     */
    protected function validateParams()
    {
        $this->validateSubscribeKey();
    }

    /**
     * @return array
     */
    protected function customParams()
    {
        $params = [];

        if (count($this->groups) > 0) {
            $params['channel-group'] = PubNubUtil::joinItems($this->groups);
        }

        if ($this->includeState) {
            $params['state'] = "1";
        }

        if (!$this->includeUuids) {
            $params['disable-uuids'] = "1";
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
    public function buildPath()
    {
        if (count($this->channels) === 0 && count($this->groups) === 0) {
            return sprintf(HereNow::GLOBAL_PATH, $this->pubnub->getConfiguration()->getSubscribeKey());
        } else {
            return sprintf(HereNow::PATH,
                $this->pubnub->getConfiguration()->getSubscribeKey(),
                PubNubUtil::joinChannels($this->channels)
            );
        }
    }

    /**
     * @return PNHereNowResult
     */
    public function sync()
    {
        return parent::sync();
    }

    /**
     * @param array $result Decoded json
     * @return PNHereNowResult
     */
    protected function createResponse($result)
    {
        return PNHereNowResult::fromJson($result, $this->channels);
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
        return PNOperationType::PNHereNowOperation;
    }

    /**
     * @return string
     */
    protected function getName()
    {
        return "HereNow";
    }
}