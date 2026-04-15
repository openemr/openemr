<?php

declare(strict_types=1);

namespace OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRSpecimenDefinition;

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
use OpenEMR\FHIR\Validation\TypeValidationsTrait;
use OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement;
use OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRCodeableConcept;
use OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRExtension;
use OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRQuantity;
use OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRString;
use OpenEMR\FHIR\Versions\R4\Types\FHIRStringPrimitive;
use OpenEMR\FHIR\Versions\R4\VersionConstants;

/**
 * A kind of specimen with associated set of requirements.
 */
#[\Attribute(\Attribute::TARGET_PROPERTY)]
class FHIRSpecimenDefinitionContainer extends FHIRBackboneElement
{
    use TypeValidationsTrait,
        JSONSerializationOptionsTrait,
        XMLSerializationOptionsTrait;

    // name of FHIR type this class describes
    public const FHIR_TYPE_NAME = VersionConstants::TYPE_NAME_SPECIMEN_DEFINITION_DOT_CONTAINER;

    /* class_default.php:56 */
    public const FIELD_MATERIAL = 'material';
    public const FIELD_TYPE = 'type';
    public const FIELD_CAP = 'cap';
    public const FIELD_DESCRIPTION = 'description';
    public const FIELD_DESCRIPTION_EXT = '_description';
    public const FIELD_CAPACITY = 'capacity';
    public const FIELD_MINIMUM_VOLUME_QUANTITY = 'minimumVolumeQuantity';
    public const FIELD_MINIMUM_VOLUME_STRING = 'minimumVolumeString';
    public const FIELD_MINIMUM_VOLUME_STRING_EXT = '_minimumVolumeString';
    public const FIELD_ADDITIVE = 'additive';
    public const FIELD_PREPARATION = 'preparation';
    public const FIELD_PREPARATION_EXT = '_preparation';

    /* class_default.php:75 */
    private const _FHIR_VALIDATION_RULES = [
    ];

    /* class_default.php:96 */
    private array $_valueXMLLocations = [
        self::FIELD_DESCRIPTION => ValueXMLLocationEnum::CONTAINER_ATTRIBUTE,
        self::FIELD_MINIMUM_VOLUME_STRING => ValueXMLLocationEnum::CONTAINER_ATTRIBUTE,
        self::FIELD_PREPARATION => ValueXMLLocationEnum::CONTAINER_ATTRIBUTE,
    ];

