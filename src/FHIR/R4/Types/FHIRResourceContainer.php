<?php

declare(strict_types=1);

    namespace OpenEMR\FHIR\Versions\R4\Types;

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

use OpenEMR\FHIR\FHIRVersion;
use OpenEMR\FHIR\Types\ContainedTypeInterface;
use OpenEMR\FHIR\Types\ResourceContainerTypeInterface;
use OpenEMR\FHIR\Validation\TypeValidationsTrait;
use OpenEMR\FHIR\Versions\R4\Version;
use OpenEMR\FHIR\Versions\R4\VersionConstants;
use OpenEMR\FHIR\Versions\R4\VersionContainedTypeInterface;

#[\Attribute(\Attribute::TARGET_PROPERTY)]
class FHIRResourceContainer implements ResourceContainerTypeInterface
{
    use TypeValidationsTrait;

    public const FHIR_TYPE_NAME = VersionConstants::TYPE_NAME_RESOURCE_CONTAINER;

    private const _FHIR_VALIDATION_RULES = [];

    /** @var null|\OpenEMR\FHIR\Versions\R4\VersionContainedTypeInterface */
    private null|VersionContainedTypeInterface $containedType = null;

    public function __construct(null|VersionContainedTypeInterface $containedType = null)
    {
        if (null !== $containedType) {
            $this->setContainedType($containedType);
        }
    }

    public function _getFHIRTypeName(): string
    {
        return self::FHIR_TYPE_NAME;
    }

    /* class_resource_container.php:83 */
    public function _getFHIRVersion(): FHIRVersion
    {
        return Version::getFHIRVersion();
    }

    /**
     * @return null|\OpenEMR\FHIR\Versions\R4\VersionContainedTypeInterface
     */
    public function getContainedType(): null|ContainedTypeInterface
    {
        return $this->containedType ?? null;
    }

    /**
     * @param null|\OpenEMR\FHIR\Versions\R4\VersionContainedTypeInterface $containedType
     * @return static
     */
    public function setContainedType(null|ContainedTypeInterface $containedType): self
    {
        if (null === $containedType) {
            unset($this->containedType);
            return $this;
        }
        if (!($containedType instanceof VersionContainedTypeInterface)) {
            throw new \InvalidArgumentException(sprintf(
                'Contained type must implement "%s", provided type "%s" does not.',
                VersionContainedTypeInterface::class,
                $containedType::class,
            ));
        }
        $this->containedType = $containedType;
        return $this;
    }

    public function __toString(): string
    {
        return (string)($this->containedType ?? self::FHIR_TYPE_NAME);
    }

    /**
     * @return null|\OpenEMR\FHIR\Versions\R4\VersionContainedTypeInterface
     */
    public function jsonSerialize(): null|VersionContainedTypeInterface
    {
        return $this->containedType ?? null;
    }
}
