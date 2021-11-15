<?php

namespace Academe\AuthorizeNet\Request\Model;

/**
 *
 */

use Academe\AuthorizeNet\TransactionRequestInterface;
use Academe\AuthorizeNet\AmountInterface;
use Academe\AuthorizeNet\AbstractModel;

class UserField extends AbstractModel
{
    protected $name;
    protected $value;

    public function __construct(
        $name,
        $value
    ) {
        parent::__construct();

        $this->setName($name);
        $this->setValue($value);
    }

    public function jsonSerialize()
    {
        $data = [];

        $data['name'] = $this->getName();
        $data['value'] = $this->getValue();

        return $data;
    }

    public function hasAny()
    {
        return true;
    }

    protected function setName($value)
    {
        $this->name = $value;
    }

    protected function setValue($value)
    {
        $this->value = $value;
    }
}
