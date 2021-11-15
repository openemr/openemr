<?php

namespace Academe\AuthorizeNet\ServerRequest\Model;

/**
 * Single profile item.
 */

use Academe\AuthorizeNet\Response\HasDataTrait;
use Academe\AuthorizeNet\AbstractModel;

class Profile extends AbstractModel
{
    use HasDataTrait;

    protected $customerProfileId;
    protected $customerPaymentProfileId;
    protected $customerShippingAddressId;

    public function __construct($data)
    {
        $this->setData($data);

        $this->setCustomerProfileId($this->getDataValue('customerProfileId'));
        $this->setCustomerPaymentProfileId($this->getDataValue('customerPaymentProfileId'));
        $this->setCustomerShippingAddressId($this->getDataValue('customerShippingAddressId'));
    }

    public function jsonSerialize()
    {
        $data = [
            'customerProfileId' => $this->getCustomerProfileId(),
            'customerPaymentProfileId' => $this->getCustomerPaymentProfileId(),
            'customerShippingAddressId' => $this->getCustomerShippingAddressId(),
        ];

        return $data;
    }

    protected function setCustomerProfileId($value)
    {
        $this->customerProfileId = $value;
    }

    protected function setCustomerPaymentProfileId($value)
    {
        $this->customerPaymentProfileId = $value;
    }

    protected function setCustomerShippingAddressId($value)
    {
        $this->customerShippingAddressId = $value;
    }

    public function hasAny()
    {
        return $this->fraudFilter !== null;
    }
}
