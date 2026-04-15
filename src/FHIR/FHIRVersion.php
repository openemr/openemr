<?php

declare(strict_types=1);

namespace OpenEMR\FHIR;

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


class FHIRVersion implements \JsonSerializable, \Stringable
{
    public const DSTU2_MIN_VERSION_INTEGER = 10000000;
    public const STU3_MIN_VERSION_INTEGER = 30000000;
    public const R4_MIN_VERSION_INTEGER = 40000000;
    public const R5_MIN_VERSION_INTEGER = 50000000;

    public function __construct(private readonly string $_versionName, private readonly string $_fhirSemanticVersion, private readonly string $_fhirShortVersion, private readonly int $_fhirVersionInteger)
    {
    }

    public function getName(): string
    {
        return $this->_versionName;
    }

    public function getFHIRSemanticVersion(): string
    {
        return $this->_fhirSemanticVersion;
    }

    public function getFHIRShortVersion(): string
    {
        return $this->_fhirShortVersion;
    }

    public function getFHIRVersionInteger(): int
    {
        return $this->_fhirVersionInteger;
    }

    public function jsonSerialize(): \stdClass
    {
        $out = new \stdClass();
        $out->versionName = $this->_versionName;
        $out->fhirSemanticVersion = $this->_fhirSemanticVersion;
        $out->fhirShortversion = $this->_fhirShortVersion;
        $out->fhirVersionInteger = $this->_fhirVersionInteger;
        return $out;
    }

    public function __toString(): string
    {
        return "{$this->_versionName} (FHIR {$this->_fhirSemanticVersion})";
    }
}
