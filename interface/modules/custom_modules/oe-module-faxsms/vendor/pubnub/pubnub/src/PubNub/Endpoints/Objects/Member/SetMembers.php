<?php

namespace PubNub\Endpoints\Objects\Member;

use PubNub\Endpoints\Objects\ObjectsCollectionEndpoint;
use PubNub\Enums\PNHttpMethod;
use PubNub\Enums\PNOperationType;
use PubNub\Exceptions\PubNubValidationException;
use PubNub\Models\Consumer\Objects\Member\PNMembersResult;
use PubNub\PubNubUtil;


class SetMembers extends ObjectsCollectionEndpoint
{
    const PATH = "/v2/objects/%s/channels/%s/uuids";

    /** @var string */
    protected $channel;

    /** @var array */
    protected $uuids;

    /** @var array */
    protected $custom;

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
     * @param array $uuids
     * @return $this
     */
    public function uuids($uuids)
    {
        $this->uuids = $uuids;

        return $this;
    }

    /**
     * @param array $custom
     * @return $this
     */
    public function custom($custom)
    {
        $this->custom = $custom;

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

        if (!is_string($this->channel)) {
            throw new PubNubValidationException("channel missing");
        }

        if (empty($this->uuids)) {
            throw new PubNubValidationException("uuids missing");
        }
    }

    /**
     * @return string
     * @throws PubNubBuildRequestException
     */
    protected function buildData()
    {
        $entries = [];

        foreach($this->uuids as $value)
        {
            $entry = [
                "uuid" => [
                    "id" => $value,
                ]
            ];

            if (!empty($this->custom))
            {
                $entry["custom"] = $this->custom;
            }

            array_push($entries, $entry);
        }

        return PubNubUtil::writeValueAsString([
            "set" => $entries
        ]);
    }

    /**
     * @return string
     */
    protected function buildPath()
    {
        return sprintf(
            static::PATH,
            $this->pubnub->getConfiguration()->getSubscribeKey(),
            $this->channel
        );
    }

    /**
     * @param array $result Decoded json
     * @return PNMembersResult
     */
    protected function createResponse($result)
    {
        return PNMembersResult::fromPayload($result);
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

            if (array_key_exists("customUUIDFields", $this->include))
            {
                array_push($includes, 'uuid.custom');
            }

            if (array_key_exists("UUIDFields", $this->include))
            {
                array_push($includes, 'uuid');
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
        return PNHttpMethod::PATCH;
    }

    /**
     * @return int
     */
    protected function getOperationType()
    {
        return PNOperationType::PNSetMembersOperation;
    }

    /**
     * @return string
     */
    protected function getName()
    {
        return "SetMembers";
    }
}
