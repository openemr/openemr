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
use OpenEMR\FHIR\Validation\Rules\MinOccursRule;
use OpenEMR\FHIR\Validation\TypeValidationsTrait;
use OpenEMR\FHIR\Versions\R4\Types\FHIRBooleanPrimitive;
use OpenEMR\FHIR\Versions\R4\Types\FHIRCodePrimitive\FHIRSpecimenContainedPreferenceList;
use OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement;
use OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBoolean;
use OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRCodeableConcept;
use OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRExtension;
use OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRQuantity\FHIRDuration;
use OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRSpecimenContainedPreference;
use OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRString;
use OpenEMR\FHIR\Versions\R4\Types\FHIRStringPrimitive;
use OpenEMR\FHIR\Versions\R4\VersionConstants;

/**
 * A kind of specimen with associated set of requirements.
 */
#[\Attribute(\Attribute::TARGET_PROPERTY)]
class FHIRSpecimenDefinitionTypeTested extends FHIRBackboneElement
{
    use TypeValidationsTrait,
        JSONSerializationOptionsTrait,
        XMLSerializationOptionsTrait;

    // name of FHIR type this class describes
    public const FHIR_TYPE_NAME = VersionConstants::TYPE_NAME_SPECIMEN_DEFINITION_DOT_TYPE_TESTED;

    /* class_default.php:56 */
    public const FIELD_IS_DERIVED = 'isDerived';
    public const FIELD_IS_DERIVED_EXT = '_isDerived';
    public const FIELD_TYPE = 'type';
    public const FIELD_PREFERENCE = 'preference';
    public const FIELD_PREFERENCE_EXT = '_preference';
    public const FIELD_CONTAINER = 'container';
    public const FIELD_REQUIREMENT = 'requirement';
    public const FIELD_REQUIREMENT_EXT = '_requirement';
    public const FIELD_RETENTION_TIME = 'retentionTime';
    public const FIELD_REJECTION_CRITERION = 'rejectionCriterion';
    public const FIELD_HANDLING = 'handling';

    /* class_default.php:75 */
    private const _FHIR_VALIDATION_RULES = [
        self::FIELD_PREFERENCE => [
            MinOccursRule::NAME => 1,
        ],
    ];

    /* class_default.php:96 */
    private array $_valueXMLLocations = [
        self::FIELD_IS_DERIVED => ValueXMLLocationEnum::CONTAINER_ATTRIBUTE,
        self::FIELD_PREFERENCE => ValueXMLLocationEnum::CONTAINER_ATTRIBUTE,
        self::FIELD_REQUIREMENT => ValueXMLLocationEnum::CONTAINER_ATTRIBUTE,
    ];

    /* class_default.php:112 */
    /**
     * Value of "true" or "false"
     * If the element is present, it must have either a \@value, an \@id, or extensions
     *
     * Primary of secondary specimen.
     *
     * @var \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBoolean
     */
    #[FHIRBoolean]
    protected FHIRBoolean $isDerived;
    /**
     * A concept that may be defined by a formal reference to a terminology or ontology
     * or may be provided by text.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * The kind of specimen conditioned for testing expected by lab.
     *
     * @var \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRCodeableConcept
     */
    #[FHIRCodeableConcept]
    protected FHIRCodeableConcept $type;
    /**
     * Degree of preference of a type of conditioned specimen.
     * If the element is present, it must have either a \@value, an \@id, or extensions
     *
     * The preference for this type of conditioned specimen.
     *
     * @var \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRSpecimenContainedPreference
     */
    #[FHIRSpecimenContainedPreference]
    protected FHIRSpecimenContainedPreference $preference;
    /**
     * A kind of specimen with associated set of requirements.
     *
     * The specimen's container.
     *
     * @var \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRSpecimenDefinition\FHIRSpecimenDefinitionContainer
     */
    #[FHIRSpecimenDefinitionContainer]
    protected FHIRSpecimenDefinitionContainer $container;
    /**
     * A sequence of Unicode characters
     * Note that FHIR strings SHALL NOT exceed 1MB in size
     * If the element is present, it must have either a \@value, an \@id, or extensions
     *
     * Requirements for delivery and special handling of this kind of conditioned
     * specimen.
     *
     * @var \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRString
     */
    #[FHIRString]
    protected FHIRString $requirement;
    /**
     * A length of time.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * The usual time that a specimen of this kind is retained after the ordered tests
     * are completed, for the purpose of additional testing.
     *
     * @var \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRQuantity\FHIRDuration
     */
    #[FHIRDuration]
    protected FHIRDuration $retentionTime;
    /**
     * A concept that may be defined by a formal reference to a terminology or ontology
     * or may be provided by text.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * Criterion for rejection of the specimen in its container by the laboratory.
     *
     * @var iterable<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRCodeableConcept>
     */
    #[FHIRCodeableConcept]
    protected array $rejectionCriterion;
    /**
     * A kind of specimen with associated set of requirements.
     *
     * Set of instructions for preservation/transport of the specimen at a defined
     * temperature interval, prior the testing process.
     *
     * @var iterable<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRSpecimenDefinition\FHIRSpecimenDefinitionHandling>
     */
    #[FHIRSpecimenDefinitionHandling]
    protected array $handling;