    /* class_default.php:112 */
    /**
     * A concept that may be defined by a formal reference to a terminology or ontology
     * or may be provided by text.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * The type of material of the container.
     *
     * @var \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRCodeableConcept
     */
    #[FHIRCodeableConcept]
    protected FHIRCodeableConcept $material;
    /**
     * A concept that may be defined by a formal reference to a terminology or ontology
     * or may be provided by text.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * The type of container used to contain this kind of specimen.
     *
     * @var \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRCodeableConcept
     */
    #[FHIRCodeableConcept]
    protected FHIRCodeableConcept $type;
    /**
     * A concept that may be defined by a formal reference to a terminology or ontology
     * or may be provided by text.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * Color of container cap.
     *
     * @var \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRCodeableConcept
     */
    #[FHIRCodeableConcept]
    protected FHIRCodeableConcept $cap;
    /**
     * A sequence of Unicode characters
     * Note that FHIR strings SHALL NOT exceed 1MB in size
     * If the element is present, it must have either a \@value, an \@id, or extensions
     *
     * The textual description of the kind of container.
     *
     * @var \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRString
     */
    #[FHIRString]
    protected FHIRString $description;
    /**
     * A measured amount (or an amount that can potentially be measured). Note that
     * measured amounts include amounts that are not precisely quantified, including
     * amounts involving arbitrary units and floating currencies.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * The capacity (volume or other measure) of this kind of container.
     *
     * @var \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRQuantity
     */
    #[FHIRQuantity]
    protected FHIRQuantity $capacity;
    /**
     * A measured amount (or an amount that can potentially be measured). Note that
     * measured amounts include amounts that are not precisely quantified, including
     * amounts involving arbitrary units and floating currencies.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * The minimum volume to be conditioned in the container. (choose any one of
     * minimumVolume*, but only one)
     *
     * @var \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRQuantity
     */
    #[FHIRQuantity]
    protected FHIRQuantity $minimumVolumeQuantity;
    /**
     * A sequence of Unicode characters
     * Note that FHIR strings SHALL NOT exceed 1MB in size
     * If the element is present, it must have either a \@value, an \@id, or extensions
     *
     * The minimum volume to be conditioned in the container. (choose any one of
     * minimumVolume*, but only one)
     *
     * @var \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRString
     */
    #[FHIRString]
    protected FHIRString $minimumVolumeString;
    /**
     * A kind of specimen with associated set of requirements.
     *
     * Substance introduced in the kind of container to preserve, maintain or enhance
     * the specimen. Examples: Formalin, Citrate, EDTA.
     *
     * @var iterable<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRSpecimenDefinition\FHIRSpecimenDefinitionAdditive>
     */
    #[FHIRSpecimenDefinitionAdditive]
    protected array $additive;
    /**
     * A sequence of Unicode characters
     * Note that FHIR strings SHALL NOT exceed 1MB in size
     * If the element is present, it must have either a \@value, an \@id, or extensions
     *
     * Special processing that should be applied to the container for this kind of
     * specimen.
     *
     * @var \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRString
     */
    #[FHIRString]
    protected FHIRString $preparation;

    /* constructor.php:61 */
    /**
     * FHIRSpecimenDefinitionContainer Constructor
     * @param null|iterable<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRExtension> $extension
     * @param null|string|\OpenEMR\FHIR\Versions\R4\Types\FHIRStringPrimitive $id
     * @param null|iterable<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRExtension> $modifierExtension
     * @param null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRCodeableConcept $material
     * @param null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRCodeableConcept $type
     * @param null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRCodeableConcept $cap
     * @param null|string|\OpenEMR\FHIR\Versions\R4\Types\FHIRStringPrimitive|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRString $description
     * @param null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRQuantity $capacity
     * @param null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRQuantity $minimumVolumeQuantity
     * @param null|string|\OpenEMR\FHIR\Versions\R4\Types\FHIRStringPrimitive|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRString $minimumVolumeString
     * @param null|iterable<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRSpecimenDefinition\FHIRSpecimenDefinitionAdditive> $additive
     * @param null|string|\OpenEMR\FHIR\Versions\R4\Types\FHIRStringPrimitive|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRString $preparation
     * @param null|string[] $fhirComments
     */
    public function __construct(null|iterable $extension = null,
                                null|string|FHIRStringPrimitive $id = null,
                                null|iterable $modifierExtension = null,
                                null|FHIRCodeableConcept $material = null,
                                null|FHIRCodeableConcept $type = null,
                                null|FHIRCodeableConcept $cap = null,
                                null|string|FHIRStringPrimitive|FHIRString $description = null,
                                null|FHIRQuantity $capacity = null,
                                null|FHIRQuantity $minimumVolumeQuantity = null,
                                null|string|FHIRStringPrimitive|FHIRString $minimumVolumeString = null,
                                null|iterable $additive = null,
                                null|string|FHIRStringPrimitive|FHIRString $preparation = null,
                                null|iterable $fhirComments = null)
    {
        parent::__construct(extension: $extension,
                            id: $id,
                            modifierExtension: $modifierExtension,
                            fhirComments: $fhirComments);
        if (null !== $material) {
            $this->setMaterial($material);
        }
        if (null !== $type) {
            $this->setType($type);
        }
        if (null !== $cap) {
            $this->setCap($cap);
        }
        if (null !== $description) {
            $this->setDescription($description);
        }
        if (null !== $capacity) {
            $this->setCapacity($capacity);
        }
        if (null !== $minimumVolumeQuantity) {
            $this->setMinimumVolumeQuantity($minimumVolumeQuantity);
        }
        if (null !== $minimumVolumeString) {
            $this->setMinimumVolumeString($minimumVolumeString);
        }
        if (null !== $additive) {
            $this->setAdditive(...$additive);
        }
        if (null !== $preparation) {
            $this->setPreparation($preparation);
        }
    }

