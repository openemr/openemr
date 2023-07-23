<?php
/**
 * @package OpenEMR
 * @link      http://www.open-emr.org
 * @author    Ken Chapple <ken@mi-squared.com>
 * @copyright Copyright (c) 2021 Ken Chapple <ken@mi-squared.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU GeneralPublic License 3
 */

namespace OpenEMR\Cqm\Qdm\BaseTypes;

use Exception;

abstract class AbstractType implements \JsonSerializable
{
    public function __construct(array $properties = [])
    {
        foreach ($properties as $property => $value) {
            if ($this->propertyExists($property)) {
                $this->{$property} = $value;
            } else {
                throw new Exception("Property ${$property} does not exist on " . get_class($this));
            }
        }
    }

    #[\ReturnTypeWillChange]
    public function jsonSerialize()
    {
        $vars = get_object_vars($this);
        return $vars;
    }

    public function propertyExists($property)
    {
        $vars = get_object_vars($this);
        return property_exists($this, $property) || isset($vars);
    }
}
