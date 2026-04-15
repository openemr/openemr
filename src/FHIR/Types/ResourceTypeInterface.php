<?php

declare(strict_types=1);

namespace OpenEMR\FHIR\Types;

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

use OpenEMR\FHIR\Encoding\SerializeConfig;
use OpenEMR\FHIR\Encoding\UnserializeConfig;
use OpenEMR\FHIR\Encoding\XMLWriter;

interface ResourceTypeInterface extends TypeInterface
{
    /**
     * Must return the root XMLNS value found in the source.  Null indicates no "xmlns" was found.  Only defined when
     * unserializing XML, and only used when serializing XML.
     *
     * @return null|string
     */
    public function _getSourceXMLNS(): null|string;

    /**
     * Must return the ID-containing element of this resource, if defined.
     *
     * @return null|\OpenEMR\FHIR\Types\ResourceIDTypeInterface
     */
    public function getId(): null|ResourceIDTypeInterface;

    /**
     * @param string|\SimpleXMLElement $element
     * @param null|\OpenEMR\FHIR\Encoding\UnserializeConfig $config
     * @param null|\OpenEMR\FHIR\Types\ResourceTypeInterface $type Instance of this class to unserialize into.  If left null, a new instance will be created.
     * @return static
     */
    public static function xmlUnserialize(string|\SimpleXMLElement $element,
                                          null|UnserializeConfig $config = null,
                                          null|ResourceTypeInterface $type = null): self;

    /**
     * @param null|\OpenEMR\FHIR\Encoding\XMLWriter $xw
     * @param null|\OpenEMR\FHIR\Encoding\SerializeConfig $config
     * @return \OpenEMR\FHIR\Encoding\XMLWriter
     */
    public function xmlSerialize(null|XMLWriter $xw = null,
                                 null|SerializeConfig $config = null): XMLWriter;

    /**
     * @param string|\stdClass $decoded Encoded or decoded JSON.
     * @param null|\OpenEMR\FHIR\Encoding\UnserializeConfig $config
     * @param null|\OpenEMR\FHIR\Types\ResourceTypeInterface $type Instance of this class to unserialize into.  If left null, a new instance will be created.
     * @return static
     */
    public static function jsonUnserialize(string|\stdClass $decoded,
                                           null|UnserializeConfig $config = null,
                                           null|ResourceTypeInterface $type = null): self;
}