    /* class_default.php:145 */
    public function _getFHIRTypeName(): string
    {
        return self::FHIR_TYPE_NAME;
    }

    /* class_default.php:174 */
    /**
     * A concept that may be defined by a formal reference to a terminology or ontology
     * or may be provided by text.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * The type of material of the container.
     *
     * @return null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRCodeableConcept
     */
    public function getMaterial(): null|FHIRCodeableConcept
    {
        return $this->material ?? null;
    }

    /**
     * A concept that may be defined by a formal reference to a terminology or ontology
     * or may be provided by text.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * The type of material of the container.
     *
     * @param null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRCodeableConcept $material
     * @return static
     */
    public function setMaterial(null|FHIRCodeableConcept $material): self
    {
        if (null === $material) {
            unset($this->material);
            return $this;
        }
        $this->material = $material;
        return $this;
    }

    /**
     * A concept that may be defined by a formal reference to a terminology or ontology
     * or may be provided by text.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * The type of container used to contain this kind of specimen.
     *
     * @return null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRCodeableConcept
     */
    public function getType(): null|FHIRCodeableConcept
    {
        return $this->type ?? null;
    }

    /**
     * A concept that may be defined by a formal reference to a terminology or ontology
     * or may be provided by text.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * The type of container used to contain this kind of specimen.
     *
     * @param null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRCodeableConcept $type
     * @return static
     */
    public function setType(null|FHIRCodeableConcept $type): self
    {
        if (null === $type) {
            unset($this->type);
            return $this;
        }
        $this->type = $type;
        return $this;
    }

    /**
     * A concept that may be defined by a formal reference to a terminology or ontology
     * or may be provided by text.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * Color of container cap.
     *
     * @return null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRCodeableConcept
     */
    public function getCap(): null|FHIRCodeableConcept
    {
        return $this->cap ?? null;
    }

    /**
     * A concept that may be defined by a formal reference to a terminology or ontology
     * or may be provided by text.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * Color of container cap.
     *
     * @param null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRCodeableConcept $cap
     * @return static
     */
    public function setCap(null|FHIRCodeableConcept $cap): self
    {
        if (null === $cap) {
            unset($this->cap);
            return $this;
        }
        $this->cap = $cap;
        return $this;
    }

    /**
     * A sequence of Unicode characters
     * Note that FHIR strings SHALL NOT exceed 1MB in size
     * If the element is present, it must have either a \@value, an \@id, or extensions
     *
     * The textual description of the kind of container.
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
     * The textual description of the kind of container.
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
     * A measured amount (or an amount that can potentially be measured). Note that
     * measured amounts include amounts that are not precisely quantified, including
     * amounts involving arbitrary units and floating currencies.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * The capacity (volume or other measure) of this kind of container.
     *
     * @return null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRQuantity
     */
    public function getCapacity(): null|FHIRQuantity
    {
        return $this->capacity ?? null;
    }

    /**
     * A measured amount (or an amount that can potentially be measured). Note that
     * measured amounts include amounts that are not precisely quantified, including
     * amounts involving arbitrary units and floating currencies.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * The capacity (volume or other measure) of this kind of container.
     *
     * @param null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRQuantity $capacity
     * @return static
     */
    public function setCapacity(null|FHIRQuantity $capacity): self
    {
        if (null === $capacity) {
            unset($this->capacity);
            return $this;
        }
        $this->capacity = $capacity;
        return $this;
    }

