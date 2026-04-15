<?php

declare(strict_types=1);

namespace OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRResearchElementDefinition;

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
use OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRString;
use OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRUsageContext;
use OpenEMR\FHIR\Versions\R4\Types\FHIRStringPrimitive;
use OpenEMR\FHIR\Versions\R4\VersionConstants;

/**
 * The ResearchElementDefinition resource describes a "PICO" element that knowledge
 * (evidence, assertion, recommendation) is about.
 */
#[\Attribute(\Attribute::TARGET_PROPERTY)]
class FHIRResearchElementDefinitionCharacteristic extends FHIRBackboneElement
{
    use TypeValidationsTrait,
        JSONSerializationOptionsTrait,
        XMLSerializationOptionsTrait;

    // name of FHIR type this class describes
    public const FHIR_TYPE_NAME = VersionConstants::TYPE_NAME_RESEARCH_ELEMENT_DEFINITION_DOT_CHARACTERISTIC;

    /* class_default.php:56 */
    public const FIELD_DEFINITION_CODEABLE_CONCEPT = 'definitionCodeableConcept';
    public const FIELD_DEFINITION_CANONICAL = 'definitionCanonical';
    public const FIELD_DEFINITION_CANONICAL_EXT = '_definitionCanonical';
    public const FIELD_DEFINITION_EXPRESSION = 'definitionExpression';
    public const FIELD_DEFINITION_DATA_REQUIREMENT = 'definitionDataRequirement';
    public const FIELD_USAGE_CONTEXT = 'usageContext';
    public const FIELD_EXCLUDE = 'exclude';
    public const FIELD_EXCLUDE_EXT = '_exclude';
    public const FIELD_UNIT_OF_MEASURE = 'unitOfMeasure';
    public const FIELD_STUDY_EFFECTIVE_DESCRIPTION = 'studyEffectiveDescription';
    public const FIELD_STUDY_EFFECTIVE_DESCRIPTION_EXT = '_studyEffectiveDescription';
    public const FIELD_STUDY_EFFECTIVE_DATE_TIME = 'studyEffectiveDateTime';
    public const FIELD_STUDY_EFFECTIVE_DATE_TIME_EXT = '_studyEffectiveDateTime';
    public const FIELD_STUDY_EFFECTIVE_PERIOD = 'studyEffectivePeriod';
    public const FIELD_STUDY_EFFECTIVE_DURATION = 'studyEffectiveDuration';
    public const FIELD_STUDY_EFFECTIVE_TIMING = 'studyEffectiveTiming';
    public const FIELD_STUDY_EFFECTIVE_TIME_FROM_START = 'studyEffectiveTimeFromStart';
    public const FIELD_STUDY_EFFECTIVE_GROUP_MEASURE = 'studyEffectiveGroupMeasure';
    public const FIELD_STUDY_EFFECTIVE_GROUP_MEASURE_EXT = '_studyEffectiveGroupMeasure';
    public const FIELD_PARTICIPANT_EFFECTIVE_DESCRIPTION = 'participantEffectiveDescription';
    public const FIELD_PARTICIPANT_EFFECTIVE_DESCRIPTION_EXT = '_participantEffectiveDescription';
    public const FIELD_PARTICIPANT_EFFECTIVE_DATE_TIME = 'participantEffectiveDateTime';
    public const FIELD_PARTICIPANT_EFFECTIVE_DATE_TIME_EXT = '_participantEffectiveDateTime';
    public const FIELD_PARTICIPANT_EFFECTIVE_PERIOD = 'participantEffectivePeriod';
    public const FIELD_PARTICIPANT_EFFECTIVE_DURATION = 'participantEffectiveDuration';
    public const FIELD_PARTICIPANT_EFFECTIVE_TIMING = 'participantEffectiveTiming';
    public const FIELD_PARTICIPANT_EFFECTIVE_TIME_FROM_START = 'participantEffectiveTimeFromStart';
    public const FIELD_PARTICIPANT_EFFECTIVE_GROUP_MEASURE = 'participantEffectiveGroupMeasure';
    public const FIELD_PARTICIPANT_EFFECTIVE_GROUP_MEASURE_EXT = '_participantEffectiveGroupMeasure';

    /* class_default.php:75 */
    private const _FHIR_VALIDATION_RULES = [
    ];

    /* class_default.php:96 */
    private array $_valueXMLLocations = [
        self::FIELD_DEFINITION_CANONICAL => ValueXMLLocationEnum::CONTAINER_ATTRIBUTE,
        self::FIELD_EXCLUDE => ValueXMLLocationEnum::CONTAINER_ATTRIBUTE,
        self::FIELD_STUDY_EFFECTIVE_DESCRIPTION => ValueXMLLocationEnum::CONTAINER_ATTRIBUTE,
        self::FIELD_STUDY_EFFECTIVE_DATE_TIME => ValueXMLLocationEnum::CONTAINER_ATTRIBUTE,
        self::FIELD_STUDY_EFFECTIVE_GROUP_MEASURE => ValueXMLLocationEnum::CONTAINER_ATTRIBUTE,
        self::FIELD_PARTICIPANT_EFFECTIVE_DESCRIPTION => ValueXMLLocationEnum::CONTAINER_ATTRIBUTE,
        self::FIELD_PARTICIPANT_EFFECTIVE_DATE_TIME => ValueXMLLocationEnum::CONTAINER_ATTRIBUTE,
        self::FIELD_PARTICIPANT_EFFECTIVE_GROUP_MEASURE => ValueXMLLocationEnum::CONTAINER_ATTRIBUTE,
    ];

