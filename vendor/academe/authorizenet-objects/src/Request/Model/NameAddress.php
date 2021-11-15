<?php

namespace Academe\AuthorizeNet\Request\Model;

/**
 *
 */

use Academe\AuthorizeNet\TransactionRequestInterface;
use Academe\AuthorizeNet\AbstractModel;

class NameAddress extends AbstractModel
{
    protected $firstName;
    protected $lastName;
    protected $company;
    protected $address;
    protected $city;
    protected $state;
    protected $zip;
    protected $country;

    protected $phoneNumber;
    protected $faxNumber;

    public function __construct(
        $firstName = null,
        $lastName = null,
        $company = null,
        $address = null,
        $city = null,
        $state = null,
        $zip = null,
        $country = null
    ) {
        parent::__construct();

        $this->setFirstName($firstName);
        $this->setLastName($lastName);
        $this->setCompany($company);
        $this->setAddress($address);
        $this->setCity($city);
        $this->setState($state);
        $this->setZip($zip);
        $this->setCountry($country);
    }

    public function jsonSerialize()
    {
        $data = [];

        if ($this->hasFirstName()) {
            $data['firstName'] = $this->getFirstName();
        }

        if ($this->hasLastName()) {
            $data['lastName'] = $this->getLastName();
        }

        if ($this->hasCompany()) {
            $data['company'] = $this->getCompany();
        }

        if ($this->hasAddress()) {
            $data['address'] = $this->getAddress();
        }

        if ($this->hasCity()) {
            $data['city'] = $this->getCity();
        }

        if ($this->hasState()) {
            $data['state'] = $this->getState();
        }

        if ($this->hasZip()) {
            $data['zip'] = $this->getZip();
        }

        if ($this->hasCountry()) {
            $data['country'] = $this->getCountry();
        }

        if ($this->hasPhoneNumber()) {
            $data['phoneNumber'] = $this->getPhoneNumber();
        }

        if ($this->hasFaxNumber()) {
            $data['faxNumber'] = $this->getFaxNumber();
        }

        return $data;
    }

    public function hasAny()
    {
        return $this->hasFirstName()
            || $this->hasLastName()
            || $this->hasCompany()
            || $this->hasAddress()
            || $this->hasCity()
            || $this->hasState()
            || $this->hasZip()
            || $this->hasCountry()
            || $this->hasPhoneNumber()
            || $this->hasFaxNumber();
    }

    protected function setFirstName($value)
    {
        $this->firstName = $value;
    }

    protected function setLastName($value)
    {
        $this->lastName = $value;
    }

    protected function setCompany($value)
    {
        $this->company = $value;
    }

    protected function setAddress($value)
    {
        $this->address = $value;
    }

    protected function setCity($value)
    {
        $this->city = $value;
    }

    protected function setState($value)
    {
        $this->state = $value;
    }

    protected function setZip($value)
    {
        $this->zip = $value;
    }

    protected function setCountry($value)
    {
        $this->country = $value;
    }

    protected function setPhoneNumber($value)
    {
        $this->phoneNumber = $value;
    }

    protected function setFaxNumber($value)
    {
        $this->faxNumber = $value;
    }
}