    /**
     * A measured amount (or an amount that can potentially be measured). Note that
     * measured amounts include amounts that are not precisely quantified, including
     * amounts involving arbitrary units and floating currencies.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * The minimum volume to be conditioned in the container. (choose any one of
     * minimumVolume*, but only one)
     *
     * @return null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRQuantity
     */
    public function getMinimumVolumeQuantity(): null|FHIRQuantity
    {
        return $this->minimumVolumeQuantity ?? null;
    }

    /**
     * A measured amount (or an amount that can potentially be measured). Note that
     * measured amounts include amounts that are not precisely quantified, including
     * amounts involving arbitrary units and floating currencies.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * The minimum volume to be conditioned in the container. (choose any one of
     * minimumVolume*, but only one)
     *
     * @param null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRQuantity $minimumVolumeQuantity
     * @return static
     */
    public function setMinimumVolumeQuantity(null|FHIRQuantity $minimumVolumeQuantity): self
    {
        if (null === $minimumVolumeQuantity) {
            unset($this->minimumVolumeQuantity);
            return $this;
        }
        $this->minimumVolumeQuantity = $minimumVolumeQuantity;
        return $this;
    }

    /**
     * A sequence of Unicode characters
     * Note that FHIR strings SHALL NOT exceed 1MB in size
     * If the element is present, it must have either a \@value, an \@id, or extensions
     *
     * The minimum volume to be conditioned in the container. (choose any one of
     * minimumVolume*, but only one)
     *
     * @return null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRString
     */
    public function getMinimumVolumeString(): null|FHIRString
    {
        return $this->minimumVolumeString ?? null;
    }

    /**
     * A sequence of Unicode characters
     * Note that FHIR strings SHALL NOT exceed 1MB in size
     * If the element is present, it must have either a \@value, an \@id, or extensions
     *
     * The minimum volume to be conditioned in the container. (choose any one of
     * minimumVolume*, but only one)
     *
     * @param null|string|\OpenEMR\FHIR\Versions\R4\Types\FHIRStringPrimitive|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRString $minimumVolumeString
     * @return static
     */
    public function setMinimumVolumeString(null|string|FHIRStringPrimitive|FHIRString $minimumVolumeString): self
    {
        if (null === $minimumVolumeString) {
            unset($this->minimumVolumeString);
            return $this;
        }
        if (!($minimumVolumeString instanceof FHIRString)) {
            $minimumVolumeString = new FHIRString(value: $minimumVolumeString);
        }
        $this->minimumVolumeString = $minimumVolumeString;
        return $this;
    }

    /**
     * A kind of specimen with associated set of requirements.
     *
     * Substance introduced in the kind of container to preserve, maintain or enhance
     * the specimen. Examples: Formalin, Citrate, EDTA.
     *
     * @return iterable<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRSpecimenDefinition\FHIRSpecimenDefinitionAdditive>
     */
    public function getAdditive(): array
    {
        return $this->additive ?? [];
    }

    /**
     * @return \ArrayIterator<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRSpecimenDefinition\FHIRSpecimenDefinitionAdditive>
     */
    public function getAdditiveIterator(): iterable
    {
        if (!isset($this->additive)) {
            return new \EmptyIterator();
        }
        return new \ArrayIterator($this->additive);
    }

    /**
     * A kind of specimen with associated set of requirements.
     *
     * Substance introduced in the kind of container to preserve, maintain or enhance
     * the specimen. Examples: Formalin, Citrate, EDTA.
     *
     * @param \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRSpecimenDefinition\FHIRSpecimenDefinitionAdditive $additive
     * @return static
     */
    public function addAdditive(FHIRSpecimenDefinitionAdditive $additive): self
    {
        if (!isset($this->additive)) {
            $this->additive = [];
        }
        $this->additive[] = $additive;
        return $this;
    }

