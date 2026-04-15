<?php

declare(strict_types=1);

namespace OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIREvidenceVariable;

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
use OpenEMR\FHIR\Versions\R4\Types\FHIRBooleanPrimitive;
use OpenEMR\FHIR\Versions\R4\Types\FHIRCanonicalPrimitive;
use OpenEMR\FHIR\Versions\R4\Types\FHIRCodePrimitive\FHIRGroupMeasureList;
use OpenEMR\FHIR\Versions\R4\Types\FHIRDateTimePrimitive;
use OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement;
use OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRTiming;
use OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBoolean;
use OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRCanonical;
use OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRCodeableConcept;
use OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRDataRequirement;
use OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRDateTime;
use OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRExpression;
use OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRExtension;
use OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRGroupMeasure;
use OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRPeriod;
use OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRQuantity\FHIRDuration;
use OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRReference;
use OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRString;
use OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRTriggerDefinition;
use OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRUsageContext;
use OpenEMR\FHIR\Versions\R4\Types\FHIRStringPrimitive;
use OpenEMR\FHIR\Versions\R4\VersionConstants;

/**
 * The EvidenceVariable resource describes a "PICO" element that knowledge
 * (evidence, assertion, recommendation) is about.
 */
#[\Attribute(\Attribute::TARGET_PROPERTY)]
class FHIREvidenceVariableCharacteristic extends FHIRBackboneElement
{
    use TypeValidationsTrait,
        JSONSerializationOptionsTrait,
        XMLSerializationOptionsTrait;

    // name of FHIR type this class describes
    public const FHIR_TYPE_NAME = VersionConstants::TYPE_NAME_EVIDENCE_VARIABLE_DOT_CHARACTERISTIC;

    /* class_default.php:56 */
    public const FIELD_DESCRIPTION = 'description';
    public const FIELD_DESCRIPTION_EXT = '_description';
    public const FIELD_DEFINITION_REFERENCE = 'definitionReference';
    public const FIELD_DEFINITION_CANONICAL = 'definitionCanonical';
    public const FIELD_DEFINITION_CANONICAL_EXT = '_definitionCanonical';
    public const FIELD_DEFINITION_CODEABLE_CONCEPT = 'definitionCodeableConcept';
    public const FIELD_DEFINITION_EXPRESSION = 'definitionExpression';
    public const FIELD_DEFINITION_DATA_REQUIREMENT = 'definitionDataRequirement';
    public const FIELD_DEFINITION_TRIGGER_DEFINITION = 'definitionTriggerDefinition';
    public const FIELD_USAGE_CONTEXT = 'usageContext';
    public const FIELD_EXCLUDE = 'exclude';
    public const FIELD_EXCLUDE_EXT = '_exclude';
    public const FIELD_PARTICIPANT_EFFECTIVE_DATE_TIME = 'participantEffectiveDateTime';
    public const FIELD_PARTICIPANT_EFFECTIVE_DATE_TIME_EXT = '_participantEffectiveDateTime';
    public const FIELD_PARTICIPANT_EFFECTIVE_PERIOD = 'participantEffectivePeriod';
    public const FIELD_PARTICIPANT_EFFECTIVE_DURATION = 'participantEffectiveDuration';
    public const FIELD_PARTICIPANT_EFFECTIVE_TIMING = 'participantEffectiveTiming';
    public const FIELD_TIME_FROM_START = 'timeFromStart';
    public const FIELD_GROUP_MEASURE = 'groupMeasure';
    public const FIELD_GROUP_MEASURE_EXT = '_groupMeasure';

    /* class_default.php:75 */
    private const _FHIR_VALIDATION_RULES = [
    ];

    /* class_default.php:96 */
    private array $_valueXMLLocations = [
        self::FIELD_DESCRIPTION => ValueXMLLocationEnum::CONTAINER_ATTRIBUTE,
        self::FIELD_DEFINITION_CANONICAL => ValueXMLLocationEnum::CONTAINER_ATTRIBUTE,
        self::FIELD_EXCLUDE => ValueXMLLocationEnum::CONTAINER_ATTRIBUTE,
        self::FIELD_PARTICIPANT_EFFECTIVE_DATE_TIME => ValueXMLLocationEnum::CONTAINER_ATTRIBUTE,
        self::FIELD_GROUP_MEASURE => ValueXMLLocationEnum::CONTAINER_ATTRIBUTE,
    ];

