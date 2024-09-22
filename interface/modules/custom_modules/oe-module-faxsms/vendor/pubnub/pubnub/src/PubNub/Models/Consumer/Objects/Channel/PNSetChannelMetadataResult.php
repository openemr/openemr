<?php

namespace PubNub\Models\Consumer\Objects\Channel;

class PNSetChannelMetadataResult
{
    /** @var string */
    protected $id;

    /** @var string */
    protected $name;

    /** @var string */
    protected $description;

    /** @var array */
    protected $custom;

    /**
     * PNSetChannelMetadataResult constructor.
     * @param string $id
     * @param string $name
     * @param string $description
     * @param array $custom
     */
    function __construct($id, $name, $description, $custom = null)
    {
        $this->id = $id;
        $this->name = $name;
        $this->description = $description;
        $this->custom = $custom;
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

    public function __toString()
    {
        $custom_string = "";
        
        foreach($this->custom as $key => $value) {
            if (strlen($custom_string) > 0) {
                $custom_string .= ", ";
            }

            $custom_string .=  "$key: $value";
        }
        
        return sprintf("Channel metadata set: id: %s, name: %s, description: %s, custom: %s",
            $this->id, $this->name, $this->description, "[" . $custom_string . "]");
    }

    /**
     * @param array $payload
     * @return PNSetChannelMetadataResult
     */
    public static function fromPayload(array $payload)
    {
        $meta = $payload["data"];
        $id = null;
        $name = null;
        $description = null;
        $custom = null;

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

        return new PNSetChannelMetadataResult($id, $name, $description, (object) $custom);
    }
}