    /**
     * A kind of specimen with associated set of requirements.
     *
     * Substance introduced in the kind of container to preserve, maintain or enhance
     * the specimen. Examples: Formalin, Citrate, EDTA.
     *
     * @param \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRSpecimenDefinition\FHIRSpecimenDefinitionAdditive ...$additive
     * @return static
     */
    public function setAdditive(FHIRSpecimenDefinitionAdditive ...$additive): self
    {
        if ([] === $additive) {
            unset($this->additive);
            return $this;
        }
        $this->additive = $additive;
        return $this;
    }

    /**
     * A sequence of Unicode characters
     * Note that FHIR strings SHALL NOT exceed 1MB in size
     * If the element is present, it must have either a \@value, an \@id, or extensions
     *
     * Special processing that should be applied to the container for this kind of
     * specimen.
     *
     * @return null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRString
     */
    public function getPreparation(): null|FHIRString
    {
        return $this->preparation ?? null;
    }

    /**
     * A sequence of Unicode characters
     * Note that FHIR strings SHALL NOT exceed 1MB in size
     * If the element is present, it must have either a \@value, an \@id, or extensions
     *
     * Special processing that should be applied to the container for this kind of
     * specimen.
     *
     * @param null|string|\OpenEMR\FHIR\Versions\R4\Types\FHIRStringPrimitive|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRString $preparation
     * @return static
     */
    public function setPreparation(null|string|FHIRStringPrimitive|FHIRString $preparation): self
    {
        if (null === $preparation) {
            unset($this->preparation);
            return $this;
        }
        if (!($preparation instanceof FHIRString)) {
            $preparation = new FHIRString(value: $preparation);
        }
        $this->preparation = $preparation;
        return $this;
    }

