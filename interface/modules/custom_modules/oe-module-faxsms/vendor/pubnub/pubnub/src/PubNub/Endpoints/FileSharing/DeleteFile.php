<?php

namespace PubNub\Endpoints\FileSharing;

use PubNub\Enums\PNHttpMethod;
use PubNub\Enums\PNOperationType;
use PubNub\Models\Consumer\FileSharing\PNDeleteFileResult;
use PubNub\PubNubUtil;

class DeleteFile extends FileSharingEndpoint
{
    protected const ENDPOINT_URL = "/v1/files/%s/channels/%s/files/%s/%s";

    protected function createResponse($result)
    {
        return new PNDeleteFileResult($result);
    }

    /**
     * @return int
     */
    protected function getOperationType()
    {
        return PNOperationType::PNDeleteFileOperation;
    }

    /**
     * @return bool
     */
    protected function isAuthRequired()
    {
        return true;
    }

    /**
     * @return null|string
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
        return sprintf(
            self::ENDPOINT_URL,
            $this->pubnub->getConfiguration()->getSubscribeKey(),
            PubNubUtil::urlEncode($this->channel),
            PubNubUtil::urlEncode($this->fileId),
            PubNubUtil::urlEncode($this->fileName)
        );
    }

    /**
     * @return array
     */
    protected function customParams()
    {
        return [];
    }

    /**
     * @return string PNHttpMethod
     */
    protected function httpMethod()
    {
        return PNHttpMethod::DELETE;
    }

    public function name()
    {
        return "Delete file";
    }
}
