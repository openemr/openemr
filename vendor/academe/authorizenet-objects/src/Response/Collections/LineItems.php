<?php

namespace Academe\AuthorizeNet\Response\Collections;

/**
 * Collection of response LineItems.
 */

use Academe\AuthorizeNet\Request\Collections\LineItems as RequestLineitems;
use Academe\AuthorizeNet\Response\Model\LineItem;

class LineItems extends RequestLineitems
{
    protected function hasExpectedStrictType($item)
    {
        // Make sure the item is the correct type, and is not empty.
        return $item instanceof LineItem && $item->hasAny();
    }
}
