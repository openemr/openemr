<?php

namespace PubNub\Endpoints\FileSharing;

use PubNub\Endpoints\Endpoint;
use PubNub\Enums\PNHttpMethod;
use PubNub\Enums\PNOperationType;
use PubNub\Models\Consumer\FileSharing\PNPublishFileMessageResult;
use PubNub\PubNubUtil;

class PublishFileMessage extends FileSharingEndpoint
{
    protected const ENDPOINT_URL = "/v1/files/publish-file/%s/%s/0/%s/0/%s";

    protected $message;
    protected $meta;
    protected $shouldStore;
    protected $ttl;

    public function message($message)
    {
        $this->message = $message;
        return $this;
    }

    public function meta($meta)
    {
        $this->meta = $meta;
        return $this;
    }

    public function shouldStore(bool $shouldStore)
    {
        $this->shouldStore = $shouldStore;
        return $this;
    }


    public function ttl($ttl)
    {
        $this->ttl = $ttl;
        return $this;
    }

    protected function buildPath()
    {
        return sprintf(
            self::ENDPOINT_URL,
            $this->pubnub->getConfiguration()->getPublishKey(),
            $this->pubnub->getConfiguration()->getSubscribeKey(),
            urlencode($this->channel),
            urlencode($this->buildMessage())
        );
    }

    public function encryptMessage($message)
    {
        $crypto = $this->pubnub->getCryptoSafe();
        $messageString = PubNubUtil::writeValueAsString($message);
        if ($crypto) {
            return $crypto->encrypt($messageString);
        }
        return $messageString;
    }

    protected function buildMessage()
    {
        $messageData = [
            "message" => $this->message,
            "file" => [
                "id" => $this->fileId,
                "name" => $this->fileName
            ]
        ];

        return $this->encryptMessage($messageData);
    }

    protected function customParams()
    {
        $params['meta'] = json_encode($this->meta);
        $params['ttl'] = $this->ttl;
        $params['store'] = $this->shouldStore ? 1 : 0;

        return $params;
    }

    protected function validateParams(): void
    {
        parent::validateParams();
        $this->validatePublishKey();
    }

    public function getOperationType(): string
    {
        return PNOperationType::PNSendFileAction;
    }

    public function getName(): string
    {
        return "Sending file upload notification";
    }

    public function createResponse($result)
    {
        return new PNPublishFileMessageResult($result);
    }

    protected function isAuthRequired()
    {
        return true;
    }

    protected function buildData()
    {
        return null;
    }

    protected function httpMethod()
    {
        return PNHttpMethod::GET;
    }
}
