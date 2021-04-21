<?php

/**
 * TokenSearchField represents a token data type that contains both a system and a code.
 * @package openemr
 * @link      http://www.open-emr.org
 * @author    Stephen Nielson <stephen@nielson.org>
 * @copyright Copyright (c) 2021 Stephen Nielson <stephen@nielson.org>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

namespace OpenEMR\Services\Search;

use OpenEMR\FHIR\R4\FHIRElement\FHIRCode;
use OpenEMR\FHIR\R4\FHIRElement\FHIRCoding;
use OpenEMR\Services\Search\SearchFieldType;

class TokenSearchField extends BasicSearchField
{
    public function __construct($field, $values)
    {
        parent::__construct($field, SearchFieldType::TOKEN, $field, $values);
    }

    public function setValues(array $values)
    {
        $convertedFields = [];

        foreach ($values as $value) {
            if ($value instanceof TokenSearchValue) {
                $convertedFields[] = $value;
                continue;
            }

            $convertedFields[] = $this->createTokenSearchValue($value);
        }
        parent::setValues($convertedFields);
    }

    private function createTokenSearchValue($value)
    {
        if (!is_string($value)) {
            throw new \InvalidArgumentException("Token value must be a valid string");
        }

        return TokenSearchValue::buildFromFHIRString($value);
    }
}
