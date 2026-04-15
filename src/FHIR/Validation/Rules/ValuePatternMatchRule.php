<?php

declare(strict_types=1);

namespace OpenEMR\FHIR\Validation\Rules;

/*!
 * This class was generated with the PHPFHIR library (https://github.com/dcarbone/php-fhir) using
 * class definitions from HL7 FHIR (https://www.hl7.org/fhir/)
 *
 * Class creation date: April 15th, 2026 16:02+0000
 *
 * PHPFHIR Copyright:
 *
 * Copyright 2016-2026 Daniel Carbone (daniel.p.carbone@gmail.com)
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 *        http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 *
 */

use OpenEMR\FHIR\Types\PrimitiveTypeInterface;
use OpenEMR\FHIR\Types\TypeInterface;
use OpenEMR\FHIR\Validation\RuleInterface;

class ValuePatternMatchRule implements RuleInterface
{
    public const NAME = 'value_pattern_match';
    public const DESCRIPTION = 'Asserts that a given string value matches the specified pattern.';

    public function getName(): string
    {
        return self::NAME;
    }

    public function getDescription(): string
    {
        return self::DESCRIPTION;
    }

    public function assert(TypeInterface $type, string $field, mixed $constraint, mixed $value): null|string
    {
        if ('' === $constraint || null === $value) {
            return null;
        }
        if ($value instanceof PrimitiveTypeInterface) {
            $value = (string)$value;
        }
        try {
            $match = preg_match($constraint, (string) $value);
            if (PREG_NO_ERROR !== preg_last_error()) {
                return sprintf(
                    'Rule %s failed to verify type "%s" field "%s" value of size %d with pattern "%s": %s',
                    self::NAME,
                    $type->_getFHIRTypeName(),
                    $field,
                    strlen((string)$value),
                    $constraint,
                    preg_last_error_msg(),
                );
            } else if (!$match) {
                return sprintf('Field "%s" on type "%s" value of "%s" does not match pattern: %s', $field, $type->_getFHIRTypeName(), $value, $constraint);
            }
        } catch (\Throwable $e) {
            return sprintf('Rule %s failed to verify type "%s" field "%s" value with pattern "%s": %s', self::NAME, $type->_getFHIRTypeName(), $field, $constraint, $e->getMessage());
        }
        return null;
    }
}
