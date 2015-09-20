<?php

namespace Doctrine\Tests\Models\CMS;

/**
 * @EmbeddedDocument
 */
class CmsAddress
{
    /** @ No Id for embedded */
    public $id;
    /** @Field(type="string") */
    public $country;
    /** @Field(type="string") */
    public $zip;
    /** @Field(type="string") */
    public $city;
    /** @Field(type="string") */
    public $street;

    public function getId() {
        return $this->id;
    }

    public function getCountry() {
        return $this->country;
    }

    public function getZipCode() {
        return $this->zip;
    }

    public function getCity() {
        return $this->city;
    }
}