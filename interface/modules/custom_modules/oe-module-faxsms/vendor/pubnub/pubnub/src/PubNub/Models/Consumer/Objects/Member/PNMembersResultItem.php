<?php

namespace PubNub\Models\Consumer\Objects\Member;

class PNMembersResultItem
{
    /** @var PnMember */
    protected $uuid;

    /** @var array */
    protected $custom;

    /** @var string */
    protected $updated;

    /** @var string */
    protected $eTag;

    /**
     * PNMembersResultItem constructor.
     * @param PnMember $uuid
     * @param object $custom
     * @param string $updated
     * @param string $eTag
     */
    function __construct($uuid, $custom, $updated, $eTag)
    {
        $this->uuid = $uuid;
        $this->custom = $custom;
        $this->updated = $updated;
        $this->eTag = $eTag;
    }

    /**
     * @return PNMember
     */
    public function getUUID()
    {
        return $this->uuid;
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

        return sprintf("uuid: %s, custom: %s, updated: %s, eTag: %s",
            $this->uuid, $this->custom, $this->updated, $this->eTag);
    }

    /**
     * @param array $payload
     * @return PNMembersResultItem
     */
    public static function fromPayload(array $payload)
    {
        $uuid = null;
        $custom = null;
        $updated = null;
        $eTag = null;

        if (array_key_exists("uuid", $payload))
        {
            $uuid = PNMember::fromPayload([ "data" => $payload["uuid"] ]);
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

        return new PNMembersResultItem($uuid, (object) $custom, $updated, $eTag);
    }
}