<?php

namespace PubNub\Endpoints\Push;

use PubNub\Endpoints\Endpoint;
use PubNub\Enums\PNHttpMethod;
use PubNub\Enums\PNOperationType;
use PubNub\Enums\PNPushType;
use PubNub\Models\Consumer\Push\PNPushRemoveAllChannelsResult;

class RemoveDeviceFromPush extends PushEndpoint
{
    protected const OPERATION_TYPE = PNOperationType::PNRemoveAllPushNotificationsOperation;
    protected const OPERATION_NAME = "RemoveDeviceFromPush";
    public const PATH = "/v1/push/sub-key/%s/devices/%s/remove";
    public const PATH_APNS2 = "/v2/push/sub-key/%s/devices-apns2/%s/remove";

    /**
     * @return array
     */
    protected function customParams()
    {
        $params = [];

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
        $path = $this->pushType == PNPushType::APNS2 ? RemoveDeviceFromPush::PATH_APNS2 : RemoveDeviceFromPush::PATH;

        return sprintf(
            $path,
            $this->pubnub->getConfiguration()->getSubscribeKey(),
            $this->deviceId
        );
    }

    /**
     * @param array $result Decoded json
     * @return PNPushRemoveAllChannelsResult
     */
    protected function createResponse($result)
    {
        return new PNPushRemoveAllChannelsResult();
    }
}