    /* class_default.php:201 */
    /**
     * @param \SimpleXMLElement $element
     * @param \OpenEMR\FHIR\Encoding\UnserializeConfig $config
     * @param null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRSpecimenDefinition\FHIRSpecimenDefinitionContainer $type
     * @return \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRSpecimenDefinition\FHIRSpecimenDefinitionContainer
     * @throws \Exception
     */
    public static function xmlUnserialize(\SimpleXMLElement $element,
                                          UnserializeConfig $config,
                                          null|ElementTypeInterface $type = null): self
    {
        if (null === $type) {
            $type = new static();
        } else if (!($type instanceof FHIRSpecimenDefinitionContainer)) {
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
            } else if (self::FIELD_MATERIAL === $cen) {
                $type->setMaterial(FHIRCodeableConcept::xmlUnserialize($ce, $config));
            } else if (self::FIELD_TYPE === $cen) {
                $type->setType(FHIRCodeableConcept::xmlUnserialize($ce, $config));
            } else if (self::FIELD_CAP === $cen) {
                $type->setCap(FHIRCodeableConcept::xmlUnserialize($ce, $config));
            } else if (self::FIELD_DESCRIPTION === $cen) {
                $type->setDescription(FHIRString::xmlUnserialize($ce, $config));
            } else if (self::FIELD_CAPACITY === $cen) {
                $type->setCapacity(FHIRQuantity::xmlUnserialize($ce, $config));
            } else if (self::FIELD_MINIMUM_VOLUME_QUANTITY === $cen) {
                $type->setMinimumVolumeQuantity(FHIRQuantity::xmlUnserialize($ce, $config));
            } else if (self::FIELD_MINIMUM_VOLUME_STRING === $cen) {
                $type->setMinimumVolumeString(FHIRString::xmlUnserialize($ce, $config));
            } else if (self::FIELD_ADDITIVE === $cen) {
                $type->addAdditive(FHIRSpecimenDefinitionAdditive::xmlUnserialize($ce, $config));
            } else if (self::FIELD_PREPARATION === $cen) {
                $type->setPreparation(FHIRString::xmlUnserialize($ce, $config));
            }
        }
        $attributes = $element->attributes();
        if (isset($attributes[self::FIELD_ID])) {
            $type->setId((string)$attributes[self::FIELD_ID]);
            $type->_setXMLFieldValueLocation(self::FIELD_ID, ValueXMLLocationEnum::PARENT_ATTRIBUTE);
        }
        if (isset($attributes[self::FIELD_DESCRIPTION])) {
            if (isset($type->description)) {
                $type->description->setValue((string)$attributes[self::FIELD_DESCRIPTION]);
            } else {
                $type->setDescription((string)$attributes[self::FIELD_DESCRIPTION]);
            }
            $type->_setXMLFieldValueLocation(self::FIELD_DESCRIPTION, ValueXMLLocationEnum::PARENT_ATTRIBUTE);
        }
        if (isset($attributes[self::FIELD_MINIMUM_VOLUME_STRING])) {
            if (isset($type->minimumVolumeString)) {
                $type->minimumVolumeString->setValue((string)$attributes[self::FIELD_MINIMUM_VOLUME_STRING]);
            } else {
                $type->setMinimumVolumeString((string)$attributes[self::FIELD_MINIMUM_VOLUME_STRING]);
            }
            $type->_setXMLFieldValueLocation(self::FIELD_MINIMUM_VOLUME_STRING, ValueXMLLocationEnum::PARENT_ATTRIBUTE);
        }
        if (isset($attributes[self::FIELD_PREPARATION])) {
            if (isset($type->preparation)) {
                $type->preparation->setValue((string)$attributes[self::FIELD_PREPARATION]);
            } else {
                $type->setPreparation((string)$attributes[self::FIELD_PREPARATION]);
            }
            $type->_setXMLFieldValueLocation(self::FIELD_PREPARATION, ValueXMLLocationEnum::PARENT_ATTRIBUTE);
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
        if (isset($this->description) && ValueXMLLocationEnum::PARENT_ATTRIBUTE === $this->_valueXMLLocations[self::FIELD_DESCRIPTION]) {
            $xw->writeAttribute(self::FIELD_DESCRIPTION, $this->description->_getValueAsString());
        }
        if (isset($this->minimumVolumeString) && ValueXMLLocationEnum::PARENT_ATTRIBUTE === $this->_valueXMLLocations[self::FIELD_MINIMUM_VOLUME_STRING]) {
            $xw->writeAttribute(self::FIELD_MINIMUM_VOLUME_STRING, $this->minimumVolumeString->_getValueAsString());
        }
        if (isset($this->preparation) && ValueXMLLocationEnum::PARENT_ATTRIBUTE === $this->_valueXMLLocations[self::FIELD_PREPARATION]) {
            $xw->writeAttribute(self::FIELD_PREPARATION, $this->preparation->_getValueAsString());
        }
        parent::xmlSerialize($xw, $config);
        if (isset($this->material)) {
            $xw->startElement(self::FIELD_MATERIAL);
            $this->material->xmlSerialize($xw, $config);
            $xw->endElement();
        }
        if (isset($this->type)) {
            $xw->startElement(self::FIELD_TYPE);
            $this->type->xmlSerialize($xw, $config);
            $xw->endElement();
        }
        if (isset($this->cap)) {
            $xw->startElement(self::FIELD_CAP);
            $this->cap->xmlSerialize($xw, $config);
            $xw->endElement();
        }
        if (isset($this->description)
            && (ValueXMLLocationEnum::PARENT_ATTRIBUTE !== $this->_valueXMLLocations[self::FIELD_DESCRIPTION]
                || $this->description->_nonValueFieldDefined())) {
            $xw->startElement(self::FIELD_DESCRIPTION);
            $this->description->xmlSerialize($xw, $config, $this->_valueXMLLocations[self::FIELD_DESCRIPTION]);
            $xw->endElement();
        }
        if (isset($this->capacity)) {
            $xw->startElement(self::FIELD_CAPACITY);
            $this->capacity->xmlSerialize($xw, $config);
            $xw->endElement();
        }
        if (isset($this->minimumVolumeQuantity)) {
            $xw->startElement(self::FIELD_MINIMUM_VOLUME_QUANTITY);
            $this->minimumVolumeQuantity->xmlSerialize($xw, $config);
            $xw->endElement();
        }
        if (isset($this->minimumVolumeString)
            && (ValueXMLLocationEnum::PARENT_ATTRIBUTE !== $this->_valueXMLLocations[self::FIELD_MINIMUM_VOLUME_STRING]
                || $this->minimumVolumeString->_nonValueFieldDefined())) {
            $xw->startElement(self::FIELD_MINIMUM_VOLUME_STRING);
            $this->minimumVolumeString->xmlSerialize($xw, $config, $this->_valueXMLLocations[self::FIELD_MINIMUM_VOLUME_STRING]);
            $xw->endElement();
        }
        if (isset($this->additive)) {
            foreach ($this->additive as $v) {
                $xw->startElement(self::FIELD_ADDITIVE);
                $v->xmlSerialize($xw, $config);
                $xw->endElement();
            }
        }
        if (isset($this->preparation)
            && (ValueXMLLocationEnum::PARENT_ATTRIBUTE !== $this->_valueXMLLocations[self::FIELD_PREPARATION]
                || $this->preparation->_nonValueFieldDefined())) {
            $xw->startElement(self::FIELD_PREPARATION);
            $this->preparation->xmlSerialize($xw, $config, $this->_valueXMLLocations[self::FIELD_PREPARATION]);
            $xw->endElement();
        }
    }

