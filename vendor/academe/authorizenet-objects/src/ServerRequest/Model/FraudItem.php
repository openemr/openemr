<?php

namespace Academe\AuthorizeNet\ServerRequest\Model;

/**
 * Single fraud item.
 */

use Academe\AuthorizeNet\Response\HasDataTrait;
use Academe\AuthorizeNet\AbstractModel;

class FraudItem extends AbstractModel
{
    use HasDataTrait;

    protected $fraudFilter;
    protected $fraudAction;

    public function __construct($data)
    {
        $this->setData($data);

        $this->setFraudFilter($this->getDataValue('fraudFilter'));
        $this->setFraudAction($this->getDataValue('fraudAction'));
    }

    public function jsonSerialize()
    {
        $data = [
            'fraudFilter' => $this->getFraudFilter(),
            'fraudAction' => $this->getFraudAction(),
        ];

        return $data;
    }

    protected function setFraudFilter($value)
    {
        $this->fraudFilter = $value;
    }

    protected function setFraudAction($value)
    {
        $this->fraudAction = $value;
    }

    public function hasAny()
    {
        return $this->fraudFilter !== null;
    }
}
