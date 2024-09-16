<?php

namespace PubNub\Models\Consumer\Objects\UUID;

use JsonSerializable;

class PNUUIDMetadata implements JsonSerializable
{
    /** @var string */
    protected $id;

    /** @var string */
    protected $name;

    /** @var string */
    protected $externalId;

    /** @var string */
    protected $profileUrl;

    /** @var string */
    protected $email;

    /** @var array */
    protected $custom;

    /**
     * PNUUIDMetadata constructor.
     * @param string $id
     * @param string $name
     * @param array $custom
     * @param array $externalId
     * @param array $profileUrl
     * @param array $email
     */
    public function __construct($id, $name, $externalId, $profileUrl, $email, $custom)
    {
        $this->id = $id;
        $this->name = $name;
        $this->externalId = $externalId;
        $this->profileUrl = $profileUrl;
        $this->email = $email;
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
    public function getExternalId()
    {
        return $this->externalId;
    }

    /**
     * @return string
     */
    public function getProfileUrl()
    {
        return $this->profileUrl;
    }

    /**
     * @return string
     */
    public function getEmail()
    {
        return $this->email;
    }

    /**
     * @return array
     */
    public function getCustom()
    {
        return $this->custom;
    }
}