    /* class_default.php:112 */
    /**
     * A sequence of Unicode characters
     * Note that FHIR strings SHALL NOT exceed 1MB in size
     * If the element is present, it must have either a \@value, an \@id, or extensions
     *
     * A short, natural language description of the characteristic that could be used
     * to communicate the criteria to an end-user.
     *
     * @var \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRString
     */
    #[FHIRString]
    protected FHIRString $description;
    /**
     * A reference from one resource to another.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * Define members of the evidence element using Codes (such as condition,
     * medication, or observation), Expressions ( using an expression language such as
     * FHIRPath or CQL) or DataRequirements (such as Diabetes diagnosis onset in the
     * last year). (choose any one of definition*, but only one)
     *
     * @var \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRReference
     */
    #[FHIRReference]
    protected FHIRReference $definitionReference;
    /**
     * A URI that is a reference to a canonical URL on a FHIR resource
     * see [Canonical References](references.html#canonical)
     * If the element is present, it must have either a \@value, an \@id referenced from
     * the Narrative, or extensions
     *
     * Define members of the evidence element using Codes (such as condition,
     * medication, or observation), Expressions ( using an expression language such as
     * FHIRPath or CQL) or DataRequirements (such as Diabetes diagnosis onset in the
     * last year). (choose any one of definition*, but only one)
     *
     * @var \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRCanonical
     */
    #[FHIRCanonical]
    protected FHIRCanonical $definitionCanonical;
    /**
     * A concept that may be defined by a formal reference to a terminology or ontology
     * or may be provided by text.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * Define members of the evidence element using Codes (such as condition,
     * medication, or observation), Expressions ( using an expression language such as
     * FHIRPath or CQL) or DataRequirements (such as Diabetes diagnosis onset in the
     * last year). (choose any one of definition*, but only one)
     *
     * @var \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRCodeableConcept
     */
    #[FHIRCodeableConcept]
    protected FHIRCodeableConcept $definitionCodeableConcept;
    /**
     * A expression that is evaluated in a specified context and returns a value. The
     * context of use of the expression must specify the context in which the
     * expression is evaluated, and how the result of the expression is used.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * Define members of the evidence element using Codes (such as condition,
     * medication, or observation), Expressions ( using an expression language such as
     * FHIRPath or CQL) or DataRequirements (such as Diabetes diagnosis onset in the
     * last year). (choose any one of definition*, but only one)
     *
     * @var \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRExpression
     */
    #[FHIRExpression]
    protected FHIRExpression $definitionExpression;
    /**
     * Describes a required data item for evaluation in terms of the type of data, and
     * optional code or date-based filters of the data.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * Define members of the evidence element using Codes (such as condition,
     * medication, or observation), Expressions ( using an expression language such as
     * FHIRPath or CQL) or DataRequirements (such as Diabetes diagnosis onset in the
     * last year). (choose any one of definition*, but only one)
     *
     * @var \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRDataRequirement
     */
    #[FHIRDataRequirement]
    protected FHIRDataRequirement $definitionDataRequirement;
    /**
     * A description of a triggering event. Triggering events can be named events, data
     * events, or periodic, as determined by the type element.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * Define members of the evidence element using Codes (such as condition,
     * medication, or observation), Expressions ( using an expression language such as
     * FHIRPath or CQL) or DataRequirements (such as Diabetes diagnosis onset in the
     * last year). (choose any one of definition*, but only one)
     *
     * @var \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRTriggerDefinition
     */
    #[FHIRTriggerDefinition]
    protected FHIRTriggerDefinition $definitionTriggerDefinition;
    /**
     * Specifies clinical/business/etc. metadata that can be used to retrieve, index
     * and/or categorize an artifact. This metadata can either be specific to the
     * applicable population (e.g., age category, DRG) or the specific context of care
     * (e.g., venue, care setting, provider of care).
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * Use UsageContext to define the members of the population, such as Age Ranges,
     * Genders, Settings.
     *
     * @var iterable<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRUsageContext>
     */
    #[FHIRUsageContext]
    protected array $usageContext;
    /**
     * Value of "true" or "false"
     * If the element is present, it must have either a \@value, an \@id, or extensions
     *
     * When true, members with this characteristic are excluded from the element.
     *
     * @var \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBoolean
     */
    #[FHIRBoolean]
    protected FHIRBoolean $exclude;
    /**
     * A date, date-time or partial date (e.g. just year or year + month). If hours and
     * minutes are specified, a time zone SHALL be populated. The format is a union of
     * the schema types gYear, gYearMonth, date and dateTime. Seconds must be provided
     * due to schema type constraints but may be zero-filled and may be ignored. Dates
     * SHALL be valid dates.
     * If the element is present, it must have either a \@value, an \@id, or extensions
     *
     * Indicates what effective period the study covers. (choose any one of
     * participantEffective*, but only one)
     *
     * @var \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRDateTime
     */
    #[FHIRDateTime]
    protected FHIRDateTime $participantEffectiveDateTime;
    /**
     * A time period defined by a start and end date and optionally time.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * Indicates what effective period the study covers. (choose any one of
     * participantEffective*, but only one)
     *
     * @var \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRPeriod
     */
    #[FHIRPeriod]
    protected FHIRPeriod $participantEffectivePeriod;
    /**
     * A length of time.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * Indicates what effective period the study covers. (choose any one of
     * participantEffective*, but only one)
     *
     * @var \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRQuantity\FHIRDuration
     */
    #[FHIRDuration]
    protected FHIRDuration $participantEffectiveDuration;
    /**
     * Specifies an event that may occur multiple times. Timing schedules are used to
     * record when things are planned, expected or requested to occur. The most common
     * usage is in dosage instructions for medications. They are also used when
     * planning care of various kinds, and may be used for reporting the schedule to
     * which past regular activities were carried out.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * Indicates what effective period the study covers. (choose any one of
     * participantEffective*, but only one)
     *
     * @var \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRTiming
     */
    #[FHIRTiming]
    protected FHIRTiming $participantEffectiveTiming;
    /**
     * A length of time.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * Indicates duration from the participant's study entry.
     *
     * @var \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRQuantity\FHIRDuration
     */
    #[FHIRDuration]
    protected FHIRDuration $timeFromStart;
    /**
     * Possible group measure aggregates (E.g. Mean, Median).
     * If the element is present, it must have either a \@value, an \@id, or extensions
     *
     * Indicates how elements are aggregated within the study effective period.
     *
     * @var \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRGroupMeasure
     */
    #[FHIRGroupMeasure]
    protected FHIRGroupMeasure $groupMeasure;

    /* constructor.php:61 */
    /**
     * FHIREvidenceVariableCharacteristic Constructor
     * @param null|iterable<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRExtension> $extension
     * @param null|string|\OpenEMR\FHIR\Versions\R4\Types\FHIRStringPrimitive $id
     * @param null|iterable<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRExtension> $modifierExtension
     * @param null|string|\OpenEMR\FHIR\Versions\R4\Types\FHIRStringPrimitive|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRString $description
     * @param null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRReference $definitionReference
     * @param null|string|\OpenEMR\FHIR\Versions\R4\Types\FHIRCanonicalPrimitive|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRCanonical $definitionCanonical
     * @param null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRCodeableConcept $definitionCodeableConcept
     * @param null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRExpression $definitionExpression
     * @param null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRDataRequirement $definitionDataRequirement
     * @param null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRTriggerDefinition $definitionTriggerDefinition
     * @param null|iterable<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRUsageContext> $usageContext
     * @param null|string|bool|\OpenEMR\FHIR\Versions\R4\Types\FHIRBooleanPrimitive|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBoolean $exclude
     * @param null|string|\DateTimeInterface|\OpenEMR\FHIR\Versions\R4\Types\FHIRDateTimePrimitive|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRDateTime $participantEffectiveDateTime
     * @param null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRPeriod $participantEffectivePeriod
     * @param null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRQuantity\FHIRDuration $participantEffectiveDuration
     * @param null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRTiming $participantEffectiveTiming
     * @param null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRQuantity\FHIRDuration $timeFromStart
     * @param null|string|\OpenEMR\FHIR\Versions\R4\Types\FHIRCodePrimitive\FHIRGroupMeasureList|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRGroupMeasure $groupMeasure
     * @param null|string[] $fhirComments
     */
    public function __construct(null|iterable $extension = null,
                                null|string|FHIRStringPrimitive $id = null,
                                null|iterable $modifierExtension = null,
                                null|string|FHIRStringPrimitive|FHIRString $description = null,
                                null|FHIRReference $definitionReference = null,
                                null|string|FHIRCanonicalPrimitive|FHIRCanonical $definitionCanonical = null,
                                null|FHIRCodeableConcept $definitionCodeableConcept = null,
                                null|FHIRExpression $definitionExpression = null,
                                null|FHIRDataRequirement $definitionDataRequirement = null,
                                null|FHIRTriggerDefinition $definitionTriggerDefinition = null,
                                null|iterable $usageContext = null,
                                null|string|bool|FHIRBooleanPrimitive|FHIRBoolean $exclude = null,
                                null|string|\DateTimeInterface|FHIRDateTimePrimitive|FHIRDateTime $participantEffectiveDateTime = null,
                                null|FHIRPeriod $participantEffectivePeriod = null,
                                null|FHIRDuration $participantEffectiveDuration = null,
                                null|FHIRTiming $participantEffectiveTiming = null,
                                null|FHIRDuration $timeFromStart = null,
                                null|string|FHIRGroupMeasureList|FHIRGroupMeasure $groupMeasure = null,
                                null|iterable $fhirComments = null)
    {
        parent::__construct(extension: $extension,
                            id: $id,
                            modifierExtension: $modifierExtension,
                            fhirComments: $fhirComments);
        if (null !== $description) {
            $this->setDescription($description);
        }
        if (null !== $definitionReference) {
            $this->setDefinitionReference($definitionReference);
        }
        if (null !== $definitionCanonical) {
            $this->setDefinitionCanonical($definitionCanonical);
        }
        if (null !== $definitionCodeableConcept) {
            $this->setDefinitionCodeableConcept($definitionCodeableConcept);
        }
        if (null !== $definitionExpression) {
            $this->setDefinitionExpression($definitionExpression);
        }
        if (null !== $definitionDataRequirement) {
            $this->setDefinitionDataRequirement($definitionDataRequirement);
        }
        if (null !== $definitionTriggerDefinition) {
            $this->setDefinitionTriggerDefinition($definitionTriggerDefinition);
        }
        if (null !== $usageContext) {
            $this->setUsageContext(...$usageContext);
        }
        if (null !== $exclude) {
            $this->setExclude($exclude);
        }
        if (null !== $participantEffectiveDateTime) {
            $this->setParticipantEffectiveDateTime($participantEffectiveDateTime);
        }
        if (null !== $participantEffectivePeriod) {
            $this->setParticipantEffectivePeriod($participantEffectivePeriod);
        }
        if (null !== $participantEffectiveDuration) {
            $this->setParticipantEffectiveDuration($participantEffectiveDuration);
        }
        if (null !== $participantEffectiveTiming) {
            $this->setParticipantEffectiveTiming($participantEffectiveTiming);
        }
        if (null !== $timeFromStart) {
            $this->setTimeFromStart($timeFromStart);
        }
        if (null !== $groupMeasure) {
            $this->setGroupMeasure($groupMeasure);
        }
    }

