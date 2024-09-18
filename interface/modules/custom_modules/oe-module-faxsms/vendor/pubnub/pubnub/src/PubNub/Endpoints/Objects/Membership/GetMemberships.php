<?php

namespace PubNub\Endpoints\Objects\Membership;

use PubNub\Endpoints\Objects\ObjectsCollectionEndpoint;
use PubNub\Enums\PNHttpMethod;
use PubNub\Enums\PNOperationType;
use PubNub\Exceptions\PubNubValidationException;
use PubNub\Models\Consumer\Objects\Membership\PNMembershipsResult;


class GetMemberships extends ObjectsCollectionEndpoint
{
    const PATH = "/v2/objects/%s/uuids/%s/channels";

    /** @var string */
    protected $uuid;

    /** @var array */
    protected $include = [];

    /**
     * @param string $uuid
     * @return $this
     */
    public function uuid($uuid)
    {
        $this->uuid = $uuid;

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

        if (!is_string($this->uuid)) {
            throw new PubNubValidationException("uuid missing");
        }
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
            $this->pubnub->getConfiguration()->getSubscribeKey(),
            $this->uuid
        );
    }

    /**
     * @param array $result Decoded json
     * @return PNMembershipsResult
     */
    protected function createResponse($result)
    {
        return PNMembershipsResult::fromPayload($result);
    }

    /**
     * @return array
     */
    protected function customParams()
    {
        $params = $this->defaultParams();

        if (count($this->include) > 0) {
            $includes = [];

            if (array_key_exists("customFields", $this->include))
            {
                array_push($includes, 'custom');
            }

            if (array_key_exists("customChannelFields", $this->include))
            {
                array_push($includes, 'channel.custom');
            }

            if (array_key_exists("channelFields", $this->include))
            {
                array_push($includes, 'channel');
            }

            $includesString = implode(",", $includes);

            if (strlen($includesString) > 0) {
                $params['include'] = $includesString;
            }
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
        return PNOperationType::PNGetMembershipsOperation;
    }

    /**
     * @return string
     */
    protected function getName()
    {
        return "GetMemberships";
    }
}
