<?php

declare(strict_types=1);

namespace OpenEMR\FHIR\Versions;

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

use OpenEMR\FHIR\Types\TypeInterface;

interface VersionTypeMapInterface
{
    /**
     * Must return the full internal class map
     *
     * @return array
     */
    public static function getMap(): array;

    /**
     * Must return the full list of containable resource types
     *
     * @return array
     */
    public static function getContainableTypes(): array;

    /**
     * Must return the fully qualified class name for FHIR Type name.  Must return null if type not found.
     *
     * @param string|\stdClass|\SimpleXMLElement $input Must expect either name of type, or unserialized JSON or XML.
     * @return string|null
     */
    public static function getTypeClassname(string|\stdClass|\SimpleXMLElement $input): null|string;

    /**
     * Must attempt to return the fully qualified classname of a contained type from the provided input, if it
     * if it represents one.
     *
     * @param string|\stdClass|\SimpleXMLElement $input Expects either name of type or unserialized JSON or XML.
     * @return string|null Name of class as string or null if type is not contained in map
     */
    public static function getContainedTypeClassname(string|\stdClass|\SimpleXMLElement $input): null|string;

    /**
     * Must attempt to determine if the provided value is or describes a containable resource type
     *
     * @param string|\stdClass|\SimpleXMLElement|\OpenEMR\FHIR\Types\TypeInterface $input
     * @return bool
     */
    public static function isContainableType(string|\stdClass|\SimpleXMLElement|TypeInterface $input): bool;

    /**
     * @param \SimpleXMLElement $node Parent element containing inline resource
     * @return string Fully qualified class name of contained resource type
     */
    public static function mustGetContainedTypeClassnameFromXML(\SimpleXMLElement $node): string;

    /**
     * @param \stdClass $decoded
     * @return string Fully qualified class name of contained resource type
     */
    public static function mustGetContainedTypeClassnameFromJSON(\stdClass $decoded): string;
}
