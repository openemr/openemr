<?php

/**
 * ISearchField represents one more data field(s) in the OpenEMR system that a search will be conducted on.
 * It holds an array of values that will be used in the search (including any comparison operators), and other values
 * needed to conduct a search on that OpenEMR field(s).
 *
 * @package openemr
 * @link      http://www.open-emr.org
 * @author    Stephen Nielson <stephen@nielson.org>
 * @copyright Copyright (c) 2021 Stephen Nielson <stephen@nielson.org>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

namespace OpenEMR\Services\Search;

interface ISearchField
{
    /**
     * The name of the field in OpenEMR that will be searched on.
     * @return string
     */
    public function getField();

    /**
     * Represents the unique documented name of this search field.  When there is a 1:1 relationship between a FHIR
     * search field and an OpenEMR field it is often the same as the field name.
     * @return mixed
     */
    public function getName();

    /**
     * Array of search values.  Can be objects or primitive values based upon whatever the implementing class decides to
     * hold.
     * @return mixed[]
     */
    public function getValues();

    /**
     * @return string The search type as defined in the SearchFieldType class
     */
    public function getType();
}
