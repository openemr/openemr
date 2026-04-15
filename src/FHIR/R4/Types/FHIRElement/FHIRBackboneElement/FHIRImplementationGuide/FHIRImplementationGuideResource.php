<?php

declare(strict_types=1);

namespace OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRImplementationGuide;

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
 *
 * FHIR Copyright Notice:
 *
 *   Copyright (c) 2011+, HL7, Inc.
 *   All rights reserved.
 *
 *   Redistribution and use in source and binary forms, with or without modification,
 *   are permitted provided that the following conditions are met:
 *
 *    * Redistributions of source code must retain the above copyright notice, this
 *      list of conditions and the following disclaimer.
 *    * Redistributions in binary form must reproduce the above copyright notice,
 *      this list of conditions and the following disclaimer in the documentation
 *      and/or other materials provided with the distribution.
 *    * Neither the name of HL7 nor the names of its contributors may be used to
 *      endorse or promote products derived from this software without specific
 *      prior written permission.
 *
 *   THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS "AS IS" AND
 *   ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT LIMITED TO, THE IMPLIED
 *   WARRANTIES OF MERCHANTABILITY AND FITNESS FOR A PARTICULAR PURPOSE ARE DISCLAIMED.
 *   IN NO EVENT SHALL THE COPYRIGHT HOLDER OR CONTRIBUTORS BE LIABLE FOR ANY DIRECT,
 *   INDIRECT, INCIDENTAL, SPECIAL, EXEMPLARY, OR CONSEQUENTIAL DAMAGES (INCLUDING, BUT
 *   NOT LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR SERVICES; LOSS OF USE, DATA, OR
 *   PROFITS; OR BUSINESS INTERRUPTION) HOWEVER CAUSED AND ON ANY THEORY OF LIABILITY,
 *   WHETHER IN CONTRACT, STRICT LIABILITY, OR TORT (INCLUDING NEGLIGENCE OR OTHERWISE)
 *   ARISING IN ANY WAY OUT OF THE USE OF THIS SOFTWARE, EVEN IF ADVISED OF THE
 *   POSSIBILITY OF SUCH DAMAGE.
 *
 *
 *   Generated on Fri, Nov 1, 2019 09:29+1100 for FHIR v4.0.1
 *
 *   Note: the schemas & schematrons do not contain all of the rules about what makes resources
 *   valid. Implementers will still need to be familiar with the content of the specification and with
 *   any profiles that apply to the resources in order to make a conformant implementation.
 *
 */
use OpenEMR\FHIR\Encoding\JSONSerializationOptionsTrait;
use OpenEMR\FHIR\Encoding\SerializeConfig;
use OpenEMR\FHIR\Encoding\UnserializeConfig;
use OpenEMR\FHIR\Encoding\ValueXMLLocationEnum;
use OpenEMR\FHIR\Encoding\XMLSerializationOptionsTrait;
use OpenEMR\FHIR\Encoding\XMLWriter;
use OpenEMR\FHIR\Types\ElementTypeInterface;
use OpenEMR\FHIR\Validation\Rules\MinOccursRule;
use OpenEMR\FHIR\Validation\TypeValidationsTrait;
use OpenEMR\FHIR\Versions\R4\Types\FHIRBooleanPrimitive;
use OpenEMR\FHIR\Versions\R4\Types\FHIRCanonicalPrimitive;
use OpenEMR\FHIR\Versions\R4\Types\FHIRCodePrimitive\FHIRFHIRVersionList;
use OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement;
use OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBoolean;
use OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRCanonical;
use OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRExtension;
use OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRFHIRVersion;
use OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRId;
use OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRReference;
use OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRString;
use OpenEMR\FHIR\Versions\R4\Types\FHIRIdPrimitive;
use OpenEMR\FHIR\Versions\R4\Types\FHIRStringPrimitive;
use OpenEMR\FHIR\Versions\R4\VersionConstants;

/**
 * A set of rules of how a particular interoperability or standards problem is
 * solved - typically through the use of FHIR resources. This resource is used to
 * gather all the parts of an implementation guide into a logical whole and to
 * publish a computable definition of all the parts.
 */
#[\Attribute(\Attribute::TARGET_PROPERTY)]
class FHIRImplementationGuideResource extends FHIRBackboneElement
{
    use TypeValidationsTrait,
        JSONSerializationOptionsTrait,
        XMLSerializationOptionsTrait;

    // name of FHIR type this class describes
    public const FHIR_TYPE_NAME = VersionConstants::TYPE_NAME_IMPLEMENTATION_GUIDE_DOT_RESOURCE;

