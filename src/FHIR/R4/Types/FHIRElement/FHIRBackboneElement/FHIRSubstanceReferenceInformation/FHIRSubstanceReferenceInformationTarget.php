<?php

declare(strict_types=1);

namespace OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRSubstanceReferenceInformation;

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
use OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRIdentifier;
use OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRQuantity;
use OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRRange;
use OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRReference;
use OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRString;
use OpenEMR\FHIR\Versions\R4\Types\FHIRStringPrimitive;
use OpenEMR\FHIR\Versions\R4\VersionConstants;

/**
 * Todo.
 */
#[\Attribute(\Attribute::TARGET_PROPERTY)]
class FHIRSubstanceReferenceInformationTarget extends FHIRBackboneElement
{
    use TypeValidationsTrait,
        JSONSerializationOptionsTrait,
        XMLSerializationOptionsTrait;

    // name of FHIR type this class describes
    public const FHIR_TYPE_NAME = VersionConstants::TYPE_NAME_SUBSTANCE_REFERENCE_INFORMATION_DOT_TARGET;

    /* class_default.php:56 */
    public const FIELD_TARGET = 'target';
    public const FIELD_TYPE = 'type';
    public const FIELD_INTERACTION = 'interaction';
    public const FIELD_ORGANISM = 'organism';
    public const FIELD_ORGANISM_TYPE = 'organismType';
    public const FIELD_AMOUNT_QUANTITY = 'amountQuantity';
    public const FIELD_AMOUNT_RANGE = 'amountRange';
    public const FIELD_AMOUNT_STRING = 'amountString';
    public const FIELD_AMOUNT_STRING_EXT = '_amountString';
    public const FIELD_AMOUNT_TYPE = 'amountType';
    public const FIELD_SOURCE = 'source';

    /* class_default.php:75 */
    private const _FHIR_VALIDATION_RULES = [
    ];

    /* class_default.php:96 */
    private array $_valueXMLLocations = [
        self::FIELD_AMOUNT_STRING => ValueXMLLocationEnum::CONTAINER_ATTRIBUTE,
    ];

    /* class_default.php:112 */
    /**
     * An identifier - identifies some entity uniquely and unambiguously. Typically
     * this is used for business identifiers.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * Todo.
     *
     * @var \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRIdentifier
     */
    #[FHIRIdentifier]
    protected FHIRIdentifier $target;
    /**
     * A concept that may be defined by a formal reference to a terminology or ontology
     * or may be provided by text.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * Todo.
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
     * Todo.
     *
     * @var \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRCodeableConcept
     */
    #[FHIRCodeableConcept]
    protected FHIRCodeableConcept $interaction;
    /**
     * A concept that may be defined by a formal reference to a terminology or ontology
     * or may be provided by text.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * Todo.
     *
     * @var \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRCodeableConcept
     */
    #[FHIRCodeableConcept]
    protected FHIRCodeableConcept $organism;
    /**
     * A concept that may be defined by a formal reference to a terminology or ontology
     * or may be provided by text.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * Todo.
     *
     * @var \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRCodeableConcept
     */
    #[FHIRCodeableConcept]
    protected FHIRCodeableConcept $organismType;
    /**
     * A measured amount (or an amount that can potentially be measured). Note that
     * measured amounts include amounts that are not precisely quantified, including
     * amounts involving arbitrary units and floating currencies.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * Todo. (choose any one of amount*, but only one)
     *
     * @var \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRQuantity
     */
    #[FHIRQuantity]
    protected FHIRQuantity $amountQuantity;
    /**
     * A set of ordered Quantities defined by a low and high limit.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * Todo. (choose any one of amount*, but only one)
     *
     * @var \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRRange
     */
    #[FHIRRange]
    protected FHIRRange $amountRange;
    /**
     * A sequence of Unicode characters
     * Note that FHIR strings SHALL NOT exceed 1MB in size
     * If the element is present, it must have either a \@value, an \@id, or extensions
     *
     * Todo. (choose any one of amount*, but only one)
     *
     * @var \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRString
     */
    #[FHIRString]
    protected FHIRString $amountString;
    /**
     * A concept that may be defined by a formal reference to a terminology or ontology
     * or may be provided by text.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * Todo.
     *
     * @var \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRCodeableConcept
     */
    #[FHIRCodeableConcept]
    protected FHIRCodeableConcept $amountType;
    /**
     * A reference from one resource to another.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * Todo.
     *
     * @var iterable<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRReference>
     */
    #[FHIRReference]
    protected array $source;

