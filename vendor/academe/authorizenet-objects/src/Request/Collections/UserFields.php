<?php

namespace Academe\AuthorizeNet\Request\Collections;

/**
 *
 */

use Academe\AuthorizeNet\AbstractCollection;
use Academe\AuthorizeNet\Request\Model\UserField;

class UserFields extends AbstractCollection
{
    protected function hasExpectedStrictType($item)
    {
        // Make sure the item is the correct type, and is not empty.
        return $item instanceof UserField && $item->hasAny();
    }

    /**
     * The array of userFields needs to be wrapped by a single userField element.
     */
    public function jsonSerialize()
    {
        $data = parent::jsonSerialize();

        return ['userField' => $data];
    }
}
