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

use OpenEMR\FHIR\Constants;
use OpenEMR\FHIR\Types\TypeInterface;
use OpenEMR\FHIR\Validation\RuleInterface;

class ValueMaxLengthRule implements RuleInterface
{
    public const NAME = 'value_max_length';
    public const DESCRIPTION = 'Asserts that a given string value is no more than x characters long.';

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
        if (Constants::UNLIMITED === $constraint || null === $value || '' === $value) {
            return null;
        }
        $len = strlen((string)$value);
        if ($constraint < $len) {
            return sprintf('Field "%s" on type "%s" must be no more than %d characters long, %d seen', $field, $type->_getFHIRTypeName(), $constraint, $len);
        }
        return null;
    }
}
