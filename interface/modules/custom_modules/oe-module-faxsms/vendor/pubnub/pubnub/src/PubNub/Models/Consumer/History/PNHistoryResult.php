<?php

namespace PubNub\Models\Consumer\History;

use PubNub\PubNubUtil;

/**
 * Class PNHistoryResult
 * @package PubNub\Models\Consumer\History
 */
class PNHistoryResult
{
    /** @var  PNHistoryItemResult[] */
    private $messages;

    /** @var  int */
    private $startTimetoken;

    /** @var  int */
    private $endTimetoken;

    /**
     * PNHistoryResult constructor.
     * @param PNHistoryItemResult[] $messages
     * @param int $startTimetoken
     * @param int $endTimetoken
     */
    public function __construct($messages, $startTimetoken, $endTimetoken)
    {
        $this->messages = $messages;
        $this->startTimetoken = $startTimetoken;
        $this->endTimetoken = $endTimetoken;
    }

    public function __toString()
    {
        return sprintf("History result for range %s..%s", $this->startTimetoken, $this->endTimetoken);
    }

    // TODO: $cipher refactoring
    /**
     * @param $jsonInput
     * @param $crypto
     * @param bool $includeTTOption
     * @param null $cipher
     * @return PNHistoryResult
     */
    public static function fromJson($jsonInput, $crypto, $includeTTOption = false, $cipher = null)
    {
        $rawItems = $jsonInput[0];
        $startTimetoken = $jsonInput[1];
        $endTimetoken = $jsonInput[2];
        $messages = [];

        foreach ($rawItems as $item) {
            if (PubNubUtil::isAssoc($item) && array_key_exists('timetoken', $item)
                                           && array_key_exists('message', $item)
                                           && $includeTTOption) {
                $message = new PNHistoryItemResult($item['message'], $crypto, $item['timetoken']);
            } else {
                $message = new PNHistoryItemResult($item, $crypto);
            }

            if ($cipher !== null) {
                $message->decrypt();
            }

            $messages[] = $message;
        }

        return new PNHistoryResult(
            $messages,
            $startTimetoken,
            $endTimetoken
        );
    }

    /**
     * @return PNHistoryItemResult[]
     */
    public function getMessages()
    {
        return $this->messages;
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