<?php

namespace Academe\AuthorizeNet\Response\Model;

/**
 * Single Transaction Response message.
 */

class TransactionMessage extends Message
{
    public function __construct($data)
    {
        $this->setData($data);

        $this->setCode($this->getDataValue('code'));
        $this->setText($this->getDataValue('description'));
    }
}
