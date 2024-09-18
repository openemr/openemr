<?php

namespace PubNub\Endpoints\Push;

use PubNub\Enums\PNHttpMethod;
use PubNub\Enums\PNOperationType;
use PubNub\Enums\PNPushType;
use PubNub\Models\Consumer\Push\PNPushListProvisionsResult;

class ListPushProvisions extends PushEndpoint
{
    protected const OPERATION_TYPE = PNOperationType::PNPushNotificationEnabledChannelsOperation;
    protected const OPERATION_NAME = "ListPushProvisions";
    public const PATH = "/v1/push/sub-key/%s/devices/%s";
    public const PATH_APNS2 = "/v2/push/sub-key/%s/devices-apns2/%s";

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
        $path = $this->pushType == PNPushType::APNS2 ? ListPushProvisions::PATH_APNS2 : ListPushProvisions::PATH;

        return sprintf(
            $path,
            $this->pubnub->getConfiguration()->getSubscribeKey(),
            $this->deviceId
        );
    }

    /**
     * @param array $result Decoded json
     * @return mixed
     */
    protected function createResponse($result)
    {
        if ($result !== null || is_array($result)) {
            return PNPushListProvisionsResult::fromJson($result);
        } else {
            return new PNPushListProvisionsResult([]);
        }
    }
}
