<?php

/**
 * SearchFieldComparableValue.php Holds the pair of a search value being searched on and the search comparator
 * (equal, not equal, greater than, etc) that will be used for the search.
 *
 * @see \OpenEMR\Services\Search\SearchComparator for the types of comparators supported.
 * @package openemr
 * @link      http://www.open-emr.org
 * @author    Stephen Nielson <stephen@nielson.org>
 * @copyright Copyright (c) 2021 Stephen Nielson <stephen@nielson.org>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

namespace OpenEMR\Services\Search;

class SearchFieldComparableValue implements \Stringable
{
    /**
     * @param mixed $value
     * @param string $comparator
     */
    public function __construct(private $value, private $comparator = SearchComparator::EQUALS)
    {
        if (!SearchComparator::isValidComparator($this->comparator)) {
            throw new \InvalidArgumentException("Invalid comparator of '" . $this->comparator . "' found");
        }
    }

    /**
     * @return string
     */
    public function getComparator(): string
    {
        return $this->comparator;
    }

    /**
     * @return mixed
     */
    public function getValue()
    {
        return $this->value;
    }

    public function __toString(): string
    {
        $value = $this->getValue() || "";
        if (is_object($value)) {
            $value = method_exists($value, '__toString') ? $value->__toString() : $value::class;
        }
        return "(value=" . $value . ",comparator=" . $this->getComparator() ?? "" . ")";
    }

    public function __clone()
    {
        if (!empty($this->value) && is_object($this->value)) {
            $this->value = clone $this->value;
        }
    }
}
