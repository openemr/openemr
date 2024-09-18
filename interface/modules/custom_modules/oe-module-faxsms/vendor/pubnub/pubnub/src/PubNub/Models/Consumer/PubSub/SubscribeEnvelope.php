<?php

namespace PubNub\Models\Consumer\PubSub;

use PubNub\Models\Server\SubscribeMessage;
use PubNub\Models\Server\SubscribeMetadata;


class SubscribeEnvelope
{
    /** @var SubscribeMessage*/
    protected $messages;

    /** @var  SubscribeMetadata */
    protected $metadata;

    /**
     * PNSubscribeResult constructor.
     * @param SubscribeMessage[] $messages
     * @param SubscribeMetadata $metadata
     */
    public function __construct(array $messages, $metadata)
    {
        $this->messages = $messages;
        $this->metadata = $metadata;
    }

    /**
     * @param $json
     * @return SubscribeEnvelope
     */
    public static function fromJson($json)
    {
        $messages = [];
        $metadata = SubscribeMetadata::fromJson($json['t']);

        try {
            foreach ($json['m'] as $message) {
                $messages[] = SubscribeMessage::fromJson($message);
            }
        } catch (\Exception $e) {
            $messages = [];
        }

        return new static($messages, $metadata);
    }

    /**
     * @return bool
     */
    public function isEmpty()
    {
        return count($this->messages) === 0;
    }

    /**
     * @return SubscribeMessage[]
     */
    public function getMessages()
    {
        return $this->messages;
    }

    /**
     * @return SubscribeMetadata
     */
    public function getMetadata()
    {
        return $this->metadata;
    }
}
