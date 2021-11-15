<?php

namespace Academe\AuthorizeNet\Payment;

/**
 * TODO: optional dataKey needed for decyrpting Visa checkout data.
 */

use Academe\AuthorizeNet\PaymentInterface;
use Academe\AuthorizeNet\AbstractModel;

class OpaqueData extends AbstractModel implements PaymentInterface
{
    protected $dataDescriptor;
    protected $dataValue;

    public function __construct($dataDescriptor, $dataValue)
    {
        parent::__construct();

        $this->setDataDescriptor($dataDescriptor);
        $this->setDataValue($dataValue);
    }

    public function jsonSerialize()
    {
        $data = [
            'dataDescriptor' => $this->getDataDescriptor(),
            'dataValue' => $this->getDataValue(),
        ];

        return $data;
    }

    protected function setDataDescriptor($value)
    {
        $this->dataDescriptor = $value;
    }

    // 8192 characters base-64 encoded data.
    protected function setDataValue($value)
    {
        $this->dataValue = $value;
    }
}
