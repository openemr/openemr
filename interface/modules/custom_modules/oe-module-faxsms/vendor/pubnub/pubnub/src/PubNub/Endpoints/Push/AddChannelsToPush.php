<?php

namespace PubNub\Endpoints\Push;

use PubNub\Enums\PNOperationType;
use PubNub\Enums\PNPushType;
use PubNub\Exceptions\PubNubValidationException;
use PubNub\Models\Consumer\Push\PNPushAddChannelResult;
use PubNub\PubNubUtil;

class AddChannelsToPush extends PushEndpoint
{
    protected const OPERATION_TYPE = PNOperationType::PNAddPushNotificationsOnChannelsOperation;
    protected const OPERATION_NAME = "AddChannelsToPush";
    public const PATH = "/v1/push/sub-key/%s/devices/%s";
    public const PATH_APNS2 = "/v2/push/sub-key/%s/devices-apns2/%s";

    /** @var  string[]|string */
    protected string | array $channels = [];

    /**
     * @param string[]|string $channels
     * @return $this
     */
    public function channels(string | array $channels)
    {
        $this->channels = PubNubUtil::extendArray($this->channels, $channels);

        return $this;
    }

    protected function validateParams()
    {
        parent::validateParams();

        if (!is_array($this->channels) || count($this->channels) === 0) {
            throw new PubNubValidationException("Channel missing");
        }
    }

    /**
     * @return array
     */
    protected function customParams()
    {
        $params = [
            'add' => PubNubUtil::joinItems($this->channels)
        ];

        if ($this->pushType != PNPushType::APNS2) {
            $params['type'] = $this->getPushType();
        } else {
            // apns2 push -> add topic and environment
            $params['topic'] = $this->topic;

            if (is_string($this->environment) && strlen($this->environment) > 0) {
                $params['environment'] = $this->environment;
            } else {
                $params['environment'] = 'development';
            }
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
        $path = $this->pushType == PNPushType::APNS2 ? AddChannelsToPush::PATH_APNS2 : AddChannelsToPush::PATH;

        return sprintf(
            $path,
            $this->pubnub->getConfiguration()->getSubscribeKey(),
            $this->deviceId
        );
    }

    /**
     * @param array $result Decoded json
     * @return PNPushAddChannelResult
     */
    protected function createResponse($result)
    {
        return new PNPushAddChannelResult();
    }
}