    /* constructor.php:61 */
    /**
     * FHIRSpecimenDefinitionTypeTested Constructor
     * @param null|iterable<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRExtension> $extension
     * @param null|string|\OpenEMR\FHIR\Versions\R4\Types\FHIRStringPrimitive $id
     * @param null|iterable<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRExtension> $modifierExtension
     * @param null|string|bool|\OpenEMR\FHIR\Versions\R4\Types\FHIRBooleanPrimitive|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBoolean $isDerived
     * @param null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRCodeableConcept $type
     * @param null|string|\OpenEMR\FHIR\Versions\R4\Types\FHIRCodePrimitive\FHIRSpecimenContainedPreferenceList|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRSpecimenContainedPreference $preference
     * @param null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRSpecimenDefinition\FHIRSpecimenDefinitionContainer $container
     * @param null|string|\OpenEMR\FHIR\Versions\R4\Types\FHIRStringPrimitive|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRString $requirement
     * @param null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRQuantity\FHIRDuration $retentionTime
     * @param null|iterable<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRCodeableConcept> $rejectionCriterion
     * @param null|iterable<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRSpecimenDefinition\FHIRSpecimenDefinitionHandling> $handling
     * @param null|string[] $fhirComments
     */
    public function __construct(null|iterable $extension = null,
                                null|string|FHIRStringPrimitive $id = null,
                                null|iterable $modifierExtension = null,
                                null|string|bool|FHIRBooleanPrimitive|FHIRBoolean $isDerived = null,
                                null|FHIRCodeableConcept $type = null,
                                null|string|FHIRSpecimenContainedPreferenceList|FHIRSpecimenContainedPreference $preference = null,
                                null|FHIRSpecimenDefinitionContainer $container = null,
                                null|string|FHIRStringPrimitive|FHIRString $requirement = null,
                                null|FHIRDuration $retentionTime = null,
                                null|iterable $rejectionCriterion = null,
                                null|iterable $handling = null,
                                null|iterable $fhirComments = null)
    {
        parent::__construct(extension: $extension,
                            id: $id,
                            modifierExtension: $modifierExtension,
                            fhirComments: $fhirComments);
        if (null !== $isDerived) {
            $this->setIsDerived($isDerived);
        }
        if (null !== $type) {
            $this->setType($type);
        }
        if (null !== $preference) {
            $this->setPreference($preference);
        }
        if (null !== $container) {
            $this->setContainer($container);
        }
        if (null !== $requirement) {
            $this->setRequirement($requirement);
        }
        if (null !== $retentionTime) {
            $this->setRetentionTime($retentionTime);
        }
        if (null !== $rejectionCriterion) {
            $this->setRejectionCriterion(...$rejectionCriterion);
        }
        if (null !== $handling) {
            $this->setHandling(...$handling);
        }
    }

    /* class_default.php:145 */
    public function _getFHIRTypeName(): string
    {
        return self::FHIR_TYPE_NAME;
    }

    /* class_default.php:174 */
    /**
     * Value of "true" or "false"
     * If the element is present, it must have either a \@value, an \@id, or extensions
     *
     * Primary of secondary specimen.
     *
     * @return null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBoolean
     */
    public function getIsDerived(): null|FHIRBoolean
    {
        return $this->isDerived ?? null;
    }