    /* class_default.php:145 */
    public function _getFHIRTypeName(): string
    {
        return self::FHIR_TYPE_NAME;
    }

    /* class_default.php:174 */
    /**
     * A sequence of Unicode characters
     * Note that FHIR strings SHALL NOT exceed 1MB in size
     * If the element is present, it must have either a \@value, an \@id, or extensions
     *
     * A short, natural language description of the characteristic that could be used
     * to communicate the criteria to an end-user.
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
     * A short, natural language description of the characteristic that could be used
     * to communicate the criteria to an end-user.
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
     * A reference from one resource to another.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * Define members of the evidence element using Codes (such as condition,
     * medication, or observation), Expressions ( using an expression language such as
     * FHIRPath or CQL) or DataRequirements (such as Diabetes diagnosis onset in the
     * last year). (choose any one of definition*, but only one)
     *
     * @return null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRReference
     */
    public function getDefinitionReference(): null|FHIRReference
    {
        return $this->definitionReference ?? null;
    }

    /**
     * A reference from one resource to another.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * Define members of the evidence element using Codes (such as condition,
     * medication, or observation), Expressions ( using an expression language such as
     * FHIRPath or CQL) or DataRequirements (such as Diabetes diagnosis onset in the
     * last year). (choose any one of definition*, but only one)
     *
     * @param null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRReference $definitionReference
     * @return static
     */
    public function setDefinitionReference(null|FHIRReference $definitionReference): self
    {
        if (null === $definitionReference) {
            unset($this->definitionReference);
            return $this;
        }
        $this->definitionReference = $definitionReference;
        return $this;
    }

    /**
     * A URI that is a reference to a canonical URL on a FHIR resource
     * see [Canonical References](references.html#canonical)
     * If the element is present, it must have either a \@value, an \@id referenced from
     * the Narrative, or extensions
     *
     * Define members of the evidence element using Codes (such as condition,
     * medication, or observation), Expressions ( using an expression language such as
     * FHIRPath or CQL) or DataRequirements (such as Diabetes diagnosis onset in the
     * last year). (choose any one of definition*, but only one)
     *
     * @return null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRCanonical
     */
    public function getDefinitionCanonical(): null|FHIRCanonical
    {
        return $this->definitionCanonical ?? null;
    }

    /**
     * A URI that is a reference to a canonical URL on a FHIR resource
     * see [Canonical References](references.html#canonical)
     * If the element is present, it must have either a \@value, an \@id referenced from
     * the Narrative, or extensions
     *
     * Define members of the evidence element using Codes (such as condition,
     * medication, or observation), Expressions ( using an expression language such as
     * FHIRPath or CQL) or DataRequirements (such as Diabetes diagnosis onset in the
     * last year). (choose any one of definition*, but only one)
     *
     * @param null|string|\OpenEMR\FHIR\Versions\R4\Types\FHIRCanonicalPrimitive|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRCanonical $definitionCanonical
     * @return static
     */
    public function setDefinitionCanonical(null|string|FHIRCanonicalPrimitive|FHIRCanonical $definitionCanonical): self
    {
        if (null === $definitionCanonical) {
            unset($this->definitionCanonical);
            return $this;
        }
        if (!($definitionCanonical instanceof FHIRCanonical)) {
            $definitionCanonical = new FHIRCanonical(value: $definitionCanonical);
        }
        $this->definitionCanonical = $definitionCanonical;
        return $this;
    }

    /**
     * A concept that may be defined by a formal reference to a terminology or ontology
     * or may be provided by text.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * Define members of the evidence element using Codes (such as condition,
     * medication, or observation), Expressions ( using an expression language such as
     * FHIRPath or CQL) or DataRequirements (such as Diabetes diagnosis onset in the
     * last year). (choose any one of definition*, but only one)
     *
     * @return null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRCodeableConcept
     */
    public function getDefinitionCodeableConcept(): null|FHIRCodeableConcept
    {
        return $this->definitionCodeableConcept ?? null;
    }

    /**
     * A concept that may be defined by a formal reference to a terminology or ontology
     * or may be provided by text.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * Define members of the evidence element using Codes (such as condition,
     * medication, or observation), Expressions ( using an expression language such as
     * FHIRPath or CQL) or DataRequirements (such as Diabetes diagnosis onset in the
     * last year). (choose any one of definition*, but only one)
     *
     * @param null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRCodeableConcept $definitionCodeableConcept
     * @return static
     */
    public function setDefinitionCodeableConcept(null|FHIRCodeableConcept $definitionCodeableConcept): self
    {
        if (null === $definitionCodeableConcept) {
            unset($this->definitionCodeableConcept);
            return $this;
        }
        $this->definitionCodeableConcept = $definitionCodeableConcept;
        return $this;
    }

    /**
     * A expression that is evaluated in a specified context and returns a value. The
     * context of use of the expression must specify the context in which the
     * expression is evaluated, and how the result of the expression is used.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * Define members of the evidence element using Codes (such as condition,
     * medication, or observation), Expressions ( using an expression language such as
     * FHIRPath or CQL) or DataRequirements (such as Diabetes diagnosis onset in the
     * last year). (choose any one of definition*, but only one)
     *
     * @return null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRExpression
     */
    public function getDefinitionExpression(): null|FHIRExpression
    {
        return $this->definitionExpression ?? null;
    }

    /**
     * A expression that is evaluated in a specified context and returns a value. The
     * context of use of the expression must specify the context in which the
     * expression is evaluated, and how the result of the expression is used.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * Define members of the evidence element using Codes (such as condition,
     * medication, or observation), Expressions ( using an expression language such as
     * FHIRPath or CQL) or DataRequirements (such as Diabetes diagnosis onset in the
     * last year). (choose any one of definition*, but only one)
     *
     * @param null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRExpression $definitionExpression
     * @return static
     */
    public function setDefinitionExpression(null|FHIRExpression $definitionExpression): self
    {
        if (null === $definitionExpression) {
            unset($this->definitionExpression);
            return $this;
        }
        $this->definitionExpression = $definitionExpression;
        return $this;
    }

