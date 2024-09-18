<?php

namespace PubNub\Models\Consumer\PubSub;

class PNMessageResult
{
    /** @var  array */
    private $message;

    /** @var  string */
    private $channel;

    /** @var  string */
    private $subscription;

    /** @var  int */
    private $timetoken;

    /** @var  string */
    private $publisher;

    private $error;

    /**
     * PNMessageResult constructor.
     * @param array $message
     * @param string $channel
     * @param string $subscription
     * @param int $timetoken
     * @param string $publisher
     */
    public function __construct($message, $channel, $subscription, $timetoken, $publisher, $error = null)
    {
        $this->message = $message;
        $this->channel = $channel;
        $this->subscription = $subscription;
        $this->timetoken = $timetoken;
        $this->publisher = $publisher;
        $this->error = $error;
    }

    /**
     * @return array
     */
    public function getMessage()
    {
        return $this->message;
    }

    /**
     * @return string
     */
    public function getChannel()
    {
        return $this->channel;
    }

    /**
     * @return string
     */
    public function getSubscription()
    {
        return $this->subscription;
    }

    /**
     * @return int
     */
    public function getTimetoken()
    {
        return $this->timetoken;
    }

    /**
     * @return string
     */
    public function getPublisher()
    {
        return $this->publisher;
    }

    public function isError(): bool
    {
        return !is_null($this->error);
    }

    public function getError()
    {
        return $this->error;
    }
}
