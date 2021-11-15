<?php

namespace Omnipay\AuthorizeNetApi\Message;

use Academe\AuthorizeNet\AmountInterface;
use Academe\AuthorizeNet\Request\Transaction\AuthCapture;

class PurchaseRequest extends AuthorizeRequest
{
    /**
     * Create a new instance of the transaction object.
     */
    protected function createTransaction(AmountInterface $amount)
    {
        return new AuthCapture($amount);
    }
}
