<?php

namespace PubNub\Models\Consumer\FileSharing;

class PNFetchFileUploadS3DataResult
{
    protected $name;
    protected $fileId;
    protected $data;
    protected $url;
    protected $method;
    protected $expirationDate;
    protected $formFields;


    public function __construct($json)
    {
        $this->name = $json["data"]["name"];
        $this->fileId = $json["data"]["id"];
        $this->data = $json["file_upload_request"];
        $this->url = $json["file_upload_request"]["url"];
        $this->method = $json["file_upload_request"]["method"];
        $this->expirationDate = $json["file_upload_request"]["expiration_date"];
        $this->formFields = $json["file_upload_request"]["form_fields"];
    }

    public function __toString()
    {
        return sprintf("Fetch file upload S3 data success with id: %s", $this->fileId);
    }

    public function getName()
    {
        return $this->name;
    }

    public function getFileId()
    {
        return $this->fileId;
    }

    public function getData()
    {
        return $this->data;
    }

    public function getUrl()
    {
        return $this->url;
    }

    public function getMethod()
    {
        return $this->method;
    }

    public function getExpirationDate()
    {
        return $this->expirationDate;
    }

    public function getFormFields()
    {
        return $this->formFields;
    }
}
