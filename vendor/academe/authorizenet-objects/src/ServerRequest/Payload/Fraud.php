<?php

namespace Academe\AuthorizeNet\ServerRequest\Payload;

/**
 * The payment notification payload.
 */

use Academe\AuthorizeNet\ServerRequest\Payload\Payment;
use Academe\AuthorizeNet\ServerRequest\Collections\FraudList;

class Fraud extends Payment
{
    /**
     * Collection of fraud details
     */
    protected $fraudList;

    public function __construct($data)
    {
        parent::__construct($data);

        $fraudList = $this->getDataValue('fraudList');

        if ($fraudList) {
            $this->fraudList = new FraudList($fraudList);
        }
    }

    public function jsonSerialize()
    {
        $data = parent::jsonSerialize();

        $data['fraudList'] = $this->fraudList->toData(true);

        return $data;
    }

    /**
     *
     */
    protected function setFraudList($value)
    {
        $this->fraudList = $value;
    }
}
