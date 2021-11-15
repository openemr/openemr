<?php

namespace Academe\AuthorizeNet\ServerRequest\Model;

/**
 * Single payment profile item.
 */

use Academe\AuthorizeNet\Response\HasDataTrait;
use Academe\AuthorizeNet\AbstractModel;

class CustomerPaymentProfile extends AbstractModel
{
    use HasDataTrait;

    protected $id;
    protected $customerType;

    public function __construct($data)
    {
        $this->setData($data);

        $this->setId($this->getDataValue('id'));
        $this->setCustomerType($this->getDataValue('customerType'));
    }

    public function jsonSerialize()
    {
        $data = [
            'id' => $this->getId(),
            'customerType' => $this->getCustomerType(),
        ];

        return $data;
    }

    protected function setId($value)
    {
        $this->id = $value;
    }

    protected function setCustomerType($value)
    {
        $this->customerType = $value;
    }

    public function hasAny()
    {
        return $this->id !== null;
    }
}
