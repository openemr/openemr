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


interface ResourceContainerTypeInterface extends TypeInterface
{
    /**
     * Must return the contained resource, or null if one is not set.
     *
     * @return null|\OpenEMR\FHIR\Types\ContainedTypeInterface
     */
    public function getContainedType(): null|ContainedTypeInterface;

    /**
     * Set or unset the contained type.
     *
     * @param null|\OpenEMR\FHIR\Types\ContainedTypeInterface $containedType
     * @return static
     */
    public function setContainedType(null|ContainedTypeInterface $containedType): self;
}
