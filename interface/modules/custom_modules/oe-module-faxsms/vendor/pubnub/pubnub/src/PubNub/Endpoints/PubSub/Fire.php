<?php

namespace PubNub\Endpoints\PubSub;

use PubNub\Endpoints\Endpoint;
use PubNub\Enums\PNHttpMethod;
use PubNub\Enums\PNOperationType;
use PubNub\Exceptions\PubNubBuildRequestException;
use PubNub\Exceptions\PubNubValidationException;
use PubNub\Models\Consumer\PNPublishResult;
use PubNub\PubNubUtil;

class Fire extends Endpoint
{
    protected const SIGNAL_PATH = "/publish/%s/%s/0/%s/%s/%s";

    /** @var string $callback The JSONP callback name to wrap the function in. Use "0" for no callback */
    protected string $callback = "0";

    /** @var string $channel The channel name to perform the operation on */
    protected string $channel;

    /** @var mixed $message to publish */
    protected mixed $message;

    /** @var bool $usePost HTTP method instead of default GET  */
    protected bool $usePost = false;

    /** @var array $meta Meta data object which can be used with the filtering ability */
    protected $meta;


    /**
     * Set the JSONP callback name to wrap the function in. Use "0" for no callback
     *
     * @param string $callback The JSONP callback name to wrap the function in. Use 0 for no callback
     *
     * @return $this
     */
    public function callback($callback): self
    {
        $this->callback = $callback;

        return $this;
    }

    /**
     * set the fire message
     *
     * @param mixed $message
     *
     * @return $this
     */
    public function message($message)
    {
        $this->message = $message;

        return $this;
    }

    /**
     * Set the channel name to perform the operation on
     *
     * @param string $channel The channel name to perform the operation on
     *
     * @return $this
     */
    public function channel($channel)
    {
        $this->channel = $channel;

        return $this;
    }

    /**
     * Use POST to publish.
     *
     * @param bool $usePost
     *
     * @return $this
     */
    public function usePost(bool $usePost)
    {
        $this->usePost = $usePost;

        return $this;
    }

    /**
     * Meta data object which can be used with the filtering ability
     *
     * @param array $meta
     *
     * @return $this
     */
    public function meta($meta)
    {
        $this->meta = $meta;

        return $this;
    }

    /**
     * @throws PubNubValidationException
     */
    protected function validateParams()
    {
        if (!is_string($this->channel) || strlen($this->channel) === 0) {
            throw new PubNubValidationException("Channel Missing");
        }

        if (!is_string($this->callback) || strlen($this->callback) === 0) {
            throw new PubNubValidationException("Callback Missing");
        }

        $this->validateSubscribeKey();
        $this->validatePublishKey();
    }

    /**
     * @return string
     * @throws PubNubBuildRequestException
     */
    protected function buildPath()
    {
        return sprintf(
            static::SIGNAL_PATH,
            $this->pubnub->getConfiguration()->getPublishKey(),
            $this->pubnub->getConfiguration()->getSubscribeKey(),
            PubNubUtil::urlEncode($this->channel),
            PubNubUtil::urlEncode($this->callback),
            PubNubUtil::urlEncode(PubNubUtil::writeValueAsString($this->message)),
        );
    }


    protected function buildData()
    {
        return [];
    }

    protected function customParams()
    {
        $result = [
            "store" => "0",
            "norep" => "1",
        ];

        if ($this->meta) {
            $result['meta'] = PubNubUtil::urlEncode(PubNubUtil::writeValueAsString($this->meta));
        }
        return $result;
    }

    /**
     * @return PNPublishResult
     */
    public function sync()
    {
        return parent::sync();
    }

    /**
     * @param array $json Decoded json
     * @return PNPublishResult
     */
    protected function createResponse($json)
    {
        $timetoken = floatval($json[2]);

        return new PNPublishResult($timetoken);
    }

    /**
     * @return bool
     */
    protected function isAuthRequired()
    {
        return true;
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
        return PNHttpMethod::GET;
    }

    /**
     * @return int
     */
    protected function getOperationType()
    {
        return PNOperationType::PNSignalOperation;
    }

    /**
     * @return string
     */
    protected function getName()
    {
        return "Fire";
    }
}
