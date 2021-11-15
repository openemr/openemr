<?php

namespace Academe\AuthorizeNet\ServerRequest\Collections;

/**
 * Collection of response UserFields.
 */

use Academe\AuthorizeNet\Response\HasDataTrait;
//use Academe\AuthorizeNet\Request\Collections\UserFields as RequestUserFields;
use Academe\AuthorizeNet\ServerRequest\Model\FraudItem;
use Academe\AuthorizeNet\AbstractCollection;

class FraudList extends AbstractCollection
{
    use HasDataTrait;

    public function __construct(array $data = [])
    {
        $this->setData($data);

        // An array of FraudItem records.

        foreach ($data as $fraudItem_data) {
            $this->push(new FraudItem($fraudItem_data));
        }
    }

    protected function hasExpectedStrictType($item)
    {
        // Make sure the item is the correct type, and is not empty.

        return $item instanceof FraudItem && $item->hasAny();
    }
}
