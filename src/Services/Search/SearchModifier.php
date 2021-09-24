<?php

/**
 * SearchModifier is an ENUM list of the types of search modifiers that can exist in the system.
 * @package openemr
 * @link      http://www.open-emr.org
 * @author    Stephen Nielson <stephen@nielson.org>
 * @copyright Copyright (c) 2021 Stephen Nielson <stephen@nielson.org>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

namespace OpenEMR\Services\Search;

final class SearchModifier
{
    public const CONTAINS = "contains";
    public const EXACT = "exact";
    public const PREFIX = "prefix"; // default for string
    public const MISSING = "missing";

    /**
     * ensures the search does not return if the values equal exactly
     * This is used internally in our services API, and does not conform to FHIR search specifiers.
     */
    public const NOT_EQUALS_EXACT = "not-in-exact";
}
