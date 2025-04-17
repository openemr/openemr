<?php

/**
 * SearchComparator is essentially an ENUM class that holds the types of comparator
 * @package openemr
 * @link      http://www.open-emr.org
 * @author    Stephen Nielson <stephen@nielson.org>
 * @copyright Copyright (c) 2021 Stephen Nielson <stephen@nielson.org>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

namespace OpenEMR\Services\Search;

final class SearchComparator
{
    // This list comes from the FHIR search comparators for number, date, and quantity
    // @see http://hl7.org/fhir/R4/search.html#prefix
    public const EQUALS = "eq";
    public const NOT_EQUALS = "ne";
    public const GREATER_THAN = "gt";
    public const LESS_THAN = "lt";
    public const GREATER_THAN_OR_EQUAL_TO = "ge";
    public const LESS_THAN_OR_EQUAL_TO = "le";

    // these two options appear to be equivalent to gt & lt, not sure why we have them.
    public const STARTS_AFTER = "sa";
    public const ENDS_BEFORE = "eb";

    // we have this here for reference but we are currently not supporting aproximation which has a recommended
    // aproximation of 10%.
    public const APROXIMATELY_SAME = "ap";

    public const ALL_COMPARATORS = [self::EQUALS, self::NOT_EQUALS, self::GREATER_THAN, self::LESS_THAN
        , self::GREATER_THAN_OR_EQUAL_TO, self::LESS_THAN_OR_EQUAL_TO, self::STARTS_AFTER, self::ENDS_BEFORE];

    public static function isValidComparator($comparator)
    {
        return array_search($comparator, self::ALL_COMPARATORS) !== false;
    }
}