    /**
     * Describes a required data item for evaluation in terms of the type of data, and
     * optional code or date-based filters of the data.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * Define members of the evidence element using Codes (such as condition,
     * medication, or observation), Expressions ( using an expression language such as
     * FHIRPath or CQL) or DataRequirements (such as Diabetes diagnosis onset in the
     * last year). (choose any one of definition*, but only one)
     *
     * @return null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRDataRequirement
     */
    public function getDefinitionDataRequirement(): null|FHIRDataRequirement
    {
        return $this->definitionDataRequirement ?? null;
    }

    /**
     * Describes a required data item for evaluation in terms of the type of data, and
     * optional code or date-based filters of the data.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * Define members of the evidence element using Codes (such as condition,
     * medication, or observation), Expressions ( using an expression language such as
     * FHIRPath or CQL) or DataRequirements (such as Diabetes diagnosis onset in the
     * last year). (choose any one of definition*, but only one)
     *
     * @param null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRDataRequirement $definitionDataRequirement
     * @return static
     */
    public function setDefinitionDataRequirement(null|FHIRDataRequirement $definitionDataRequirement): self
    {
        if (null === $definitionDataRequirement) {
            unset($this->definitionDataRequirement);
            return $this;
        }
        $this->definitionDataRequirement = $definitionDataRequirement;
        return $this;
    }

    /**
     * A description of a triggering event. Triggering events can be named events, data
     * events, or periodic, as determined by the type element.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * Define members of the evidence element using Codes (such as condition,
     * medication, or observation), Expressions ( using an expression language such as
     * FHIRPath or CQL) or DataRequirements (such as Diabetes diagnosis onset in the
     * last year). (choose any one of definition*, but only one)
     *
     * @return null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRTriggerDefinition
     */
    public function getDefinitionTriggerDefinition(): null|FHIRTriggerDefinition
    {
        return $this->definitionTriggerDefinition ?? null;
    }

    /**
     * A description of a triggering event. Triggering events can be named events, data
     * events, or periodic, as determined by the type element.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * Define members of the evidence element using Codes (such as condition,
     * medication, or observation), Expressions ( using an expression language such as
     * FHIRPath or CQL) or DataRequirements (such as Diabetes diagnosis onset in the
     * last year). (choose any one of definition*, but only one)
     *
     * @param null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRTriggerDefinition $definitionTriggerDefinition
     * @return static
     */
    public function setDefinitionTriggerDefinition(null|FHIRTriggerDefinition $definitionTriggerDefinition): self
    {
        if (null === $definitionTriggerDefinition) {
            unset($this->definitionTriggerDefinition);
            return $this;
        }
        $this->definitionTriggerDefinition = $definitionTriggerDefinition;
        return $this;
    }

    /**
     * Specifies clinical/business/etc. metadata that can be used to retrieve, index
     * and/or categorize an artifact. This metadata can either be specific to the
     * applicable population (e.g., age category, DRG) or the specific context of care
     * (e.g., venue, care setting, provider of care).
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * Use UsageContext to define the members of the population, such as Age Ranges,
     * Genders, Settings.
     *
     * @return iterable<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRUsageContext>
     */
    public function getUsageContext(): array
    {
        return $this->usageContext ?? [];
    }

    /**
     * @return \ArrayIterator<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRUsageContext>
     */
    public function getUsageContextIterator(): iterable
    {
        if (!isset($this->usageContext)) {
            return new \EmptyIterator();
        }
        return new \ArrayIterator($this->usageContext);
    }

    /**
     * Specifies clinical/business/etc. metadata that can be used to retrieve, index
     * and/or categorize an artifact. This metadata can either be specific to the
     * applicable population (e.g., age category, DRG) or the specific context of care
     * (e.g., venue, care setting, provider of care).
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * Use UsageContext to define the members of the population, such as Age Ranges,
     * Genders, Settings.
     *
     * @param \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRUsageContext $usageContext
     * @return static
     */
    public function addUsageContext(FHIRUsageContext $usageContext): self
    {
        if (!isset($this->usageContext)) {
            $this->usageContext = [];
        }
        $this->usageContext[] = $usageContext;
        return $this;
    }

    /**
     * Specifies clinical/business/etc. metadata that can be used to retrieve, index
     * and/or categorize an artifact. This metadata can either be specific to the
     * applicable population (e.g., age category, DRG) or the specific context of care
     * (e.g., venue, care setting, provider of care).
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * Use UsageContext to define the members of the population, such as Age Ranges,
     * Genders, Settings.
     *
     * @param \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRUsageContext ...$usageContext
     * @return static
     */
    public function setUsageContext(FHIRUsageContext ...$usageContext): self
    {
        if ([] === $usageContext) {
            unset($this->usageContext);
            return $this;
        }
        $this->usageContext = $usageContext;
        return $this;
    }

    /**
     * Value of "true" or "false"
     * If the element is present, it must have either a \@value, an \@id, or extensions
     *
     * When true, members with this characteristic are excluded from the element.
     *
     * @return null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBoolean
     */
    public function getExclude(): null|FHIRBoolean
    {
        return $this->exclude ?? null;
    }

    /**
     * Value of "true" or "false"
     * If the element is present, it must have either a \@value, an \@id, or extensions
     *
     * When true, members with this characteristic are excluded from the element.
     *
     * @param null|string|bool|\OpenEMR\FHIR\Versions\R4\Types\FHIRBooleanPrimitive|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBoolean $exclude
     * @return static
     */
    public function setExclude(null|string|bool|FHIRBooleanPrimitive|FHIRBoolean $exclude): self
    {
        if (null === $exclude) {
            unset($this->exclude);
            return $this;
        }
        if (!($exclude instanceof FHIRBoolean)) {
            $exclude = new FHIRBoolean(value: $exclude);
        }
        $this->exclude = $exclude;
        return $this;
    }

    /**
     * A date, date-time or partial date (e.g. just year or year + month). If hours and
     * minutes are specified, a time zone SHALL be populated. The format is a union of
     * the schema types gYear, gYearMonth, date and dateTime. Seconds must be provided
     * due to schema type constraints but may be zero-filled and may be ignored. Dates
     * SHALL be valid dates.
     * If the element is present, it must have either a \@value, an \@id, or extensions
     *
     * Indicates what effective period the study covers. (choose any one of
     * participantEffective*, but only one)
     *
     * @return null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRDateTime
     */
    public function getParticipantEffectiveDateTime(): null|FHIRDateTime
    {
        return $this->participantEffectiveDateTime ?? null;
    }

    /**
     * A date, date-time or partial date (e.g. just year or year + month). If hours and
     * minutes are specified, a time zone SHALL be populated. The format is a union of
     * the schema types gYear, gYearMonth, date and dateTime. Seconds must be provided
     * due to schema type constraints but may be zero-filled and may be ignored. Dates
     * SHALL be valid dates.
     * If the element is present, it must have either a \@value, an \@id, or extensions
     *
     * Indicates what effective period the study covers. (choose any one of
     * participantEffective*, but only one)
     *
     * @param null|string|\DateTimeInterface|\OpenEMR\FHIR\Versions\R4\Types\FHIRDateTimePrimitive|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRDateTime $participantEffectiveDateTime
     * @return static
     */
    public function setParticipantEffectiveDateTime(null|string|\DateTimeInterface|FHIRDateTimePrimitive|FHIRDateTime $participantEffectiveDateTime): self
    {
        if (null === $participantEffectiveDateTime) {
            unset($this->participantEffectiveDateTime);
            return $this;
        }
        if (!($participantEffectiveDateTime instanceof FHIRDateTime)) {
            $participantEffectiveDateTime = new FHIRDateTime(value: $participantEffectiveDateTime);
        }
        $this->participantEffectiveDateTime = $participantEffectiveDateTime;
        return $this;
    }

