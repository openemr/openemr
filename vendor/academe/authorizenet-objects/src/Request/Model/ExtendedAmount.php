<?php

namespace Academe\AuthorizeNet\Request\Model;

/**
 *
 */

use Academe\AuthorizeNet\TransactionRequestInterface;
use Academe\AuthorizeNet\AmountInterface;
use Academe\AuthorizeNet\AbstractModel;

class ExtendedAmount extends AbstractModel
{
    protected $amount;
    protected $name;
    protected $description;

    public function __construct(
        AmountInterface $amount = null,
        $name = null,
        $description = null
    ) {
        parent::__construct();

        $this->setAmount($amount);
        $this->setName($name);
        $this->setDescription($description);
    }

    public function jsonSerialize()
    {
        $data = [];

        if ($this->hasAmount()) {
            $data['amount'] = $this->getAmount();
        }

        if ($this->hasName()) {
            $data['name'] = $this->getName();
        }

        if ($this->hasDescription()) {
            $data['description'] = $this->getDescription();
        }

        return $data;
    }

    public function hasAny()
    {
        return $this->hasAmount()
            || $this->hasName()
            || $this->hasDescription();
    }

    protected function setAmount(AmountInterface $value = null)
    {
        $this->amount = $value;
    }

    protected function setName($value = null)
    {
        $this->name = $value;
    }

    protected function setDescription($value = null)
    {
        $this->description = $value;
    }
}