    /* class_default.php:112 */
    /**
     * A concept that may be defined by a formal reference to a terminology or ontology
     * or may be provided by text.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * Define members of the research element using Codes (such as condition,
     * medication, or observation), Expressions ( using an expression language such as
     * FHIRPath or CQL) or DataRequirements (such as Diabetes diagnosis onset in the
     * last year). (choose any one of definition*, but only one)
     *
     * @var \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRCodeableConcept
     */
    #[FHIRCodeableConcept]
    protected FHIRCodeableConcept $definitionCodeableConcept;
    /**
     * A URI that is a reference to a canonical URL on a FHIR resource
     * see [Canonical References](references.html#canonical)
     * If the element is present, it must have either a \@value, an \@id referenced from
     * the Narrative, or extensions
     *
     * Define members of the research element using Codes (such as condition,
     * medication, or observation), Expressions ( using an expression language such as
     * FHIRPath or CQL) or DataRequirements (such as Diabetes diagnosis onset in the
     * last year). (choose any one of definition*, but only one)
     *
     * @var \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRCanonical
     */
    #[FHIRCanonical]
    protected FHIRCanonical $definitionCanonical;
    /**
     * A expression that is evaluated in a specified context and returns a value. The
     * context of use of the expression must specify the context in which the
     * expression is evaluated, and how the result of the expression is used.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * Define members of the research element using Codes (such as condition,
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
     * Define members of the research element using Codes (such as condition,
     * medication, or observation), Expressions ( using an expression language such as
     * FHIRPath or CQL) or DataRequirements (such as Diabetes diagnosis onset in the
     * last year). (choose any one of definition*, but only one)
     *
     * @var \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRDataRequirement
     */
    #[FHIRDataRequirement]
    protected FHIRDataRequirement $definitionDataRequirement;
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
     * A concept that may be defined by a formal reference to a terminology or ontology
     * or may be provided by text.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * Specifies the UCUM unit for the outcome.
     *
     * @var \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRCodeableConcept
     */
    #[FHIRCodeableConcept]
    protected FHIRCodeableConcept $unitOfMeasure;
    /**
     * A sequence of Unicode characters
     * Note that FHIR strings SHALL NOT exceed 1MB in size
     * If the element is present, it must have either a \@value, an \@id, or extensions
     *
     * A narrative description of the time period the study covers.
     *
     * @var \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRString
     */
    #[FHIRString]
    protected FHIRString $studyEffectiveDescription;
    /**
     * A date, date-time or partial date (e.g. just year or year + month). If hours and
     * minutes are specified, a time zone SHALL be populated. The format is a union of
     * the schema types gYear, gYearMonth, date and dateTime. Seconds must be provided
     * due to schema type constraints but may be zero-filled and may be ignored. Dates
     * SHALL be valid dates.
     * If the element is present, it must have either a \@value, an \@id, or extensions
     *
     * Indicates what effective period the study covers. (choose any one of
     * studyEffective*, but only one)
     *
     * @var \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRDateTime
     */
    #[FHIRDateTime]
    protected FHIRDateTime $studyEffectiveDateTime;
    /**
     * A time period defined by a start and end date and optionally time.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * Indicates what effective period the study covers. (choose any one of
     * studyEffective*, but only one)
     *
     * @var \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRPeriod
     */
    #[FHIRPeriod]
    protected FHIRPeriod $studyEffectivePeriod;
    /**
     * A length of time.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * Indicates what effective period the study covers. (choose any one of
     * studyEffective*, but only one)
     *
     * @var \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRQuantity\FHIRDuration
     */
    #[FHIRDuration]
    protected FHIRDuration $studyEffectiveDuration;
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
     * studyEffective*, but only one)
     *
     * @var \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRTiming
     */
    #[FHIRTiming]
    protected FHIRTiming $studyEffectiveTiming;
    /**
     * A length of time.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * Indicates duration from the study initiation.
     *
     * @var \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRQuantity\FHIRDuration
     */
    #[FHIRDuration]
    protected FHIRDuration $studyEffectiveTimeFromStart;
    /**
     * Possible group measure aggregates (E.g. Mean, Median).
     * If the element is present, it must have either a \@value, an \@id, or extensions
     *
     * Indicates how elements are aggregated within the study effective period.
     *
     * @var \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRGroupMeasure
     */
    #[FHIRGroupMeasure]
    protected FHIRGroupMeasure $studyEffectiveGroupMeasure;
    /**
     * A sequence of Unicode characters
     * Note that FHIR strings SHALL NOT exceed 1MB in size
     * If the element is present, it must have either a \@value, an \@id, or extensions
     *
     * A narrative description of the time period the study covers.
     *
     * @var \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRString
     */
    #[FHIRString]
    protected FHIRString $participantEffectiveDescription;
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
    protected FHIRDuration $participantEffectiveTimeFromStart;
    /**
     * Possible group measure aggregates (E.g. Mean, Median).
     * If the element is present, it must have either a \@value, an \@id, or extensions
     *
     * Indicates how elements are aggregated within the study effective period.
     *
     * @var \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRGroupMeasure
     */
    #[FHIRGroupMeasure]
    protected FHIRGroupMeasure $participantEffectiveGroupMeasure;

    /* constructor.php:61 */
    /**
     * FHIRResearchElementDefinitionCharacteristic Constructor
     * @param null|iterable<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRExtension> $extension
     * @param null|string|\OpenEMR\FHIR\Versions\R4\Types\FHIRStringPrimitive $id
     * @param null|iterable<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRExtension> $modifierExtension
     * @param null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRCodeableConcept $definitionCodeableConcept
     * @param null|string|\OpenEMR\FHIR\Versions\R4\Types\FHIRCanonicalPrimitive|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRCanonical $definitionCanonical
     * @param null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRExpression $definitionExpression
     * @param null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRDataRequirement $definitionDataRequirement
     * @param null|iterable<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRUsageContext> $usageContext
     * @param null|string|bool|\OpenEMR\FHIR\Versions\R4\Types\FHIRBooleanPrimitive|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBoolean $exclude
     * @param null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRCodeableConcept $unitOfMeasure
     * @param null|string|\OpenEMR\FHIR\Versions\R4\Types\FHIRStringPrimitive|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRString $studyEffectiveDescription
     * @param null|string|\DateTimeInterface|\OpenEMR\FHIR\Versions\R4\Types\FHIRDateTimePrimitive|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRDateTime $studyEffectiveDateTime
     * @param null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRPeriod $studyEffectivePeriod
     * @param null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRQuantity\FHIRDuration $studyEffectiveDuration
     * @param null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRTiming $studyEffectiveTiming
     * @param null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRQuantity\FHIRDuration $studyEffectiveTimeFromStart
     * @param null|string|\OpenEMR\FHIR\Versions\R4\Types\FHIRCodePrimitive\FHIRGroupMeasureList|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRGroupMeasure $studyEffectiveGroupMeasure
     * @param null|string|\OpenEMR\FHIR\Versions\R4\Types\FHIRStringPrimitive|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRString $participantEffectiveDescription
     * @param null|string|\DateTimeInterface|\OpenEMR\FHIR\Versions\R4\Types\FHIRDateTimePrimitive|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRDateTime $participantEffectiveDateTime
     * @param null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRPeriod $participantEffectivePeriod
     * @param null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRQuantity\FHIRDuration $participantEffectiveDuration
     * @param null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRTiming $participantEffectiveTiming
     * @param null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRQuantity\FHIRDuration $participantEffectiveTimeFromStart
     * @param null|string|\OpenEMR\FHIR\Versions\R4\Types\FHIRCodePrimitive\FHIRGroupMeasureList|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRGroupMeasure $participantEffectiveGroupMeasure
     * @param null|string[] $fhirComments
     */
    public function __construct(null|iterable $extension = null,
                                null|string|FHIRStringPrimitive $id = null,
                                null|iterable $modifierExtension = null,
                                null|FHIRCodeableConcept $definitionCodeableConcept = null,
                                null|string|FHIRCanonicalPrimitive|FHIRCanonical $definitionCanonical = null,
                                null|FHIRExpression $definitionExpression = null,
                                null|FHIRDataRequirement $definitionDataRequirement = null,
                                null|iterable $usageContext = null,
                                null|string|bool|FHIRBooleanPrimitive|FHIRBoolean $exclude = null,
                                null|FHIRCodeableConcept $unitOfMeasure = null,
                                null|string|FHIRStringPrimitive|FHIRString $studyEffectiveDescription = null,
                                null|string|\DateTimeInterface|FHIRDateTimePrimitive|FHIRDateTime $studyEffectiveDateTime = null,
                                null|FHIRPeriod $studyEffectivePeriod = null,
                                null|FHIRDuration $studyEffectiveDuration = null,
                                null|FHIRTiming $studyEffectiveTiming = null,
                                null|FHIRDuration $studyEffectiveTimeFromStart = null,
                                null|string|FHIRGroupMeasureList|FHIRGroupMeasure $studyEffectiveGroupMeasure = null,
                                null|string|FHIRStringPrimitive|FHIRString $participantEffectiveDescription = null,
                                null|string|\DateTimeInterface|FHIRDateTimePrimitive|FHIRDateTime $participantEffectiveDateTime = null,
                                null|FHIRPeriod $participantEffectivePeriod = null,
                                null|FHIRDuration $participantEffectiveDuration = null,
                                null|FHIRTiming $participantEffectiveTiming = null,
                                null|FHIRDuration $participantEffectiveTimeFromStart = null,
                                null|string|FHIRGroupMeasureList|FHIRGroupMeasure $participantEffectiveGroupMeasure = null,
                                null|iterable $fhirComments = null)
    {
        parent::__construct(extension: $extension,
                            id: $id,
                            modifierExtension: $modifierExtension,
                            fhirComments: $fhirComments);
        if (null !== $definitionCodeableConcept) {
            $this->setDefinitionCodeableConcept($definitionCodeableConcept);
        }
        if (null !== $definitionCanonical) {
            $this->setDefinitionCanonical($definitionCanonical);
        }
        if (null !== $definitionExpression) {
            $this->setDefinitionExpression($definitionExpression);
        }
        if (null !== $definitionDataRequirement) {
            $this->setDefinitionDataRequirement($definitionDataRequirement);
        }
        if (null !== $usageContext) {
            $this->setUsageContext(...$usageContext);
        }
        if (null !== $exclude) {
            $this->setExclude($exclude);
        }
        if (null !== $unitOfMeasure) {
            $this->setUnitOfMeasure($unitOfMeasure);
        }
        if (null !== $studyEffectiveDescription) {
            $this->setStudyEffectiveDescription($studyEffectiveDescription);
        }
        if (null !== $studyEffectiveDateTime) {
            $this->setStudyEffectiveDateTime($studyEffectiveDateTime);
        }
        if (null !== $studyEffectivePeriod) {
            $this->setStudyEffectivePeriod($studyEffectivePeriod);
        }
        if (null !== $studyEffectiveDuration) {
            $this->setStudyEffectiveDuration($studyEffectiveDuration);
        }
        if (null !== $studyEffectiveTiming) {
            $this->setStudyEffectiveTiming($studyEffectiveTiming);
        }
        if (null !== $studyEffectiveTimeFromStart) {
            $this->setStudyEffectiveTimeFromStart($studyEffectiveTimeFromStart);
        }
        if (null !== $studyEffectiveGroupMeasure) {
            $this->setStudyEffectiveGroupMeasure($studyEffectiveGroupMeasure);
        }
        if (null !== $participantEffectiveDescription) {
            $this->setParticipantEffectiveDescription($participantEffectiveDescription);
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
        if (null !== $participantEffectiveTimeFromStart) {
            $this->setParticipantEffectiveTimeFromStart($participantEffectiveTimeFromStart);
        }
        if (null !== $participantEffectiveGroupMeasure) {
            $this->setParticipantEffectiveGroupMeasure($participantEffectiveGroupMeasure);
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
     * Define members of the research element using Codes (such as condition,
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
     * Define members of the research element using Codes (such as condition,
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
     * A URI that is a reference to a canonical URL on a FHIR resource
     * see [Canonical References](references.html#canonical)
     * If the element is present, it must have either a \@value, an \@id referenced from
     * the Narrative, or extensions
     *
     * Define members of the research element using Codes (such as condition,
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
     * Define members of the research element using Codes (such as condition,
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
     * A expression that is evaluated in a specified context and returns a value. The
     * context of use of the expression must specify the context in which the
     * expression is evaluated, and how the result of the expression is used.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * Define members of the research element using Codes (such as condition,
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
     * Define members of the research element using Codes (such as condition,
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
     * Define members of the research element using Codes (such as condition,
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
     * Define members of the research element using Codes (such as condition,
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
     * A concept that may be defined by a formal reference to a terminology or ontology
     * or may be provided by text.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * Specifies the UCUM unit for the outcome.
     *
     * @return null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRCodeableConcept
     */
    public function getUnitOfMeasure(): null|FHIRCodeableConcept
    {
        return $this->unitOfMeasure ?? null;
    }

    /**
     * A concept that may be defined by a formal reference to a terminology or ontology
     * or may be provided by text.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * Specifies the UCUM unit for the outcome.
     *
     * @param null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRCodeableConcept $unitOfMeasure
     * @return static
     */
    public function setUnitOfMeasure(null|FHIRCodeableConcept $unitOfMeasure): self
    {
        if (null === $unitOfMeasure) {
            unset($this->unitOfMeasure);
            return $this;
        }
        $this->unitOfMeasure = $unitOfMeasure;
        return $this;
    }

    /**
     * A sequence of Unicode characters
     * Note that FHIR strings SHALL NOT exceed 1MB in size
     * If the element is present, it must have either a \@value, an \@id, or extensions
     *
     * A narrative description of the time period the study covers.
     *
     * @return null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRString
     */
    public function getStudyEffectiveDescription(): null|FHIRString
    {
        return $this->studyEffectiveDescription ?? null;
    }

    /**
     * A sequence of Unicode characters
     * Note that FHIR strings SHALL NOT exceed 1MB in size
     * If the element is present, it must have either a \@value, an \@id, or extensions
     *
     * A narrative description of the time period the study covers.
     *
     * @param null|string|\OpenEMR\FHIR\Versions\R4\Types\FHIRStringPrimitive|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRString $studyEffectiveDescription
     * @return static
     */
    public function setStudyEffectiveDescription(null|string|FHIRStringPrimitive|FHIRString $studyEffectiveDescription): self
    {
        if (null === $studyEffectiveDescription) {
            unset($this->studyEffectiveDescription);
            return $this;
        }
        if (!($studyEffectiveDescription instanceof FHIRString)) {
            $studyEffectiveDescription = new FHIRString(value: $studyEffectiveDescription);
        }
        $this->studyEffectiveDescription = $studyEffectiveDescription;
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
     * studyEffective*, but only one)
     *
     * @return null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRDateTime
     */
    public function getStudyEffectiveDateTime(): null|FHIRDateTime
    {
        return $this->studyEffectiveDateTime ?? null;
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
     * studyEffective*, but only one)
     *
     * @param null|string|\DateTimeInterface|\OpenEMR\FHIR\Versions\R4\Types\FHIRDateTimePrimitive|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRDateTime $studyEffectiveDateTime
     * @return static
     */
    public function setStudyEffectiveDateTime(null|string|\DateTimeInterface|FHIRDateTimePrimitive|FHIRDateTime $studyEffectiveDateTime): self
    {
        if (null === $studyEffectiveDateTime) {
            unset($this->studyEffectiveDateTime);
            return $this;
        }
        if (!($studyEffectiveDateTime instanceof FHIRDateTime)) {
            $studyEffectiveDateTime = new FHIRDateTime(value: $studyEffectiveDateTime);
        }
        $this->studyEffectiveDateTime = $studyEffectiveDateTime;
        return $this;
    }

    /**
     * A time period defined by a start and end date and optionally time.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * Indicates what effective period the study covers. (choose any one of
     * studyEffective*, but only one)
     *
     * @return null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRPeriod
     */
    public function getStudyEffectivePeriod(): null|FHIRPeriod
    {
        return $this->studyEffectivePeriod ?? null;
    }

    /**
     * A time period defined by a start and end date and optionally time.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * Indicates what effective period the study covers. (choose any one of
     * studyEffective*, but only one)
     *
     * @param null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRPeriod $studyEffectivePeriod
     * @return static
     */
    public function setStudyEffectivePeriod(null|FHIRPeriod $studyEffectivePeriod): self
    {
        if (null === $studyEffectivePeriod) {
            unset($this->studyEffectivePeriod);
            return $this;
        }
        $this->studyEffectivePeriod = $studyEffectivePeriod;
        return $this;
    }

    /**
     * A length of time.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * Indicates what effective period the study covers. (choose any one of
     * studyEffective*, but only one)
     *
     * @return null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRQuantity\FHIRDuration
     */
    public function getStudyEffectiveDuration(): null|FHIRDuration
    {
        return $this->studyEffectiveDuration ?? null;
    }

    /**
     * A length of time.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * Indicates what effective period the study covers. (choose any one of
     * studyEffective*, but only one)
     *
     * @param null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRQuantity\FHIRDuration $studyEffectiveDuration
     * @return static
     */
    public function setStudyEffectiveDuration(null|FHIRDuration $studyEffectiveDuration): self
    {
        if (null === $studyEffectiveDuration) {
            unset($this->studyEffectiveDuration);
            return $this;
        }
        $this->studyEffectiveDuration = $studyEffectiveDuration;
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
     * studyEffective*, but only one)
     *
     * @return null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRTiming
     */
    public function getStudyEffectiveTiming(): null|FHIRTiming
    {
        return $this->studyEffectiveTiming ?? null;
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
     * studyEffective*, but only one)
     *
     * @param null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRTiming $studyEffectiveTiming
     * @return static
     */
    public function setStudyEffectiveTiming(null|FHIRTiming $studyEffectiveTiming): self
    {
        if (null === $studyEffectiveTiming) {
            unset($this->studyEffectiveTiming);
            return $this;
        }
        $this->studyEffectiveTiming = $studyEffectiveTiming;
        return $this;
    }

    /**
     * A length of time.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * Indicates duration from the study initiation.
     *
     * @return null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRQuantity\FHIRDuration
     */
    public function getStudyEffectiveTimeFromStart(): null|FHIRDuration
    {
        return $this->studyEffectiveTimeFromStart ?? null;
    }

    /**
     * A length of time.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * Indicates duration from the study initiation.
     *
     * @param null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRQuantity\FHIRDuration $studyEffectiveTimeFromStart
     * @return static
     */
    public function setStudyEffectiveTimeFromStart(null|FHIRDuration $studyEffectiveTimeFromStart): self
    {
        if (null === $studyEffectiveTimeFromStart) {
            unset($this->studyEffectiveTimeFromStart);
            return $this;
        }
        $this->studyEffectiveTimeFromStart = $studyEffectiveTimeFromStart;
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
    public function getStudyEffectiveGroupMeasure(): null|FHIRGroupMeasure
    {
        return $this->studyEffectiveGroupMeasure ?? null;
    }

    /**
     * Possible group measure aggregates (E.g. Mean, Median).
     * If the element is present, it must have either a \@value, an \@id, or extensions
     *
     * Indicates how elements are aggregated within the study effective period.
     *
     * @param null|string|\OpenEMR\FHIR\Versions\R4\Types\FHIRCodePrimitive\FHIRGroupMeasureList|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRGroupMeasure $studyEffectiveGroupMeasure
     * @return static
     */
    public function setStudyEffectiveGroupMeasure(null|string|FHIRGroupMeasureList|FHIRGroupMeasure $studyEffectiveGroupMeasure): self
    {
        if (null === $studyEffectiveGroupMeasure) {
            unset($this->studyEffectiveGroupMeasure);
            return $this;
        }
        if (!($studyEffectiveGroupMeasure instanceof FHIRGroupMeasure)) {
            $studyEffectiveGroupMeasure = new FHIRGroupMeasure(value: $studyEffectiveGroupMeasure);
        }
        $this->studyEffectiveGroupMeasure = $studyEffectiveGroupMeasure;
        return $this;
    }

    /**
     * A sequence of Unicode characters
     * Note that FHIR strings SHALL NOT exceed 1MB in size
     * If the element is present, it must have either a \@value, an \@id, or extensions
     *
     * A narrative description of the time period the study covers.
     *
     * @return null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRString
     */
    public function getParticipantEffectiveDescription(): null|FHIRString
    {
        return $this->participantEffectiveDescription ?? null;
    }

    /**
     * A sequence of Unicode characters
     * Note that FHIR strings SHALL NOT exceed 1MB in size
     * If the element is present, it must have either a \@value, an \@id, or extensions
     *
     * A narrative description of the time period the study covers.
     *
     * @param null|string|\OpenEMR\FHIR\Versions\R4\Types\FHIRStringPrimitive|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRString $participantEffectiveDescription
     * @return static
     */
    public function setParticipantEffectiveDescription(null|string|FHIRStringPrimitive|FHIRString $participantEffectiveDescription): self
    {
        if (null === $participantEffectiveDescription) {
            unset($this->participantEffectiveDescription);
            return $this;
        }
        if (!($participantEffectiveDescription instanceof FHIRString)) {
            $participantEffectiveDescription = new FHIRString(value: $participantEffectiveDescription);
        }
        $this->participantEffectiveDescription = $participantEffectiveDescription;
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
    public function getParticipantEffectiveTimeFromStart(): null|FHIRDuration
    {
        return $this->participantEffectiveTimeFromStart ?? null;
    }

    /**
     * A length of time.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * Indicates duration from the participant's study entry.
     *
     * @param null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRQuantity\FHIRDuration $participantEffectiveTimeFromStart
     * @return static
     */
    public function setParticipantEffectiveTimeFromStart(null|FHIRDuration $participantEffectiveTimeFromStart): self
    {
        if (null === $participantEffectiveTimeFromStart) {
            unset($this->participantEffectiveTimeFromStart);
            return $this;
        }
        $this->participantEffectiveTimeFromStart = $participantEffectiveTimeFromStart;
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
    public function getParticipantEffectiveGroupMeasure(): null|FHIRGroupMeasure
    {
        return $this->participantEffectiveGroupMeasure ?? null;
    }

    /**
     * Possible group measure aggregates (E.g. Mean, Median).
     * If the element is present, it must have either a \@value, an \@id, or extensions
     *
     * Indicates how elements are aggregated within the study effective period.
     *
     * @param null|string|\OpenEMR\FHIR\Versions\R4\Types\FHIRCodePrimitive\FHIRGroupMeasureList|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRGroupMeasure $participantEffectiveGroupMeasure
     * @return static
     */
    public function setParticipantEffectiveGroupMeasure(null|string|FHIRGroupMeasureList|FHIRGroupMeasure $participantEffectiveGroupMeasure): self
    {
        if (null === $participantEffectiveGroupMeasure) {
            unset($this->participantEffectiveGroupMeasure);
            return $this;
        }
        if (!($participantEffectiveGroupMeasure instanceof FHIRGroupMeasure)) {
            $participantEffectiveGroupMeasure = new FHIRGroupMeasure(value: $participantEffectiveGroupMeasure);
        }
        $this->participantEffectiveGroupMeasure = $participantEffectiveGroupMeasure;
        return $this;
    }

    /* class_default.php:201 */
    /**
     * @param \SimpleXMLElement $element
     * @param \OpenEMR\FHIR\Encoding\UnserializeConfig $config
     * @param null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRResearchElementDefinition\FHIRResearchElementDefinitionCharacteristic $type
     * @return \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRResearchElementDefinition\FHIRResearchElementDefinitionCharacteristic
     * @throws \Exception
     */
    public static function xmlUnserialize(\SimpleXMLElement $element,
                                          UnserializeConfig $config,
                                          null|ElementTypeInterface $type = null): self
    {
        if (null === $type) {
            $type = new static();
        } else if (!($type instanceof FHIRResearchElementDefinitionCharacteristic)) {
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
            } else if (self::FIELD_DEFINITION_CODEABLE_CONCEPT === $cen) {
                $type->setDefinitionCodeableConcept(FHIRCodeableConcept::xmlUnserialize($ce, $config));
            } else if (self::FIELD_DEFINITION_CANONICAL === $cen) {
                $type->setDefinitionCanonical(FHIRCanonical::xmlUnserialize($ce, $config));
            } else if (self::FIELD_DEFINITION_EXPRESSION === $cen) {
                $type->setDefinitionExpression(FHIRExpression::xmlUnserialize($ce, $config));
            } else if (self::FIELD_DEFINITION_DATA_REQUIREMENT === $cen) {
                $type->setDefinitionDataRequirement(FHIRDataRequirement::xmlUnserialize($ce, $config));
            } else if (self::FIELD_USAGE_CONTEXT === $cen) {
                $type->addUsageContext(FHIRUsageContext::xmlUnserialize($ce, $config));
            } else if (self::FIELD_EXCLUDE === $cen) {
                $type->setExclude(FHIRBoolean::xmlUnserialize($ce, $config));
            } else if (self::FIELD_UNIT_OF_MEASURE === $cen) {
                $type->setUnitOfMeasure(FHIRCodeableConcept::xmlUnserialize($ce, $config));
            } else if (self::FIELD_STUDY_EFFECTIVE_DESCRIPTION === $cen) {
                $type->setStudyEffectiveDescription(FHIRString::xmlUnserialize($ce, $config));
            } else if (self::FIELD_STUDY_EFFECTIVE_DATE_TIME === $cen) {
                $type->setStudyEffectiveDateTime(FHIRDateTime::xmlUnserialize($ce, $config));
            } else if (self::FIELD_STUDY_EFFECTIVE_PERIOD === $cen) {
                $type->setStudyEffectivePeriod(FHIRPeriod::xmlUnserialize($ce, $config));
            } else if (self::FIELD_STUDY_EFFECTIVE_DURATION === $cen) {
                $type->setStudyEffectiveDuration(FHIRDuration::xmlUnserialize($ce, $config));
            } else if (self::FIELD_STUDY_EFFECTIVE_TIMING === $cen) {
                $type->setStudyEffectiveTiming(FHIRTiming::xmlUnserialize($ce, $config));
            } else if (self::FIELD_STUDY_EFFECTIVE_TIME_FROM_START === $cen) {
                $type->setStudyEffectiveTimeFromStart(FHIRDuration::xmlUnserialize($ce, $config));
            } else if (self::FIELD_STUDY_EFFECTIVE_GROUP_MEASURE === $cen) {
                $type->setStudyEffectiveGroupMeasure(FHIRGroupMeasure::xmlUnserialize($ce, $config));
            } else if (self::FIELD_PARTICIPANT_EFFECTIVE_DESCRIPTION === $cen) {
                $type->setParticipantEffectiveDescription(FHIRString::xmlUnserialize($ce, $config));
            } else if (self::FIELD_PARTICIPANT_EFFECTIVE_DATE_TIME === $cen) {
                $type->setParticipantEffectiveDateTime(FHIRDateTime::xmlUnserialize($ce, $config));
            } else if (self::FIELD_PARTICIPANT_EFFECTIVE_PERIOD === $cen) {
                $type->setParticipantEffectivePeriod(FHIRPeriod::xmlUnserialize($ce, $config));
            } else if (self::FIELD_PARTICIPANT_EFFECTIVE_DURATION === $cen) {
                $type->setParticipantEffectiveDuration(FHIRDuration::xmlUnserialize($ce, $config));
            } else if (self::FIELD_PARTICIPANT_EFFECTIVE_TIMING === $cen) {
                $type->setParticipantEffectiveTiming(FHIRTiming::xmlUnserialize($ce, $config));
            } else if (self::FIELD_PARTICIPANT_EFFECTIVE_TIME_FROM_START === $cen) {
                $type->setParticipantEffectiveTimeFromStart(FHIRDuration::xmlUnserialize($ce, $config));
            } else if (self::FIELD_PARTICIPANT_EFFECTIVE_GROUP_MEASURE === $cen) {
                $type->setParticipantEffectiveGroupMeasure(FHIRGroupMeasure::xmlUnserialize($ce, $config));
            }
        }
        $attributes = $element->attributes();
        if (isset($attributes[self::FIELD_ID])) {
            $type->setId((string)$attributes[self::FIELD_ID]);
            $type->_setXMLFieldValueLocation(self::FIELD_ID, ValueXMLLocationEnum::PARENT_ATTRIBUTE);
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
        if (isset($attributes[self::FIELD_STUDY_EFFECTIVE_DESCRIPTION])) {
            if (isset($type->studyEffectiveDescription)) {
                $type->studyEffectiveDescription->setValue((string)$attributes[self::FIELD_STUDY_EFFECTIVE_DESCRIPTION]);
            } else {
                $type->setStudyEffectiveDescription((string)$attributes[self::FIELD_STUDY_EFFECTIVE_DESCRIPTION]);
            }
            $type->_setXMLFieldValueLocation(self::FIELD_STUDY_EFFECTIVE_DESCRIPTION, ValueXMLLocationEnum::PARENT_ATTRIBUTE);
        }
        if (isset($attributes[self::FIELD_STUDY_EFFECTIVE_DATE_TIME])) {
            if (isset($type->studyEffectiveDateTime)) {
                $type->studyEffectiveDateTime->setValue((string)$attributes[self::FIELD_STUDY_EFFECTIVE_DATE_TIME]);
            } else {
                $type->setStudyEffectiveDateTime((string)$attributes[self::FIELD_STUDY_EFFECTIVE_DATE_TIME]);
            }
            $type->_setXMLFieldValueLocation(self::FIELD_STUDY_EFFECTIVE_DATE_TIME, ValueXMLLocationEnum::PARENT_ATTRIBUTE);
        }
        if (isset($attributes[self::FIELD_STUDY_EFFECTIVE_GROUP_MEASURE])) {
            if (isset($type->studyEffectiveGroupMeasure)) {
                $type->studyEffectiveGroupMeasure->setValue((string)$attributes[self::FIELD_STUDY_EFFECTIVE_GROUP_MEASURE]);
            } else {
                $type->setStudyEffectiveGroupMeasure((string)$attributes[self::FIELD_STUDY_EFFECTIVE_GROUP_MEASURE]);
            }
            $type->_setXMLFieldValueLocation(self::FIELD_STUDY_EFFECTIVE_GROUP_MEASURE, ValueXMLLocationEnum::PARENT_ATTRIBUTE);
        }
        if (isset($attributes[self::FIELD_PARTICIPANT_EFFECTIVE_DESCRIPTION])) {
            if (isset($type->participantEffectiveDescription)) {
                $type->participantEffectiveDescription->setValue((string)$attributes[self::FIELD_PARTICIPANT_EFFECTIVE_DESCRIPTION]);
            } else {
                $type->setParticipantEffectiveDescription((string)$attributes[self::FIELD_PARTICIPANT_EFFECTIVE_DESCRIPTION]);
            }
            $type->_setXMLFieldValueLocation(self::FIELD_PARTICIPANT_EFFECTIVE_DESCRIPTION, ValueXMLLocationEnum::PARENT_ATTRIBUTE);
        }
        if (isset($attributes[self::FIELD_PARTICIPANT_EFFECTIVE_DATE_TIME])) {
            if (isset($type->participantEffectiveDateTime)) {
                $type->participantEffectiveDateTime->setValue((string)$attributes[self::FIELD_PARTICIPANT_EFFECTIVE_DATE_TIME]);
            } else {
                $type->setParticipantEffectiveDateTime((string)$attributes[self::FIELD_PARTICIPANT_EFFECTIVE_DATE_TIME]);
            }
            $type->_setXMLFieldValueLocation(self::FIELD_PARTICIPANT_EFFECTIVE_DATE_TIME, ValueXMLLocationEnum::PARENT_ATTRIBUTE);
        }
        if (isset($attributes[self::FIELD_PARTICIPANT_EFFECTIVE_GROUP_MEASURE])) {
            if (isset($type->participantEffectiveGroupMeasure)) {
                $type->participantEffectiveGroupMeasure->setValue((string)$attributes[self::FIELD_PARTICIPANT_EFFECTIVE_GROUP_MEASURE]);
            } else {
                $type->setParticipantEffectiveGroupMeasure((string)$attributes[self::FIELD_PARTICIPANT_EFFECTIVE_GROUP_MEASURE]);
            }
            $type->_setXMLFieldValueLocation(self::FIELD_PARTICIPANT_EFFECTIVE_GROUP_MEASURE, ValueXMLLocationEnum::PARENT_ATTRIBUTE);
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
        if (isset($this->definitionCanonical) && ValueXMLLocationEnum::PARENT_ATTRIBUTE === $this->_valueXMLLocations[self::FIELD_DEFINITION_CANONICAL]) {
            $xw->writeAttribute(self::FIELD_DEFINITION_CANONICAL, $this->definitionCanonical->_getValueAsString());
        }
        if (isset($this->exclude) && ValueXMLLocationEnum::PARENT_ATTRIBUTE === $this->_valueXMLLocations[self::FIELD_EXCLUDE]) {
            $xw->writeAttribute(self::FIELD_EXCLUDE, $this->exclude->_getValueAsString());
        }
        if (isset($this->studyEffectiveDescription) && ValueXMLLocationEnum::PARENT_ATTRIBUTE === $this->_valueXMLLocations[self::FIELD_STUDY_EFFECTIVE_DESCRIPTION]) {
            $xw->writeAttribute(self::FIELD_STUDY_EFFECTIVE_DESCRIPTION, $this->studyEffectiveDescription->_getValueAsString());
        }
        if (isset($this->studyEffectiveDateTime) && ValueXMLLocationEnum::PARENT_ATTRIBUTE === $this->_valueXMLLocations[self::FIELD_STUDY_EFFECTIVE_DATE_TIME]) {
            $xw->writeAttribute(self::FIELD_STUDY_EFFECTIVE_DATE_TIME, $this->studyEffectiveDateTime->_getValueAsString());
        }
        if (isset($this->studyEffectiveGroupMeasure) && ValueXMLLocationEnum::PARENT_ATTRIBUTE === $this->_valueXMLLocations[self::FIELD_STUDY_EFFECTIVE_GROUP_MEASURE]) {
            $xw->writeAttribute(self::FIELD_STUDY_EFFECTIVE_GROUP_MEASURE, $this->studyEffectiveGroupMeasure->_getValueAsString());
        }
        if (isset($this->participantEffectiveDescription) && ValueXMLLocationEnum::PARENT_ATTRIBUTE === $this->_valueXMLLocations[self::FIELD_PARTICIPANT_EFFECTIVE_DESCRIPTION]) {
            $xw->writeAttribute(self::FIELD_PARTICIPANT_EFFECTIVE_DESCRIPTION, $this->participantEffectiveDescription->_getValueAsString());
        }
        if (isset($this->participantEffectiveDateTime) && ValueXMLLocationEnum::PARENT_ATTRIBUTE === $this->_valueXMLLocations[self::FIELD_PARTICIPANT_EFFECTIVE_DATE_TIME]) {
            $xw->writeAttribute(self::FIELD_PARTICIPANT_EFFECTIVE_DATE_TIME, $this->participantEffectiveDateTime->_getValueAsString());
        }
        if (isset($this->participantEffectiveGroupMeasure) && ValueXMLLocationEnum::PARENT_ATTRIBUTE === $this->_valueXMLLocations[self::FIELD_PARTICIPANT_EFFECTIVE_GROUP_MEASURE]) {
            $xw->writeAttribute(self::FIELD_PARTICIPANT_EFFECTIVE_GROUP_MEASURE, $this->participantEffectiveGroupMeasure->_getValueAsString());
        }
        parent::xmlSerialize($xw, $config);
        if (isset($this->definitionCodeableConcept)) {
            $xw->startElement(self::FIELD_DEFINITION_CODEABLE_CONCEPT);
            $this->definitionCodeableConcept->xmlSerialize($xw, $config);
            $xw->endElement();
        }
        if (isset($this->definitionCanonical)
            && (ValueXMLLocationEnum::PARENT_ATTRIBUTE !== $this->_valueXMLLocations[self::FIELD_DEFINITION_CANONICAL]
                || $this->definitionCanonical->_nonValueFieldDefined())) {
            $xw->startElement(self::FIELD_DEFINITION_CANONICAL);
            $this->definitionCanonical->xmlSerialize($xw, $config, $this->_valueXMLLocations[self::FIELD_DEFINITION_CANONICAL]);
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
        if (isset($this->unitOfMeasure)) {
            $xw->startElement(self::FIELD_UNIT_OF_MEASURE);
            $this->unitOfMeasure->xmlSerialize($xw, $config);
            $xw->endElement();
        }
        if (isset($this->studyEffectiveDescription)
            && (ValueXMLLocationEnum::PARENT_ATTRIBUTE !== $this->_valueXMLLocations[self::FIELD_STUDY_EFFECTIVE_DESCRIPTION]
                || $this->studyEffectiveDescription->_nonValueFieldDefined())) {
            $xw->startElement(self::FIELD_STUDY_EFFECTIVE_DESCRIPTION);
            $this->studyEffectiveDescription->xmlSerialize($xw, $config, $this->_valueXMLLocations[self::FIELD_STUDY_EFFECTIVE_DESCRIPTION]);
            $xw->endElement();
        }
        if (isset($this->studyEffectiveDateTime)
            && (ValueXMLLocationEnum::PARENT_ATTRIBUTE !== $this->_valueXMLLocations[self::FIELD_STUDY_EFFECTIVE_DATE_TIME]
                || $this->studyEffectiveDateTime->_nonValueFieldDefined())) {
            $xw->startElement(self::FIELD_STUDY_EFFECTIVE_DATE_TIME);
            $this->studyEffectiveDateTime->xmlSerialize($xw, $config, $this->_valueXMLLocations[self::FIELD_STUDY_EFFECTIVE_DATE_TIME]);
            $xw->endElement();
        }
        if (isset($this->studyEffectivePeriod)) {
            $xw->startElement(self::FIELD_STUDY_EFFECTIVE_PERIOD);
            $this->studyEffectivePeriod->xmlSerialize($xw, $config);
            $xw->endElement();
        }
        if (isset($this->studyEffectiveDuration)) {
            $xw->startElement(self::FIELD_STUDY_EFFECTIVE_DURATION);
            $this->studyEffectiveDuration->xmlSerialize($xw, $config);
            $xw->endElement();
        }
        if (isset($this->studyEffectiveTiming)) {
            $xw->startElement(self::FIELD_STUDY_EFFECTIVE_TIMING);
            $this->studyEffectiveTiming->xmlSerialize($xw, $config);
            $xw->endElement();
        }
        if (isset($this->studyEffectiveTimeFromStart)) {
            $xw->startElement(self::FIELD_STUDY_EFFECTIVE_TIME_FROM_START);
            $this->studyEffectiveTimeFromStart->xmlSerialize($xw, $config);
            $xw->endElement();
        }
        if (isset($this->studyEffectiveGroupMeasure)
            && (ValueXMLLocationEnum::PARENT_ATTRIBUTE !== $this->_valueXMLLocations[self::FIELD_STUDY_EFFECTIVE_GROUP_MEASURE]
                || $this->studyEffectiveGroupMeasure->_nonValueFieldDefined())) {
            $xw->startElement(self::FIELD_STUDY_EFFECTIVE_GROUP_MEASURE);
            $this->studyEffectiveGroupMeasure->xmlSerialize($xw, $config, $this->_valueXMLLocations[self::FIELD_STUDY_EFFECTIVE_GROUP_MEASURE]);
            $xw->endElement();
        }
        if (isset($this->participantEffectiveDescription)
            && (ValueXMLLocationEnum::PARENT_ATTRIBUTE !== $this->_valueXMLLocations[self::FIELD_PARTICIPANT_EFFECTIVE_DESCRIPTION]
                || $this->participantEffectiveDescription->_nonValueFieldDefined())) {
            $xw->startElement(self::FIELD_PARTICIPANT_EFFECTIVE_DESCRIPTION);
            $this->participantEffectiveDescription->xmlSerialize($xw, $config, $this->_valueXMLLocations[self::FIELD_PARTICIPANT_EFFECTIVE_DESCRIPTION]);
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
        if (isset($this->participantEffectiveTimeFromStart)) {
            $xw->startElement(self::FIELD_PARTICIPANT_EFFECTIVE_TIME_FROM_START);
            $this->participantEffectiveTimeFromStart->xmlSerialize($xw, $config);
            $xw->endElement();
        }
        if (isset($this->participantEffectiveGroupMeasure)
            && (ValueXMLLocationEnum::PARENT_ATTRIBUTE !== $this->_valueXMLLocations[self::FIELD_PARTICIPANT_EFFECTIVE_GROUP_MEASURE]
                || $this->participantEffectiveGroupMeasure->_nonValueFieldDefined())) {
            $xw->startElement(self::FIELD_PARTICIPANT_EFFECTIVE_GROUP_MEASURE);
            $this->participantEffectiveGroupMeasure->xmlSerialize($xw, $config, $this->_valueXMLLocations[self::FIELD_PARTICIPANT_EFFECTIVE_GROUP_MEASURE]);
            $xw->endElement();
        }
    }

    /**
     * @param \stdClass $decoded
     * @param \OpenEMR\FHIR\Encoding\UnserializeConfig $config
     * @param null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRResearchElementDefinition\FHIRResearchElementDefinitionCharacteristic $type
     * @return \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRResearchElementDefinition\FHIRResearchElementDefinitionCharacteristic
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
        } else if (!($type instanceof FHIRResearchElementDefinitionCharacteristic)) {
            throw new \RuntimeException(sprintf(
                '%s::jsonUnserialize - $type must be instance of \\%s or null, %s seen.',
                ltrim(substr(self::class, (int)strrpos(self::class, '\\')), '\\'),
                static::class,
                $type::class
            ));
        }
        parent::jsonUnserialize($decoded, $config, $type);
        if (isset($decoded->definitionCodeableConcept) || property_exists($decoded, self::FIELD_DEFINITION_CODEABLE_CONCEPT)) {
            if (is_array($decoded->definitionCodeableConcept)) {
                $type->setDefinitionCodeableConcept(FHIRCodeableConcept::jsonUnserialize(reset($decoded->definitionCodeableConcept), $config));
            } else {
                $type->setDefinitionCodeableConcept(FHIRCodeableConcept::jsonUnserialize($decoded->definitionCodeableConcept, $config));
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
        if (isset($decoded->unitOfMeasure) || property_exists($decoded, self::FIELD_UNIT_OF_MEASURE)) {
            if (is_array($decoded->unitOfMeasure)) {
                $type->setUnitOfMeasure(FHIRCodeableConcept::jsonUnserialize(reset($decoded->unitOfMeasure), $config));
            } else {
                $type->setUnitOfMeasure(FHIRCodeableConcept::jsonUnserialize($decoded->unitOfMeasure, $config));
            }
        }
        if (isset($decoded->studyEffectiveDescription)
            || isset($decoded->_studyEffectiveDescription)
            || property_exists($decoded, self::FIELD_STUDY_EFFECTIVE_DESCRIPTION)
            || property_exists($decoded, self::FIELD_STUDY_EFFECTIVE_DESCRIPTION_EXT)) {
            $v = $decoded->_studyEffectiveDescription ?? new \stdClass();
            $v->value = $decoded->studyEffectiveDescription ?? null;
            $type->setStudyEffectiveDescription(FHIRString::jsonUnserialize($v, $config));
        }
        if (isset($decoded->studyEffectiveDateTime)
            || isset($decoded->_studyEffectiveDateTime)
            || property_exists($decoded, self::FIELD_STUDY_EFFECTIVE_DATE_TIME)
            || property_exists($decoded, self::FIELD_STUDY_EFFECTIVE_DATE_TIME_EXT)) {
            $v = $decoded->_studyEffectiveDateTime ?? new \stdClass();
            $v->value = $decoded->studyEffectiveDateTime ?? null;
            $type->setStudyEffectiveDateTime(FHIRDateTime::jsonUnserialize($v, $config));
        }
        if (isset($decoded->studyEffectivePeriod) || property_exists($decoded, self::FIELD_STUDY_EFFECTIVE_PERIOD)) {
            if (is_array($decoded->studyEffectivePeriod)) {
                $type->setStudyEffectivePeriod(FHIRPeriod::jsonUnserialize(reset($decoded->studyEffectivePeriod), $config));
            } else {
                $type->setStudyEffectivePeriod(FHIRPeriod::jsonUnserialize($decoded->studyEffectivePeriod, $config));
            }
        }
        if (isset($decoded->studyEffectiveDuration) || property_exists($decoded, self::FIELD_STUDY_EFFECTIVE_DURATION)) {
            if (is_array($decoded->studyEffectiveDuration)) {
                $type->setStudyEffectiveDuration(FHIRDuration::jsonUnserialize(reset($decoded->studyEffectiveDuration), $config));
            } else {
                $type->setStudyEffectiveDuration(FHIRDuration::jsonUnserialize($decoded->studyEffectiveDuration, $config));
            }
        }
        if (isset($decoded->studyEffectiveTiming) || property_exists($decoded, self::FIELD_STUDY_EFFECTIVE_TIMING)) {
            if (is_array($decoded->studyEffectiveTiming)) {
                $type->setStudyEffectiveTiming(FHIRTiming::jsonUnserialize(reset($decoded->studyEffectiveTiming), $config));
            } else {
                $type->setStudyEffectiveTiming(FHIRTiming::jsonUnserialize($decoded->studyEffectiveTiming, $config));
            }
        }
        if (isset($decoded->studyEffectiveTimeFromStart) || property_exists($decoded, self::FIELD_STUDY_EFFECTIVE_TIME_FROM_START)) {
            if (is_array($decoded->studyEffectiveTimeFromStart)) {
                $type->setStudyEffectiveTimeFromStart(FHIRDuration::jsonUnserialize(reset($decoded->studyEffectiveTimeFromStart), $config));
            } else {
                $type->setStudyEffectiveTimeFromStart(FHIRDuration::jsonUnserialize($decoded->studyEffectiveTimeFromStart, $config));
            }
        }
        if (isset($decoded->studyEffectiveGroupMeasure)
            || isset($decoded->_studyEffectiveGroupMeasure)
            || property_exists($decoded, self::FIELD_STUDY_EFFECTIVE_GROUP_MEASURE)
            || property_exists($decoded, self::FIELD_STUDY_EFFECTIVE_GROUP_MEASURE_EXT)) {
            $v = $decoded->_studyEffectiveGroupMeasure ?? new \stdClass();
            $v->value = $decoded->studyEffectiveGroupMeasure ?? null;
            $type->setStudyEffectiveGroupMeasure(FHIRGroupMeasure::jsonUnserialize($v, $config));
        }
        if (isset($decoded->participantEffectiveDescription)
            || isset($decoded->_participantEffectiveDescription)
            || property_exists($decoded, self::FIELD_PARTICIPANT_EFFECTIVE_DESCRIPTION)
            || property_exists($decoded, self::FIELD_PARTICIPANT_EFFECTIVE_DESCRIPTION_EXT)) {
            $v = $decoded->_participantEffectiveDescription ?? new \stdClass();
            $v->value = $decoded->participantEffectiveDescription ?? null;
            $type->setParticipantEffectiveDescription(FHIRString::jsonUnserialize($v, $config));
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
        if (isset($decoded->participantEffectiveTimeFromStart) || property_exists($decoded, self::FIELD_PARTICIPANT_EFFECTIVE_TIME_FROM_START)) {
            if (is_array($decoded->participantEffectiveTimeFromStart)) {
                $type->setParticipantEffectiveTimeFromStart(FHIRDuration::jsonUnserialize(reset($decoded->participantEffectiveTimeFromStart), $config));
            } else {
                $type->setParticipantEffectiveTimeFromStart(FHIRDuration::jsonUnserialize($decoded->participantEffectiveTimeFromStart, $config));
            }
        }
        if (isset($decoded->participantEffectiveGroupMeasure)
            || isset($decoded->_participantEffectiveGroupMeasure)
            || property_exists($decoded, self::FIELD_PARTICIPANT_EFFECTIVE_GROUP_MEASURE)
            || property_exists($decoded, self::FIELD_PARTICIPANT_EFFECTIVE_GROUP_MEASURE_EXT)) {
            $v = $decoded->_participantEffectiveGroupMeasure ?? new \stdClass();
            $v->value = $decoded->participantEffectiveGroupMeasure ?? null;
            $type->setParticipantEffectiveGroupMeasure(FHIRGroupMeasure::jsonUnserialize($v, $config));
        }
        return $type;
    }

    /**
     * @return \stdClass
     */
    public function jsonSerialize(): mixed
    {
        $out = parent::jsonSerialize();
        if (isset($this->definitionCodeableConcept)) {
            $out->definitionCodeableConcept = $this->definitionCodeableConcept;
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
        if (isset($this->definitionExpression)) {
            $out->definitionExpression = $this->definitionExpression;
        }
        if (isset($this->definitionDataRequirement)) {
            $out->definitionDataRequirement = $this->definitionDataRequirement;
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
        if (isset($this->unitOfMeasure)) {
            $out->unitOfMeasure = $this->unitOfMeasure;
        }
        if (isset($this->studyEffectiveDescription)) {
            if (null !== ($val = $this->studyEffectiveDescription->getValue())) {
                $out->studyEffectiveDescription = $val;
            }
            if ($this->studyEffectiveDescription->_nonValueFieldDefined()) {
                $ext = $this->studyEffectiveDescription->jsonSerialize();
                unset($ext->value);
                $out->_studyEffectiveDescription = $ext;
            }
        }
        if (isset($this->studyEffectiveDateTime)) {
            if (null !== ($val = $this->studyEffectiveDateTime->getValue())) {
                $out->studyEffectiveDateTime = $val;
            }
            if ($this->studyEffectiveDateTime->_nonValueFieldDefined()) {
                $ext = $this->studyEffectiveDateTime->jsonSerialize();
                unset($ext->value);
                $out->_studyEffectiveDateTime = $ext;
            }
        }
        if (isset($this->studyEffectivePeriod)) {
            $out->studyEffectivePeriod = $this->studyEffectivePeriod;
        }
        if (isset($this->studyEffectiveDuration)) {
            $out->studyEffectiveDuration = $this->studyEffectiveDuration;
        }
        if (isset($this->studyEffectiveTiming)) {
            $out->studyEffectiveTiming = $this->studyEffectiveTiming;
        }
        if (isset($this->studyEffectiveTimeFromStart)) {
            $out->studyEffectiveTimeFromStart = $this->studyEffectiveTimeFromStart;
        }
        if (isset($this->studyEffectiveGroupMeasure)) {
            if (null !== ($val = $this->studyEffectiveGroupMeasure->getValue())) {
                $out->studyEffectiveGroupMeasure = $val;
            }
            if ($this->studyEffectiveGroupMeasure->_nonValueFieldDefined()) {
                $ext = $this->studyEffectiveGroupMeasure->jsonSerialize();
                unset($ext->value);
                $out->_studyEffectiveGroupMeasure = $ext;
            }
        }
        if (isset($this->participantEffectiveDescription)) {
            if (null !== ($val = $this->participantEffectiveDescription->getValue())) {
                $out->participantEffectiveDescription = $val;
            }
            if ($this->participantEffectiveDescription->_nonValueFieldDefined()) {
                $ext = $this->participantEffectiveDescription->jsonSerialize();
                unset($ext->value);
                $out->_participantEffectiveDescription = $ext;
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
        if (isset($this->participantEffectiveTimeFromStart)) {
            $out->participantEffectiveTimeFromStart = $this->participantEffectiveTimeFromStart;
        }
        if (isset($this->participantEffectiveGroupMeasure)) {
            if (null !== ($val = $this->participantEffectiveGroupMeasure->getValue())) {
                $out->participantEffectiveGroupMeasure = $val;
            }
            if ($this->participantEffectiveGroupMeasure->_nonValueFieldDefined()) {
                $ext = $this->participantEffectiveGroupMeasure->jsonSerialize();
                unset($ext->value);
                $out->_participantEffectiveGroupMeasure = $ext;
            }
        }
        return $out;
    }
}