    /* class_default.php:56 */
    public const FIELD_REFERENCE = 'reference';
    public const FIELD_FHIR_VERSION = 'fhirVersion';
    public const FIELD_FHIR_VERSION_EXT = '_fhirVersion';
    public const FIELD_NAME = 'name';
    public const FIELD_NAME_EXT = '_name';
    public const FIELD_DESCRIPTION = 'description';
    public const FIELD_DESCRIPTION_EXT = '_description';
    public const FIELD_EXAMPLE_BOOLEAN = 'exampleBoolean';
    public const FIELD_EXAMPLE_BOOLEAN_EXT = '_exampleBoolean';
    public const FIELD_EXAMPLE_CANONICAL = 'exampleCanonical';
    public const FIELD_EXAMPLE_CANONICAL_EXT = '_exampleCanonical';
    public const FIELD_GROUPING_ID = 'groupingId';
    public const FIELD_GROUPING_ID_EXT = '_groupingId';

    /* class_default.php:75 */
    private const _FHIR_VALIDATION_RULES = [
        self::FIELD_REFERENCE => [
            MinOccursRule::NAME => 1,
        ],
    ];

    /* class_default.php:96 */
    private array $_valueXMLLocations = [
        self::FIELD_NAME => ValueXMLLocationEnum::CONTAINER_ATTRIBUTE,
        self::FIELD_DESCRIPTION => ValueXMLLocationEnum::CONTAINER_ATTRIBUTE,
        self::FIELD_EXAMPLE_BOOLEAN => ValueXMLLocationEnum::CONTAINER_ATTRIBUTE,
        self::FIELD_EXAMPLE_CANONICAL => ValueXMLLocationEnum::CONTAINER_ATTRIBUTE,
        self::FIELD_GROUPING_ID => ValueXMLLocationEnum::CONTAINER_ATTRIBUTE,
    ];

    /* class_default.php:112 */
    /**
     * A reference from one resource to another.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * Where this resource is found.
     *
     * @var \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRReference
     */
    #[FHIRReference]
    protected FHIRReference $reference;
    /**
     * If the element is present, it must have either a \@value, an \@id, or extensions
     *
     * Indicates the FHIR Version(s) this artifact is intended to apply to. If no
     * versions are specified, the resource is assumed to apply to all the versions
     * stated in ImplementationGuide.fhirVersion.
     *
     * @var iterable<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRFHIRVersion>
     */
    #[FHIRFHIRVersion]
    protected array $fhirVersion;
    /**
     * A sequence of Unicode characters
     * Note that FHIR strings SHALL NOT exceed 1MB in size
     * If the element is present, it must have either a \@value, an \@id, or extensions
     *
     * A human assigned name for the resource. All resources SHOULD have a name, but
     * the name may be extracted from the resource (e.g. ValueSet.name).
     *
     * @var \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRString
     */
    #[FHIRString]
    protected FHIRString $name;
    /**
     * A sequence of Unicode characters
     * Note that FHIR strings SHALL NOT exceed 1MB in size
     * If the element is present, it must have either a \@value, an \@id, or extensions
     *
     * A description of the reason that a resource has been included in the
     * implementation guide.
     *
     * @var \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRString
     */
    #[FHIRString]
    protected FHIRString $description;
    /**
     * Value of "true" or "false"
     * If the element is present, it must have either a \@value, an \@id, or extensions
     *
     * If true or a reference, indicates the resource is an example instance. If a
     * reference is present, indicates that the example is an example of the specified
     * profile. (choose any one of example*, but only one)
     *
     * @var \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBoolean
     */
    #[FHIRBoolean]
    protected FHIRBoolean $exampleBoolean;
    /**
     * A URI that is a reference to a canonical URL on a FHIR resource
     * see [Canonical References](references.html#canonical)
     * If the element is present, it must have either a \@value, an \@id referenced from
     * the Narrative, or extensions
     *
     * If true or a reference, indicates the resource is an example instance. If a
     * reference is present, indicates that the example is an example of the specified
     * profile. (choose any one of example*, but only one)
     *
     * @var \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRCanonical
     */
    #[FHIRCanonical]
    protected FHIRCanonical $exampleCanonical;
    /**
     * Any combination of letters, numerals, "-" and ".", with a length limit of 64
     * characters. (This might be an integer, an unprefixed OID, UUID or any other
     * identifier pattern that meets these constraints.) Ids are case-insensitive.
     * RFC 4122
     * If the element is present, it must have either a \@value, an \@id referenced from
     * the Narrative, or extensions
     *
     * Reference to the id of the grouping this resource appears in.
     *
     * @var \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRId
     */
    #[FHIRId]
    protected FHIRId $groupingId;

