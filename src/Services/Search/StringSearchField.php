<?php

/**
 * StringSearchField represents a string data field that can be searched on.
 * @package openemr
 * @link      http://www.open-emr.org
 * @author    Stephen Nielson <stephen@nielson.org>
 * @copyright Copyright (c) 2021 Stephen Nielson <stephen@nielson.org>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

namespace OpenEMR\Services\Search;

use OpenEMR\Services\Search\SearchFieldType;

class StringSearchField extends BasicSearchField
{
    private const VALID_MODIFIERS = [SearchModifier::CONTAINS, SearchModifier::EXACT, SearchModifier::PREFIX, SearchModifier::MISSING];
    public function __construct($field, $values, $modifier = null, $isAnd = true)
    {
        if (array_search($modifier, self::VALID_MODIFIERS) === false) {
            $modifier = SearchModifier::PREFIX;
        }
        parent::__construct($field, SearchFieldType::STRING, $field, $values, $modifier, $isAnd);
    }
}
