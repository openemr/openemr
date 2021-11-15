<?php

namespace Academe\AuthorizeNet\Request\Model;

/**
 * FIXME: the driversLicense is an object, not a scalar.
 * See https://github.com/academe/authorizenet-objects/issues/10
 */

use Academe\AuthorizeNet\TransactionRequestInterface;
use Academe\AuthorizeNet\AbstractModel;

class Customer extends AbstractModel
{
    const CUSTOMER_TYPE_INDIVIDUAL = 'individual';
    const CUSTOMER_TYPE_BUSINESS = 'business';

    protected $customerType;
    protected $id;
    protected $email;
    protected $driversLicense;
    protected $taxId;

    public function __construct(
        $customerType = null,
        $id = null,
        $email = null,
        $driversLicense = null,
        $taxId = null
    ) {
        parent::__construct();

        $this->setCustomerType($customerType);
        $this->setId($id);
        $this->setEmail($email);
        $this->setDriversLicense($driversLicense);
        $this->setTaxId($taxId);
    }

    public function jsonSerialize()
    {
        $data = [];

        if ($this->hasCustomerType()) {
            $data['type'] = $this->getCustomerType();
        }

        if ($this->hasId()) {
            $data['id'] = $this->getId();
        }

        if ($this->hasEmail()) {
            $data['email'] = $this->getEmail();
        }

        if ($this->hasDriversLicense()) {
            $data['driversLicense'] = $this->getDriversLicense();
        }

        if ($this->hasTaxId()) {
            $data['taxId'] = $this->getTaxId();
        }

        return $data;
    }

    public function hasAny()
    {
        return $this->hasCustomerType()
            || $this->hasId()
            || $this->hasEmail()
            || $this->hasDriversLicense()
            || $this->hasTaxId();
    }

    protected function setCustomerType($value)
    {
        $this->assertValueCustomerType($value);
        $this->customerType = $value;
    }

    protected function setId($value)
    {
        $this->id = $value;
    }

    protected function setEmail($value)
    {
        $this->email = $value;
    }

    protected function setDriversLicense($value)
    {
        $this->driversLicense = $value;
    }

    protected function setTaxId($value)
    {
        $this->taxId = $value;
    }
}