    /* constructor.php:61 */
    /**
     * FHIRImplementationGuideResource Constructor
     * @param null|iterable<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRExtension> $extension
     * @param null|string|\OpenEMR\FHIR\Versions\R4\Types\FHIRStringPrimitive $id
     * @param null|iterable<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRExtension> $modifierExtension
     * @param null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRReference $reference
     * @param null|iterable<string>|iterable<\OpenEMR\FHIR\Versions\R4\Types\FHIRCodePrimitive\FHIRFHIRVersionList>|iterable<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRFHIRVersion> $fhirVersion
     * @param null|string|\OpenEMR\FHIR\Versions\R4\Types\FHIRStringPrimitive|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRString $name
     * @param null|string|\OpenEMR\FHIR\Versions\R4\Types\FHIRStringPrimitive|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRString $description
     * @param null|string|bool|\OpenEMR\FHIR\Versions\R4\Types\FHIRBooleanPrimitive|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBoolean $exampleBoolean
     * @param null|string|\OpenEMR\FHIR\Versions\R4\Types\FHIRCanonicalPrimitive|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRCanonical $exampleCanonical
     * @param null|string|\OpenEMR\FHIR\Versions\R4\Types\FHIRIdPrimitive|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRId $groupingId
     * @param null|string[] $fhirComments
     */
    public function __construct(null|iterable $extension = null,
                                null|string|FHIRStringPrimitive $id = null,
                                null|iterable $modifierExtension = null,
                                null|FHIRReference $reference = null,
                                null|iterable $fhirVersion = null,
                                null|string|FHIRStringPrimitive|FHIRString $name = null,
                                null|string|FHIRStringPrimitive|FHIRString $description = null,
                                null|string|bool|FHIRBooleanPrimitive|FHIRBoolean $exampleBoolean = null,
                                null|string|FHIRCanonicalPrimitive|FHIRCanonical $exampleCanonical = null,
                                null|string|FHIRIdPrimitive|FHIRId $groupingId = null,
                                null|iterable $fhirComments = null)
    {
        parent::__construct(extension: $extension,
                            id: $id,
                            modifierExtension: $modifierExtension,
                            fhirComments: $fhirComments);
        if (null !== $reference) {
            $this->setReference($reference);
        }
        if (null !== $fhirVersion) {
            $this->setFhirVersion(...$fhirVersion);
        }
        if (null !== $name) {
            $this->setName($name);
        }
        if (null !== $description) {
            $this->setDescription($description);
        }
        if (null !== $exampleBoolean) {
            $this->setExampleBoolean($exampleBoolean);
        }
        if (null !== $exampleCanonical) {
            $this->setExampleCanonical($exampleCanonical);
        }
        if (null !== $groupingId) {
            $this->setGroupingId($groupingId);
        }
    }

    /* class_default.php:145 */
    public function _getFHIRTypeName(): string
    {
        return self::FHIR_TYPE_NAME;
    }

    /* class_default.php:174 */
    /**
     * A reference from one resource to another.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * Where this resource is found.
     *
     * @return null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRReference
     */
    public function getReference(): null|FHIRReference
    {
        return $this->reference ?? null;
    }

    /**
     * A reference from one resource to another.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * Where this resource is found.
     *
     * @param null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRReference $reference
     * @return static
     */
    public function setReference(null|FHIRReference $reference): self
    {
        if (null === $reference) {
            unset($this->reference);
            return $this;
        }
        $this->reference = $reference;
        return $this;
    }

    /**
     * If the element is present, it must have either a \@value, an \@id, or extensions
     *
     * Indicates the FHIR Version(s) this artifact is intended to apply to. If no
     * versions are specified, the resource is assumed to apply to all the versions
     * stated in ImplementationGuide.fhirVersion.
     *
     * @return iterable<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRFHIRVersion>
     */
    public function getFhirVersion(): array
    {
        return $this->fhirVersion ?? [];
    }

    /**
     * @return \ArrayIterator<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRFHIRVersion>
     */
    public function getFhirVersionIterator(): iterable
    {
        if (!isset($this->fhirVersion)) {
            return new \EmptyIterator();
        }
        return new \ArrayIterator($this->fhirVersion);
    }

    /**
     * If the element is present, it must have either a \@value, an \@id, or extensions
     *
     * Indicates the FHIR Version(s) this artifact is intended to apply to. If no
     * versions are specified, the resource is assumed to apply to all the versions
     * stated in ImplementationGuide.fhirVersion.
     *
     * @param string|\OpenEMR\FHIR\Versions\R4\Types\FHIRCodePrimitive\FHIRFHIRVersionList|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRFHIRVersion $fhirVersion
     * @return static
     */
    public function addFhirVersion(string|FHIRFHIRVersionList|FHIRFHIRVersion $fhirVersion): self
    {
        if (!($fhirVersion instanceof FHIRFHIRVersion)) {
            $fhirVersion = new FHIRFHIRVersion(value: $fhirVersion);
        }
        if (!isset($this->fhirVersion)) {
            $this->fhirVersion = [];
        }
        $this->fhirVersion[] = $fhirVersion;
        return $this;
    }

    /**
     * If the element is present, it must have either a \@value, an \@id, or extensions
     *
     * Indicates the FHIR Version(s) this artifact is intended to apply to. If no
     * versions are specified, the resource is assumed to apply to all the versions
     * stated in ImplementationGuide.fhirVersion.
     *
     * @param string|\OpenEMR\FHIR\Versions\R4\Types\FHIRCodePrimitive\FHIRFHIRVersionList|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRFHIRVersion ...$fhirVersion
     * @return static
     */
    public function setFhirVersion(string|FHIRFHIRVersionList|FHIRFHIRVersion ...$fhirVersion): self
    {
        if ([] === $fhirVersion) {
            unset($this->fhirVersion);
            return $this;
        }
        $this->fhirVersion = [];
        foreach($fhirVersion as $v) {
            if ($v instanceof FHIRFHIRVersion) {
                $this->fhirVersion[] = $v;
            } else {
                $this->fhirVersion[] = new FHIRFHIRVersion(value: $v);
            }
        }
        return $this;
    }

