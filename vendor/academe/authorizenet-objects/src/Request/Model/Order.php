<?php

namespace Academe\AuthorizeNet\Request\Model;

/**
 *
 */

use Academe\AuthorizeNet\TransactionRequestInterface;
use Academe\AuthorizeNet\PaymentInterface;
use Academe\AuthorizeNet\AbstractModel;

class Order extends AbstractModel
{
    protected $invoiceNumber;
    protected $description;

    public function __construct($invoiceNumber = null, $description = null)
    {
        parent::__construct();

        $this->setInvoiceNumber($invoiceNumber);
        $this->setDescription($description);
    }

    public function hasAny()
    {
        return $this->hasInvoiceNumber() || $this->hasDescription();
    }

    public function jsonSerialize()
    {
        $data = [];

        if ($this->hasInvoiceNumber()) {
            $data['invoiceNumber'] = $this->getInvoiceNumber();
        }

        if ($this->hasDescription()) {
            $data['description'] = $this->getDescription();
        }

        return $data;
    }

    protected function setInvoiceNumber($value)
    {
        $this->invoiceNumber = $value;
    }

    protected function setDescription($value)
    {
        $this->description = $value;
    }
}