    /* constructor.php:61 */
    /**
     * FHIRSubstanceReferenceInformationTarget Constructor
     * @param null|iterable<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRExtension> $extension
     * @param null|string|\OpenEMR\FHIR\Versions\R4\Types\FHIRStringPrimitive $id
     * @param null|iterable<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRExtension> $modifierExtension
     * @param null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRIdentifier $target
     * @param null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRCodeableConcept $type
     * @param null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRCodeableConcept $interaction
     * @param null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRCodeableConcept $organism
     * @param null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRCodeableConcept $organismType
     * @param null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRQuantity $amountQuantity
     * @param null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRRange $amountRange
     * @param null|string|\OpenEMR\FHIR\Versions\R4\Types\FHIRStringPrimitive|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRString $amountString
     * @param null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRCodeableConcept $amountType
     * @param null|iterable<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRReference> $source
     * @param null|string[] $fhirComments
     */
    public function __construct(null|iterable $extension = null,
                                null|string|FHIRStringPrimitive $id = null,
                                null|iterable $modifierExtension = null,
                                null|FHIRIdentifier $target = null,
                                null|FHIRCodeableConcept $type = null,
                                null|FHIRCodeableConcept $interaction = null,
                                null|FHIRCodeableConcept $organism = null,
                                null|FHIRCodeableConcept $organismType = null,
                                null|FHIRQuantity $amountQuantity = null,
                                null|FHIRRange $amountRange = null,
                                null|string|FHIRStringPrimitive|FHIRString $amountString = null,
                                null|FHIRCodeableConcept $amountType = null,
                                null|iterable $source = null,
                                null|iterable $fhirComments = null)
    {
        parent::__construct(extension: $extension,
                            id: $id,
                            modifierExtension: $modifierExtension,
                            fhirComments: $fhirComments);
        if (null !== $target) {
            $this->setTarget($target);
        }
        if (null !== $type) {
            $this->setType($type);
        }
        if (null !== $interaction) {
            $this->setInteraction($interaction);
        }
        if (null !== $organism) {
            $this->setOrganism($organism);
        }
        if (null !== $organismType) {
            $this->setOrganismType($organismType);
        }
        if (null !== $amountQuantity) {
            $this->setAmountQuantity($amountQuantity);
        }
        if (null !== $amountRange) {
            $this->setAmountRange($amountRange);
        }
        if (null !== $amountString) {
            $this->setAmountString($amountString);
        }
        if (null !== $amountType) {
            $this->setAmountType($amountType);
        }
        if (null !== $source) {
            $this->setSource(...$source);
        }
    }

    /* class_default.php:145 */
    public function _getFHIRTypeName(): string
    {
        return self::FHIR_TYPE_NAME;
    }

    /* class_default.php:174 */
    /**
     * An identifier - identifies some entity uniquely and unambiguously. Typically
     * this is used for business identifiers.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * Todo.
     *
     * @return null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRIdentifier
     */
    public function getTarget(): null|FHIRIdentifier
    {
        return $this->target ?? null;
    }

    /**
     * An identifier - identifies some entity uniquely and unambiguously. Typically
     * this is used for business identifiers.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * Todo.
     *
     * @param null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRIdentifier $target
     * @return static
     */
    public function setTarget(null|FHIRIdentifier $target): self
    {
        if (null === $target) {
            unset($this->target);
            return $this;
        }
        $this->target = $target;
        return $this;
    }

    /**
     * A concept that may be defined by a formal reference to a terminology or ontology
     * or may be provided by text.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * Todo.
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
     * Todo.
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
     * Todo.
     *
     * @return null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRCodeableConcept
     */
    public function getInteraction(): null|FHIRCodeableConcept
    {
        return $this->interaction ?? null;
    }

    /**
     * A concept that may be defined by a formal reference to a terminology or ontology
     * or may be provided by text.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * Todo.
     *
     * @param null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRCodeableConcept $interaction
     * @return static
     */
    public function setInteraction(null|FHIRCodeableConcept $interaction): self
    {
        if (null === $interaction) {
            unset($this->interaction);
            return $this;
        }
        $this->interaction = $interaction;
        return $this;
    }

    /**
     * A concept that may be defined by a formal reference to a terminology or ontology
     * or may be provided by text.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * Todo.
     *
     * @return null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRCodeableConcept
     */
    public function getOrganism(): null|FHIRCodeableConcept
    {
        return $this->organism ?? null;
    }