    /**
     * A sequence of Unicode characters
     * Note that FHIR strings SHALL NOT exceed 1MB in size
     * If the element is present, it must have either a \@value, an \@id, or extensions
     *
     * A human assigned name for the resource. All resources SHOULD have a name, but
     * the name may be extracted from the resource (e.g. ValueSet.name).
     *
     * @return null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRString
     */
    public function getName(): null|FHIRString
    {
        return $this->name ?? null;
    }

    /**
     * A sequence of Unicode characters
     * Note that FHIR strings SHALL NOT exceed 1MB in size
     * If the element is present, it must have either a \@value, an \@id, or extensions
     *
     * A human assigned name for the resource. All resources SHOULD have a name, but
     * the name may be extracted from the resource (e.g. ValueSet.name).
     *
     * @param null|string|\OpenEMR\FHIR\Versions\R4\Types\FHIRStringPrimitive|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRString $name
     * @return static
     */
    public function setName(null|string|FHIRStringPrimitive|FHIRString $name): self
    {
        if (null === $name) {
            unset($this->name);
            return $this;
        }
        if (!($name instanceof FHIRString)) {
            $name = new FHIRString(value: $name);
        }
        $this->name = $name;
        return $this;
    }

    /**
     * A sequence of Unicode characters
     * Note that FHIR strings SHALL NOT exceed 1MB in size
     * If the element is present, it must have either a \@value, an \@id, or extensions
     *
     * A description of the reason that a resource has been included in the
     * implementation guide.
     *
     * @return null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRString
     */
    public function getDescription(): null|FHIRString
    {
        return $this->description ?? null;
    }

    /**
     * A sequence of Unicode characters
     * Note that FHIR strings SHALL NOT exceed 1MB in size
     * If the element is present, it must have either a \@value, an \@id, or extensions
     *
     * A description of the reason that a resource has been included in the
     * implementation guide.
     *
     * @param null|string|\OpenEMR\FHIR\Versions\R4\Types\FHIRStringPrimitive|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRString $description
     * @return static
     */
    public function setDescription(null|string|FHIRStringPrimitive|FHIRString $description): self
    {
        if (null === $description) {
            unset($this->description);
            return $this;
        }
        if (!($description instanceof FHIRString)) {
            $description = new FHIRString(value: $description);
        }
        $this->description = $description;
        return $this;
    }

    /**
     * Value of "true" or "false"
     * If the element is present, it must have either a \@value, an \@id, or extensions
     *
     * If true or a reference, indicates the resource is an example instance. If a
     * reference is present, indicates that the example is an example of the specified
     * profile. (choose any one of example*, but only one)
     *
     * @return null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBoolean
     */
    public function getExampleBoolean(): null|FHIRBoolean
    {
        return $this->exampleBoolean ?? null;
    }

    /**
     * Value of "true" or "false"
     * If the element is present, it must have either a \@value, an \@id, or extensions
     *
     * If true or a reference, indicates the resource is an example instance. If a
     * reference is present, indicates that the example is an example of the specified
     * profile. (choose any one of example*, but only one)
     *
     * @param null|string|bool|\OpenEMR\FHIR\Versions\R4\Types\FHIRBooleanPrimitive|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBoolean $exampleBoolean
     * @return static
     */
    public function setExampleBoolean(null|string|bool|FHIRBooleanPrimitive|FHIRBoolean $exampleBoolean): self
    {
        if (null === $exampleBoolean) {
            unset($this->exampleBoolean);
            return $this;
        }
        if (!($exampleBoolean instanceof FHIRBoolean)) {
            $exampleBoolean = new FHIRBoolean(value: $exampleBoolean);
        }
        $this->exampleBoolean = $exampleBoolean;
        return $this;
    }

    /**
     * A URI that is a reference to a canonical URL on a FHIR resource
     * see [Canonical References](references.html#canonical)
     * If the element is present, it must have either a \@value, an \@id referenced from
     * the Narrative, or extensions
     *
     * If true or a reference, indicates the resource is an example instance. If a
     * reference is present, indicates that the example is an example of the specified
     * profile. (choose any one of example*, but only one)
     *
     * @return null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRCanonical
     */
    public function getExampleCanonical(): null|FHIRCanonical
    {
        return $this->exampleCanonical ?? null;
    }

