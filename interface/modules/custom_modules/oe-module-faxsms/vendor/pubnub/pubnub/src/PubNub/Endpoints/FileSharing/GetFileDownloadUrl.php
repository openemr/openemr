<?php

namespace PubNub\Endpoints\FileSharing;

use PubNub\Enums\PNHttpMethod;
use PubNub\Enums\PNOperationType;
use PubNub\Models\Consumer\FileSharing\PNDownloadFileResult;
use PubNub\Models\Consumer\FileSharing\PNGetFileDownloadURLResult;

class GetFileDownloadUrl extends FileSharingEndpoint
{
    protected const ENDPOINT_URL = "/v1/files/%s/channels/%s/files/%s/%s";
    protected const RESPONSE_IS_JSON = false;

    protected $downloadData;
    protected $followRedirects = false;

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
        return new PNGetFileDownloadURLResult($result);
    }

    protected function getOperationType()
    {
        return PNOperationType::PNGetFileDownloadURLAction;
    }

    protected function name()
    {
        return "Downloading file";
    }

    protected function buildData()
    {
        return null;
    }

    public function sync(): PNGetFileDownloadURLResult
    {
        return parent::sync();
    }
}