    /**
     * A time period defined by a start and end date and optionally time.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * Indicates what effective period the study covers. (choose any one of
     * participantEffective*, but only one)
     *
     * @return null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRPeriod
     */
    public function getParticipantEffectivePeriod(): null|FHIRPeriod
    {
        return $this->participantEffectivePeriod ?? null;
    }

    /**
     * A time period defined by a start and end date and optionally time.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * Indicates what effective period the study covers. (choose any one of
     * participantEffective*, but only one)
     *
     * @param null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRPeriod $participantEffectivePeriod
     * @return static
     */
    public function setParticipantEffectivePeriod(null|FHIRPeriod $participantEffectivePeriod): self
    {
        if (null === $participantEffectivePeriod) {
            unset($this->participantEffectivePeriod);
            return $this;
        }
        $this->participantEffectivePeriod = $participantEffectivePeriod;
        return $this;
    }

    /**
     * A length of time.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * Indicates what effective period the study covers. (choose any one of
     * participantEffective*, but only one)
     *
     * @return null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRQuantity\FHIRDuration
     */
    public function getParticipantEffectiveDuration(): null|FHIRDuration
    {
        return $this->participantEffectiveDuration ?? null;
    }

    /**
     * A length of time.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * Indicates what effective period the study covers. (choose any one of
     * participantEffective*, but only one)
     *
     * @param null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRQuantity\FHIRDuration $participantEffectiveDuration
     * @return static
     */
    public function setParticipantEffectiveDuration(null|FHIRDuration $participantEffectiveDuration): self
    {
        if (null === $participantEffectiveDuration) {
            unset($this->participantEffectiveDuration);
            return $this;
        }
        $this->participantEffectiveDuration = $participantEffectiveDuration;
        return $this;
    }

    /**
     * Specifies an event that may occur multiple times. Timing schedules are used to
     * record when things are planned, expected or requested to occur. The most common
     * usage is in dosage instructions for medications. They are also used when
     * planning care of various kinds, and may be used for reporting the schedule to
     * which past regular activities were carried out.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * Indicates what effective period the study covers. (choose any one of
     * participantEffective*, but only one)
     *
     * @return null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRTiming
     */
    public function getParticipantEffectiveTiming(): null|FHIRTiming
    {
        return $this->participantEffectiveTiming ?? null;
    }

    /**
     * Specifies an event that may occur multiple times. Timing schedules are used to
     * record when things are planned, expected or requested to occur. The most common
     * usage is in dosage instructions for medications. They are also used when
     * planning care of various kinds, and may be used for reporting the schedule to
     * which past regular activities were carried out.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * Indicates what effective period the study covers. (choose any one of
     * participantEffective*, but only one)
     *
     * @param null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRTiming $participantEffectiveTiming
     * @return static
     */
    public function setParticipantEffectiveTiming(null|FHIRTiming $participantEffectiveTiming): self
    {
        if (null === $participantEffectiveTiming) {
            unset($this->participantEffectiveTiming);
            return $this;
        }
        $this->participantEffectiveTiming = $participantEffectiveTiming;
        return $this;
    }

    /**
     * A length of time.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * Indicates duration from the participant's study entry.
     *
     * @return null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRQuantity\FHIRDuration
     */
    public function getTimeFromStart(): null|FHIRDuration
    {
        return $this->timeFromStart ?? null;
    }

    /**
     * A length of time.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * Indicates duration from the participant's study entry.
     *
     * @param null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRQuantity\FHIRDuration $timeFromStart
     * @return static
     */
    public function setTimeFromStart(null|FHIRDuration $timeFromStart): self
    {
        if (null === $timeFromStart) {
            unset($this->timeFromStart);
            return $this;
        }
        $this->timeFromStart = $timeFromStart;
        return $this;
    }

    /**
     * Possible group measure aggregates (E.g. Mean, Median).
     * If the element is present, it must have either a \@value, an \@id, or extensions
     *
     * Indicates how elements are aggregated within the study effective period.
     *
     * @return null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRGroupMeasure
     */
    public function getGroupMeasure(): null|FHIRGroupMeasure
    {
        return $this->groupMeasure ?? null;
    }

    /**
     * Possible group measure aggregates (E.g. Mean, Median).
     * If the element is present, it must have either a \@value, an \@id, or extensions
     *
     * Indicates how elements are aggregated within the study effective period.
     *
     * @param null|string|\OpenEMR\FHIR\Versions\R4\Types\FHIRCodePrimitive\FHIRGroupMeasureList|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRGroupMeasure $groupMeasure
     * @return static
     */
    public function setGroupMeasure(null|string|FHIRGroupMeasureList|FHIRGroupMeasure $groupMeasure): self
    {
        if (null === $groupMeasure) {
            unset($this->groupMeasure);
            return $this;
        }
        if (!($groupMeasure instanceof FHIRGroupMeasure)) {
            $groupMeasure = new FHIRGroupMeasure(value: $groupMeasure);
        }
        $this->groupMeasure = $groupMeasure;
        return $this;
    }

