<?php

namespace PubNub\Endpoints\Objects\Channel;

use PubNub\Endpoints\Objects\ObjectsCollectionEndpoint;
use PubNub\Enums\PNHttpMethod;
use PubNub\Enums\PNOperationType;
use PubNub\Exceptions\PubNubValidationException;
use PubNub\Models\Consumer\Objects\Channel\PNGetAllChannelMetadataResult;

class GetAllChannelMetadata extends ObjectsCollectionEndpoint
{
    const PATH = "/v2/objects/%s/channels";

    /** @var string */
    protected $channel;

    /** @var array */
    protected $include = [];

    /**
     * @param string $ch
     * @return $this
     */
    public function channel($ch)
    {
        $this->channel = $ch;

        return $this;
    }

    /**
     * @param array $include
     * @return $this
     */
    public function includeFields($include)
    {
        $this->include = $include;

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
     * @return string
     * @throws PubNubBuildRequestException
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
            static::PATH,
            $this->pubnub->getConfiguration()->getSubscribeKey()
        );
    }

    /**
     * @param array $result Decoded json
     * @return PNGetAllChannelMetadataResult
     */
    protected function createResponse($result)
    {
        return PNGetAllChannelMetadataResult::fromPayload($result);
    }

    /**
     * @return array
     */
    protected function customParams()
    {
        $params = $this->defaultParams();

        if (array_key_exists("customFields", $this->include))
        {
            $params['include'] = 'custom';
        }

        if (array_key_exists("totalCount", $this->include))
        {
            $params['count'] = "true";
        }

        if (array_key_exists("next", $this->page))
        {
            $params['start'] = $this->page["next"];
        }

        if (array_key_exists("prev", $this->page))
        {
            $params['end'] = $this->page["prev"];
        }

        if (!empty($this->filter))
        {
            $params['filter'] = $this->filter;
        }

        if (!empty($this->limit))
        {
            $params['limit'] = $this->limit;
        }

        if (!empty($this->sort))
        {
          $sortEntries = [];

          foreach ($this->sort as $key => $value)
          {
            if ($value === 'asc' || $value === 'desc') {
              array_push($sortEntries, "$key:$value");
            } else {
                array_push($sortEntries, $key);
            }
          }

          $params['sort'] = $sortEntries;
        }

        return $params;
    }

    /**
     * @return bool
     */
    protected function isAuthRequired()
    {
        return True;
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
        return PNOperationType::PNGetAllChannelMetadataOperation;
    }

    /**
     * @return string
     */
    protected function getName()
    {
        return "GetAllChannelMetadata";
    }
}
