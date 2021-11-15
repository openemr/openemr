<?php

namespace Academe\AuthorizeNet\Amount;

/**
 * Value object for the amount, with NO currency.
 * The currency is stored as it is delivered from the gateway,
 * as a float. The currency is unknown.
 */

use Academe\AuthorizeNet\AmountInterface;
use Academe\AuthorizeNet\AbstractModel;

class Simple extends AbstractModel implements AmountInterface
{
    protected $amount;

    public function __construct($amount = 0.0)
    {
        parent::__construct();

        $this->setAmount($amount);
    }

    public function getFormatted()
    {
        return $this->getAmount();
    }

    public function getCurrencyCode()
    {
        return null;
    }

    public function jsonSerialize()
    {
        return $this->getFormatted();
    }

    protected function setAmount($value)
    {
        $this->amount = $value;
    }
}
