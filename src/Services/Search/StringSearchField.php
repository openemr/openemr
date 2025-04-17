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
    private const VALID_MODIFIERS = [SearchModifier::CONTAINS, SearchModifier::EXACT, SearchModifier::PREFIX, SearchModifier::MISSING, SearchModifier::NOT_EQUALS_EXACT];
    public function __construct($field, $values, $modifier = null, $isAnd = null)
    {
        if (array_search($modifier, self::VALID_MODIFIERS) === false) {
            $modifier = SearchModifier::PREFIX;
        }
        parent::__construct($field, SearchFieldType::STRING, $field, $values, $modifier);
        // backwards compatability to let the isAnd parameter be overridden by the basic search
        // prior to this check $isAnd would default to true and break UNION search values
        if ($isAnd === true || $isAnd === false) {
            $this->setIsAnd($isAnd);
        }
    }
}