    /**
     * A concept that may be defined by a formal reference to a terminology or ontology
     * or may be provided by text.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * Todo.
     *
     * @param null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRCodeableConcept $organism
     * @return static
     */
    public function setOrganism(null|FHIRCodeableConcept $organism): self
    {
        if (null === $organism) {
            unset($this->organism);
            return $this;
        }
        $this->organism = $organism;
        return $this;
    }

    /**
     * A concept that may be defined by a formal reference to a terminology or ontology
     * or may be provided by text.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * Todo.
     *
     * @return null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRCodeableConcept
     */
    public function getOrganismType(): null|FHIRCodeableConcept
    {
        return $this->organismType ?? null;
    }

    /**
     * A concept that may be defined by a formal reference to a terminology or ontology
     * or may be provided by text.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * Todo.
     *
     * @param null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRCodeableConcept $organismType
     * @return static
     */
    public function setOrganismType(null|FHIRCodeableConcept $organismType): self
    {
        if (null === $organismType) {
            unset($this->organismType);
            return $this;
        }
        $this->organismType = $organismType;
        return $this;
    }

    /**
     * A measured amount (or an amount that can potentially be measured). Note that
     * measured amounts include amounts that are not precisely quantified, including
     * amounts involving arbitrary units and floating currencies.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * Todo. (choose any one of amount*, but only one)
     *
     * @return null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRQuantity
     */
    public function getAmountQuantity(): null|FHIRQuantity
    {
        return $this->amountQuantity ?? null;
    }

    /**
     * A measured amount (or an amount that can potentially be measured). Note that
     * measured amounts include amounts that are not precisely quantified, including
     * amounts involving arbitrary units and floating currencies.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * Todo. (choose any one of amount*, but only one)
     *
     * @param null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRQuantity $amountQuantity
     * @return static
     */
    public function setAmountQuantity(null|FHIRQuantity $amountQuantity): self
    {
        if (null === $amountQuantity) {
            unset($this->amountQuantity);
            return $this;
        }
        $this->amountQuantity = $amountQuantity;
        return $this;
    }

    /**
     * A set of ordered Quantities defined by a low and high limit.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * Todo. (choose any one of amount*, but only one)
     *
     * @return null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRRange
     */
    public function getAmountRange(): null|FHIRRange
    {
        return $this->amountRange ?? null;
    }

    /**
     * A set of ordered Quantities defined by a low and high limit.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * Todo. (choose any one of amount*, but only one)
     *
     * @param null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRRange $amountRange
     * @return static
     */
    public function setAmountRange(null|FHIRRange $amountRange): self
    {
        if (null === $amountRange) {
            unset($this->amountRange);
            return $this;
        }
        $this->amountRange = $amountRange;
        return $this;
    }

    /**
     * A sequence of Unicode characters
     * Note that FHIR strings SHALL NOT exceed 1MB in size
     * If the element is present, it must have either a \@value, an \@id, or extensions
     *
     * Todo. (choose any one of amount*, but only one)
     *
     * @return null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRString
     */
    public function getAmountString(): null|FHIRString
    {
        return $this->amountString ?? null;
    }

    /**
     * A sequence of Unicode characters
     * Note that FHIR strings SHALL NOT exceed 1MB in size
     * If the element is present, it must have either a \@value, an \@id, or extensions
     *
     * Todo. (choose any one of amount*, but only one)
     *
     * @param null|string|\OpenEMR\FHIR\Versions\R4\Types\FHIRStringPrimitive|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRString $amountString
     * @return static
     */
    public function setAmountString(null|string|FHIRStringPrimitive|FHIRString $amountString): self
    {
        if (null === $amountString) {
            unset($this->amountString);
            return $this;
        }
        if (!($amountString instanceof FHIRString)) {
            $amountString = new FHIRString(value: $amountString);
        }
        $this->amountString = $amountString;
        return $this;
    }

    /**
     * A concept that may be defined by a formal reference to a terminology or ontology
     * or may be provided by text.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * Todo.
     *
     * @return null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRCodeableConcept
     */
    public function getAmountType(): null|FHIRCodeableConcept
    {
        return $this->amountType ?? null;
    }

