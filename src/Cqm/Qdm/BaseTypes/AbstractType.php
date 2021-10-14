<?php

namespace OpenEMR\Cqm\Qdm\BaseTypes;

use Exception;

abstract class AbstractType implements \JsonSerializable
{
    public function __construct(array $properties = [])
    {
        foreach ($properties as $property => $value) {
            if (property_exists($this, $property)) {
                $this->{$property} = $value;
            } else {
                throw new Exception("Property ${$property} does not exist on " . get_class($this));
            }
        }
    }

    public function jsonSerialize()
    {
        $vars = get_object_vars($this);
        return $vars;
    }
}