    /**
     * Value of "true" or "false"
     * If the element is present, it must have either a \@value, an \@id, or extensions
     *
     * Primary of secondary specimen.
     *
     * @param null|string|bool|\OpenEMR\FHIR\Versions\R4\Types\FHIRBooleanPrimitive|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBoolean $isDerived
     * @return static
     */
    public function setIsDerived(null|string|bool|FHIRBooleanPrimitive|FHIRBoolean $isDerived): self
    {
        if (null === $isDerived) {
            unset($this->isDerived);
            return $this;
        }
        if (!($isDerived instanceof FHIRBoolean)) {
            $isDerived = new FHIRBoolean(value: $isDerived);
        }
        $this->isDerived = $isDerived;
        return $this;
    }

    /**
     * A concept that may be defined by a formal reference to a terminology or ontology
     * or may be provided by text.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * The kind of specimen conditioned for testing expected by lab.
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
     * The kind of specimen conditioned for testing expected by lab.
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
     * Degree of preference of a type of conditioned specimen.
     * If the element is present, it must have either a \@value, an \@id, or extensions
     *
     * The preference for this type of conditioned specimen.
     *
     * @return null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRSpecimenContainedPreference
     */
    public function getPreference(): null|FHIRSpecimenContainedPreference
    {
        return $this->preference ?? null;
    }

    /**
     * Degree of preference of a type of conditioned specimen.
     * If the element is present, it must have either a \@value, an \@id, or extensions
     *
     * The preference for this type of conditioned specimen.
     *
     * @param null|string|\OpenEMR\FHIR\Versions\R4\Types\FHIRCodePrimitive\FHIRSpecimenContainedPreferenceList|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRSpecimenContainedPreference $preference
     * @return static
     */
    public function setPreference(null|string|FHIRSpecimenContainedPreferenceList|FHIRSpecimenContainedPreference $preference): self
    {
        if (null === $preference) {
            unset($this->preference);
            return $this;
        }
        if (!($preference instanceof FHIRSpecimenContainedPreference)) {
            $preference = new FHIRSpecimenContainedPreference(value: $preference);
        }
        $this->preference = $preference;
        return $this;
    }

    /**
     * A kind of specimen with associated set of requirements.
     *
     * The specimen's container.
     *
     * @return null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRSpecimenDefinition\FHIRSpecimenDefinitionContainer
     */
    public function getContainer(): null|FHIRSpecimenDefinitionContainer
    {
        return $this->container ?? null;
    }

    /**
     * A kind of specimen with associated set of requirements.
     *
     * The specimen's container.
     *
     * @param null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRSpecimenDefinition\FHIRSpecimenDefinitionContainer $container
     * @return static
     */
    public function setContainer(null|FHIRSpecimenDefinitionContainer $container): self
    {
        if (null === $container) {
            unset($this->container);
            return $this;
        }
        $this->container = $container;
        return $this;
    }

    /**
     * A sequence of Unicode characters
     * Note that FHIR strings SHALL NOT exceed 1MB in size
     * If the element is present, it must have either a \@value, an \@id, or extensions
     *
     * Requirements for delivery and special handling of this kind of conditioned
     * specimen.
     *
     * @return null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRString
     */
    public function getRequirement(): null|FHIRString
    {
        return $this->requirement ?? null;
    }

    /**
     * A sequence of Unicode characters
     * Note that FHIR strings SHALL NOT exceed 1MB in size
     * If the element is present, it must have either a \@value, an \@id, or extensions
     *
     * Requirements for delivery and special handling of this kind of conditioned
     * specimen.
     *
     * @param null|string|\OpenEMR\FHIR\Versions\R4\Types\FHIRStringPrimitive|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRString $requirement
     * @return static
     */
    public function setRequirement(null|string|FHIRStringPrimitive|FHIRString $requirement): self
    {
        if (null === $requirement) {
            unset($this->requirement);
            return $this;
        }
        if (!($requirement instanceof FHIRString)) {
            $requirement = new FHIRString(value: $requirement);
        }
        $this->requirement = $requirement;
        return $this;
    }

    /**
     * A length of time.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * The usual time that a specimen of this kind is retained after the ordered tests
     * are completed, for the purpose of additional testing.
     *
     * @return null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRQuantity\FHIRDuration
     */
    public function getRetentionTime(): null|FHIRDuration
    {
        return $this->retentionTime ?? null;
    }