    /**
     * A concept that may be defined by a formal reference to a terminology or ontology
     * or may be provided by text.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * Todo.
     *
     * @param null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRCodeableConcept $amountType
     * @return static
     */
    public function setAmountType(null|FHIRCodeableConcept $amountType): self
    {
        if (null === $amountType) {
            unset($this->amountType);
            return $this;
        }
        $this->amountType = $amountType;
        return $this;
    }

    /**
     * A reference from one resource to another.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * Todo.
     *
     * @return iterable<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRReference>
     */
    public function getSource(): array
    {
        return $this->source ?? [];
    }

    /**
     * @return \ArrayIterator<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRReference>
     */
    public function getSourceIterator(): iterable
    {
        if (!isset($this->source)) {
            return new \EmptyIterator();
        }
        return new \ArrayIterator($this->source);
    }

    /**
     * A reference from one resource to another.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * Todo.
     *
     * @param \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRReference $source
     * @return static
     */
    public function addSource(FHIRReference $source): self
    {
        if (!isset($this->source)) {
            $this->source = [];
        }
        $this->source[] = $source;
        return $this;
    }

    /**
     * A reference from one resource to another.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * Todo.
     *
     * @param \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRReference ...$source
     * @return static
     */
    public function setSource(FHIRReference ...$source): self
    {
        if ([] === $source) {
            unset($this->source);
            return $this;
        }
        $this->source = $source;
        return $this;
    }

    /* class_default.php:201 */
    /**
     * @param \SimpleXMLElement $element
     * @param \OpenEMR\FHIR\Encoding\UnserializeConfig $config
     * @param null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRSubstanceReferenceInformation\FHIRSubstanceReferenceInformationTarget $type
     * @return \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRSubstanceReferenceInformation\FHIRSubstanceReferenceInformationTarget
     * @throws \Exception
     */
    public static function xmlUnserialize(\SimpleXMLElement $element,
                                          UnserializeConfig $config,
                                          null|ElementTypeInterface $type = null): self
    {
        if (null === $type) {
            $type = new static();
        } else if (!($type instanceof FHIRSubstanceReferenceInformationTarget)) {
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
            } else if (self::FIELD_TARGET === $cen) {
                $type->setTarget(FHIRIdentifier::xmlUnserialize($ce, $config));
            } else if (self::FIELD_TYPE === $cen) {
                $type->setType(FHIRCodeableConcept::xmlUnserialize($ce, $config));
            } else if (self::FIELD_INTERACTION === $cen) {
                $type->setInteraction(FHIRCodeableConcept::xmlUnserialize($ce, $config));
            } else if (self::FIELD_ORGANISM === $cen) {
                $type->setOrganism(FHIRCodeableConcept::xmlUnserialize($ce, $config));
            } else if (self::FIELD_ORGANISM_TYPE === $cen) {
                $type->setOrganismType(FHIRCodeableConcept::xmlUnserialize($ce, $config));
            } else if (self::FIELD_AMOUNT_QUANTITY === $cen) {
                $type->setAmountQuantity(FHIRQuantity::xmlUnserialize($ce, $config));
            } else if (self::FIELD_AMOUNT_RANGE === $cen) {
                $type->setAmountRange(FHIRRange::xmlUnserialize($ce, $config));
            } else if (self::FIELD_AMOUNT_STRING === $cen) {
                $type->setAmountString(FHIRString::xmlUnserialize($ce, $config));
            } else if (self::FIELD_AMOUNT_TYPE === $cen) {
                $type->setAmountType(FHIRCodeableConcept::xmlUnserialize($ce, $config));
            } else if (self::FIELD_SOURCE === $cen) {
                $type->addSource(FHIRReference::xmlUnserialize($ce, $config));
            }
        }
        $attributes = $element->attributes();
        if (isset($attributes[self::FIELD_ID])) {
            $type->setId((string)$attributes[self::FIELD_ID]);
            $type->_setXMLFieldValueLocation(self::FIELD_ID, ValueXMLLocationEnum::PARENT_ATTRIBUTE);
        }
        if (isset($attributes[self::FIELD_AMOUNT_STRING])) {
            if (isset($type->amountString)) {
                $type->amountString->setValue((string)$attributes[self::FIELD_AMOUNT_STRING]);
            } else {
                $type->setAmountString((string)$attributes[self::FIELD_AMOUNT_STRING]);
            }
            $type->_setXMLFieldValueLocation(self::FIELD_AMOUNT_STRING, ValueXMLLocationEnum::PARENT_ATTRIBUTE);
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
        if (isset($this->amountString) && ValueXMLLocationEnum::PARENT_ATTRIBUTE === $this->_valueXMLLocations[self::FIELD_AMOUNT_STRING]) {
            $xw->writeAttribute(self::FIELD_AMOUNT_STRING, $this->amountString->_getValueAsString());
        }
        parent::xmlSerialize($xw, $config);
        if (isset($this->target)) {
            $xw->startElement(self::FIELD_TARGET);
            $this->target->xmlSerialize($xw, $config);
            $xw->endElement();
        }
        if (isset($this->type)) {
            $xw->startElement(self::FIELD_TYPE);
            $this->type->xmlSerialize($xw, $config);
            $xw->endElement();
        }
        if (isset($this->interaction)) {
            $xw->startElement(self::FIELD_INTERACTION);
            $this->interaction->xmlSerialize($xw, $config);
            $xw->endElement();
        }
        if (isset($this->organism)) {
            $xw->startElement(self::FIELD_ORGANISM);
            $this->organism->xmlSerialize($xw, $config);
            $xw->endElement();
        }
        if (isset($this->organismType)) {
            $xw->startElement(self::FIELD_ORGANISM_TYPE);
            $this->organismType->xmlSerialize($xw, $config);
            $xw->endElement();
        }
        if (isset($this->amountQuantity)) {
            $xw->startElement(self::FIELD_AMOUNT_QUANTITY);
            $this->amountQuantity->xmlSerialize($xw, $config);
            $xw->endElement();
        }
        if (isset($this->amountRange)) {
            $xw->startElement(self::FIELD_AMOUNT_RANGE);
            $this->amountRange->xmlSerialize($xw, $config);
            $xw->endElement();
        }
        if (isset($this->amountString)
            && (ValueXMLLocationEnum::PARENT_ATTRIBUTE !== $this->_valueXMLLocations[self::FIELD_AMOUNT_STRING]
                || $this->amountString->_nonValueFieldDefined())) {
            $xw->startElement(self::FIELD_AMOUNT_STRING);
            $this->amountString->xmlSerialize($xw, $config, $this->_valueXMLLocations[self::FIELD_AMOUNT_STRING]);
            $xw->endElement();
        }
        if (isset($this->amountType)) {
            $xw->startElement(self::FIELD_AMOUNT_TYPE);
            $this->amountType->xmlSerialize($xw, $config);
            $xw->endElement();
        }
        if (isset($this->source)) {
            foreach ($this->source as $v) {
                $xw->startElement(self::FIELD_SOURCE);
                $v->xmlSerialize($xw, $config);
                $xw->endElement();
            }
        }
    }

