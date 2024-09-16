<?php

namespace PubNub\Models\Consumer\Objects\Channel;

class PNGetChannelMetadataResult
{
    /** @var string */
    protected $id;

    /** @var string */
    protected $name;

    /** @var string */
    protected $description;

    /** @var array */
    protected $custom;

    /** @var string */
    protected $updated;

    /** @var string */
    protected $eTag;

    /**
     * PNGetChannelMetadataResult constructor.
     * @param string $id
     * @param string $name
     * @param string $description
     * @param array $custom
     * @param string $updated
     * @param string $eTag
     */
    function __construct($id, $name, $description, $custom = null, $updated = null, $eTag = null)
    {
        $this->id = $id;
        $this->name = $name;
        $this->description = $description;
        $this->custom = $custom;
        $this->updated = $updated;
        $this->eTag = $eTag;
    }

    /**
     * @return string
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @return string
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * @return object
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
     * @return string
     */
    public function getETag()
    {
        return $this->eTag;
    }

    public function __toString()
    {
        $custom_string = "";
        
        foreach($this->custom as $key => $value) {
            if (strlen($custom_string) > 0) {
                $custom_string .= ", ";
            }

            $custom_string .=  "$key: $value";
        }
        
        return sprintf("Channel metadata set: id: %s, name: %s, description: %s, custom: %s, updated: %s, eTag: %s",
            $this->id, $this->name, $this->description, "[" . $custom_string . "]", $this->updated, $this->eTag);
    }

    /**
     * @param array $payload
     * @return PNGetChannelMetadataResult
     */
    public static function fromPayload(array $payload)
    {
        $meta = $payload["data"];
        $id = null;
        $name = null;
        $description = null;
        $custom = null;
        $updated = null;
        $eTag = null;

        if (array_key_exists("id", $meta))
        {
            $id = $meta["id"];
        }

        if (array_key_exists("name", $meta))
        {
            $name = $meta["name"];
        }

        if (array_key_exists("description", $meta))
        {
            $description = $meta["description"];
        }

        if (array_key_exists("custom", $meta))
        {
            $custom = (object)$meta["custom"];
        }

        if (array_key_exists("updated", $meta))
        {
            $updated = (object)$meta["updated"];
        }

        if (array_key_exists("eTag", $meta))
        {
            $eTag = (object)$meta["eTag"];
        }

        return new PNGetChannelMetadataResult($id, $name, $description, (object) $custom, $updated, $eTag);
    }
}