<?php

namespace Academe\AuthorizeNet\Request\Model;

/**
 *
 */

use Academe\AuthorizeNet\TransactionRequestInterface;
use Academe\AuthorizeNet\AmountInterface;
use Academe\AuthorizeNet\AbstractModel;

class LineItem extends AbstractModel
{
    protected $itemId;
    protected $name;
    protected $description;
    protected $quantity;
    protected $unitPrice;
    protected $taxable;

    public function __construct(
        $itemId = null,
        $name = null,
        $description = null,
        $quantity = null,
        AmountInterface $unitPrice = null,
        $taxable = null
    ) {
        parent::__construct();

        $this->setItemId($itemId);
        $this->setName($name);
        $this->setDescription($description);
        $this->setQuantity($quantity);
        $this->setUnitPrice($unitPrice);
        $this->setTaxable($taxable);
    }

    public function jsonSerialize()
    {
        $data = [];

        if ($this->hasItemId()) {
            $data['itemId'] = $this->getItemId();
        }

        if ($this->hasName()) {
            $data['name'] = $this->getName();
        }

        if ($this->hasDescription()) {
            $data['description'] = $this->getDescription();
        }

        if ($this->hasQuantity()) {
            $data['quantity'] = $this->getQuantity();
        }

        if ($this->hasUnitPrice()) {
            $data['unitPrice'] = $this->getUnitPrice();
        }

        if ($this->hasTaxable()) {
            $data['taxable'] = $this->getTaxable();
        }

        return $data;
    }

    public function hasAny()
    {
        return $this->hasItemId()
            || $this->hasName()
            || $this->hasDescription()
            || $this->hasQuantity()
            || $this->hasUnitPrice()
            || $this->hasTaxable();
    }

    protected function setItemId($value)
    {
        $this->itemId = $value;
    }

    protected function setName($value)
    {
        $this->name = $value;
    }

    protected function setDescription($value)
    {
        $this->description = $value;
    }

    protected function setQuantity($value)
    {
        // Hmm, we really don't want to be messing with floats like this.
        // The aim is to ensure the value goes to a maximum of 2dp.

        if ($value !== null) {
            $value = round((float)$value, 2);
        }

        $this->quantity = $value;
    }

    protected function setUnitPrice(AmountInterface $value = null)
    {
        $this->unitPrice = $value;
    }

    protected function setTaxable($value)
    {
        if ($value !== null) {
            $value = (bool)$value;
        }

        $this->taxable = $value;
    }
}
