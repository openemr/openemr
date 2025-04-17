<?php

/**
 * SearchFieldType represents the different parameter types that search parameters can be.  Each parameter
 * type has a different way they should be handled per the FHIR search specification.
 * @see https://www.hl7.org/fhir/search.html for more information
 * @package openemr
 * @link      http://www.open-emr.org
 * @author    Stephen Nielson <stephen@nielson.org>
 * @copyright Copyright (c) 2021 Stephen Nielson <stephen@nielson.org>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

namespace OpenEMR\Services\Search;

class SearchFieldType
{
    /**
     * @see https://www.hl7.org/fhir/search.html#table
     */
    const TOKEN = 'token';
    const DATE = 'date';
    const URI = 'uri';
    const STRING = 'string';
    const NUMBER = 'number';
    const COMPOSITE = "composite";

    // we added datetime so we could better distinguish between types of dates here.
    const DATETIME = 'datetime';

    const REFERENCE = 'reference';
}