    /**
     * A URI that is a reference to a canonical URL on a FHIR resource
     * see [Canonical References](references.html#canonical)
     * If the element is present, it must have either a \@value, an \@id referenced from
     * the Narrative, or extensions
     *
     * If true or a reference, indicates the resource is an example instance. If a
     * reference is present, indicates that the example is an example of the specified
     * profile. (choose any one of example*, but only one)
     *
     * @param null|string|\OpenEMR\FHIR\Versions\R4\Types\FHIRCanonicalPrimitive|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRCanonical $exampleCanonical
     * @return static
     */
    public function setExampleCanonical(null|string|FHIRCanonicalPrimitive|FHIRCanonical $exampleCanonical): self
    {
        if (null === $exampleCanonical) {
            unset($this->exampleCanonical);
            return $this;
        }
        if (!($exampleCanonical instanceof FHIRCanonical)) {
            $exampleCanonical = new FHIRCanonical(value: $exampleCanonical);
        }
        $this->exampleCanonical = $exampleCanonical;
        return $this;
    }

    /**
     * Any combination of letters, numerals, "-" and ".", with a length limit of 64
     * characters. (This might be an integer, an unprefixed OID, UUID or any other
     * identifier pattern that meets these constraints.) Ids are case-insensitive.
     * RFC 4122
     * If the element is present, it must have either a \@value, an \@id referenced from
     * the Narrative, or extensions
     *
     * Reference to the id of the grouping this resource appears in.
     *
     * @return null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRId
     */
    public function getGroupingId(): null|FHIRId
    {
        return $this->groupingId ?? null;
    }

    /**
     * Any combination of letters, numerals, "-" and ".", with a length limit of 64
     * characters. (This might be an integer, an unprefixed OID, UUID or any other
     * identifier pattern that meets these constraints.) Ids are case-insensitive.
     * RFC 4122
     * If the element is present, it must have either a \@value, an \@id referenced from
     * the Narrative, or extensions
     *
     * Reference to the id of the grouping this resource appears in.
     *
     * @param null|string|\OpenEMR\FHIR\Versions\R4\Types\FHIRIdPrimitive|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRId $groupingId
     * @return static
     */
    public function setGroupingId(null|string|FHIRIdPrimitive|FHIRId $groupingId): self
    {
        if (null === $groupingId) {
            unset($this->groupingId);
            return $this;
        }
        if (!($groupingId instanceof FHIRId)) {
            $groupingId = new FHIRId(value: $groupingId);
        }
        $this->groupingId = $groupingId;
        return $this;
    }

    /* class_default.php:201 */
    /**
     * @param \SimpleXMLElement $element
     * @param \OpenEMR\FHIR\Encoding\UnserializeConfig $config
     * @param null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRImplementationGuide\FHIRImplementationGuideResource $type
     * @return \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRImplementationGuide\FHIRImplementationGuideResource
     * @throws \Exception
     */
    public static function xmlUnserialize(\SimpleXMLElement $element,
                                          UnserializeConfig $config,
                                          null|ElementTypeInterface $type = null): self
    {
        if (null === $type) {
            $type = new static();
        } else if (!($type instanceof FHIRImplementationGuideResource)) {
            throw new \RuntimeException(sprintf(
                '%s::xmlUnserialize - $type must be instance of \\%s or null, %s seen.',
                ltrim(substr(self::class, (int)strrpos(self::class, '\\')), '\\'),
                static::class,
                $type::class
            ));
        }
        foreach ($element->children() as $ce) {
            $cen = $ce->getName();
            if (self::FIELD_EXTENSION === $cen) {
                $type->addExtension(FHIRExtension::xmlUnserialize($ce, $config));
            } else if (self::FIELD_ID === $cen) {
                $va = $ce->attributes()[FHIRStringPrimitive::FIELD_VALUE] ?? null;
                if (null !== $va) {
                    $type->setId((string)$va);
                    $type->_setXMLFieldValueLocation(self::FIELD_ID, ValueXMLLocationEnum::ELEMENT_ATTRIBUTE);
                } else {
                    $type->setId((string)$ce);
                    $type->_setXMLFieldValueLocation(self::FIELD_ID, ValueXMLLocationEnum::ELEMENT_VALUE);
                }
            } else if (self::FIELD_MODIFIER_EXTENSION === $cen) {
                $type->addModifierExtension(FHIRExtension::xmlUnserialize($ce, $config));
            } else if (self::FIELD_REFERENCE === $cen) {
                $type->setReference(FHIRReference::xmlUnserialize($ce, $config));
            } else if (self::FIELD_FHIR_VERSION === $cen) {
                $type->addFhirVersion(FHIRFHIRVersion::xmlUnserialize($ce, $config));
            } else if (self::FIELD_NAME === $cen) {
                $type->setName(FHIRString::xmlUnserialize($ce, $config));
            } else if (self::FIELD_DESCRIPTION === $cen) {
                $type->setDescription(FHIRString::xmlUnserialize($ce, $config));
            } else if (self::FIELD_EXAMPLE_BOOLEAN === $cen) {
                $type->setExampleBoolean(FHIRBoolean::xmlUnserialize($ce, $config));
            } else if (self::FIELD_EXAMPLE_CANONICAL === $cen) {
                $type->setExampleCanonical(FHIRCanonical::xmlUnserialize($ce, $config));
            } else if (self::FIELD_GROUPING_ID === $cen) {
                $type->setGroupingId(FHIRId::xmlUnserialize($ce, $config));
            }
        }
        $attributes = $element->attributes();
        if (isset($attributes[self::FIELD_ID])) {
            $type->setId((string)$attributes[self::FIELD_ID]);
            $type->_setXMLFieldValueLocation(self::FIELD_ID, ValueXMLLocationEnum::PARENT_ATTRIBUTE);
        }
        if (isset($attributes[self::FIELD_NAME])) {
            if (isset($type->name)) {
                $type->name->setValue((string)$attributes[self::FIELD_NAME]);
            } else {
                $type->setName((string)$attributes[self::FIELD_NAME]);
            }
            $type->_setXMLFieldValueLocation(self::FIELD_NAME, ValueXMLLocationEnum::PARENT_ATTRIBUTE);
        }
        if (isset($attributes[self::FIELD_DESCRIPTION])) {
            if (isset($type->description)) {
                $type->description->setValue((string)$attributes[self::FIELD_DESCRIPTION]);
            } else {
                $type->setDescription((string)$attributes[self::FIELD_DESCRIPTION]);
            }
            $type->_setXMLFieldValueLocation(self::FIELD_DESCRIPTION, ValueXMLLocationEnum::PARENT_ATTRIBUTE);
        }
        if (isset($attributes[self::FIELD_EXAMPLE_BOOLEAN])) {
            if (isset($type->exampleBoolean)) {
                $type->exampleBoolean->setValue((string)$attributes[self::FIELD_EXAMPLE_BOOLEAN]);
            } else {
                $type->setExampleBoolean((string)$attributes[self::FIELD_EXAMPLE_BOOLEAN]);
            }
            $type->_setXMLFieldValueLocation(self::FIELD_EXAMPLE_BOOLEAN, ValueXMLLocationEnum::PARENT_ATTRIBUTE);
        }
        if (isset($attributes[self::FIELD_EXAMPLE_CANONICAL])) {
            if (isset($type->exampleCanonical)) {
                $type->exampleCanonical->setValue((string)$attributes[self::FIELD_EXAMPLE_CANONICAL]);
            } else {
                $type->setExampleCanonical((string)$attributes[self::FIELD_EXAMPLE_CANONICAL]);
            }
            $type->_setXMLFieldValueLocation(self::FIELD_EXAMPLE_CANONICAL, ValueXMLLocationEnum::PARENT_ATTRIBUTE);
        }
        if (isset($attributes[self::FIELD_GROUPING_ID])) {
            if (isset($type->groupingId)) {
                $type->groupingId->setValue((string)$attributes[self::FIELD_GROUPING_ID]);
            } else {
                $type->setGroupingId((string)$attributes[self::FIELD_GROUPING_ID]);
            }
            $type->_setXMLFieldValueLocation(self::FIELD_GROUPING_ID, ValueXMLLocationEnum::PARENT_ATTRIBUTE);
        }
        return $type;
    }