    /**
     * @param \stdClass $decoded
     * @param \OpenEMR\FHIR\Encoding\UnserializeConfig $config
     * @param null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRSpecimenDefinition\FHIRSpecimenDefinitionContainer $type
     * @return \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRSpecimenDefinition\FHIRSpecimenDefinitionContainer
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
        } else if (!($type instanceof FHIRSpecimenDefinitionContainer)) {
            throw new \RuntimeException(sprintf(
                '%s::jsonUnserialize - $type must be instance of \\%s or null, %s seen.',
                ltrim(substr(self::class, (int)strrpos(self::class, '\\')), '\\'),
                static::class,
                $type::class
            ));
        }
        parent::jsonUnserialize($decoded, $config, $type);
        if (isset($decoded->material) || property_exists($decoded, self::FIELD_MATERIAL)) {
            if (is_array($decoded->material)) {
                $type->setMaterial(FHIRCodeableConcept::jsonUnserialize(reset($decoded->material), $config));
            } else {
                $type->setMaterial(FHIRCodeableConcept::jsonUnserialize($decoded->material, $config));
            }
        }
        if (isset($decoded->type) || property_exists($decoded, self::FIELD_TYPE)) {
            if (is_array($decoded->type)) {
                $type->setType(FHIRCodeableConcept::jsonUnserialize(reset($decoded->type), $config));
            } else {
                $type->setType(FHIRCodeableConcept::jsonUnserialize($decoded->type, $config));
            }
        }
        if (isset($decoded->cap) || property_exists($decoded, self::FIELD_CAP)) {
            if (is_array($decoded->cap)) {
                $type->setCap(FHIRCodeableConcept::jsonUnserialize(reset($decoded->cap), $config));
            } else {
                $type->setCap(FHIRCodeableConcept::jsonUnserialize($decoded->cap, $config));
            }
        }
        if (isset($decoded->description)
            || isset($decoded->_description)
            || property_exists($decoded, self::FIELD_DESCRIPTION)
            || property_exists($decoded, self::FIELD_DESCRIPTION_EXT)) {
            $v = $decoded->_description ?? new \stdClass();
            $v->value = $decoded->description ?? null;
            $type->setDescription(FHIRString::jsonUnserialize($v, $config));
        }
        if (isset($decoded->capacity) || property_exists($decoded, self::FIELD_CAPACITY)) {
            if (is_array($decoded->capacity)) {
                $type->setCapacity(FHIRQuantity::jsonUnserialize(reset($decoded->capacity), $config));
            } else {
                $type->setCapacity(FHIRQuantity::jsonUnserialize($decoded->capacity, $config));
            }
        }
        if (isset($decoded->minimumVolumeQuantity) || property_exists($decoded, self::FIELD_MINIMUM_VOLUME_QUANTITY)) {
            if (is_array($decoded->minimumVolumeQuantity)) {
                $type->setMinimumVolumeQuantity(FHIRQuantity::jsonUnserialize(reset($decoded->minimumVolumeQuantity), $config));
            } else {
                $type->setMinimumVolumeQuantity(FHIRQuantity::jsonUnserialize($decoded->minimumVolumeQuantity, $config));
            }
        }
        if (isset($decoded->minimumVolumeString)
            || isset($decoded->_minimumVolumeString)
            || property_exists($decoded, self::FIELD_MINIMUM_VOLUME_STRING)
            || property_exists($decoded, self::FIELD_MINIMUM_VOLUME_STRING_EXT)) {
            $v = $decoded->_minimumVolumeString ?? new \stdClass();
            $v->value = $decoded->minimumVolumeString ?? null;
            $type->setMinimumVolumeString(FHIRString::jsonUnserialize($v, $config));
        }
        if (isset($decoded->additive) || property_exists($decoded, self::FIELD_ADDITIVE)) {
            if (is_object($decoded->additive)) {
                $vals = [$decoded->additive];
                $type->_setJSONFieldElideSingletonArray(self::FIELD_ADDITIVE, true);
            } else {
                $vals = $decoded->additive;
            }
            foreach($vals as $v) {
                $type->addAdditive(FHIRSpecimenDefinitionAdditive::jsonUnserialize($v, $config));
            }
        }
        if (isset($decoded->preparation)
            || isset($decoded->_preparation)
            || property_exists($decoded, self::FIELD_PREPARATION)
            || property_exists($decoded, self::FIELD_PREPARATION_EXT)) {
            $v = $decoded->_preparation ?? new \stdClass();
            $v->value = $decoded->preparation ?? null;
            $type->setPreparation(FHIRString::jsonUnserialize($v, $config));
        }
        return $type;
    }

    /**
     * @return \stdClass
     */
    public function jsonSerialize(): mixed
    {
        $out = parent::jsonSerialize();
        if (isset($this->material)) {
            $out->material = $this->material;
        }
        if (isset($this->type)) {
            $out->type = $this->type;
        }
        if (isset($this->cap)) {
            $out->cap = $this->cap;
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
        if (isset($this->capacity)) {
            $out->capacity = $this->capacity;
        }
        if (isset($this->minimumVolumeQuantity)) {
            $out->minimumVolumeQuantity = $this->minimumVolumeQuantity;
        }
        if (isset($this->minimumVolumeString)) {
            if (null !== ($val = $this->minimumVolumeString->getValue())) {
                $out->minimumVolumeString = $val;
            }
            if ($this->minimumVolumeString->_nonValueFieldDefined()) {
                $ext = $this->minimumVolumeString->jsonSerialize();
                unset($ext->value);
                $out->_minimumVolumeString = $ext;
            }
        }
        if (isset($this->additive) && [] !== $this->additive) {
            if ($this->_getJSONFieldElideSingletonArray(self::FIELD_ADDITIVE) && 1 === count($this->additive)) {
                $out->additive = $this->additive[0];
            } else {
                $out->additive = $this->additive;
            }
        }
        if (isset($this->preparation)) {
            if (null !== ($val = $this->preparation->getValue())) {
                $out->preparation = $val;
            }
            if ($this->preparation->_nonValueFieldDefined()) {
                $ext = $this->preparation->jsonSerialize();
                unset($ext->value);
                $out->_preparation = $ext;
            }
        }
        return $out;
    }
}
