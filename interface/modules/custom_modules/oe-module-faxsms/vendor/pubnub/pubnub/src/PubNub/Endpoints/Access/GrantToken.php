<?php

namespace PubNub\Endpoints\Access;

use PubNub\Endpoints\Endpoint;
use PubNub\Exceptions\PubNubValidationException;
use PubNub\Models\Consumer\AccessManager\PNAccessManagerGrantResult;
use PubNub\PubNubUtil;
use PubNub\Enums\PNHttpMethod;
use PubNub\Enums\PNOperationType;
use PubNub\Exceptions\PubNubTokenParseException;
use PubNub\Models\Consumer\AccessManager\PNAccessManagerTokenResult;
use PubNub\PubNubCborDecode;

class GrantToken extends Endpoint
{
    private const PATH = '/v3/pam/%s/grant';

    /** @var  int */
    protected $ttl;

    protected $meta;

    /** @var string */
    protected $authorizedUuid;

    /** @var string[] */
    protected $resources = [];

    /** @var string[] */
    protected $patterns = [];

    /** @var bool */
    protected $sortParams = true;

    private $channels = [];

    private $groups = [];

    private $uuids = [];

    /**
     * Set time in minutes for which granted permissions are valid
     *
     * Max: 525600
     * Min: 1
     * Default: 1440
     *
     * Setting 0 will apply the grant indefinitely (forever grant).
     *
     * @param int $value
     * @return $this
     */
    public function ttl($value)
    {
        $this->ttl = $value;
        return $this;
    }

    public function meta($meta)
    {
        $this->meta = $meta;
        return $this;
    }

    /**
     * @param string $uuid
     * @return $this
     */
    public function authorizedUuid($uuid)
    {
        $this->authorizedUuid = $uuid;
        return $this;
    }

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
     * @param string|string[] $groups
     * @return $this
     */
    public function groups($groups)
    {
        $this->groups = PubNubUtil::extendArray($this->groups, $groups);
        return $this;
    }

    /**
     * @param string|string[] $uuids
     * @return $this
     */
    public function uuids($uuids)
    {
        $this->uuids = PubNubUtil::extendArray($this->uuids, $uuids);
        return $this;
    }

    private function parsePermissions($permissions)
    {
        $mapping = [
            'read' => 1,
            'write' =>  2,
            'manage' => 4,
            'delete' => 8,
            'create' => 16,
            'get' => 32,
            'update' => 64,
            'join' => 128,
        ];
        $result = 0;

        foreach ($permissions as $key => $value) {
            if ($value) {
                $result += $mapping[$key];
            }
        }

        return $result;
    }

    private function addResources($type, $res)
    {
        if (is_array($res)) {
            foreach ($res as $name => $permissions) {
                $this->resources[$type][$name] = $this->parsePermissions($permissions);
            }
        }
    }

    private function addPatterns($type, $res)
    {
        if (is_array($res)) {
            foreach ($res as $name => $permissions) {
                $this->patterns[$type][$name] = $this->parsePermissions($permissions);
            }
        }
    }

    public function addChannelResources($res)
    {
        $this->addResources('channels', $res);
        return $this;
    }

    public function addChannelGroupResources($res)
    {
        $this->addResources('groups', $res);
        return $this;
    }

    public function addUuidResources($res)
    {
        $this->addResources('uuids', $res);
        return $this;
    }

    public function addChannelPatterns($res)
    {
        $this->addPatterns('channels', $res);
        return $this;
    }

    public function addChannelGroupPatterns($res)
    {
        $this->addPatterns('groups', $res);
        return $this;
    }

    public function addUuidPatterns($res)
    {
        $this->addPatterns('uuids', $res);
        return $this;
    }

    /**
     * @throws PubNubValidationException
     */
    public function validateParams()
    {
        $this->validateSubscribeKey();
        $this->validateSecretKey();
    }

    /**
     * @return array
     */
    public function customParams()
    {
        return [];
    }

    protected function customHeaders()
    {
        return [ 'Content-Type' => 'application/json' ];
    }

    /**
     * @return null
     */
    public function buildData()
    {
        $params = [
            'ttl' => $this->ttl,
            'permissions' => [],
        ];

        if ($this->resources) {
            $params['permissions']['resources'] = $this->resources;
        }
        if ($this->patterns) {
            $params['permissions']['patterns'] = $this->patterns;
        }
        if ($this->authorizedUuid) {
            $params['permissions']['uuid'] = $this->authorizedUuid;
        }
        if ($this->meta) {
            $params['permissions']['meta'] = $this->meta;
        }

        return json_encode($params);
    }

    /**
     * @return string
     */
    public function buildPath()
    {
        return sprintf(static::PATH, $this->pubnub->getConfiguration()->getSubscribeKey());
    }

    /**
     * @return PNAccessManagerGrantResult
     */
    public function sync()
    {
        return parent::sync();
    }

    /**
     * @param string $token
     * @return PNAccessManagerGrantResult
     */
    public function createResponse($response)
    {
        return $response['data']['token'];
    }

    public function parseToken($token)
    {
        $tokenSanitized = str_replace(['-', '_'], ['/', '+'], $token);
        $cborBytes = base64_decode($tokenSanitized);

        if (!$cborBytes) {
            throw new PubNubTokenParseException('Token parse error');
        }
        $tokenArray = PubNubCborDecode::decode(bin2hex($cborBytes));
        return PNAccessManagerTokenResult::fromArray($tokenArray);
    }

    /**
     * @return bool
     */
    public function isAuthRequired()
    {
        return false;
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
     * @return string
     */
    public function httpMethod()
    {
        return PNHttpMethod::POST;
    }

    /**
     * @return int
     */
    public function getOperationType()
    {
        return PNOperationType::PNAccessManagerGrantToken;
    }
}
