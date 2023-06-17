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

use OpenEMR\Services\Search\SearchFieldType;

class TokenSearchField extends BasicSearchField
{
    /**
     * @var boolean True if the token represents a UUID that is a binary field in the database
     */
    private $isUUID;

    public function __construct($field, $values, $isUUID = false)
    {
        $this->isUUID = $isUUID;
        parent::__construct($field, SearchFieldType::TOKEN, $field, $values);
    }

    public function isUuid()
    {
        return $this->isUUID;
    }

    public function addValue(TokenSearchValue $value)
    {
        $values = $this->getValues();
        $values[] = $value;
        $this->setValues($values);
    }

    public function hasCodeValue($code, $system = null)
    {
        $checkSystem = $system !== null;
        foreach ($this->getValues() as $tokenValue) {
            if ($tokenValue instanceof TokenSearchValue && $code == $tokenValue->getCode()) {
                if ($checkSystem) {
                    return $system == $tokenValue->getSystem();
                }
                return true;
            }
        }
        return false;
    }

    public function transformValues(callable $transformer)
    {
        if (!is_callable($transformer)) {
            throw new \BadMethodCallException("transformer function must be callable");
        }
        $values = $this->getValues() ?? [];
        $values = array_map($transformer, $values);
        $this->setValues($values);
        return $values;
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

        return TokenSearchValue::buildFromFHIRString($value, $this->isUUID);
    }
}
