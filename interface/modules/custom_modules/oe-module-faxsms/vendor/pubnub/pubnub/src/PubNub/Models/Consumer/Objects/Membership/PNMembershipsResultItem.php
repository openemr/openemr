<?php

namespace PubNub\Models\Consumer\Objects\Membership;

class PNMembershipsResultItem
{
    /** @var PnMembership */
    protected $channel;

    /** @var array */
    protected $custom;

    /** @var string */
    protected $updated;

    /** @var string */
    protected $eTag;

    /**
     * PNMembershipsResultItem constructor.
     * @param PnMembership $channel
     * @param object $custom
     * @param string $updated
     * @param string $eTag
     */
    function __construct($channel, $custom, $updated, $eTag)
    {
        $this->channel = $channel;
        $this->custom = $custom;
        $this->updated = $updated;
        $this->eTag = $eTag;
    }

    /**
     * @return PNMembership
     */
    public function getChannel()
    {
        return $this->channel;
    }

    /**
     * @return array
     */
    public function getCustom()
    {
        return $this->custom;
    }

    /**
     * @return string
     */
    public function getUpdated()
    {
        return $this->updated;
    }

    /**
     * @return array
     */
    public function getETag()
    {
        return $this->eTag;
    }

    public function __toString()
    {
        if (!empty($data))
        {
          $data_string = json_encode($data);
        }

        return sprintf("channel: %s, custom: %s, updated: %s, eTag: %s",
            $this->channel, $this->custom, $this->updated, $this->eTag);
    }

    /**
     * @param array $payload
     * @return PNMembershipsResultItem
     */
    public static function fromPayload(array $payload)
    {
        $channel = null;
        $custom = null;
        $updated = null;
        $eTag = null;

        if (array_key_exists("channel", $payload))
        {
            $channel = PNMembership::fromPayload([ "data" => $payload["channel"] ]);
        }

        if (array_key_exists("custom", $payload))
        {
            $custom = $payload["custom"];
        }

        if (array_key_exists("updated", $payload))
        {
            $updated = $payload["updated"];
        }

        if (array_key_exists("eTag", $payload))
        {
            $eTag = $payload["eTag"];
        }

        return new PNMembershipsResultItem($channel, (object) $custom, $updated, $eTag);
    }
}