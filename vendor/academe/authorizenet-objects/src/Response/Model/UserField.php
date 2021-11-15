<?php

namespace Academe\AuthorizeNet\Response\Model;

/**
 * The Response UserField is identical to the Request UserField.
 */

use Academe\AuthorizeNet\Request\Model\UserField as RequestUserField;

class UserField extends RequestUserField
{
    public function __construct(
        $name,
        $value = null
    ) {
        if (is_string($name) && is_string($value)) {
            parent::__construct($name, $value);
        }

        if (is_array($name) && is_null($value)) {
            parent::__construct($name['name'], $name['value']);
        }
    }
}


