<?php

namespace PubNub\Endpoints\FileSharing;

use PubNub\Endpoints\Endpoint;
use PubNub\Enums\PNHttpMethod;
use PubNub\Enums\PNOperationType;
use PubNub\Exceptions\PubNubValidationException;
use PubNub\Models\Consumer\FileSharing\PNGetFilesResult;
use PubNub\PubNubUtil;

class ListFiles extends Endpoint
{
    protected const ENDPOINT_URL = "/v1/files/%s/channels/%s/files";

    protected ?string $channel;

    public function channel($channel): self
    {
        $this->channel = $channel;
        return $this;
    }

    protected function validateParams(): void
    {
        $this->validateSubscribeKey();
        $this->validateChannel();
    }

    protected function getRequestTimeout(): int
    {
        return $this->pubnub->getConfiguration()->getNonSubscribeRequestTimeout();
    }

    protected function getConnectTimeout(): int
    {
        return $this->pubnub->getConfiguration()->getConnectTimeout();
    }

    protected function validateChannel(): void
    {
        if (!$this->channel) {
            throw new PubNubValidationException("Channel missing");
        }
    }

    protected function buildPath()
    {
        return sprintf(
            static::ENDPOINT_URL,
            $this->pubnub->getConfiguration()->getSubscribeKey(),
            PubNubUtil::urlEncode($this->channel)
        );
    }


    protected function httpMethod(): string
    {
        return PNHttpMethod::GET;
    }

    protected function customParams(): array
    {
        return [];
    }

    protected function buildData()
    {
        return null;
    }

    protected function isAuthRequired(): bool
    {
        return true;
    }

    protected function createResponse($result): PNGetFilesResult
    {
        return new PNGetFilesResult($result);
    }

    protected function getOperationType()
    {
        return PNOperationType::PNGetFilesAction;
    }

    protected function name()
    {
        return "List files";
    }
}