    /**
     * @param \OpenEMR\FHIR\Encoding\XMLWriter $xw
     * @param \OpenEMR\FHIR\Encoding\SerializeConfig $config
     */
    public function xmlSerialize(XMLWriter $xw,
                                 SerializeConfig $config): void
    {
        if (isset($this->name) && ValueXMLLocationEnum::PARENT_ATTRIBUTE === $this->_valueXMLLocations[self::FIELD_NAME]) {
            $xw->writeAttribute(self::FIELD_NAME, $this->name->_getValueAsString());
        }
        if (isset($this->description) && ValueXMLLocationEnum::PARENT_ATTRIBUTE === $this->_valueXMLLocations[self::FIELD_DESCRIPTION]) {
            $xw->writeAttribute(self::FIELD_DESCRIPTION, $this->description->_getValueAsString());
        }
        if (isset($this->exampleBoolean) && ValueXMLLocationEnum::PARENT_ATTRIBUTE === $this->_valueXMLLocations[self::FIELD_EXAMPLE_BOOLEAN]) {
            $xw->writeAttribute(self::FIELD_EXAMPLE_BOOLEAN, $this->exampleBoolean->_getValueAsString());
        }
        if (isset($this->exampleCanonical) && ValueXMLLocationEnum::PARENT_ATTRIBUTE === $this->_valueXMLLocations[self::FIELD_EXAMPLE_CANONICAL]) {
            $xw->writeAttribute(self::FIELD_EXAMPLE_CANONICAL, $this->exampleCanonical->_getValueAsString());
        }
        if (isset($this->groupingId) && ValueXMLLocationEnum::PARENT_ATTRIBUTE === $this->_valueXMLLocations[self::FIELD_GROUPING_ID]) {
            $xw->writeAttribute(self::FIELD_GROUPING_ID, $this->groupingId->_getValueAsString());
        }
        parent::xmlSerialize($xw, $config);
        if (isset($this->reference)) {
            $xw->startElement(self::FIELD_REFERENCE);
            $this->reference->xmlSerialize($xw, $config);
            $xw->endElement();
        }
        if (isset($this->fhirVersion) && [] !== $this->fhirVersion) {
            foreach($this->fhirVersion as $v) {
                $xw->startElement(self::FIELD_FHIR_VERSION);
                $v->xmlSerialize($xw, $config);
                $xw->endElement();
            }
        }
        if (isset($this->name)
            && (ValueXMLLocationEnum::PARENT_ATTRIBUTE !== $this->_valueXMLLocations[self::FIELD_NAME]
                || $this->name->_nonValueFieldDefined())) {
            $xw->startElement(self::FIELD_NAME);
            $this->name->xmlSerialize($xw, $config, $this->_valueXMLLocations[self::FIELD_NAME]);
            $xw->endElement();
        }
        if (isset($this->description)
            && (ValueXMLLocationEnum::PARENT_ATTRIBUTE !== $this->_valueXMLLocations[self::FIELD_DESCRIPTION]
                || $this->description->_nonValueFieldDefined())) {
            $xw->startElement(self::FIELD_DESCRIPTION);
            $this->description->xmlSerialize($xw, $config, $this->_valueXMLLocations[self::FIELD_DESCRIPTION]);
            $xw->endElement();
        }
        if (isset($this->exampleBoolean)
            && (ValueXMLLocationEnum::PARENT_ATTRIBUTE !== $this->_valueXMLLocations[self::FIELD_EXAMPLE_BOOLEAN]
                || $this->exampleBoolean->_nonValueFieldDefined())) {
            $xw->startElement(self::FIELD_EXAMPLE_BOOLEAN);
            $this->exampleBoolean->xmlSerialize($xw, $config, $this->_valueXMLLocations[self::FIELD_EXAMPLE_BOOLEAN]);
            $xw->endElement();
        }
        if (isset($this->exampleCanonical)
            && (ValueXMLLocationEnum::PARENT_ATTRIBUTE !== $this->_valueXMLLocations[self::FIELD_EXAMPLE_CANONICAL]
                || $this->exampleCanonical->_nonValueFieldDefined())) {
            $xw->startElement(self::FIELD_EXAMPLE_CANONICAL);
            $this->exampleCanonical->xmlSerialize($xw, $config, $this->_valueXMLLocations[self::FIELD_EXAMPLE_CANONICAL]);
            $xw->endElement();
        }
        if (isset($this->groupingId)
            && (ValueXMLLocationEnum::PARENT_ATTRIBUTE !== $this->_valueXMLLocations[self::FIELD_GROUPING_ID]
                || $this->groupingId->_nonValueFieldDefined())) {
            $xw->startElement(self::FIELD_GROUPING_ID);
            $this->groupingId->xmlSerialize($xw, $config, $this->_valueXMLLocations[self::FIELD_GROUPING_ID]);
            $xw->endElement();
        }
    }

