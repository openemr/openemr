<?php

namespace PubNub\Endpoints\FileSharing;

use PubNub\Endpoints\Endpoint;
use PubNub\Enums\PNHttpMethod;
use PubNub\Enums\PNOperationType;
use PubNub\Exceptions\PubNubValidationException;
use PubNub\Models\Consumer\FileSharing\PNFetchFileUploadS3DataResult;
use PubNub\PubNubUtil;

class FetchFileUploadS3Data extends Endpoint
{
    protected const ENDPOINT_URL = "/v1/files/%s/channels/%s/generate-upload-url";

    protected ?string $channel;
    protected ?string $fileName;

    public function channel($channel): self
    {
        $this->channel = $channel;
        return $this;
    }

    public function fileName($fileName)
    {
        $this->fileName = $fileName;
        return $this;
    }

    protected function validateParams(): void
    {
        $this->validateSubscribeKey();
        $this->validateChannel();
        $this->validateFileName();
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

    protected function validateFileName(): void
    {
        if ($this->fileName === null) {
            throw new PubNubValidationException("File name missing");
        }
    }
    public function buildPath()
    {
        return sprintf(
            static::ENDPOINT_URL,
            $this->pubnub->getConfiguration()->getSubscribeKey(),
            PubNubUtil::urlEncode($this->channel)
        );
    }

    public function getOperationType()
    {
        return PNOperationType::PNFetchFileUploadS3DataAction;
    }

    public function name()
    {
        return "Fetch file upload S3 data";
    }

    public function httpMethod()
    {
        return PNHttpMethod::POST;
    }

    public function createResponse($result)
    {
        return new PNFetchFileUploadS3DataResult($result);
    }

    public function isAuthRequired()
    {
        return true;
    }

    public function buildData()
    {
        $params = [
            "name" => $this->fileName
        ];
        return PubNubUtil::writeValueAsString($params);
    }

    public function customParams()
    {
        return [];
    }
}
