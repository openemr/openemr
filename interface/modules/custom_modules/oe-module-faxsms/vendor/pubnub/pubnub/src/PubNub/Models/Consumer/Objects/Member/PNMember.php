<?php

namespace PubNub\Models\Consumer\Objects\Member;

class PNMember
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
     * PNMember constructor.
     * @param string $id
     * @param string $name
     * @param string $externalId
     * @param string $profileUrl
     * @param string $email
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
        
        return sprintf("id: %s, custom: %s, updated: %s, eTag: %s",
            $this->id, "[" . $custom_string . "]", $this->updated, $this->eTag);
    }

    /**
     * @param array $payload
     * @return PNMember 
     */
    public static function fromPayload(array $payload)
    {
        $data = $payload["data"];
        $id = null;
        $name = null;
        $externalId = null;
        $profileUrl = null;
        $email = null;
        $custom = null;
        $updated = null;
        $eTag = null;

        if (array_key_exists("id", $data))
        {
            $id = $data["id"];
        }

        if (array_key_exists("name", $data))
        {
            $name = $data["name"];
        }

        if (array_key_exists("externalId", $data))
        {
            $externalId = $data["externalId"];
        }

        if (array_key_exists("profileUrl", $data))
        {
            $profileUrl = $data["profileUrl"];
        }

        if (array_key_exists("email", $data))
        {
            $email = $data["email"];
        }

        if (array_key_exists("custom", $data))
        {
            $custom = (object)$data["custom"];
        }

        if (array_key_exists("updated", $data))
        {
            $updated = (object)$data["updated"];
        }

        if (array_key_exists("eTag", $data))
        {
            $eTag = (object)$data["eTag"];
        }

        return new PNMember($id, $name, $externalId, $profileUrl, $email, (object) $custom, $updated, $eTag);
    }
}