    /* class_default.php:201 */
    /**
     * @param \SimpleXMLElement $element
     * @param \OpenEMR\FHIR\Encoding\UnserializeConfig $config
     * @param null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIREvidenceVariable\FHIREvidenceVariableCharacteristic $type
     * @return \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIREvidenceVariable\FHIREvidenceVariableCharacteristic
     * @throws \Exception
     */
    public static function xmlUnserialize(\SimpleXMLElement $element,
                                          UnserializeConfig $config,
                                          null|ElementTypeInterface $type = null): self
    {
        if (null === $type) {
            $type = new static();
        } else if (!($type instanceof FHIREvidenceVariableCharacteristic)) {
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
            } else if (self::FIELD_DESCRIPTION === $cen) {
                $type->setDescription(FHIRString::xmlUnserialize($ce, $config));
            } else if (self::FIELD_DEFINITION_REFERENCE === $cen) {
                $type->setDefinitionReference(FHIRReference::xmlUnserialize($ce, $config));
            } else if (self::FIELD_DEFINITION_CANONICAL === $cen) {
                $type->setDefinitionCanonical(FHIRCanonical::xmlUnserialize($ce, $config));
            } else if (self::FIELD_DEFINITION_CODEABLE_CONCEPT === $cen) {
                $type->setDefinitionCodeableConcept(FHIRCodeableConcept::xmlUnserialize($ce, $config));
            } else if (self::FIELD_DEFINITION_EXPRESSION === $cen) {
                $type->setDefinitionExpression(FHIRExpression::xmlUnserialize($ce, $config));
            } else if (self::FIELD_DEFINITION_DATA_REQUIREMENT === $cen) {
                $type->setDefinitionDataRequirement(FHIRDataRequirement::xmlUnserialize($ce, $config));
            } else if (self::FIELD_DEFINITION_TRIGGER_DEFINITION === $cen) {
                $type->setDefinitionTriggerDefinition(FHIRTriggerDefinition::xmlUnserialize($ce, $config));
            } else if (self::FIELD_USAGE_CONTEXT === $cen) {
                $type->addUsageContext(FHIRUsageContext::xmlUnserialize($ce, $config));
            } else if (self::FIELD_EXCLUDE === $cen) {
                $type->setExclude(FHIRBoolean::xmlUnserialize($ce, $config));
            } else if (self::FIELD_PARTICIPANT_EFFECTIVE_DATE_TIME === $cen) {
                $type->setParticipantEffectiveDateTime(FHIRDateTime::xmlUnserialize($ce, $config));
            } else if (self::FIELD_PARTICIPANT_EFFECTIVE_PERIOD === $cen) {
                $type->setParticipantEffectivePeriod(FHIRPeriod::xmlUnserialize($ce, $config));
            } else if (self::FIELD_PARTICIPANT_EFFECTIVE_DURATION === $cen) {
                $type->setParticipantEffectiveDuration(FHIRDuration::xmlUnserialize($ce, $config));
            } else if (self::FIELD_PARTICIPANT_EFFECTIVE_TIMING === $cen) {
                $type->setParticipantEffectiveTiming(FHIRTiming::xmlUnserialize($ce, $config));
            } else if (self::FIELD_TIME_FROM_START === $cen) {
                $type->setTimeFromStart(FHIRDuration::xmlUnserialize($ce, $config));
            } else if (self::FIELD_GROUP_MEASURE === $cen) {
                $type->setGroupMeasure(FHIRGroupMeasure::xmlUnserialize($ce, $config));
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
        if (isset($attributes[self::FIELD_DEFINITION_CANONICAL])) {
            if (isset($type->definitionCanonical)) {
                $type->definitionCanonical->setValue((string)$attributes[self::FIELD_DEFINITION_CANONICAL]);
            } else {
                $type->setDefinitionCanonical((string)$attributes[self::FIELD_DEFINITION_CANONICAL]);
            }
            $type->_setXMLFieldValueLocation(self::FIELD_DEFINITION_CANONICAL, ValueXMLLocationEnum::PARENT_ATTRIBUTE);
        }
        if (isset($attributes[self::FIELD_EXCLUDE])) {
            if (isset($type->exclude)) {
                $type->exclude->setValue((string)$attributes[self::FIELD_EXCLUDE]);
            } else {
                $type->setExclude((string)$attributes[self::FIELD_EXCLUDE]);
            }
            $type->_setXMLFieldValueLocation(self::FIELD_EXCLUDE, ValueXMLLocationEnum::PARENT_ATTRIBUTE);
        }
        if (isset($attributes[self::FIELD_PARTICIPANT_EFFECTIVE_DATE_TIME])) {
            if (isset($type->participantEffectiveDateTime)) {
                $type->participantEffectiveDateTime->setValue((string)$attributes[self::FIELD_PARTICIPANT_EFFECTIVE_DATE_TIME]);
            } else {
                $type->setParticipantEffectiveDateTime((string)$attributes[self::FIELD_PARTICIPANT_EFFECTIVE_DATE_TIME]);
            }
            $type->_setXMLFieldValueLocation(self::FIELD_PARTICIPANT_EFFECTIVE_DATE_TIME, ValueXMLLocationEnum::PARENT_ATTRIBUTE);
        }
        if (isset($attributes[self::FIELD_GROUP_MEASURE])) {
            if (isset($type->groupMeasure)) {
                $type->groupMeasure->setValue((string)$attributes[self::FIELD_GROUP_MEASURE]);
            } else {
                $type->setGroupMeasure((string)$attributes[self::FIELD_GROUP_MEASURE]);
            }
            $type->_setXMLFieldValueLocation(self::FIELD_GROUP_MEASURE, ValueXMLLocationEnum::PARENT_ATTRIBUTE);
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
        if (isset($this->definitionCanonical) && ValueXMLLocationEnum::PARENT_ATTRIBUTE === $this->_valueXMLLocations[self::FIELD_DEFINITION_CANONICAL]) {
            $xw->writeAttribute(self::FIELD_DEFINITION_CANONICAL, $this->definitionCanonical->_getValueAsString());
        }
        if (isset($this->exclude) && ValueXMLLocationEnum::PARENT_ATTRIBUTE === $this->_valueXMLLocations[self::FIELD_EXCLUDE]) {
            $xw->writeAttribute(self::FIELD_EXCLUDE, $this->exclude->_getValueAsString());
        }
        if (isset($this->participantEffectiveDateTime) && ValueXMLLocationEnum::PARENT_ATTRIBUTE === $this->_valueXMLLocations[self::FIELD_PARTICIPANT_EFFECTIVE_DATE_TIME]) {
            $xw->writeAttribute(self::FIELD_PARTICIPANT_EFFECTIVE_DATE_TIME, $this->participantEffectiveDateTime->_getValueAsString());
        }
        if (isset($this->groupMeasure) && ValueXMLLocationEnum::PARENT_ATTRIBUTE === $this->_valueXMLLocations[self::FIELD_GROUP_MEASURE]) {
            $xw->writeAttribute(self::FIELD_GROUP_MEASURE, $this->groupMeasure->_getValueAsString());
        }
        parent::xmlSerialize($xw, $config);
        if (isset($this->description)
            && (ValueXMLLocationEnum::PARENT_ATTRIBUTE !== $this->_valueXMLLocations[self::FIELD_DESCRIPTION]
                || $this->description->_nonValueFieldDefined())) {
            $xw->startElement(self::FIELD_DESCRIPTION);
            $this->description->xmlSerialize($xw, $config, $this->_valueXMLLocations[self::FIELD_DESCRIPTION]);
            $xw->endElement();
        }
        if (isset($this->definitionReference)) {
            $xw->startElement(self::FIELD_DEFINITION_REFERENCE);
            $this->definitionReference->xmlSerialize($xw, $config);
            $xw->endElement();
        }
        if (isset($this->definitionCanonical)
            && (ValueXMLLocationEnum::PARENT_ATTRIBUTE !== $this->_valueXMLLocations[self::FIELD_DEFINITION_CANONICAL]
                || $this->definitionCanonical->_nonValueFieldDefined())) {
            $xw->startElement(self::FIELD_DEFINITION_CANONICAL);
            $this->definitionCanonical->xmlSerialize($xw, $config, $this->_valueXMLLocations[self::FIELD_DEFINITION_CANONICAL]);
            $xw->endElement();
        }
        if (isset($this->definitionCodeableConcept)) {
            $xw->startElement(self::FIELD_DEFINITION_CODEABLE_CONCEPT);
            $this->definitionCodeableConcept->xmlSerialize($xw, $config);
            $xw->endElement();
        }
        if (isset($this->definitionExpression)) {
            $xw->startElement(self::FIELD_DEFINITION_EXPRESSION);
            $this->definitionExpression->xmlSerialize($xw, $config);
            $xw->endElement();
        }
        if (isset($this->definitionDataRequirement)) {
            $xw->startElement(self::FIELD_DEFINITION_DATA_REQUIREMENT);
            $this->definitionDataRequirement->xmlSerialize($xw, $config);
            $xw->endElement();
        }
        if (isset($this->definitionTriggerDefinition)) {
            $xw->startElement(self::FIELD_DEFINITION_TRIGGER_DEFINITION);
            $this->definitionTriggerDefinition->xmlSerialize($xw, $config);
            $xw->endElement();
        }
        if (isset($this->usageContext)) {
            foreach ($this->usageContext as $v) {
                $xw->startElement(self::FIELD_USAGE_CONTEXT);
                $v->xmlSerialize($xw, $config);
                $xw->endElement();
            }
        }
        if (isset($this->exclude)
            && (ValueXMLLocationEnum::PARENT_ATTRIBUTE !== $this->_valueXMLLocations[self::FIELD_EXCLUDE]
                || $this->exclude->_nonValueFieldDefined())) {
            $xw->startElement(self::FIELD_EXCLUDE);
            $this->exclude->xmlSerialize($xw, $config, $this->_valueXMLLocations[self::FIELD_EXCLUDE]);
            $xw->endElement();
        }
        if (isset($this->participantEffectiveDateTime)
            && (ValueXMLLocationEnum::PARENT_ATTRIBUTE !== $this->_valueXMLLocations[self::FIELD_PARTICIPANT_EFFECTIVE_DATE_TIME]
                || $this->participantEffectiveDateTime->_nonValueFieldDefined())) {
            $xw->startElement(self::FIELD_PARTICIPANT_EFFECTIVE_DATE_TIME);
            $this->participantEffectiveDateTime->xmlSerialize($xw, $config, $this->_valueXMLLocations[self::FIELD_PARTICIPANT_EFFECTIVE_DATE_TIME]);
            $xw->endElement();
        }
        if (isset($this->participantEffectivePeriod)) {
            $xw->startElement(self::FIELD_PARTICIPANT_EFFECTIVE_PERIOD);
            $this->participantEffectivePeriod->xmlSerialize($xw, $config);
            $xw->endElement();
        }
        if (isset($this->participantEffectiveDuration)) {
            $xw->startElement(self::FIELD_PARTICIPANT_EFFECTIVE_DURATION);
            $this->participantEffectiveDuration->xmlSerialize($xw, $config);
            $xw->endElement();
        }
        if (isset($this->participantEffectiveTiming)) {
            $xw->startElement(self::FIELD_PARTICIPANT_EFFECTIVE_TIMING);
            $this->participantEffectiveTiming->xmlSerialize($xw, $config);
            $xw->endElement();
        }
        if (isset($this->timeFromStart)) {
            $xw->startElement(self::FIELD_TIME_FROM_START);
            $this->timeFromStart->xmlSerialize($xw, $config);
            $xw->endElement();
        }
        if (isset($this->groupMeasure)
            && (ValueXMLLocationEnum::PARENT_ATTRIBUTE !== $this->_valueXMLLocations[self::FIELD_GROUP_MEASURE]
                || $this->groupMeasure->_nonValueFieldDefined())) {
            $xw->startElement(self::FIELD_GROUP_MEASURE);
            $this->groupMeasure->xmlSerialize($xw, $config, $this->_valueXMLLocations[self::FIELD_GROUP_MEASURE]);
            $xw->endElement();
        }
    }

    /**
     * @param \stdClass $decoded
     * @param \OpenEMR\FHIR\Encoding\UnserializeConfig $config
     * @param null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIREvidenceVariable\FHIREvidenceVariableCharacteristic $type
     * @return \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIREvidenceVariable\FHIREvidenceVariableCharacteristic
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
        } else if (!($type instanceof FHIREvidenceVariableCharacteristic)) {
            throw new \RuntimeException(sprintf(
                '%s::jsonUnserialize - $type must be instance of \\%s or null, %s seen.',
                ltrim(substr(self::class, (int)strrpos(self::class, '\\')), '\\'),
                static::class,
                $type::class
            ));
        }
        parent::jsonUnserialize($decoded, $config, $type);
        if (isset($decoded->description)
            || isset($decoded->_description)
            || property_exists($decoded, self::FIELD_DESCRIPTION)
            || property_exists($decoded, self::FIELD_DESCRIPTION_EXT)) {
            $v = $decoded->_description ?? new \stdClass();
            $v->value = $decoded->description ?? null;
            $type->setDescription(FHIRString::jsonUnserialize($v, $config));
        }
        if (isset($decoded->definitionReference) || property_exists($decoded, self::FIELD_DEFINITION_REFERENCE)) {
            if (is_array($decoded->definitionReference)) {
                $type->setDefinitionReference(FHIRReference::jsonUnserialize(reset($decoded->definitionReference), $config));
            } else {
                $type->setDefinitionReference(FHIRReference::jsonUnserialize($decoded->definitionReference, $config));
            }
        }
        if (isset($decoded->definitionCanonical)
            || isset($decoded->_definitionCanonical)
            || property_exists($decoded, self::FIELD_DEFINITION_CANONICAL)
            || property_exists($decoded, self::FIELD_DEFINITION_CANONICAL_EXT)) {
            $v = $decoded->_definitionCanonical ?? new \stdClass();
            $v->value = $decoded->definitionCanonical ?? null;
            $type->setDefinitionCanonical(FHIRCanonical::jsonUnserialize($v, $config));
        }
        if (isset($decoded->definitionCodeableConcept) || property_exists($decoded, self::FIELD_DEFINITION_CODEABLE_CONCEPT)) {
            if (is_array($decoded->definitionCodeableConcept)) {
                $type->setDefinitionCodeableConcept(FHIRCodeableConcept::jsonUnserialize(reset($decoded->definitionCodeableConcept), $config));
            } else {
                $type->setDefinitionCodeableConcept(FHIRCodeableConcept::jsonUnserialize($decoded->definitionCodeableConcept, $config));
            }
        }
        if (isset($decoded->definitionExpression) || property_exists($decoded, self::FIELD_DEFINITION_EXPRESSION)) {
            if (is_array($decoded->definitionExpression)) {
                $type->setDefinitionExpression(FHIRExpression::jsonUnserialize(reset($decoded->definitionExpression), $config));
            } else {
                $type->setDefinitionExpression(FHIRExpression::jsonUnserialize($decoded->definitionExpression, $config));
            }
        }
        if (isset($decoded->definitionDataRequirement) || property_exists($decoded, self::FIELD_DEFINITION_DATA_REQUIREMENT)) {
            if (is_array($decoded->definitionDataRequirement)) {
                $type->setDefinitionDataRequirement(FHIRDataRequirement::jsonUnserialize(reset($decoded->definitionDataRequirement), $config));
            } else {
                $type->setDefinitionDataRequirement(FHIRDataRequirement::jsonUnserialize($decoded->definitionDataRequirement, $config));
            }
        }
        if (isset($decoded->definitionTriggerDefinition) || property_exists($decoded, self::FIELD_DEFINITION_TRIGGER_DEFINITION)) {
            if (is_array($decoded->definitionTriggerDefinition)) {
                $type->setDefinitionTriggerDefinition(FHIRTriggerDefinition::jsonUnserialize(reset($decoded->definitionTriggerDefinition), $config));
            } else {
                $type->setDefinitionTriggerDefinition(FHIRTriggerDefinition::jsonUnserialize($decoded->definitionTriggerDefinition, $config));
            }
        }
        if (isset($decoded->usageContext) || property_exists($decoded, self::FIELD_USAGE_CONTEXT)) {
            if (is_object($decoded->usageContext)) {
                $vals = [$decoded->usageContext];
                $type->_setJSONFieldElideSingletonArray(self::FIELD_USAGE_CONTEXT, true);
            } else {
                $vals = $decoded->usageContext;
            }
            foreach($vals as $v) {
                $type->addUsageContext(FHIRUsageContext::jsonUnserialize($v, $config));
            }
        }
        if (isset($decoded->exclude)
            || isset($decoded->_exclude)
            || property_exists($decoded, self::FIELD_EXCLUDE)
            || property_exists($decoded, self::FIELD_EXCLUDE_EXT)) {
            $v = $decoded->_exclude ?? new \stdClass();
            $v->value = $decoded->exclude ?? null;
            $type->setExclude(FHIRBoolean::jsonUnserialize($v, $config));
        }
        if (isset($decoded->participantEffectiveDateTime)
            || isset($decoded->_participantEffectiveDateTime)
            || property_exists($decoded, self::FIELD_PARTICIPANT_EFFECTIVE_DATE_TIME)
            || property_exists($decoded, self::FIELD_PARTICIPANT_EFFECTIVE_DATE_TIME_EXT)) {
            $v = $decoded->_participantEffectiveDateTime ?? new \stdClass();
            $v->value = $decoded->participantEffectiveDateTime ?? null;
            $type->setParticipantEffectiveDateTime(FHIRDateTime::jsonUnserialize($v, $config));
        }
        if (isset($decoded->participantEffectivePeriod) || property_exists($decoded, self::FIELD_PARTICIPANT_EFFECTIVE_PERIOD)) {
            if (is_array($decoded->participantEffectivePeriod)) {
                $type->setParticipantEffectivePeriod(FHIRPeriod::jsonUnserialize(reset($decoded->participantEffectivePeriod), $config));
            } else {
                $type->setParticipantEffectivePeriod(FHIRPeriod::jsonUnserialize($decoded->participantEffectivePeriod, $config));
            }
        }
        if (isset($decoded->participantEffectiveDuration) || property_exists($decoded, self::FIELD_PARTICIPANT_EFFECTIVE_DURATION)) {
            if (is_array($decoded->participantEffectiveDuration)) {
                $type->setParticipantEffectiveDuration(FHIRDuration::jsonUnserialize(reset($decoded->participantEffectiveDuration), $config));
            } else {
                $type->setParticipantEffectiveDuration(FHIRDuration::jsonUnserialize($decoded->participantEffectiveDuration, $config));
            }
        }
        if (isset($decoded->participantEffectiveTiming) || property_exists($decoded, self::FIELD_PARTICIPANT_EFFECTIVE_TIMING)) {
            if (is_array($decoded->participantEffectiveTiming)) {
                $type->setParticipantEffectiveTiming(FHIRTiming::jsonUnserialize(reset($decoded->participantEffectiveTiming), $config));
            } else {
                $type->setParticipantEffectiveTiming(FHIRTiming::jsonUnserialize($decoded->participantEffectiveTiming, $config));
            }
        }
        if (isset($decoded->timeFromStart) || property_exists($decoded, self::FIELD_TIME_FROM_START)) {
            if (is_array($decoded->timeFromStart)) {
                $type->setTimeFromStart(FHIRDuration::jsonUnserialize(reset($decoded->timeFromStart), $config));
            } else {
                $type->setTimeFromStart(FHIRDuration::jsonUnserialize($decoded->timeFromStart, $config));
            }
        }
        if (isset($decoded->groupMeasure)
            || isset($decoded->_groupMeasure)
            || property_exists($decoded, self::FIELD_GROUP_MEASURE)
            || property_exists($decoded, self::FIELD_GROUP_MEASURE_EXT)) {
            $v = $decoded->_groupMeasure ?? new \stdClass();
            $v->value = $decoded->groupMeasure ?? null;
            $type->setGroupMeasure(FHIRGroupMeasure::jsonUnserialize($v, $config));
        }
        return $type;
    }

    /**
     * @return \stdClass
     */
    public function jsonSerialize(): mixed
    {
        $out = parent::jsonSerialize();
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
        if (isset($this->definitionReference)) {
            $out->definitionReference = $this->definitionReference;
        }
        if (isset($this->definitionCanonical)) {
            if (null !== ($val = $this->definitionCanonical->getValue())) {
                $out->definitionCanonical = $val;
            }
            if ($this->definitionCanonical->_nonValueFieldDefined()) {
                $ext = $this->definitionCanonical->jsonSerialize();
                unset($ext->value);
                $out->_definitionCanonical = $ext;
            }
        }
        if (isset($this->definitionCodeableConcept)) {
            $out->definitionCodeableConcept = $this->definitionCodeableConcept;
        }
        if (isset($this->definitionExpression)) {
            $out->definitionExpression = $this->definitionExpression;
        }
        if (isset($this->definitionDataRequirement)) {
            $out->definitionDataRequirement = $this->definitionDataRequirement;
        }
        if (isset($this->definitionTriggerDefinition)) {
            $out->definitionTriggerDefinition = $this->definitionTriggerDefinition;
        }
        if (isset($this->usageContext) && [] !== $this->usageContext) {
            if ($this->_getJSONFieldElideSingletonArray(self::FIELD_USAGE_CONTEXT) && 1 === count($this->usageContext)) {
                $out->usageContext = $this->usageContext[0];
            } else {
                $out->usageContext = $this->usageContext;
            }
        }
        if (isset($this->exclude)) {
            if (null !== ($val = $this->exclude->getValue())) {
                $out->exclude = $val;
            }
            if ($this->exclude->_nonValueFieldDefined()) {
                $ext = $this->exclude->jsonSerialize();
                unset($ext->value);
                $out->_exclude = $ext;
            }
        }
        if (isset($this->participantEffectiveDateTime)) {
            if (null !== ($val = $this->participantEffectiveDateTime->getValue())) {
                $out->participantEffectiveDateTime = $val;
            }
            if ($this->participantEffectiveDateTime->_nonValueFieldDefined()) {
                $ext = $this->participantEffectiveDateTime->jsonSerialize();
                unset($ext->value);
                $out->_participantEffectiveDateTime = $ext;
            }
        }
        if (isset($this->participantEffectivePeriod)) {
            $out->participantEffectivePeriod = $this->participantEffectivePeriod;
        }
        if (isset($this->participantEffectiveDuration)) {
            $out->participantEffectiveDuration = $this->participantEffectiveDuration;
        }
        if (isset($this->participantEffectiveTiming)) {
            $out->participantEffectiveTiming = $this->participantEffectiveTiming;
        }
        if (isset($this->timeFromStart)) {
            $out->timeFromStart = $this->timeFromStart;
        }
        if (isset($this->groupMeasure)) {
            if (null !== ($val = $this->groupMeasure->getValue())) {
                $out->groupMeasure = $val;
            }
            if ($this->groupMeasure->_nonValueFieldDefined()) {
                $ext = $this->groupMeasure->jsonSerialize();
                unset($ext->value);
                $out->_groupMeasure = $ext;
            }
        }
        return $out;
    }
}
