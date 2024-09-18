<?php

namespace PubNub\Models\Consumer\Objects\Channel;

use JsonSerializable;

class PNChannelMetadata implements JsonSerializable
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
     * PNChannelMetadata constructor.
     * @param string $id
     * @param string $name
     * @param string $description
     * @param array $custom
     */
    public function __construct($id, $name, $description, $custom)
    {
        $this->id = $id;
        $this->name = $name;
        $this->description = $description;
        $this->custom = $custom;
    }

    public function jsonSerialize()
    {
        $vars = get_object_vars($this);

        return $vars;
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
     * @return array
     */
    public function getCustom()
    {
        return $this->custom;
    }
}
