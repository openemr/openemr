<?php

namespace PubNub\Endpoints\FileSharing;

use PubNub\Enums\PNHttpMethod;
use PubNub\Enums\PNOperationType;
use PubNub\Models\Consumer\FileSharing\PNDownloadFileResult;

class DownloadFile extends FileSharingEndpoint
{
    protected const ENDPOINT_URL = "/v1/files/%s/channels/%s/files/%s/%s";
    protected const RESPONSE_IS_JSON = false;

    protected $downloadData;

    protected function buildPath(): string
    {
        return sprintf(
            self::ENDPOINT_URL,
            $this->pubnub->getConfiguration()->getSubscribeKey(),
            $this->channel,
            $this->fileId,
            $this->fileName
        );
    }

    protected function customParams()
    {
        return [];
    }

    protected function httpMethod()
    {
        return PNHttpMethod::GET;
    }

    protected function isAuthRequired()
    {
        return false;
    }

    protected function createResponse($result)
    {
        if ($this->pubnub->isCryptoEnabled()) {
            return new PNDownloadFileResult($this->pubnub->getCrypto()->decrypt((string)$result));
        }
        return new PNDownloadFileResult($result);
    }

    protected function getOperationType()
    {
        return PNOperationType::PNDownloadFileAction;
    }

    protected function name()
    {
        return "Downloading file";
    }

    protected function buildData()
    {
        return null;
    }
}
