<?php

namespace Academe\AuthorizeNet\Response\Collections;

/**
 * Collection of response messages, with an overall result code.
 */

use Academe\AuthorizeNet\Request\Model\HostedPaymentSetting;
use Academe\AuthorizeNet\Response\Model\SplitTenderPayment;
use Academe\AuthorizeNet\Response\HasDataTrait;
use Academe\AuthorizeNet\AbstractCollection;

class SplitTenderPayments extends AbstractCollection
{
    use HasDataTrait;

    public function __construct(array $data = [])
    {
        $this->setData($data);

        // An array of splitTenderPayment records.
        foreach ($this->getDataValue('splitTenderPayment') as $splitTenderPayment_data) {
            $this->push(new SplitTenderPayment($splitTenderPayment_data));
        }
    }

    protected function hasExpectedStrictType($item)
    {
        // Make sure the item is the correct type, and is not empty.
        return $item instanceof SplitTenderPayment;
    }
}
