<?php

/**
 * StringSearchField represents a string data field that can be searched on.
 * @package openemr
 * @link      https://www.open-emr.org
 * @author    Stephen Nielson <stephen@nielson.org>
 * @copyright Copyright (c) 2021 Stephen Nielson <stephen@nielson.org>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

namespace OpenEMR\Services\Search;

use OpenEMR\Services\Search\SearchFieldType;

class StringSearchField extends BasicSearchField
{
    private const VALID_MODIFIERS = [SearchModifier::CONTAINS, SearchModifier::EXACT, SearchModifier::PREFIX
        , SearchModifier::SUFFIX, SearchModifier::MISSING, SearchModifier::NOT_EQUALS_EXACT];
    /**
     * @param bool $isLiteral True when a backslash, % or _ in a value is a plain character, as FHIR string search
     *     needs. Other callers may put a LIKE wildcard in a value on purpose, so it is false by default.
     */
    public function __construct(
        string $field,
        array|string|int $values,
        ?string $modifier = null,
        ?bool $isAnd = null,
        private readonly bool $isLiteral = false
    ) {
        if (array_search($modifier, self::VALID_MODIFIERS) === false) {
            $modifier = SearchModifier::PREFIX;
        }
        parent::__construct($field, SearchFieldType::STRING, $field, $values, $modifier);
        // backwards compatibility to let the isAnd parameter be overridden by the basic search
        // prior to this check $isAnd would default to true and break UNION search values
        if ($isAnd === true || $isAnd === false) {
            $this->setIsAnd($isAnd);
        }
    }

    public function isLiteral(): bool
    {
        return $this->isLiteral;
    }
}