    /**
     * A length of time.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * The usual time that a specimen of this kind is retained after the ordered tests
     * are completed, for the purpose of additional testing.
     *
     * @param null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRQuantity\FHIRDuration $retentionTime
     * @return static
     */
    public function setRetentionTime(null|FHIRDuration $retentionTime): self
    {
        if (null === $retentionTime) {
            unset($this->retentionTime);
            return $this;
        }
        $this->retentionTime = $retentionTime;
        return $this;
    }

    /**
     * A concept that may be defined by a formal reference to a terminology or ontology
     * or may be provided by text.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * Criterion for rejection of the specimen in its container by the laboratory.
     *
     * @return iterable<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRCodeableConcept>
     */
    public function getRejectionCriterion(): array
    {
        return $this->rejectionCriterion ?? [];
    }

    /**
     * @return \ArrayIterator<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRCodeableConcept>
     */
    public function getRejectionCriterionIterator(): iterable
    {
        if (!isset($this->rejectionCriterion)) {
            return new \EmptyIterator();
        }
        return new \ArrayIterator($this->rejectionCriterion);
    }

    /**
     * A concept that may be defined by a formal reference to a terminology or ontology
     * or may be provided by text.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * Criterion for rejection of the specimen in its container by the laboratory.
     *
     * @param \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRCodeableConcept $rejectionCriterion
     * @return static
     */
    public function addRejectionCriterion(FHIRCodeableConcept $rejectionCriterion): self
    {
        if (!isset($this->rejectionCriterion)) {
            $this->rejectionCriterion = [];
        }
        $this->rejectionCriterion[] = $rejectionCriterion;
        return $this;
    }

    /**
     * A concept that may be defined by a formal reference to a terminology or ontology
     * or may be provided by text.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * Criterion for rejection of the specimen in its container by the laboratory.
     *
     * @param \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRCodeableConcept ...$rejectionCriterion
     * @return static
     */
    public function setRejectionCriterion(FHIRCodeableConcept ...$rejectionCriterion): self
    {
        if ([] === $rejectionCriterion) {
            unset($this->rejectionCriterion);
            return $this;
        }
        $this->rejectionCriterion = $rejectionCriterion;
        return $this;
    }

    /**
     * A kind of specimen with associated set of requirements.
     *
     * Set of instructions for preservation/transport of the specimen at a defined
     * temperature interval, prior the testing process.
     *
     * @return iterable<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRSpecimenDefinition\FHIRSpecimenDefinitionHandling>
     */
    public function getHandling(): array
    {
        return $this->handling ?? [];
    }

    /**
     * @return \ArrayIterator<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRSpecimenDefinition\FHIRSpecimenDefinitionHandling>
     */
    public function getHandlingIterator(): iterable
    {
        if (!isset($this->handling)) {
            return new \EmptyIterator();
        }
        return new \ArrayIterator($this->handling);
    }

    /**
     * A kind of specimen with associated set of requirements.
     *
     * Set of instructions for preservation/transport of the specimen at a defined
     * temperature interval, prior the testing process.
     *
     * @param \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRSpecimenDefinition\FHIRSpecimenDefinitionHandling $handling
     * @return static
     */
    public function addHandling(FHIRSpecimenDefinitionHandling $handling): self
    {
        if (!isset($this->handling)) {
            $this->handling = [];
        }
        $this->handling[] = $handling;
        return $this;
    }

    /**
     * A kind of specimen with associated set of requirements.
     *
     * Set of instructions for preservation/transport of the specimen at a defined
     * temperature interval, prior the testing process.
     *
     * @param \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRSpecimenDefinition\FHIRSpecimenDefinitionHandling ...$handling
     * @return static
     */
    public function setHandling(FHIRSpecimenDefinitionHandling ...$handling): self
    {
        if ([] === $handling) {
            unset($this->handling);
            return $this;
        }
        $this->handling = $handling;
        return $this;
    }