    /**
     * @param \stdClass $decoded
     * @param \OpenEMR\FHIR\Encoding\UnserializeConfig $config
     * @param null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRImplementationGuide\FHIRImplementationGuideResource $type
     * @return \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRImplementationGuide\FHIRImplementationGuideResource
     * @throws \Exception
     */
    public static function jsonUnserialize(\stdClass $decoded,
                                           UnserializeConfig $config,
                                           null|ElementTypeInterface $type = null): self
    {
        if (null === $type) {
            if (isset($decoded->resourceType) && $decoded->resourceType !== static::FHIR_TYPE_NAME) {
                throw new \DomainException(sprintf(
                    '%s::jsonUnserialize - Cannot unmarshal data for resource type "%s" into this type.',
                    ltrim(substr(self::class, (int)strrpos(self::class, '\\')), '\\'),
                    $decoded->resourceType,
                ));
            }
            $type = new static();
        } else if (!($type instanceof FHIRImplementationGuideResource)) {
            throw new \RuntimeException(sprintf(
                '%s::jsonUnserialize - $type must be instance of \\%s or null, %s seen.',
                ltrim(substr(self::class, (int)strrpos(self::class, '\\')), '\\'),
                static::class,
                $type::class
            ));
        }
        parent::jsonUnserialize($decoded, $config, $type);
        if (isset($decoded->reference) || property_exists($decoded, self::FIELD_REFERENCE)) {
            if (is_array($decoded->reference)) {
                $type->setReference(FHIRReference::jsonUnserialize(reset($decoded->reference), $config));
            } else {
                $type->setReference(FHIRReference::jsonUnserialize($decoded->reference, $config));
            }
        }
        if (isset($decoded->fhirVersion)
            || isset($decoded->_fhirVersion)
            || property_exists($decoded, self::FIELD_FHIR_VERSION)
            || property_exists($decoded, self::FIELD_FHIR_VERSION_EXT)) {
            $vals = (array)($decoded->fhirVersion ?? []);
            $exts = (array)($decoded->_fhirVersion ?? []);
            $valCnt = count($vals);
            $extCnt = count($exts);
            if ($extCnt > $valCnt) {
                $valCnt = $extCnt;
            }
            for ($i = 0; $i < $valCnt; $i++) {
                $v = $exts[$i] ?? new \stdClass();
                $v->value = $vals[$i] ?? null;
                $type->addFhirVersion(FHIRFHIRVersion::jsonUnserialize($v, $config));
            }
        }
        if (isset($decoded->name)
            || isset($decoded->_name)
            || property_exists($decoded, self::FIELD_NAME)
            || property_exists($decoded, self::FIELD_NAME_EXT)) {
            $v = $decoded->_name ?? new \stdClass();
            $v->value = $decoded->name ?? null;
            $type->setName(FHIRString::jsonUnserialize($v, $config));
        }
        if (isset($decoded->description)
            || isset($decoded->_description)
            || property_exists($decoded, self::FIELD_DESCRIPTION)
            || property_exists($decoded, self::FIELD_DESCRIPTION_EXT)) {
            $v = $decoded->_description ?? new \stdClass();
            $v->value = $decoded->description ?? null;
            $type->setDescription(FHIRString::jsonUnserialize($v, $config));
        }
        if (isset($decoded->exampleBoolean)
            || isset($decoded->_exampleBoolean)
            || property_exists($decoded, self::FIELD_EXAMPLE_BOOLEAN)
            || property_exists($decoded, self::FIELD_EXAMPLE_BOOLEAN_EXT)) {
            $v = $decoded->_exampleBoolean ?? new \stdClass();
            $v->value = $decoded->exampleBoolean ?? null;
            $type->setExampleBoolean(FHIRBoolean::jsonUnserialize($v, $config));
        }
        if (isset($decoded->exampleCanonical)
            || isset($decoded->_exampleCanonical)
            || property_exists($decoded, self::FIELD_EXAMPLE_CANONICAL)
            || property_exists($decoded, self::FIELD_EXAMPLE_CANONICAL_EXT)) {
            $v = $decoded->_exampleCanonical ?? new \stdClass();
            $v->value = $decoded->exampleCanonical ?? null;
            $type->setExampleCanonical(FHIRCanonical::jsonUnserialize($v, $config));
        }
        if (isset($decoded->groupingId)
            || isset($decoded->_groupingId)
            || property_exists($decoded, self::FIELD_GROUPING_ID)
            || property_exists($decoded, self::FIELD_GROUPING_ID_EXT)) {
            $v = $decoded->_groupingId ?? new \stdClass();
            $v->value = $decoded->groupingId ?? null;
            $type->setGroupingId(FHIRId::jsonUnserialize($v, $config));
        }
        return $type;
    }

