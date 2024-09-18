<?php

namespace PubNub\Endpoints\FileSharing;

use Exception;
use PubNub\Endpoints\Endpoint;
use PubNub\Enums\PNHttpMethod;
use PubNub\Enums\PNOperationType;
use PubNub\Exceptions\PubNubValidationException;
use PubNub\PubNubUtil;
use WpOrg\Requests\Requests;

class SendFile extends Endpoint
{
    protected string $channel;
    protected string $fileName;
    protected mixed $message;
    protected mixed $meta;
    protected bool $shouldStore;
    protected int $ttl;
    protected mixed $fileContent;
    protected mixed $fileHandle;
    protected mixed $fileUploadEnvelope;
    protected bool $shouldCompress = false;
    protected string $boundary;

    protected array $customParamMapping = [];

    public function channel($channel)
    {
        $this->channel = $channel;
        return $this;
    }

    public function fileName($fileName)
    {
        $this->fileName = $fileName;
        return $this;
    }

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

    public function shouldStore($shouldStore)
    {
        $this->shouldStore = $shouldStore;
        return $this;
    }

    public function ttl($ttl)
    {
        $this->ttl = $ttl;
        return $this;
    }

    public function fileHandle($fileHandle)
    {
        $this->fileHandle = $fileHandle;
        return $this;
    }

    public function fileContent($fileContent)
    {
        $this->fileContent = $fileContent;
        return $this;
    }

    public function requestTimeout()
    {
        return $this->pubnub->getConfiguration()->getNonSubscribeRequestTimeout();
    }

    protected function connectTimeout()
    {
        return $this->pubnub->getConfiguration()->getConnectTimeout();
    }
    /**
     * @throws PubNubValidationException
     */
    protected function validateParams()
    {
        $this->validateSubscribeKey();
        $this->validateChannel();
    }

    protected function validateChannel(): void
    {
        if (!$this->channel) {
            throw new PubNubValidationException("Channel missing");
        }
    }

    /**
     * @param array $result Decoded json
     * @return PNPublishResult
     */
    protected function createResponse($result)
    {
        throw new Exception('Not implemented');
    }

    /**
     * @return bool
     */
    protected function isAuthRequired()
    {
        return true;
    }

    protected function buildData()
    {
        return null;
    }

    /**
     * @return int
     */
    protected function getRequestTimeout()
    {
        return $this->pubnub->getConfiguration()->getNonSubscribeRequestTimeout();
    }

    /**
     * @return int
     */
    protected function getConnectTimeout()
    {
        return $this->pubnub->getConfiguration()->getConnectTimeout();
    }

    /**
     * @return string
     */
    protected function httpMethod()
    {
        return PNHttpMethod::POST;
    }

    /**
     * @return int
     */
    protected function getOperationType()
    {
        return PNOperationType::PNSendFileAction;
    }

    /**
     * @return string
     */
    protected function getName()
    {
        return "Send File";
    }


    /**
     * @return array
     */
    protected function customParams()
    {
        $params = [];
        foreach ($this->customParamMapping as $customParam => $requestParam) {
            if (isset($this->$customParam) && !empty($this->$customParam)) {
                $params[$requestParam] = $this->$customParam;
            }
        }

        return $params;
    }

    /**
     * @return string
     * @throws PubNubBuildRequestException
     */
    protected function buildPath()
    {
        return parse_url($this->fileUploadEnvelope->getUrl(), PHP_URL_PATH);
    }

    protected function encryptPayload()
    {
        $crypto = $this->pubnub->getCryptoSafe();
        if ($this->fileHandle) {
            $fileContent = stream_get_contents($this->fileHandle);
        } else {
            $fileContent = $this->fileContent;
        }

        if ($crypto) {
            return $crypto->encrypt($fileContent);
        }
        return $fileContent;
    }

    protected function getBoundary()
    {
        if (!isset($this->boundary)) {
            $this->boundary = '---' . PubNubUtil::uuid() . '---';
        }
        return $this->boundary;
    }

    protected function buildPayload($data, $fileName, $fileContent)
    {
        $boundary = $this->getBoundary();

        $payload = '';

        foreach ($data as $element) {
            $payload .= "--$boundary\r\n";
            $payload .= "Content-Disposition: form-data; name=\"{$element['key']}\"\r\n\r\n";
            $payload .= "{$element['value']}\r\n";
        }

        $payload .= "--$boundary\r\n";
        $payload .= "Content-Disposition: form-data; name=\"file\"; filename=\"{$fileName}\"\r\n\r\n";
        $payload .= "{$fileContent}\r\n";

        $payload .= "--$boundary--\r\n";

        return $payload;
    }

    protected function uploadFile()
    {

        $response = Requests::POST(
            $this->fileUploadEnvelope->getUrl(),
            ['Content-Type' => 'multipart/form-data; boundary=' . $this->getBoundary()],
            $this->buildPayload(
                $this->fileUploadEnvelope->getFormFields(),
                $this->fileName,
                $this->encryptPayload()
            )
        );
        return $response;
    }

    public function sync()
    {
        $this->fileUploadEnvelope = (new FetchFileUploadS3Data($this->pubnub))
            ->channel($this->channel)
            ->fileName($this->fileName)
            ->sync();

        try {
            $this->uploadFile();
        } catch (\Exception $e) {
            throw $e;
        }

        $publishRequest = new PublishFileMessage($this->pubnub);
        $publishRequest->channel($this->channel)
            ->fileId($this->fileUploadEnvelope->getFileId())
            ->fileName($this->fileName);

        if (isset($this->meta)) {
            $publishRequest->meta($this->meta);
        }
        if (isset($this->message)) {
            $publishRequest->message($this->message);
        }
        if (isset($this->shouldStore)) {
            $publishRequest->shouldStore($this->shouldStore);
        }
        if (isset($this->ttl)) {
            $publishRequest->ttl($this->ttl);
        }

        $publishResponse = $publishRequest->sync();

        return $publishResponse;
    }
}
