<?php

namespace Academe\AuthorizeNet\Response\Model;

/**
 * Single Transaction Response Error.
 */

class Error extends Message
{
    public function __construct($data)
    {
        $this->setData($data);

        $this->setCode($this->getDataValue('errorCode'));
        $this->setText($this->getDataValue('errorText'));
    }
}
