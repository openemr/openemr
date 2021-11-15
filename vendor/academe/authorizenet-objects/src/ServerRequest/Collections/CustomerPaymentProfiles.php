<?php

namespace Academe\AuthorizeNet\ServerRequest\Collections;

/**
 * Collection of response UserFields.
 */

use Academe\AuthorizeNet\Response\HasDataTrait;
use Academe\AuthorizeNet\ServerRequest\Model\CustomerPaymentProfile;
use Academe\AuthorizeNet\AbstractCollection;

class CustomerPaymentProfiles extends AbstractCollection
{
    use HasDataTrait;

    public function __construct(array $data = [])
    {
        $this->setData($data);

        // An array of PaymentProfile records.

        foreach ($data as $item_data) {
            $this->push(new CustomerPaymentProfile($item_data));
        }
    }

    protected function hasExpectedStrictType($item)
    {
        // Make sure the item is the correct type, and is not empty.

        return $item instanceof CustomerPaymentProfile && $item->hasAny();
    }
}
