<?php

declare(strict_types=1);

namespace OpenEMR\FHIR\Encoding;

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

trait JSONSerializationOptionsTrait
{
    /** @var array */
    private array $_fieldElideMap = [];

    /**
     * Declare the provided field must be serialized to JSON as an object, rather than an array of objects, when
     * it contains a singular element.
     *
     * @param string $field Name of field on this type.
     */
    public function _setJSONFieldElideSingletonArray(string $field, bool $elideSingleton): void
    {
        $this->_fieldElideMap[$field] = $elideSingleton;
    }

    /**
     * Returns whether the provided field should be JSON serialized as an object, rather than an array of objects, when
     * it contains a singular element.
     *
     * @return true
     */
    public function _getJSONFieldElideSingletonArray(string $field): bool
    {
        return $this->_fieldElideMap[$field] ?? false;
    }
}
