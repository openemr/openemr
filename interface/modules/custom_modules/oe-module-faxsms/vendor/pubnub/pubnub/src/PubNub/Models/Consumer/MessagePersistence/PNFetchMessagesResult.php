<?php

namespace PubNub\Models\Consumer\MessagePersistence;

use PubNub\Models\Consumer\MessagePersistence\PNFetchMessagesItemResult;

class PNFetchMessagesResult
{
    private array $channels;

    /** @var  int */
    private $startTimetoken;

    /** @var  int */
    private $endTimetoken;


    public function __construct($channels, $startTimetoken, $endTimetoken)
    {
        $this->channels = $channels;
        $this->startTimetoken = $startTimetoken;
        $this->endTimetoken = $endTimetoken;
    }

    public function __toString()
    {
        return sprintf("Fetch messages result for range %d..%d", $this->startTimetoken, $this->endTimetoken);
    }

    public static function fromJson($jsonInput, $crypto, $startTimetoken, $endTimetoken)
    {
        $channels = [];

        foreach ($jsonInput['channels'] as $channel => $messages) {
            foreach ($messages as $item) {
                $channels[$channel][] = PNFetchMessagesItemResult::fromJson($item, $crypto);
            }
        }
        return new static($channels, $startTimetoken, $endTimetoken);
    }

    public function getChannels()
    {
        return $this->channels;
    }

    /**
     * @return int
     */
    public function getStartTimetoken()
    {
        return $this->startTimetoken;
    }

    /**
     * @return int
     */
    public function getEndTimetoken()
    {
        return $this->endTimetoken;
    }
}
