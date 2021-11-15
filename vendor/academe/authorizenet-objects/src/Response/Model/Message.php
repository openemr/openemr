<?php

namespace Academe\AuthorizeNet\Response\Model;

/**
 * Single Response message.
 * This is the top level of the response, not a message you would find
 * within a transacton response.
 */

use Academe\AuthorizeNet\Response\HasDataTrait;
use Academe\AuthorizeNet\AbstractModel;

class Message extends AbstractModel
{
    use HasDataTrait;

    protected $code;
    protected $text;

    public function __construct($data)
    {
        $this->setData($data);

        $this->setCode($this->getDataValue('code'));
        $this->setText($this->getDataValue('text'));
    }

    public function jsonSerialize()
    {
        $data = [
            'code' => $this->getCode(),
            'text' => $this->getText(),
        ];

        return $data;
    }

    protected function setCode($value)
    {
        $this->code = $value;
    }

    protected function setText($value)
    {
        $this->text = $value;
    }
}