    /* class_default.php:201 */
    /**
     * @param \SimpleXMLElement $element
     * @param \OpenEMR\FHIR\Encoding\UnserializeConfig $config
     * @param null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRSpecimenDefinition\FHIRSpecimenDefinitionTypeTested $type
     * @return \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRSpecimenDefinition\FHIRSpecimenDefinitionTypeTested
     * @throws \Exception
     */
    public static function xmlUnserialize(\SimpleXMLElement $element,
                                          UnserializeConfig $config,
                                          null|ElementTypeInterface $type = null): self
    {
        if (null === $type) {
            $type = new static();
        } else if (!($type instanceof FHIRSpecimenDefinitionTypeTested)) {
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
            } else if (self::FIELD_IS_DERIVED === $cen) {
                $type->setIsDerived(FHIRBoolean::xmlUnserialize($ce, $config));
            } else if (self::FIELD_TYPE === $cen) {
                $type->setType(FHIRCodeableConcept::xmlUnserialize($ce, $config));
            } else if (self::FIELD_PREFERENCE === $cen) {
                $type->setPreference(FHIRSpecimenContainedPreference::xmlUnserialize($ce, $config));
            } else if (self::FIELD_CONTAINER === $cen) {
                $type->setContainer(FHIRSpecimenDefinitionContainer::xmlUnserialize($ce, $config));
            } else if (self::FIELD_REQUIREMENT === $cen) {
                $type->setRequirement(FHIRString::xmlUnserialize($ce, $config));
            } else if (self::FIELD_RETENTION_TIME === $cen) {
                $type->setRetentionTime(FHIRDuration::xmlUnserialize($ce, $config));
            } else if (self::FIELD_REJECTION_CRITERION === $cen) {
                $type->addRejectionCriterion(FHIRCodeableConcept::xmlUnserialize($ce, $config));
            } else if (self::FIELD_HANDLING === $cen) {
                $type->addHandling(FHIRSpecimenDefinitionHandling::xmlUnserialize($ce, $config));
            }
        }
        $attributes = $element->attributes();
        if (isset($attributes[self::FIELD_ID])) {
            $type->setId((string)$attributes[self::FIELD_ID]);
            $type->_setXMLFieldValueLocation(self::FIELD_ID, ValueXMLLocationEnum::PARENT_ATTRIBUTE);
        }
        if (isset($attributes[self::FIELD_IS_DERIVED])) {
            if (isset($type->isDerived)) {
                $type->isDerived->setValue((string)$attributes[self::FIELD_IS_DERIVED]);
            } else {
                $type->setIsDerived((string)$attributes[self::FIELD_IS_DERIVED]);
            }
            $type->_setXMLFieldValueLocation(self::FIELD_IS_DERIVED, ValueXMLLocationEnum::PARENT_ATTRIBUTE);
        }
        if (isset($attributes[self::FIELD_PREFERENCE])) {
            if (isset($type->preference)) {
                $type->preference->setValue((string)$attributes[self::FIELD_PREFERENCE]);
            } else {
                $type->setPreference((string)$attributes[self::FIELD_PREFERENCE]);
            }
            $type->_setXMLFieldValueLocation(self::FIELD_PREFERENCE, ValueXMLLocationEnum::PARENT_ATTRIBUTE);
        }
        if (isset($attributes[self::FIELD_REQUIREMENT])) {
            if (isset($type->requirement)) {
                $type->requirement->setValue((string)$attributes[self::FIELD_REQUIREMENT]);
            } else {
                $type->setRequirement((string)$attributes[self::FIELD_REQUIREMENT]);
            }
            $type->_setXMLFieldValueLocation(self::FIELD_REQUIREMENT, ValueXMLLocationEnum::PARENT_ATTRIBUTE);
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
        if (isset($this->isDerived) && ValueXMLLocationEnum::PARENT_ATTRIBUTE === $this->_valueXMLLocations[self::FIELD_IS_DERIVED]) {
            $xw->writeAttribute(self::FIELD_IS_DERIVED, $this->isDerived->_getValueAsString());
        }
        if (isset($this->preference) && ValueXMLLocationEnum::PARENT_ATTRIBUTE === $this->_valueXMLLocations[self::FIELD_PREFERENCE]) {
            $xw->writeAttribute(self::FIELD_PREFERENCE, $this->preference->_getValueAsString());
        }
        if (isset($this->requirement) && ValueXMLLocationEnum::PARENT_ATTRIBUTE === $this->_valueXMLLocations[self::FIELD_REQUIREMENT]) {
            $xw->writeAttribute(self::FIELD_REQUIREMENT, $this->requirement->_getValueAsString());
        }
        parent::xmlSerialize($xw, $config);
        if (isset($this->isDerived)
            && (ValueXMLLocationEnum::PARENT_ATTRIBUTE !== $this->_valueXMLLocations[self::FIELD_IS_DERIVED]
                || $this->isDerived->_nonValueFieldDefined())) {
            $xw->startElement(self::FIELD_IS_DERIVED);
            $this->isDerived->xmlSerialize($xw, $config, $this->_valueXMLLocations[self::FIELD_IS_DERIVED]);
            $xw->endElement();
        }
        if (isset($this->type)) {
            $xw->startElement(self::FIELD_TYPE);
            $this->type->xmlSerialize($xw, $config);
            $xw->endElement();
        }
        if (isset($this->preference)
            && (ValueXMLLocationEnum::PARENT_ATTRIBUTE !== $this->_valueXMLLocations[self::FIELD_PREFERENCE]
                || $this->preference->_nonValueFieldDefined())) {
            $xw->startElement(self::FIELD_PREFERENCE);
            $this->preference->xmlSerialize($xw, $config, $this->_valueXMLLocations[self::FIELD_PREFERENCE]);
            $xw->endElement();
        }
        if (isset($this->container)) {
            $xw->startElement(self::FIELD_CONTAINER);
            $this->container->xmlSerialize($xw, $config);
            $xw->endElement();
        }
        if (isset($this->requirement)
            && (ValueXMLLocationEnum::PARENT_ATTRIBUTE !== $this->_valueXMLLocations[self::FIELD_REQUIREMENT]
                || $this->requirement->_nonValueFieldDefined())) {
            $xw->startElement(self::FIELD_REQUIREMENT);
            $this->requirement->xmlSerialize($xw, $config, $this->_valueXMLLocations[self::FIELD_REQUIREMENT]);
            $xw->endElement();
        }
        if (isset($this->retentionTime)) {
            $xw->startElement(self::FIELD_RETENTION_TIME);
            $this->retentionTime->xmlSerialize($xw, $config);
            $xw->endElement();
        }
        if (isset($this->rejectionCriterion)) {
            foreach ($this->rejectionCriterion as $v) {
                $xw->startElement(self::FIELD_REJECTION_CRITERION);
                $v->xmlSerialize($xw, $config);
                $xw->endElement();
            }
        }
        if (isset($this->handling)) {
            foreach ($this->handling as $v) {
                $xw->startElement(self::FIELD_HANDLING);
                $v->xmlSerialize($xw, $config);
                $xw->endElement();
            }
        }
    }