    /**
     * @return \stdClass
     */
    public function jsonSerialize(): mixed
    {
        $out = parent::jsonSerialize();
        if (isset($this->reference)) {
            $out->reference = $this->reference;
        }
        if (isset($this->fhirVersion) && [] !== $this->fhirVersion) {
            $vals = [];
            $exts = [];
            $hasVals = false;
            $hasExts = false;
            foreach ($this->fhirVersion as $v) {
                $val = $v->getValue();
                if (null !== $val) {
                    $hasVals = true;
                    $vals[] = $val;
                } else {
                    $vals[] = null;
                }
                if ($v->_nonValueFieldDefined()) {
                    $hasExts = true;
                    $ext = $v->jsonSerialize();
                    unset($ext->value);
                    $exts[] = $ext;
                } else {
                    $exts[] = null;
                }
            }
            if ($hasVals) {
                $out->fhirVersion = $vals;
            }
            if ($hasExts) {
                $out->_fhirVersion = $exts;
            }
        }
        if (isset($this->name)) {
            if (null !== ($val = $this->name->getValue())) {
                $out->name = $val;
            }
            if ($this->name->_nonValueFieldDefined()) {
                $ext = $this->name->jsonSerialize();
                unset($ext->value);
                $out->_name = $ext;
            }
        }
        if (isset($this->description)) {
            if (null !== ($val = $this->description->getValue())) {
                $out->description = $val;
            }
            if ($this->description->_nonValueFieldDefined()) {
                $ext = $this->description->jsonSerialize();
                unset($ext->value);
                $out->_description = $ext;
            }
        }
        if (isset($this->exampleBoolean)) {
            if (null !== ($val = $this->exampleBoolean->getValue())) {
                $out->exampleBoolean = $val;
            }
            if ($this->exampleBoolean->_nonValueFieldDefined()) {
                $ext = $this->exampleBoolean->jsonSerialize();
                unset($ext->value);
                $out->_exampleBoolean = $ext;
            }
        }
        if (isset($this->exampleCanonical)) {
            if (null !== ($val = $this->exampleCanonical->getValue())) {
                $out->exampleCanonical = $val;
            }
            if ($this->exampleCanonical->_nonValueFieldDefined()) {
                $ext = $this->exampleCanonical->jsonSerialize();
                unset($ext->value);
                $out->_exampleCanonical = $ext;
            }
        }
        if (isset($this->groupingId)) {
            if (null !== ($val = $this->groupingId->getValue())) {
                $out->groupingId = $val;
            }
            if ($this->groupingId->_nonValueFieldDefined()) {
                $ext = $this->groupingId->jsonSerialize();
                unset($ext->value);
                $out->_groupingId = $ext;
            }
        }
        return $out;
    }
}