    /**
     * @param \stdClass $decoded
     * @param \OpenEMR\FHIR\Encoding\UnserializeConfig $config
     * @param null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRSubstanceReferenceInformation\FHIRSubstanceReferenceInformationTarget $type
     * @return \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRSubstanceReferenceInformation\FHIRSubstanceReferenceInformationTarget
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
        } else if (!($type instanceof FHIRSubstanceReferenceInformationTarget)) {
            throw new \RuntimeException(sprintf(
                '%s::jsonUnserialize - $type must be instance of \\%s or null, %s seen.',
                ltrim(substr(self::class, (int)strrpos(self::class, '\\')), '\\'),
                static::class,
                $type::class
            ));
        }
        parent::jsonUnserialize($decoded, $config, $type);
        if (isset($decoded->target) || property_exists($decoded, self::FIELD_TARGET)) {
            if (is_array($decoded->target)) {
                $type->setTarget(FHIRIdentifier::jsonUnserialize(reset($decoded->target), $config));
            } else {
                $type->setTarget(FHIRIdentifier::jsonUnserialize($decoded->target, $config));
            }
        }
        if (isset($decoded->type) || property_exists($decoded, self::FIELD_TYPE)) {
            if (is_array($decoded->type)) {
                $type->setType(FHIRCodeableConcept::jsonUnserialize(reset($decoded->type), $config));
            } else {
                $type->setType(FHIRCodeableConcept::jsonUnserialize($decoded->type, $config));
            }
        }
        if (isset($decoded->interaction) || property_exists($decoded, self::FIELD_INTERACTION)) {
            if (is_array($decoded->interaction)) {
                $type->setInteraction(FHIRCodeableConcept::jsonUnserialize(reset($decoded->interaction), $config));
            } else {
                $type->setInteraction(FHIRCodeableConcept::jsonUnserialize($decoded->interaction, $config));
            }
        }
        if (isset($decoded->organism) || property_exists($decoded, self::FIELD_ORGANISM)) {
            if (is_array($decoded->organism)) {
                $type->setOrganism(FHIRCodeableConcept::jsonUnserialize(reset($decoded->organism), $config));
            } else {
                $type->setOrganism(FHIRCodeableConcept::jsonUnserialize($decoded->organism, $config));
            }
        }
        if (isset($decoded->organismType) || property_exists($decoded, self::FIELD_ORGANISM_TYPE)) {
            if (is_array($decoded->organismType)) {
                $type->setOrganismType(FHIRCodeableConcept::jsonUnserialize(reset($decoded->organismType), $config));
            } else {
                $type->setOrganismType(FHIRCodeableConcept::jsonUnserialize($decoded->organismType, $config));
            }
        }
        if (isset($decoded->amountQuantity) || property_exists($decoded, self::FIELD_AMOUNT_QUANTITY)) {
            if (is_array($decoded->amountQuantity)) {
                $type->setAmountQuantity(FHIRQuantity::jsonUnserialize(reset($decoded->amountQuantity), $config));
            } else {
                $type->setAmountQuantity(FHIRQuantity::jsonUnserialize($decoded->amountQuantity, $config));
            }
        }
        if (isset($decoded->amountRange) || property_exists($decoded, self::FIELD_AMOUNT_RANGE)) {
            if (is_array($decoded->amountRange)) {
                $type->setAmountRange(FHIRRange::jsonUnserialize(reset($decoded->amountRange), $config));
            } else {
                $type->setAmountRange(FHIRRange::jsonUnserialize($decoded->amountRange, $config));
            }
        }
        if (isset($decoded->amountString)
            || isset($decoded->_amountString)
            || property_exists($decoded, self::FIELD_AMOUNT_STRING)
            || property_exists($decoded, self::FIELD_AMOUNT_STRING_EXT)) {
            $v = $decoded->_amountString ?? new \stdClass();
            $v->value = $decoded->amountString ?? null;
            $type->setAmountString(FHIRString::jsonUnserialize($v, $config));
        }
        if (isset($decoded->amountType) || property_exists($decoded, self::FIELD_AMOUNT_TYPE)) {
            if (is_array($decoded->amountType)) {
                $type->setAmountType(FHIRCodeableConcept::jsonUnserialize(reset($decoded->amountType), $config));
            } else {
                $type->setAmountType(FHIRCodeableConcept::jsonUnserialize($decoded->amountType, $config));
            }
        }
        if (isset($decoded->source) || property_exists($decoded, self::FIELD_SOURCE)) {
            if (is_object($decoded->source)) {
                $vals = [$decoded->source];
                $type->_setJSONFieldElideSingletonArray(self::FIELD_SOURCE, true);
            } else {
                $vals = $decoded->source;
            }
            foreach($vals as $v) {
                $type->addSource(FHIRReference::jsonUnserialize($v, $config));
            }
        }
        return $type;
    }

    /**
     * @return \stdClass
     */
    public function jsonSerialize(): mixed
    {
        $out = parent::jsonSerialize();
        if (isset($this->target)) {
            $out->target = $this->target;
        }
        if (isset($this->type)) {
            $out->type = $this->type;
        }
        if (isset($this->interaction)) {
            $out->interaction = $this->interaction;
        }
        if (isset($this->organism)) {
            $out->organism = $this->organism;
        }
        if (isset($this->organismType)) {
            $out->organismType = $this->organismType;
        }
        if (isset($this->amountQuantity)) {
            $out->amountQuantity = $this->amountQuantity;
        }
        if (isset($this->amountRange)) {
            $out->amountRange = $this->amountRange;
        }
        if (isset($this->amountString)) {
            if (null !== ($val = $this->amountString->getValue())) {
                $out->amountString = $val;
            }
            if ($this->amountString->_nonValueFieldDefined()) {
                $ext = $this->amountString->jsonSerialize();
                unset($ext->value);
                $out->_amountString = $ext;
            }
        }
        if (isset($this->amountType)) {
            $out->amountType = $this->amountType;
        }
        if (isset($this->source) && [] !== $this->source) {
            if ($this->_getJSONFieldElideSingletonArray(self::FIELD_SOURCE) && 1 === count($this->source)) {
                $out->source = $this->source[0];
            } else {
                $out->source = $this->source;
            }
        }
        return $out;
    }
}
