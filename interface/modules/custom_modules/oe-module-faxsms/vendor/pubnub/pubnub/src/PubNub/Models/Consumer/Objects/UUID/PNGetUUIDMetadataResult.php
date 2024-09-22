<?php

namespace PubNub\Models\Consumer\Objects\UUID;

class PNGetUUIDMetadataResult
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

    /** @var string */
    protected $updated;

    /** @var string */
    protected $eTag;

    /**
     * PNGetUUIDMetadataResult constructor.
     * @param string $id
     * @param string $name
     * @param array $externalId
     * @param array $profileUrl
     * @param array $email
     * @param array $custom
     * @param string $updated
     * @param string $eTag
     */
    function __construct($id, $name, $externalId, $profileUrl, $email, $custom = null, $updated = null, $eTag = null)
    {
        $this->id = $id;
        $this->name = $name;
        $this->externalId = $externalId;
        $this->profileUrl = $profileUrl;
        $this->email = $email;
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

        return sprintf("UUID metadata set: id: %s, name: %s, externalId: %s, profileUrl: %s, email: %s, custom: %s, updated: %s, eTag: %s",
        $this->id, $this->name, $this->externalId, $this->profileUrl, $this->email, "[" . $custom_string . "]", $this->updated, $this->eTag);
    }

    /**
     * @param array $payload
     * @return PNGetUUIDMetadataResult
     */
    public static function fromPayload(array $payload)
    {
        $meta = $payload["data"];
        $id = null;
        $name = null;
        $externalId = null;
        $profileUrl = null;
        $email = null;
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

        if (array_key_exists("externalId", $meta))
        {
            $externalId = $meta["externalId"];
        }

        if (array_key_exists("profileUrl", $meta))
        {
            $profileUrl = $meta["profileUrl"];
        }

        if (array_key_exists("email", $meta))
        {
            $email = $meta["email"];
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

        return new PNGetUUIDMetadataResult($id, $name, $externalId, $profileUrl, $email, (object) $custom, $updated, $eTag);
    }
}