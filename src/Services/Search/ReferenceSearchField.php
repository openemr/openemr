<?php

/**
 * ReferenceSearchField.php
 * @package openemr
 * @link      http://www.open-emr.org
 * @author    Stephen Nielson <stephen@nielson.org>
 * @copyright Copyright (c) 2021 Stephen Nielson <stephen@nielson.org>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

namespace OpenEMR\Services\Search;

use Psr\Log\InvalidArgumentException;

class ReferenceSearchField extends BasicSearchField
{
    /**
     * @var boolean
     */
    private $isUuid;

    public function __construct($field, $values, $isUuid = false)
    {
        $this->isUuid = $isUuid;
        parent::__construct($field, SearchFieldType::REFERENCE, $field, $values);
    }

    public function setValues(array $values)
    {
        $convertedFields = [];

        foreach ($values as $value) {
            if ($value instanceof ReferenceSearchValue) {
                $convertedFields[] = $value;
                continue;
            }

            $convertedFields[] = $this->createReferenceSearchValue($value);
        }
        parent::setValues($convertedFields);
    }

    /**
     * @param $value
     * @return ReferenceSearchValue
     * @throws InvalidArgumentException if $value is not a valid string
     */
    private function createReferenceSearchValue($value)
    {
        if (!is_string($value)) {
            throw new \InvalidArgumentException("Reference value must be a valid string");
        }

        return ReferenceSearchValue::createFromRelativeUri($value, $this->isUuid);
    }
}