    /**
     * @param \stdClass $decoded
     * @param \OpenEMR\FHIR\Encoding\UnserializeConfig $config
     * @param null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRSpecimenDefinition\FHIRSpecimenDefinitionTypeTested $type
     * @return \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRSpecimenDefinition\FHIRSpecimenDefinitionTypeTested
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
        } else if (!($type instanceof FHIRSpecimenDefinitionTypeTested)) {
            throw new \RuntimeException(sprintf(
                '%s::jsonUnserialize - $type must be instance of \\%s or null, %s seen.',
                ltrim(substr(self::class, (int)strrpos(self::class, '\\')), '\\'),
                static::class,
                $type::class
            ));
        }
        parent::jsonUnserialize($decoded, $config, $type);
        if (isset($decoded->isDerived)
            || isset($decoded->_isDerived)
            || property_exists($decoded, self::FIELD_IS_DERIVED)
            || property_exists($decoded, self::FIELD_IS_DERIVED_EXT)) {
            $v = $decoded->_isDerived ?? new \stdClass();
            $v->value = $decoded->isDerived ?? null;
            $type->setIsDerived(FHIRBoolean::jsonUnserialize($v, $config));
        }
        if (isset($decoded->type) || property_exists($decoded, self::FIELD_TYPE)) {
            if (is_array($decoded->type)) {
                $type->setType(FHIRCodeableConcept::jsonUnserialize(reset($decoded->type), $config));
            } else {
                $type->setType(FHIRCodeableConcept::jsonUnserialize($decoded->type, $config));
            }
        }
        if (isset($decoded->preference)
            || isset($decoded->_preference)
            || property_exists($decoded, self::FIELD_PREFERENCE)
            || property_exists($decoded, self::FIELD_PREFERENCE_EXT)) {
            $v = $decoded->_preference ?? new \stdClass();
            $v->value = $decoded->preference ?? null;
            $type->setPreference(FHIRSpecimenContainedPreference::jsonUnserialize($v, $config));
        }
        if (isset($decoded->container) || property_exists($decoded, self::FIELD_CONTAINER)) {
            if (is_array($decoded->container)) {
                $type->setContainer(FHIRSpecimenDefinitionContainer::jsonUnserialize(reset($decoded->container), $config));
            } else {
                $type->setContainer(FHIRSpecimenDefinitionContainer::jsonUnserialize($decoded->container, $config));
            }
        }
        if (isset($decoded->requirement)
            || isset($decoded->_requirement)
            || property_exists($decoded, self::FIELD_REQUIREMENT)
            || property_exists($decoded, self::FIELD_REQUIREMENT_EXT)) {
            $v = $decoded->_requirement ?? new \stdClass();
            $v->value = $decoded->requirement ?? null;
            $type->setRequirement(FHIRString::jsonUnserialize($v, $config));
        }
        if (isset($decoded->retentionTime) || property_exists($decoded, self::FIELD_RETENTION_TIME)) {
            if (is_array($decoded->retentionTime)) {
                $type->setRetentionTime(FHIRDuration::jsonUnserialize(reset($decoded->retentionTime), $config));
            } else {
                $type->setRetentionTime(FHIRDuration::jsonUnserialize($decoded->retentionTime, $config));
            }
        }
        if (isset($decoded->rejectionCriterion) || property_exists($decoded, self::FIELD_REJECTION_CRITERION)) {
            if (is_object($decoded->rejectionCriterion)) {
                $vals = [$decoded->rejectionCriterion];
                $type->_setJSONFieldElideSingletonArray(self::FIELD_REJECTION_CRITERION, true);
            } else {
                $vals = $decoded->rejectionCriterion;
            }
            foreach($vals as $v) {
                $type->addRejectionCriterion(FHIRCodeableConcept::jsonUnserialize($v, $config));
            }
        }
        if (isset($decoded->handling) || property_exists($decoded, self::FIELD_HANDLING)) {
            if (is_object($decoded->handling)) {
                $vals = [$decoded->handling];
                $type->_setJSONFieldElideSingletonArray(self::FIELD_HANDLING, true);
            } else {
                $vals = $decoded->handling;
            }
            foreach($vals as $v) {
                $type->addHandling(FHIRSpecimenDefinitionHandling::jsonUnserialize($v, $config));
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
        if (isset($this->isDerived)) {
            if (null !== ($val = $this->isDerived->getValue())) {
                $out->isDerived = $val;
            }
            if ($this->isDerived->_nonValueFieldDefined()) {
                $ext = $this->isDerived->jsonSerialize();
                unset($ext->value);
                $out->_isDerived = $ext;
            }
        }
        if (isset($this->type)) {
            $out->type = $this->type;
        }
        if (isset($this->preference)) {
            if (null !== ($val = $this->preference->getValue())) {
                $out->preference = $val;
            }
            if ($this->preference->_nonValueFieldDefined()) {
                $ext = $this->preference->jsonSerialize();
                unset($ext->value);
                $out->_preference = $ext;
            }
        }
        if (isset($this->container)) {
            $out->container = $this->container;
        }
        if (isset($this->requirement)) {
            if (null !== ($val = $this->requirement->getValue())) {
                $out->requirement = $val;
            }
            if ($this->requirement->_nonValueFieldDefined()) {
                $ext = $this->requirement->jsonSerialize();
                unset($ext->value);
                $out->_requirement = $ext;
            }
        }
        if (isset($this->retentionTime)) {
            $out->retentionTime = $this->retentionTime;
        }
        if (isset($this->rejectionCriterion) && [] !== $this->rejectionCriterion) {
            if ($this->_getJSONFieldElideSingletonArray(self::FIELD_REJECTION_CRITERION) && 1 === count($this->rejectionCriterion)) {
                $out->rejectionCriterion = $this->rejectionCriterion[0];
            } else {
                $out->rejectionCriterion = $this->rejectionCriterion;
            }
        }
        if (isset($this->handling) && [] !== $this->handling) {
            if ($this->_getJSONFieldElideSingletonArray(self::FIELD_HANDLING) && 1 === count($this->handling)) {
                $out->handling = $this->handling[0];
            } else {
                $out->handling = $this->handling;
            }
        }
        return $out;
    }
}
