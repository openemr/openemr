<?php

declare(strict_types=1);

namespace OpenEMR\FHIR\Versions\R4\Types\FHIRResource\FHIRDomainResource;

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
use OpenEMR\FHIR\Types\ResourceTypeInterface;
use OpenEMR\FHIR\Validation\Rules\MinOccursRule;
use OpenEMR\FHIR\Validation\TypeValidationsTrait;
use OpenEMR\FHIR\Versions\R4\Types\FHIRCodePrimitive;
use OpenEMR\FHIR\Versions\R4\Types\FHIRDateTimePrimitive;
use OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRAnnotation;
use OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRCondition\FHIRConditionEvidence;
use OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRCondition\FHIRConditionStage;
use OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRCode;
use OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRCodeableConcept;
use OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRDateTime;
use OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRExtension;
use OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRId;
use OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRIdentifier;
use OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRMeta;
use OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRNarrative;
use OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRPeriod;
use OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRQuantity\FHIRAge;
use OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRRange;
use OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRReference;
use OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRString;
use OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRUri;
use OpenEMR\FHIR\Versions\R4\Types\FHIRIdPrimitive;
use OpenEMR\FHIR\Versions\R4\Types\FHIRResource\FHIRDomainResource;
use OpenEMR\FHIR\Versions\R4\Types\FHIRStringPrimitive;
use OpenEMR\FHIR\Versions\R4\Types\FHIRUriPrimitive;
use OpenEMR\FHIR\Versions\R4\Version;
use OpenEMR\FHIR\Versions\R4\VersionConstants;
use OpenEMR\FHIR\Versions\R4\VersionContainedTypeInterface;
use OpenEMR\FHIR\Versions\R4\VersionTypeMap;

/**
 * A clinical condition, problem, diagnosis, or other event, situation, issue, or
 * clinical concept that has risen to a level of concern.
 * If the element is present, it must have either a \@value, an \@id, or extensions
 */
#[\Attribute(\Attribute::TARGET_PROPERTY)]
class FHIRCondition extends FHIRDomainResource implements VersionContainedTypeInterface
{
    use TypeValidationsTrait,
        JSONSerializationOptionsTrait,
        XMLSerializationOptionsTrait;

    // name of FHIR type this class describes
    public const FHIR_TYPE_NAME = VersionConstants::TYPE_NAME_CONDITION;

    /* class_default.php:56 */
    public const FIELD_IDENTIFIER = 'identifier';
    public const FIELD_CLINICAL_STATUS = 'clinicalStatus';
    public const FIELD_VERIFICATION_STATUS = 'verificationStatus';
    public const FIELD_CATEGORY = 'category';
    public const FIELD_SEVERITY = 'severity';
    public const FIELD_CODE = 'code';
    public const FIELD_BODY_SITE = 'bodySite';
    public const FIELD_SUBJECT = 'subject';
    public const FIELD_ENCOUNTER = 'encounter';
    public const FIELD_ONSET_DATE_TIME = 'onsetDateTime';
    public const FIELD_ONSET_DATE_TIME_EXT = '_onsetDateTime';
    public const FIELD_ONSET_AGE = 'onsetAge';
    public const FIELD_ONSET_PERIOD = 'onsetPeriod';
    public const FIELD_ONSET_RANGE = 'onsetRange';
    public const FIELD_ONSET_STRING = 'onsetString';
    public const FIELD_ONSET_STRING_EXT = '_onsetString';
    public const FIELD_ABATEMENT_DATE_TIME = 'abatementDateTime';
    public const FIELD_ABATEMENT_DATE_TIME_EXT = '_abatementDateTime';
    public const FIELD_ABATEMENT_AGE = 'abatementAge';
    public const FIELD_ABATEMENT_PERIOD = 'abatementPeriod';
    public const FIELD_ABATEMENT_RANGE = 'abatementRange';
    public const FIELD_ABATEMENT_STRING = 'abatementString';
    public const FIELD_ABATEMENT_STRING_EXT = '_abatementString';
    public const FIELD_RECORDED_DATE = 'recordedDate';
    public const FIELD_RECORDED_DATE_EXT = '_recordedDate';
    public const FIELD_RECORDER = 'recorder';
    public const FIELD_ASSERTER = 'asserter';
    public const FIELD_STAGE = 'stage';
    public const FIELD_EVIDENCE = 'evidence';
    public const FIELD_NOTE = 'note';

    /* class_default.php:75 */
    private const _FHIR_VALIDATION_RULES = [
        self::FIELD_SUBJECT => [
            MinOccursRule::NAME => 1,
        ],
    ];

    /* class_default.php:96 */
    private array $_valueXMLLocations = [
        self::FIELD_ONSET_DATE_TIME => ValueXMLLocationEnum::CONTAINER_ATTRIBUTE,
        self::FIELD_ONSET_STRING => ValueXMLLocationEnum::CONTAINER_ATTRIBUTE,
        self::FIELD_ABATEMENT_DATE_TIME => ValueXMLLocationEnum::CONTAINER_ATTRIBUTE,
        self::FIELD_ABATEMENT_STRING => ValueXMLLocationEnum::CONTAINER_ATTRIBUTE,
        self::FIELD_RECORDED_DATE => ValueXMLLocationEnum::CONTAINER_ATTRIBUTE,
    ];

    /* class_default.php:112 */
    /**
     * An identifier - identifies some entity uniquely and unambiguously. Typically
     * this is used for business identifiers.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * Business identifiers assigned to this condition by the performer or other
     * systems which remain constant as the resource is updated and propagates from
     * server to server.
     *
     * @var iterable<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRIdentifier>
     */
    #[FHIRIdentifier]
    protected array $identifier;
    /**
     * A concept that may be defined by a formal reference to a terminology or ontology
     * or may be provided by text.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * The clinical status of the condition.
     *
     * @var \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRCodeableConcept
     */
    #[FHIRCodeableConcept]
    protected FHIRCodeableConcept $clinicalStatus;
    /**
     * A concept that may be defined by a formal reference to a terminology or ontology
     * or may be provided by text.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * The verification status to support the clinical status of the condition.
     *
     * @var \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRCodeableConcept
     */
    #[FHIRCodeableConcept]
    protected FHIRCodeableConcept $verificationStatus;
    /**
     * A concept that may be defined by a formal reference to a terminology or ontology
     * or may be provided by text.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * A category assigned to the condition.
     *
     * @var iterable<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRCodeableConcept>
     */
    #[FHIRCodeableConcept]
    protected array $category;
    /**
     * A concept that may be defined by a formal reference to a terminology or ontology
     * or may be provided by text.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * A subjective assessment of the severity of the condition as evaluated by the
     * clinician.
     *
     * @var \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRCodeableConcept
     */
    #[FHIRCodeableConcept]
    protected FHIRCodeableConcept $severity;
    /**
     * A concept that may be defined by a formal reference to a terminology or ontology
     * or may be provided by text.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * Identification of the condition, problem or diagnosis.
     *
     * @var \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRCodeableConcept
     */
    #[FHIRCodeableConcept]
    protected FHIRCodeableConcept $code;
    /**
     * A concept that may be defined by a formal reference to a terminology or ontology
     * or may be provided by text.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * The anatomical location where this condition manifests itself.
     *
     * @var iterable<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRCodeableConcept>
     */
    #[FHIRCodeableConcept]
    protected array $bodySite;
    /**
     * A reference from one resource to another.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * Indicates the patient or group who the condition record is associated with.
     *
     * @var \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRReference
     */
    #[FHIRReference]
    protected FHIRReference $subject;
    /**
     * A reference from one resource to another.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * The Encounter during which this Condition was created or to which the creation
     * of this record is tightly associated.
     *
     * @var \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRReference
     */
    #[FHIRReference]
    protected FHIRReference $encounter;
    /**
     * A date, date-time or partial date (e.g. just year or year + month). If hours and
     * minutes are specified, a time zone SHALL be populated. The format is a union of
     * the schema types gYear, gYearMonth, date and dateTime. Seconds must be provided
     * due to schema type constraints but may be zero-filled and may be ignored. Dates
     * SHALL be valid dates.
     * If the element is present, it must have either a \@value, an \@id, or extensions
     *
     * Estimated or actual date or date-time the condition began, in the opinion of the
     * clinician. (choose any one of onset*, but only one)
     *
     * @var \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRDateTime
     */
    #[FHIRDateTime]
    protected FHIRDateTime $onsetDateTime;
    /**
     * A duration of time during which an organism (or a process) has existed.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * Estimated or actual date or date-time the condition began, in the opinion of the
     * clinician. (choose any one of onset*, but only one)
     *
     * @var \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRQuantity\FHIRAge
     */
    #[FHIRAge]
    protected FHIRAge $onsetAge;
    /**
     * A time period defined by a start and end date and optionally time.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * Estimated or actual date or date-time the condition began, in the opinion of the
     * clinician. (choose any one of onset*, but only one)
     *
     * @var \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRPeriod
     */
    #[FHIRPeriod]
    protected FHIRPeriod $onsetPeriod;
    /**
     * A set of ordered Quantities defined by a low and high limit.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * Estimated or actual date or date-time the condition began, in the opinion of the
     * clinician. (choose any one of onset*, but only one)
     *
     * @var \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRRange
     */
    #[FHIRRange]
    protected FHIRRange $onsetRange;
    /**
     * A sequence of Unicode characters
     * Note that FHIR strings SHALL NOT exceed 1MB in size
     * If the element is present, it must have either a \@value, an \@id, or extensions
     *
     * Estimated or actual date or date-time the condition began, in the opinion of the
     * clinician. (choose any one of onset*, but only one)
     *
     * @var \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRString
     */
    #[FHIRString]
    protected FHIRString $onsetString;
    /**
     * A date, date-time or partial date (e.g. just year or year + month). If hours and
     * minutes are specified, a time zone SHALL be populated. The format is a union of
     * the schema types gYear, gYearMonth, date and dateTime. Seconds must be provided
     * due to schema type constraints but may be zero-filled and may be ignored. Dates
     * SHALL be valid dates.
     * If the element is present, it must have either a \@value, an \@id, or extensions
     *
     * The date or estimated date that the condition resolved or went into remission.
     * This is called "abatement" because of the many overloaded connotations
     * associated with "remission" or "resolution" - Conditions are never really
     * resolved, but they can abate. (choose any one of abatement*, but only one)
     *
     * @var \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRDateTime
     */
    #[FHIRDateTime]
    protected FHIRDateTime $abatementDateTime;
    /**
     * A duration of time during which an organism (or a process) has existed.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * The date or estimated date that the condition resolved or went into remission.
     * This is called "abatement" because of the many overloaded connotations
     * associated with "remission" or "resolution" - Conditions are never really
     * resolved, but they can abate. (choose any one of abatement*, but only one)
     *
     * @var \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRQuantity\FHIRAge
     */
    #[FHIRAge]
    protected FHIRAge $abatementAge;
    /**
     * A time period defined by a start and end date and optionally time.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * The date or estimated date that the condition resolved or went into remission.
     * This is called "abatement" because of the many overloaded connotations
     * associated with "remission" or "resolution" - Conditions are never really
     * resolved, but they can abate. (choose any one of abatement*, but only one)
     *
     * @var \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRPeriod
     */
    #[FHIRPeriod]
    protected FHIRPeriod $abatementPeriod;
    /**
     * A set of ordered Quantities defined by a low and high limit.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * The date or estimated date that the condition resolved or went into remission.
     * This is called "abatement" because of the many overloaded connotations
     * associated with "remission" or "resolution" - Conditions are never really
     * resolved, but they can abate. (choose any one of abatement*, but only one)
     *
     * @var \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRRange
     */
    #[FHIRRange]
    protected FHIRRange $abatementRange;
    /**
     * A sequence of Unicode characters
     * Note that FHIR strings SHALL NOT exceed 1MB in size
     * If the element is present, it must have either a \@value, an \@id, or extensions
     *
     * The date or estimated date that the condition resolved or went into remission.
     * This is called "abatement" because of the many overloaded connotations
     * associated with "remission" or "resolution" - Conditions are never really
     * resolved, but they can abate. (choose any one of abatement*, but only one)
     *
     * @var \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRString
     */
    #[FHIRString]
    protected FHIRString $abatementString;
    /**
     * A date, date-time or partial date (e.g. just year or year + month). If hours and
     * minutes are specified, a time zone SHALL be populated. The format is a union of
     * the schema types gYear, gYearMonth, date and dateTime. Seconds must be provided
     * due to schema type constraints but may be zero-filled and may be ignored. Dates
     * SHALL be valid dates.
     * If the element is present, it must have either a \@value, an \@id, or extensions
     *
     * The recordedDate represents when this particular Condition record was created in
     * the system, which is often a system-generated date.
     *
     * @var \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRDateTime
     */
    #[FHIRDateTime]
    protected FHIRDateTime $recordedDate;
    /**
     * A reference from one resource to another.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * Individual who recorded the record and takes responsibility for its content.
     *
     * @var \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRReference
     */
    #[FHIRReference]
    protected FHIRReference $recorder;
    /**
     * A reference from one resource to another.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * Individual who is making the condition statement.
     *
     * @var \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRReference
     */
    #[FHIRReference]
    protected FHIRReference $asserter;
    /**
     * A clinical condition, problem, diagnosis, or other event, situation, issue, or
     * clinical concept that has risen to a level of concern.
     *
     * Clinical stage or grade of a condition. May include formal severity assessments.
     *
     * @var iterable<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRCondition\FHIRConditionStage>
     */
    #[FHIRConditionStage]
    protected array $stage;
    /**
     * A clinical condition, problem, diagnosis, or other event, situation, issue, or
     * clinical concept that has risen to a level of concern.
     *
     * Supporting evidence / manifestations that are the basis of the Condition's
     * verification status, such as evidence that confirmed or refuted the condition.
     *
     * @var iterable<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRCondition\FHIRConditionEvidence>
     */
    #[FHIRConditionEvidence]
    protected array $evidence;
    /**
     * A text note which also contains information about who made the statement and
     * when.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * Additional information about the Condition. This is a general notes/comments
     * entry for description of the Condition, its diagnosis and prognosis.
     *
     * @var iterable<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRAnnotation>
     */
    #[FHIRAnnotation]
    protected array $note;

    /* constructor.php:61 */
    /**
     * FHIRCondition Constructor
     * @param null|string|\OpenEMR\FHIR\Versions\R4\Types\FHIRIdPrimitive|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRId $id
     * @param null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRMeta $meta
     * @param null|string|\OpenEMR\FHIR\Versions\R4\Types\FHIRUriPrimitive|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRUri $implicitRules
     * @param null|string|\OpenEMR\FHIR\Versions\R4\Types\FHIRCodePrimitive|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRCode $language
     * @param null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRNarrative $text
     * @param null|iterable<\OpenEMR\FHIR\Versions\R4\Types\FHIRResourceContainer>|iterable<\OpenEMR\FHIR\Versions\R4\VersionContainedTypeInterface> $contained
     * @param null|iterable<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRExtension> $extension
     * @param null|iterable<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRExtension> $modifierExtension
     * @param null|iterable<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRIdentifier> $identifier
     * @param null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRCodeableConcept $clinicalStatus
     * @param null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRCodeableConcept $verificationStatus
     * @param null|iterable<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRCodeableConcept> $category
     * @param null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRCodeableConcept $severity
     * @param null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRCodeableConcept $code
     * @param null|iterable<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRCodeableConcept> $bodySite
     * @param null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRReference $subject
     * @param null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRReference $encounter
     * @param null|string|\DateTimeInterface|\OpenEMR\FHIR\Versions\R4\Types\FHIRDateTimePrimitive|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRDateTime $onsetDateTime
     * @param null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRQuantity\FHIRAge $onsetAge
     * @param null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRPeriod $onsetPeriod
     * @param null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRRange $onsetRange
     * @param null|string|\OpenEMR\FHIR\Versions\R4\Types\FHIRStringPrimitive|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRString $onsetString
     * @param null|string|\DateTimeInterface|\OpenEMR\FHIR\Versions\R4\Types\FHIRDateTimePrimitive|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRDateTime $abatementDateTime
     * @param null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRQuantity\FHIRAge $abatementAge
     * @param null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRPeriod $abatementPeriod
     * @param null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRRange $abatementRange
     * @param null|string|\OpenEMR\FHIR\Versions\R4\Types\FHIRStringPrimitive|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRString $abatementString
     * @param null|string|\DateTimeInterface|\OpenEMR\FHIR\Versions\R4\Types\FHIRDateTimePrimitive|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRDateTime $recordedDate
     * @param null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRReference $recorder
     * @param null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRReference $asserter
     * @param null|iterable<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRCondition\FHIRConditionStage> $stage
     * @param null|iterable<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRCondition\FHIRConditionEvidence> $evidence
     * @param null|iterable<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRAnnotation> $note
     * @param null|string[] $fhirComments
     */
    public function __construct(null|string|FHIRIdPrimitive|FHIRId $id = null,
                                null|FHIRMeta $meta = null,
                                null|string|FHIRUriPrimitive|FHIRUri $implicitRules = null,
                                null|string|FHIRCodePrimitive|FHIRCode $language = null,
                                null|FHIRNarrative $text = null,
                                null|iterable $contained = null,
                                null|iterable $extension = null,
                                null|iterable $modifierExtension = null,
                                null|iterable $identifier = null,
                                null|FHIRCodeableConcept $clinicalStatus = null,
                                null|FHIRCodeableConcept $verificationStatus = null,
                                null|iterable $category = null,
                                null|FHIRCodeableConcept $severity = null,
                                null|FHIRCodeableConcept $code = null,
                                null|iterable $bodySite = null,
                                null|FHIRReference $subject = null,
                                null|FHIRReference $encounter = null,
                                null|string|\DateTimeInterface|FHIRDateTimePrimitive|FHIRDateTime $onsetDateTime = null,
                                null|FHIRAge $onsetAge = null,
                                null|FHIRPeriod $onsetPeriod = null,
                                null|FHIRRange $onsetRange = null,
                                null|string|FHIRStringPrimitive|FHIRString $onsetString = null,
                                null|string|\DateTimeInterface|FHIRDateTimePrimitive|FHIRDateTime $abatementDateTime = null,
                                null|FHIRAge $abatementAge = null,
                                null|FHIRPeriod $abatementPeriod = null,
                                null|FHIRRange $abatementRange = null,
                                null|string|FHIRStringPrimitive|FHIRString $abatementString = null,
                                null|string|\DateTimeInterface|FHIRDateTimePrimitive|FHIRDateTime $recordedDate = null,
                                null|FHIRReference $recorder = null,
                                null|FHIRReference $asserter = null,
                                null|iterable $stage = null,
                                null|iterable $evidence = null,
                                null|iterable $note = null,
                                null|iterable $fhirComments = null)
    {
        parent::__construct(id: $id,
                            meta: $meta,
                            implicitRules: $implicitRules,
                            language: $language,
                            text: $text,
                            contained: $contained,
                            extension: $extension,
                            modifierExtension: $modifierExtension,
                            fhirComments: $fhirComments);
        if (null !== $identifier) {
            $this->setIdentifier(...$identifier);
        }
        if (null !== $clinicalStatus) {
            $this->setClinicalStatus($clinicalStatus);
        }
        if (null !== $verificationStatus) {
            $this->setVerificationStatus($verificationStatus);
        }
        if (null !== $category) {
            $this->setCategory(...$category);
        }
        if (null !== $severity) {
            $this->setSeverity($severity);
        }
        if (null !== $code) {
            $this->setCode($code);
        }
        if (null !== $bodySite) {
            $this->setBodySite(...$bodySite);
        }
        if (null !== $subject) {
            $this->setSubject($subject);
        }
        if (null !== $encounter) {
            $this->setEncounter($encounter);
        }
        if (null !== $onsetDateTime) {
            $this->setOnsetDateTime($onsetDateTime);
        }
        if (null !== $onsetAge) {
            $this->setOnsetAge($onsetAge);
        }
        if (null !== $onsetPeriod) {
            $this->setOnsetPeriod($onsetPeriod);
        }
        if (null !== $onsetRange) {
            $this->setOnsetRange($onsetRange);
        }
        if (null !== $onsetString) {
            $this->setOnsetString($onsetString);
        }
        if (null !== $abatementDateTime) {
            $this->setAbatementDateTime($abatementDateTime);
        }
        if (null !== $abatementAge) {
            $this->setAbatementAge($abatementAge);
        }
        if (null !== $abatementPeriod) {
            $this->setAbatementPeriod($abatementPeriod);
        }
        if (null !== $abatementRange) {
            $this->setAbatementRange($abatementRange);
        }
        if (null !== $abatementString) {
            $this->setAbatementString($abatementString);
        }
        if (null !== $recordedDate) {
            $this->setRecordedDate($recordedDate);
        }
        if (null !== $recorder) {
            $this->setRecorder($recorder);
        }
        if (null !== $asserter) {
            $this->setAsserter($asserter);
        }
        if (null !== $stage) {
            $this->setStage(...$stage);
        }
        if (null !== $evidence) {
            $this->setEvidence(...$evidence);
        }
        if (null !== $note) {
            $this->setNote(...$note);
        }
    }

    /* class_default.php:145 */
    public function _getFHIRTypeName(): string
    {
        return self::FHIR_TYPE_NAME;
    }

    /* class_default.php:163 */
    public function _getResourceType(): string
    {
        return static::FHIR_TYPE_NAME;
    }

    /* class_default.php:174 */
    /**
     * An identifier - identifies some entity uniquely and unambiguously. Typically
     * this is used for business identifiers.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * Business identifiers assigned to this condition by the performer or other
     * systems which remain constant as the resource is updated and propagates from
     * server to server.
     *
     * @return iterable<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRIdentifier>
     */
    public function getIdentifier(): array
    {
        return $this->identifier ?? [];
    }

    /**
     * @return \ArrayIterator<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRIdentifier>
     */
    public function getIdentifierIterator(): iterable
    {
        if (!isset($this->identifier)) {
            return new \EmptyIterator();
        }
        return new \ArrayIterator($this->identifier);
    }

    /**
     * An identifier - identifies some entity uniquely and unambiguously. Typically
     * this is used for business identifiers.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * Business identifiers assigned to this condition by the performer or other
     * systems which remain constant as the resource is updated and propagates from
     * server to server.
     *
     * @param \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRIdentifier $identifier
     * @return static
     */
    public function addIdentifier(FHIRIdentifier $identifier): self
    {
        if (!isset($this->identifier)) {
            $this->identifier = [];
        }
        $this->identifier[] = $identifier;
        return $this;
    }

    /**
     * An identifier - identifies some entity uniquely and unambiguously. Typically
     * this is used for business identifiers.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * Business identifiers assigned to this condition by the performer or other
     * systems which remain constant as the resource is updated and propagates from
     * server to server.
     *
     * @param \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRIdentifier ...$identifier
     * @return static
     */
    public function setIdentifier(FHIRIdentifier ...$identifier): self
    {
        if ([] === $identifier) {
            unset($this->identifier);
            return $this;
        }
        $this->identifier = $identifier;
        return $this;
    }

    /**
     * A concept that may be defined by a formal reference to a terminology or ontology
     * or may be provided by text.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * The clinical status of the condition.
     *
     * @return null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRCodeableConcept
     */
    public function getClinicalStatus(): null|FHIRCodeableConcept
    {
        return $this->clinicalStatus ?? null;
    }

    /**
     * A concept that may be defined by a formal reference to a terminology or ontology
     * or may be provided by text.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * The clinical status of the condition.
     *
     * @param null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRCodeableConcept $clinicalStatus
     * @return static
     */
    public function setClinicalStatus(null|FHIRCodeableConcept $clinicalStatus): self
    {
        if (null === $clinicalStatus) {
            unset($this->clinicalStatus);
            return $this;
        }
        $this->clinicalStatus = $clinicalStatus;
        return $this;
    }

    /**
     * A concept that may be defined by a formal reference to a terminology or ontology
     * or may be provided by text.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * The verification status to support the clinical status of the condition.
     *
     * @return null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRCodeableConcept
     */
    public function getVerificationStatus(): null|FHIRCodeableConcept
    {
        return $this->verificationStatus ?? null;
    }

    /**
     * A concept that may be defined by a formal reference to a terminology or ontology
     * or may be provided by text.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * The verification status to support the clinical status of the condition.
     *
     * @param null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRCodeableConcept $verificationStatus
     * @return static
     */
    public function setVerificationStatus(null|FHIRCodeableConcept $verificationStatus): self
    {
        if (null === $verificationStatus) {
            unset($this->verificationStatus);
            return $this;
        }
        $this->verificationStatus = $verificationStatus;
        return $this;
    }

    /**
     * A concept that may be defined by a formal reference to a terminology or ontology
     * or may be provided by text.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * A category assigned to the condition.
     *
     * @return iterable<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRCodeableConcept>
     */
    public function getCategory(): array
    {
        return $this->category ?? [];
    }

    /**
     * @return \ArrayIterator<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRCodeableConcept>
     */
    public function getCategoryIterator(): iterable
    {
        if (!isset($this->category)) {
            return new \EmptyIterator();
        }
        return new \ArrayIterator($this->category);
    }

    /**
     * A concept that may be defined by a formal reference to a terminology or ontology
     * or may be provided by text.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * A category assigned to the condition.
     *
     * @param \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRCodeableConcept $category
     * @return static
     */
    public function addCategory(FHIRCodeableConcept $category): self
    {
        if (!isset($this->category)) {
            $this->category = [];
        }
        $this->category[] = $category;
        return $this;
    }

    /**
     * A concept that may be defined by a formal reference to a terminology or ontology
     * or may be provided by text.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * A category assigned to the condition.
     *
     * @param \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRCodeableConcept ...$category
     * @return static
     */
    public function setCategory(FHIRCodeableConcept ...$category): self
    {
        if ([] === $category) {
            unset($this->category);
            return $this;
        }
        $this->category = $category;
        return $this;
    }

    /**
     * A concept that may be defined by a formal reference to a terminology or ontology
     * or may be provided by text.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * A subjective assessment of the severity of the condition as evaluated by the
     * clinician.
     *
     * @return null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRCodeableConcept
     */
    public function getSeverity(): null|FHIRCodeableConcept
    {
        return $this->severity ?? null;
    }

    /**
     * A concept that may be defined by a formal reference to a terminology or ontology
     * or may be provided by text.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * A subjective assessment of the severity of the condition as evaluated by the
     * clinician.
     *
     * @param null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRCodeableConcept $severity
     * @return static
     */
    public function setSeverity(null|FHIRCodeableConcept $severity): self
    {
        if (null === $severity) {
            unset($this->severity);
            return $this;
        }
        $this->severity = $severity;
        return $this;
    }

    /**
     * A concept that may be defined by a formal reference to a terminology or ontology
     * or may be provided by text.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * Identification of the condition, problem or diagnosis.
     *
     * @return null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRCodeableConcept
     */
    public function getCode(): null|FHIRCodeableConcept
    {
        return $this->code ?? null;
    }

    /**
     * A concept that may be defined by a formal reference to a terminology or ontology
     * or may be provided by text.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * Identification of the condition, problem or diagnosis.
     *
     * @param null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRCodeableConcept $code
     * @return static
     */
    public function setCode(null|FHIRCodeableConcept $code): self
    {
        if (null === $code) {
            unset($this->code);
            return $this;
        }
        $this->code = $code;
        return $this;
    }

    /**
     * A concept that may be defined by a formal reference to a terminology or ontology
     * or may be provided by text.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * The anatomical location where this condition manifests itself.
     *
     * @return iterable<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRCodeableConcept>
     */
    public function getBodySite(): array
    {
        return $this->bodySite ?? [];
    }

    /**
     * @return \ArrayIterator<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRCodeableConcept>
     */
    public function getBodySiteIterator(): iterable
    {
        if (!isset($this->bodySite)) {
            return new \EmptyIterator();
        }
        return new \ArrayIterator($this->bodySite);
    }

    /**
     * A concept that may be defined by a formal reference to a terminology or ontology
     * or may be provided by text.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * The anatomical location where this condition manifests itself.
     *
     * @param \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRCodeableConcept $bodySite
     * @return static
     */
    public function addBodySite(FHIRCodeableConcept $bodySite): self
    {
        if (!isset($this->bodySite)) {
            $this->bodySite = [];
        }
        $this->bodySite[] = $bodySite;
        return $this;
    }

    /**
     * A concept that may be defined by a formal reference to a terminology or ontology
     * or may be provided by text.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * The anatomical location where this condition manifests itself.
     *
     * @param \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRCodeableConcept ...$bodySite
     * @return static
     */
    public function setBodySite(FHIRCodeableConcept ...$bodySite): self
    {
        if ([] === $bodySite) {
            unset($this->bodySite);
            return $this;
        }
        $this->bodySite = $bodySite;
        return $this;
    }

    /**
     * A reference from one resource to another.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * Indicates the patient or group who the condition record is associated with.
     *
     * @return null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRReference
     */
    public function getSubject(): null|FHIRReference
    {
        return $this->subject ?? null;
    }

    /**
     * A reference from one resource to another.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * Indicates the patient or group who the condition record is associated with.
     *
     * @param null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRReference $subject
     * @return static
     */
    public function setSubject(null|FHIRReference $subject): self
    {
        if (null === $subject) {
            unset($this->subject);
            return $this;
        }
        $this->subject = $subject;
        return $this;
    }

    /**
     * A reference from one resource to another.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * The Encounter during which this Condition was created or to which the creation
     * of this record is tightly associated.
     *
     * @return null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRReference
     */
    public function getEncounter(): null|FHIRReference
    {
        return $this->encounter ?? null;
    }

    /**
     * A reference from one resource to another.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * The Encounter during which this Condition was created or to which the creation
     * of this record is tightly associated.
     *
     * @param null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRReference $encounter
     * @return static
     */
    public function setEncounter(null|FHIRReference $encounter): self
    {
        if (null === $encounter) {
            unset($this->encounter);
            return $this;
        }
        $this->encounter = $encounter;
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
     * Estimated or actual date or date-time the condition began, in the opinion of the
     * clinician. (choose any one of onset*, but only one)
     *
     * @return null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRDateTime
     */
    public function getOnsetDateTime(): null|FHIRDateTime
    {
        return $this->onsetDateTime ?? null;
    }

    /**
     * A date, date-time or partial date (e.g. just year or year + month). If hours and
     * minutes are specified, a time zone SHALL be populated. The format is a union of
     * the schema types gYear, gYearMonth, date and dateTime. Seconds must be provided
     * due to schema type constraints but may be zero-filled and may be ignored. Dates
     * SHALL be valid dates.
     * If the element is present, it must have either a \@value, an \@id, or extensions
     *
     * Estimated or actual date or date-time the condition began, in the opinion of the
     * clinician. (choose any one of onset*, but only one)
     *
     * @param null|string|\DateTimeInterface|\OpenEMR\FHIR\Versions\R4\Types\FHIRDateTimePrimitive|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRDateTime $onsetDateTime
     * @return static
     */
    public function setOnsetDateTime(null|string|\DateTimeInterface|FHIRDateTimePrimitive|FHIRDateTime $onsetDateTime): self
    {
        if (null === $onsetDateTime) {
            unset($this->onsetDateTime);
            return $this;
        }
        if (!($onsetDateTime instanceof FHIRDateTime)) {
            $onsetDateTime = new FHIRDateTime(value: $onsetDateTime);
        }
        $this->onsetDateTime = $onsetDateTime;
        return $this;
    }

    /**
     * A duration of time during which an organism (or a process) has existed.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * Estimated or actual date or date-time the condition began, in the opinion of the
     * clinician. (choose any one of onset*, but only one)
     *
     * @return null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRQuantity\FHIRAge
     */
    public function getOnsetAge(): null|FHIRAge
    {
        return $this->onsetAge ?? null;
    }

    /**
     * A duration of time during which an organism (or a process) has existed.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * Estimated or actual date or date-time the condition began, in the opinion of the
     * clinician. (choose any one of onset*, but only one)
     *
     * @param null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRQuantity\FHIRAge $onsetAge
     * @return static
     */
    public function setOnsetAge(null|FHIRAge $onsetAge): self
    {
        if (null === $onsetAge) {
            unset($this->onsetAge);
            return $this;
        }
        $this->onsetAge = $onsetAge;
        return $this;
    }

    /**
     * A time period defined by a start and end date and optionally time.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * Estimated or actual date or date-time the condition began, in the opinion of the
     * clinician. (choose any one of onset*, but only one)
     *
     * @return null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRPeriod
     */
    public function getOnsetPeriod(): null|FHIRPeriod
    {
        return $this->onsetPeriod ?? null;
    }

    /**
     * A time period defined by a start and end date and optionally time.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * Estimated or actual date or date-time the condition began, in the opinion of the
     * clinician. (choose any one of onset*, but only one)
     *
     * @param null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRPeriod $onsetPeriod
     * @return static
     */
    public function setOnsetPeriod(null|FHIRPeriod $onsetPeriod): self
    {
        if (null === $onsetPeriod) {
            unset($this->onsetPeriod);
            return $this;
        }
        $this->onsetPeriod = $onsetPeriod;
        return $this;
    }

    /**
     * A set of ordered Quantities defined by a low and high limit.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * Estimated or actual date or date-time the condition began, in the opinion of the
     * clinician. (choose any one of onset*, but only one)
     *
     * @return null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRRange
     */
    public function getOnsetRange(): null|FHIRRange
    {
        return $this->onsetRange ?? null;
    }

    /**
     * A set of ordered Quantities defined by a low and high limit.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * Estimated or actual date or date-time the condition began, in the opinion of the
     * clinician. (choose any one of onset*, but only one)
     *
     * @param null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRRange $onsetRange
     * @return static
     */
    public function setOnsetRange(null|FHIRRange $onsetRange): self
    {
        if (null === $onsetRange) {
            unset($this->onsetRange);
            return $this;
        }
        $this->onsetRange = $onsetRange;
        return $this;
    }

    /**
     * A sequence of Unicode characters
     * Note that FHIR strings SHALL NOT exceed 1MB in size
     * If the element is present, it must have either a \@value, an \@id, or extensions
     *
     * Estimated or actual date or date-time the condition began, in the opinion of the
     * clinician. (choose any one of onset*, but only one)
     *
     * @return null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRString
     */
    public function getOnsetString(): null|FHIRString
    {
        return $this->onsetString ?? null;
    }

    /**
     * A sequence of Unicode characters
     * Note that FHIR strings SHALL NOT exceed 1MB in size
     * If the element is present, it must have either a \@value, an \@id, or extensions
     *
     * Estimated or actual date or date-time the condition began, in the opinion of the
     * clinician. (choose any one of onset*, but only one)
     *
     * @param null|string|\OpenEMR\FHIR\Versions\R4\Types\FHIRStringPrimitive|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRString $onsetString
     * @return static
     */
    public function setOnsetString(null|string|FHIRStringPrimitive|FHIRString $onsetString): self
    {
        if (null === $onsetString) {
            unset($this->onsetString);
            return $this;
        }
        if (!($onsetString instanceof FHIRString)) {
            $onsetString = new FHIRString(value: $onsetString);
        }
        $this->onsetString = $onsetString;
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
     * The date or estimated date that the condition resolved or went into remission.
     * This is called "abatement" because of the many overloaded connotations
     * associated with "remission" or "resolution" - Conditions are never really
     * resolved, but they can abate. (choose any one of abatement*, but only one)
     *
     * @return null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRDateTime
     */
    public function getAbatementDateTime(): null|FHIRDateTime
    {
        return $this->abatementDateTime ?? null;
    }

    /**
     * A date, date-time or partial date (e.g. just year or year + month). If hours and
     * minutes are specified, a time zone SHALL be populated. The format is a union of
     * the schema types gYear, gYearMonth, date and dateTime. Seconds must be provided
     * due to schema type constraints but may be zero-filled and may be ignored. Dates
     * SHALL be valid dates.
     * If the element is present, it must have either a \@value, an \@id, or extensions
     *
     * The date or estimated date that the condition resolved or went into remission.
     * This is called "abatement" because of the many overloaded connotations
     * associated with "remission" or "resolution" - Conditions are never really
     * resolved, but they can abate. (choose any one of abatement*, but only one)
     *
     * @param null|string|\DateTimeInterface|\OpenEMR\FHIR\Versions\R4\Types\FHIRDateTimePrimitive|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRDateTime $abatementDateTime
     * @return static
     */
    public function setAbatementDateTime(null|string|\DateTimeInterface|FHIRDateTimePrimitive|FHIRDateTime $abatementDateTime): self
    {
        if (null === $abatementDateTime) {
            unset($this->abatementDateTime);
            return $this;
        }
        if (!($abatementDateTime instanceof FHIRDateTime)) {
            $abatementDateTime = new FHIRDateTime(value: $abatementDateTime);
        }
        $this->abatementDateTime = $abatementDateTime;
        return $this;
    }

    /**
     * A duration of time during which an organism (or a process) has existed.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * The date or estimated date that the condition resolved or went into remission.
     * This is called "abatement" because of the many overloaded connotations
     * associated with "remission" or "resolution" - Conditions are never really
     * resolved, but they can abate. (choose any one of abatement*, but only one)
     *
     * @return null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRQuantity\FHIRAge
     */
    public function getAbatementAge(): null|FHIRAge
    {
        return $this->abatementAge ?? null;
    }

    /**
     * A duration of time during which an organism (or a process) has existed.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * The date or estimated date that the condition resolved or went into remission.
     * This is called "abatement" because of the many overloaded connotations
     * associated with "remission" or "resolution" - Conditions are never really
     * resolved, but they can abate. (choose any one of abatement*, but only one)
     *
     * @param null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRQuantity\FHIRAge $abatementAge
     * @return static
     */
    public function setAbatementAge(null|FHIRAge $abatementAge): self
    {
        if (null === $abatementAge) {
            unset($this->abatementAge);
            return $this;
        }
        $this->abatementAge = $abatementAge;
        return $this;
    }

    /**
     * A time period defined by a start and end date and optionally time.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * The date or estimated date that the condition resolved or went into remission.
     * This is called "abatement" because of the many overloaded connotations
     * associated with "remission" or "resolution" - Conditions are never really
     * resolved, but they can abate. (choose any one of abatement*, but only one)
     *
     * @return null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRPeriod
     */
    public function getAbatementPeriod(): null|FHIRPeriod
    {
        return $this->abatementPeriod ?? null;
    }

    /**
     * A time period defined by a start and end date and optionally time.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * The date or estimated date that the condition resolved or went into remission.
     * This is called "abatement" because of the many overloaded connotations
     * associated with "remission" or "resolution" - Conditions are never really
     * resolved, but they can abate. (choose any one of abatement*, but only one)
     *
     * @param null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRPeriod $abatementPeriod
     * @return static
     */
    public function setAbatementPeriod(null|FHIRPeriod $abatementPeriod): self
    {
        if (null === $abatementPeriod) {
            unset($this->abatementPeriod);
            return $this;
        }
        $this->abatementPeriod = $abatementPeriod;
        return $this;
    }

    /**
     * A set of ordered Quantities defined by a low and high limit.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * The date or estimated date that the condition resolved or went into remission.
     * This is called "abatement" because of the many overloaded connotations
     * associated with "remission" or "resolution" - Conditions are never really
     * resolved, but they can abate. (choose any one of abatement*, but only one)
     *
     * @return null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRRange
     */
    public function getAbatementRange(): null|FHIRRange
    {
        return $this->abatementRange ?? null;
    }

    /**
     * A set of ordered Quantities defined by a low and high limit.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * The date or estimated date that the condition resolved or went into remission.
     * This is called "abatement" because of the many overloaded connotations
     * associated with "remission" or "resolution" - Conditions are never really
     * resolved, but they can abate. (choose any one of abatement*, but only one)
     *
     * @param null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRRange $abatementRange
     * @return static
     */
    public function setAbatementRange(null|FHIRRange $abatementRange): self
    {
        if (null === $abatementRange) {
            unset($this->abatementRange);
            return $this;
        }
        $this->abatementRange = $abatementRange;
        return $this;
    }

    /**
     * A sequence of Unicode characters
     * Note that FHIR strings SHALL NOT exceed 1MB in size
     * If the element is present, it must have either a \@value, an \@id, or extensions
     *
     * The date or estimated date that the condition resolved or went into remission.
     * This is called "abatement" because of the many overloaded connotations
     * associated with "remission" or "resolution" - Conditions are never really
     * resolved, but they can abate. (choose any one of abatement*, but only one)
     *
     * @return null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRString
     */
    public function getAbatementString(): null|FHIRString
    {
        return $this->abatementString ?? null;
    }

    /**
     * A sequence of Unicode characters
     * Note that FHIR strings SHALL NOT exceed 1MB in size
     * If the element is present, it must have either a \@value, an \@id, or extensions
     *
     * The date or estimated date that the condition resolved or went into remission.
     * This is called "abatement" because of the many overloaded connotations
     * associated with "remission" or "resolution" - Conditions are never really
     * resolved, but they can abate. (choose any one of abatement*, but only one)
     *
     * @param null|string|\OpenEMR\FHIR\Versions\R4\Types\FHIRStringPrimitive|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRString $abatementString
     * @return static
     */
    public function setAbatementString(null|string|FHIRStringPrimitive|FHIRString $abatementString): self
    {
        if (null === $abatementString) {
            unset($this->abatementString);
            return $this;
        }
        if (!($abatementString instanceof FHIRString)) {
            $abatementString = new FHIRString(value: $abatementString);
        }
        $this->abatementString = $abatementString;
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
     * The recordedDate represents when this particular Condition record was created in
     * the system, which is often a system-generated date.
     *
     * @return null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRDateTime
     */
    public function getRecordedDate(): null|FHIRDateTime
    {
        return $this->recordedDate ?? null;
    }

    /**
     * A date, date-time or partial date (e.g. just year or year + month). If hours and
     * minutes are specified, a time zone SHALL be populated. The format is a union of
     * the schema types gYear, gYearMonth, date and dateTime. Seconds must be provided
     * due to schema type constraints but may be zero-filled and may be ignored. Dates
     * SHALL be valid dates.
     * If the element is present, it must have either a \@value, an \@id, or extensions
     *
     * The recordedDate represents when this particular Condition record was created in
     * the system, which is often a system-generated date.
     *
     * @param null|string|\DateTimeInterface|\OpenEMR\FHIR\Versions\R4\Types\FHIRDateTimePrimitive|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRDateTime $recordedDate
     * @return static
     */
    public function setRecordedDate(null|string|\DateTimeInterface|FHIRDateTimePrimitive|FHIRDateTime $recordedDate): self
    {
        if (null === $recordedDate) {
            unset($this->recordedDate);
            return $this;
        }
        if (!($recordedDate instanceof FHIRDateTime)) {
            $recordedDate = new FHIRDateTime(value: $recordedDate);
        }
        $this->recordedDate = $recordedDate;
        return $this;
    }

    /**
     * A reference from one resource to another.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * Individual who recorded the record and takes responsibility for its content.
     *
     * @return null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRReference
     */
    public function getRecorder(): null|FHIRReference
    {
        return $this->recorder ?? null;
    }

    /**
     * A reference from one resource to another.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * Individual who recorded the record and takes responsibility for its content.
     *
     * @param null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRReference $recorder
     * @return static
     */
    public function setRecorder(null|FHIRReference $recorder): self
    {
        if (null === $recorder) {
            unset($this->recorder);
            return $this;
        }
        $this->recorder = $recorder;
        return $this;
    }

    /**
     * A reference from one resource to another.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * Individual who is making the condition statement.
     *
     * @return null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRReference
     */
    public function getAsserter(): null|FHIRReference
    {
        return $this->asserter ?? null;
    }

    /**
     * A reference from one resource to another.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * Individual who is making the condition statement.
     *
     * @param null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRReference $asserter
     * @return static
     */
    public function setAsserter(null|FHIRReference $asserter): self
    {
        if (null === $asserter) {
            unset($this->asserter);
            return $this;
        }
        $this->asserter = $asserter;
        return $this;
    }

    /**
     * A clinical condition, problem, diagnosis, or other event, situation, issue, or
     * clinical concept that has risen to a level of concern.
     *
     * Clinical stage or grade of a condition. May include formal severity assessments.
     *
     * @return iterable<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRCondition\FHIRConditionStage>
     */
    public function getStage(): array
    {
        return $this->stage ?? [];
    }

    /**
     * @return \ArrayIterator<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRCondition\FHIRConditionStage>
     */
    public function getStageIterator(): iterable
    {
        if (!isset($this->stage)) {
            return new \EmptyIterator();
        }
        return new \ArrayIterator($this->stage);
    }

    /**
     * A clinical condition, problem, diagnosis, or other event, situation, issue, or
     * clinical concept that has risen to a level of concern.
     *
     * Clinical stage or grade of a condition. May include formal severity assessments.
     *
     * @param \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRCondition\FHIRConditionStage $stage
     * @return static
     */
    public function addStage(FHIRConditionStage $stage): self
    {
        if (!isset($this->stage)) {
            $this->stage = [];
        }
        $this->stage[] = $stage;
        return $this;
    }

    /**
     * A clinical condition, problem, diagnosis, or other event, situation, issue, or
     * clinical concept that has risen to a level of concern.
     *
     * Clinical stage or grade of a condition. May include formal severity assessments.
     *
     * @param \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRCondition\FHIRConditionStage ...$stage
     * @return static
     */
    public function setStage(FHIRConditionStage ...$stage): self
    {
        if ([] === $stage) {
            unset($this->stage);
            return $this;
        }
        $this->stage = $stage;
        return $this;
    }

    /**
     * A clinical condition, problem, diagnosis, or other event, situation, issue, or
     * clinical concept that has risen to a level of concern.
     *
     * Supporting evidence / manifestations that are the basis of the Condition's
     * verification status, such as evidence that confirmed or refuted the condition.
     *
     * @return iterable<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRCondition\FHIRConditionEvidence>
     */
    public function getEvidence(): array
    {
        return $this->evidence ?? [];
    }

    /**
     * @return \ArrayIterator<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRCondition\FHIRConditionEvidence>
     */
    public function getEvidenceIterator(): iterable
    {
        if (!isset($this->evidence)) {
            return new \EmptyIterator();
        }
        return new \ArrayIterator($this->evidence);
    }

    /**
     * A clinical condition, problem, diagnosis, or other event, situation, issue, or
     * clinical concept that has risen to a level of concern.
     *
     * Supporting evidence / manifestations that are the basis of the Condition's
     * verification status, such as evidence that confirmed or refuted the condition.
     *
     * @param \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRCondition\FHIRConditionEvidence $evidence
     * @return static
     */
    public function addEvidence(FHIRConditionEvidence $evidence): self
    {
        if (!isset($this->evidence)) {
            $this->evidence = [];
        }
        $this->evidence[] = $evidence;
        return $this;
    }

    /**
     * A clinical condition, problem, diagnosis, or other event, situation, issue, or
     * clinical concept that has risen to a level of concern.
     *
     * Supporting evidence / manifestations that are the basis of the Condition's
     * verification status, such as evidence that confirmed or refuted the condition.
     *
     * @param \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRCondition\FHIRConditionEvidence ...$evidence
     * @return static
     */
    public function setEvidence(FHIRConditionEvidence ...$evidence): self
    {
        if ([] === $evidence) {
            unset($this->evidence);
            return $this;
        }
        $this->evidence = $evidence;
        return $this;
    }

    /**
     * A text note which also contains information about who made the statement and
     * when.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * Additional information about the Condition. This is a general notes/comments
     * entry for description of the Condition, its diagnosis and prognosis.
     *
     * @return iterable<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRAnnotation>
     */
    public function getNote(): array
    {
        return $this->note ?? [];
    }

    /**
     * @return \ArrayIterator<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRAnnotation>
     */
    public function getNoteIterator(): iterable
    {
        if (!isset($this->note)) {
            return new \EmptyIterator();
        }
        return new \ArrayIterator($this->note);
    }

    /**
     * A text note which also contains information about who made the statement and
     * when.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * Additional information about the Condition. This is a general notes/comments
     * entry for description of the Condition, its diagnosis and prognosis.
     *
     * @param \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRAnnotation $note
     * @return static
     */
    public function addNote(FHIRAnnotation $note): self
    {
        if (!isset($this->note)) {
            $this->note = [];
        }
        $this->note[] = $note;
        return $this;
    }

    /**
     * A text note which also contains information about who made the statement and
     * when.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * Additional information about the Condition. This is a general notes/comments
     * entry for description of the Condition, its diagnosis and prognosis.
     *
     * @param \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRAnnotation ...$note
     * @return static
     */
    public function setNote(FHIRAnnotation ...$note): self
    {
        if ([] === $note) {
            unset($this->note);
            return $this;
        }
        $this->note = $note;
        return $this;
    }

    /* class_default.php:201 */
    /**
     * @param string|\SimpleXMLElement $element
     * @param null|\OpenEMR\FHIR\Encoding\UnserializeConfig $config
     * @param null|\OpenEMR\FHIR\Versions\R4\Types\FHIRResource\FHIRDomainResource\FHIRCondition $type
     * @return \OpenEMR\FHIR\Versions\R4\Types\FHIRResource\FHIRDomainResource\FHIRCondition
     * @throws \Exception
     */
    public static function xmlUnserialize(string|\SimpleXMLElement $element,
                                          null|UnserializeConfig $config = null,
                                          null|ResourceTypeInterface $type = null): self
    {
        if (null === $type) {
            $type = new static();
        } else if (!($type instanceof FHIRCondition)) {
            throw new \RuntimeException(sprintf(
                '%s::xmlUnserialize - $type must be instance of \\%s or null, %s seen.',
                ltrim(substr(self::class, (int)strrpos(self::class, '\\')), '\\'),
                static::class,
                $type::class
            ));
        }
        if (null === $config) {
            $config = (new Version())->getConfig()->getUnserializeConfig();
        }
        if (is_string($element)) {
            $element = new \SimpleXMLElement($element, $config->getLibxmlOpts());
        }
        if (null !== ($ns = $element->getNamespaces()[''] ?? null)) {
            $type->_setSourceXMLNS((string)$ns);
        }
        foreach ($element->children() as $ce) {
            $cen = $ce->getName();
            if (self::FIELD_ID === $cen) {
                $type->setId(FHIRId::xmlUnserialize($ce, $config));
            } else if (self::FIELD_META === $cen) {
                $type->setMeta(FHIRMeta::xmlUnserialize($ce, $config));
            } else if (self::FIELD_IMPLICIT_RULES === $cen) {
                $type->setImplicitRules(FHIRUri::xmlUnserialize($ce, $config));
            } else if (self::FIELD_LANGUAGE === $cen) {
                $type->setLanguage(FHIRCode::xmlUnserialize($ce, $config));
            } else if (self::FIELD_TEXT === $cen) {
                $type->setText(FHIRNarrative::xmlUnserialize($ce, $config));
            } else if (self::FIELD_CONTAINED === $cen) {
                foreach ($ce->children() as $cen) {
                    /** @var \OpenEMR\FHIR\Versions\R4\VersionContainedTypeInterface $cn */
                    $cn = VersionTypeMap::mustGetContainedTypeClassnameFromXML($cen);
                    $type->addContained($cn::xmlUnserialize($cen, $config));
                }
            } else if (self::FIELD_EXTENSION === $cen) {
                $type->addExtension(FHIRExtension::xmlUnserialize($ce, $config));
            } else if (self::FIELD_MODIFIER_EXTENSION === $cen) {
                $type->addModifierExtension(FHIRExtension::xmlUnserialize($ce, $config));
            } else if (self::FIELD_IDENTIFIER === $cen) {
                $type->addIdentifier(FHIRIdentifier::xmlUnserialize($ce, $config));
            } else if (self::FIELD_CLINICAL_STATUS === $cen) {
                $type->setClinicalStatus(FHIRCodeableConcept::xmlUnserialize($ce, $config));
            } else if (self::FIELD_VERIFICATION_STATUS === $cen) {
                $type->setVerificationStatus(FHIRCodeableConcept::xmlUnserialize($ce, $config));
            } else if (self::FIELD_CATEGORY === $cen) {
                $type->addCategory(FHIRCodeableConcept::xmlUnserialize($ce, $config));
            } else if (self::FIELD_SEVERITY === $cen) {
                $type->setSeverity(FHIRCodeableConcept::xmlUnserialize($ce, $config));
            } else if (self::FIELD_CODE === $cen) {
                $type->setCode(FHIRCodeableConcept::xmlUnserialize($ce, $config));
            } else if (self::FIELD_BODY_SITE === $cen) {
                $type->addBodySite(FHIRCodeableConcept::xmlUnserialize($ce, $config));
            } else if (self::FIELD_SUBJECT === $cen) {
                $type->setSubject(FHIRReference::xmlUnserialize($ce, $config));
            } else if (self::FIELD_ENCOUNTER === $cen) {
                $type->setEncounter(FHIRReference::xmlUnserialize($ce, $config));
            } else if (self::FIELD_ONSET_DATE_TIME === $cen) {
                $type->setOnsetDateTime(FHIRDateTime::xmlUnserialize($ce, $config));
            } else if (self::FIELD_ONSET_AGE === $cen) {
                $type->setOnsetAge(FHIRAge::xmlUnserialize($ce, $config));
            } else if (self::FIELD_ONSET_PERIOD === $cen) {
                $type->setOnsetPeriod(FHIRPeriod::xmlUnserialize($ce, $config));
            } else if (self::FIELD_ONSET_RANGE === $cen) {
                $type->setOnsetRange(FHIRRange::xmlUnserialize($ce, $config));
            } else if (self::FIELD_ONSET_STRING === $cen) {
                $type->setOnsetString(FHIRString::xmlUnserialize($ce, $config));
            } else if (self::FIELD_ABATEMENT_DATE_TIME === $cen) {
                $type->setAbatementDateTime(FHIRDateTime::xmlUnserialize($ce, $config));
            } else if (self::FIELD_ABATEMENT_AGE === $cen) {
                $type->setAbatementAge(FHIRAge::xmlUnserialize($ce, $config));
            } else if (self::FIELD_ABATEMENT_PERIOD === $cen) {
                $type->setAbatementPeriod(FHIRPeriod::xmlUnserialize($ce, $config));
            } else if (self::FIELD_ABATEMENT_RANGE === $cen) {
                $type->setAbatementRange(FHIRRange::xmlUnserialize($ce, $config));
            } else if (self::FIELD_ABATEMENT_STRING === $cen) {
                $type->setAbatementString(FHIRString::xmlUnserialize($ce, $config));
            } else if (self::FIELD_RECORDED_DATE === $cen) {
                $type->setRecordedDate(FHIRDateTime::xmlUnserialize($ce, $config));
            } else if (self::FIELD_RECORDER === $cen) {
                $type->setRecorder(FHIRReference::xmlUnserialize($ce, $config));
            } else if (self::FIELD_ASSERTER === $cen) {
                $type->setAsserter(FHIRReference::xmlUnserialize($ce, $config));
            } else if (self::FIELD_STAGE === $cen) {
                $type->addStage(FHIRConditionStage::xmlUnserialize($ce, $config));
            } else if (self::FIELD_EVIDENCE === $cen) {
                $type->addEvidence(FHIRConditionEvidence::xmlUnserialize($ce, $config));
            } else if (self::FIELD_NOTE === $cen) {
                $type->addNote(FHIRAnnotation::xmlUnserialize($ce, $config));
            }
        }
        $attributes = $element->attributes();
        if (isset($attributes[self::FIELD_ID])) {
            if (isset($type->id)) {
                $type->id->setValue((string)$attributes[self::FIELD_ID]);
            } else {
                $type->setId((string)$attributes[self::FIELD_ID]);
            }
            $type->_setXMLFieldValueLocation(self::FIELD_ID, ValueXMLLocationEnum::PARENT_ATTRIBUTE);
        }
        if (isset($attributes[self::FIELD_IMPLICIT_RULES])) {
            if (isset($type->implicitRules)) {
                $type->implicitRules->setValue((string)$attributes[self::FIELD_IMPLICIT_RULES]);
            } else {
                $type->setImplicitRules((string)$attributes[self::FIELD_IMPLICIT_RULES]);
            }
            $type->_setXMLFieldValueLocation(self::FIELD_IMPLICIT_RULES, ValueXMLLocationEnum::PARENT_ATTRIBUTE);
        }
        if (isset($attributes[self::FIELD_LANGUAGE])) {
            if (isset($type->language)) {
                $type->language->setValue((string)$attributes[self::FIELD_LANGUAGE]);
            } else {
                $type->setLanguage((string)$attributes[self::FIELD_LANGUAGE]);
            }
            $type->_setXMLFieldValueLocation(self::FIELD_LANGUAGE, ValueXMLLocationEnum::PARENT_ATTRIBUTE);
        }
        if (isset($attributes[self::FIELD_ONSET_DATE_TIME])) {
            if (isset($type->onsetDateTime)) {
                $type->onsetDateTime->setValue((string)$attributes[self::FIELD_ONSET_DATE_TIME]);
            } else {
                $type->setOnsetDateTime((string)$attributes[self::FIELD_ONSET_DATE_TIME]);
            }
            $type->_setXMLFieldValueLocation(self::FIELD_ONSET_DATE_TIME, ValueXMLLocationEnum::PARENT_ATTRIBUTE);
        }
        if (isset($attributes[self::FIELD_ONSET_STRING])) {
            if (isset($type->onsetString)) {
                $type->onsetString->setValue((string)$attributes[self::FIELD_ONSET_STRING]);
            } else {
                $type->setOnsetString((string)$attributes[self::FIELD_ONSET_STRING]);
            }
            $type->_setXMLFieldValueLocation(self::FIELD_ONSET_STRING, ValueXMLLocationEnum::PARENT_ATTRIBUTE);
        }
        if (isset($attributes[self::FIELD_ABATEMENT_DATE_TIME])) {
            if (isset($type->abatementDateTime)) {
                $type->abatementDateTime->setValue((string)$attributes[self::FIELD_ABATEMENT_DATE_TIME]);
            } else {
                $type->setAbatementDateTime((string)$attributes[self::FIELD_ABATEMENT_DATE_TIME]);
            }
            $type->_setXMLFieldValueLocation(self::FIELD_ABATEMENT_DATE_TIME, ValueXMLLocationEnum::PARENT_ATTRIBUTE);
        }
        if (isset($attributes[self::FIELD_ABATEMENT_STRING])) {
            if (isset($type->abatementString)) {
                $type->abatementString->setValue((string)$attributes[self::FIELD_ABATEMENT_STRING]);
            } else {
                $type->setAbatementString((string)$attributes[self::FIELD_ABATEMENT_STRING]);
            }
            $type->_setXMLFieldValueLocation(self::FIELD_ABATEMENT_STRING, ValueXMLLocationEnum::PARENT_ATTRIBUTE);
        }
        if (isset($attributes[self::FIELD_RECORDED_DATE])) {
            if (isset($type->recordedDate)) {
                $type->recordedDate->setValue((string)$attributes[self::FIELD_RECORDED_DATE]);
            } else {
                $type->setRecordedDate((string)$attributes[self::FIELD_RECORDED_DATE]);
            }
            $type->_setXMLFieldValueLocation(self::FIELD_RECORDED_DATE, ValueXMLLocationEnum::PARENT_ATTRIBUTE);
        }
        return $type;
    }

    /**
     * @param null|\OpenEMR\FHIR\Encoding\XMLWriter $xw
     * @param null|\OpenEMR\FHIR\Encoding\SerializeConfig $config
     * @return \OpenEMR\FHIR\Encoding\XMLWriter
     */
    public function xmlSerialize(null|XMLWriter $xw = null,
                                 null|SerializeConfig $config = null): XMLWriter
    {
        if (null === $config) {
            $config = (new Version())->getConfig()->getSerializeConfig();
        }
        if (null === $xw) {
            $xw = new XMLWriter($config);
        }
        if (!$xw->isOpen()) {
            $xw->openMemory();
        }
        if (!$xw->isDocStarted()) {
            $docStarted = true;
            $xw->startDocument();
        }
        if (!$xw->isRootOpen()) {
            $rootOpened = true;
            $xw->openRootNode('Condition', $this->_getSourceXMLNS());
        }
        if (isset($this->onsetDateTime) && ValueXMLLocationEnum::PARENT_ATTRIBUTE === $this->_valueXMLLocations[self::FIELD_ONSET_DATE_TIME]) {
            $xw->writeAttribute(self::FIELD_ONSET_DATE_TIME, $this->onsetDateTime->_getValueAsString());
        }
        if (isset($this->onsetString) && ValueXMLLocationEnum::PARENT_ATTRIBUTE === $this->_valueXMLLocations[self::FIELD_ONSET_STRING]) {
            $xw->writeAttribute(self::FIELD_ONSET_STRING, $this->onsetString->_getValueAsString());
        }
        if (isset($this->abatementDateTime) && ValueXMLLocationEnum::PARENT_ATTRIBUTE === $this->_valueXMLLocations[self::FIELD_ABATEMENT_DATE_TIME]) {
            $xw->writeAttribute(self::FIELD_ABATEMENT_DATE_TIME, $this->abatementDateTime->_getValueAsString());
        }
        if (isset($this->abatementString) && ValueXMLLocationEnum::PARENT_ATTRIBUTE === $this->_valueXMLLocations[self::FIELD_ABATEMENT_STRING]) {
            $xw->writeAttribute(self::FIELD_ABATEMENT_STRING, $this->abatementString->_getValueAsString());
        }
        if (isset($this->recordedDate) && ValueXMLLocationEnum::PARENT_ATTRIBUTE === $this->_valueXMLLocations[self::FIELD_RECORDED_DATE]) {
            $xw->writeAttribute(self::FIELD_RECORDED_DATE, $this->recordedDate->_getValueAsString());
        }
        parent::xmlSerialize($xw, $config);
        if (isset($this->identifier)) {
            foreach ($this->identifier as $v) {
                $xw->startElement(self::FIELD_IDENTIFIER);
                $v->xmlSerialize($xw, $config);
                $xw->endElement();
            }
        }
        if (isset($this->clinicalStatus)) {
            $xw->startElement(self::FIELD_CLINICAL_STATUS);
            $this->clinicalStatus->xmlSerialize($xw, $config);
            $xw->endElement();
        }
        if (isset($this->verificationStatus)) {
            $xw->startElement(self::FIELD_VERIFICATION_STATUS);
            $this->verificationStatus->xmlSerialize($xw, $config);
            $xw->endElement();
        }
        if (isset($this->category)) {
            foreach ($this->category as $v) {
                $xw->startElement(self::FIELD_CATEGORY);
                $v->xmlSerialize($xw, $config);
                $xw->endElement();
            }
        }
        if (isset($this->severity)) {
            $xw->startElement(self::FIELD_SEVERITY);
            $this->severity->xmlSerialize($xw, $config);
            $xw->endElement();
        }
        if (isset($this->code)) {
            $xw->startElement(self::FIELD_CODE);
            $this->code->xmlSerialize($xw, $config);
            $xw->endElement();
        }
        if (isset($this->bodySite)) {
            foreach ($this->bodySite as $v) {
                $xw->startElement(self::FIELD_BODY_SITE);
                $v->xmlSerialize($xw, $config);
                $xw->endElement();
            }
        }
        if (isset($this->subject)) {
            $xw->startElement(self::FIELD_SUBJECT);
            $this->subject->xmlSerialize($xw, $config);
            $xw->endElement();
        }
        if (isset($this->encounter)) {
            $xw->startElement(self::FIELD_ENCOUNTER);
            $this->encounter->xmlSerialize($xw, $config);
            $xw->endElement();
        }
        if (isset($this->onsetDateTime)
            && (ValueXMLLocationEnum::PARENT_ATTRIBUTE !== $this->_valueXMLLocations[self::FIELD_ONSET_DATE_TIME]
                || $this->onsetDateTime->_nonValueFieldDefined())) {
            $xw->startElement(self::FIELD_ONSET_DATE_TIME);
            $this->onsetDateTime->xmlSerialize($xw, $config, $this->_valueXMLLocations[self::FIELD_ONSET_DATE_TIME]);
            $xw->endElement();
        }
        if (isset($this->onsetAge)) {
            $xw->startElement(self::FIELD_ONSET_AGE);
            $this->onsetAge->xmlSerialize($xw, $config);
            $xw->endElement();
        }
        if (isset($this->onsetPeriod)) {
            $xw->startElement(self::FIELD_ONSET_PERIOD);
            $this->onsetPeriod->xmlSerialize($xw, $config);
            $xw->endElement();
        }
        if (isset($this->onsetRange)) {
            $xw->startElement(self::FIELD_ONSET_RANGE);
            $this->onsetRange->xmlSerialize($xw, $config);
            $xw->endElement();
        }
        if (isset($this->onsetString)
            && (ValueXMLLocationEnum::PARENT_ATTRIBUTE !== $this->_valueXMLLocations[self::FIELD_ONSET_STRING]
                || $this->onsetString->_nonValueFieldDefined())) {
            $xw->startElement(self::FIELD_ONSET_STRING);
            $this->onsetString->xmlSerialize($xw, $config, $this->_valueXMLLocations[self::FIELD_ONSET_STRING]);
            $xw->endElement();
        }
        if (isset($this->abatementDateTime)
            && (ValueXMLLocationEnum::PARENT_ATTRIBUTE !== $this->_valueXMLLocations[self::FIELD_ABATEMENT_DATE_TIME]
                || $this->abatementDateTime->_nonValueFieldDefined())) {
            $xw->startElement(self::FIELD_ABATEMENT_DATE_TIME);
            $this->abatementDateTime->xmlSerialize($xw, $config, $this->_valueXMLLocations[self::FIELD_ABATEMENT_DATE_TIME]);
            $xw->endElement();
        }
        if (isset($this->abatementAge)) {
            $xw->startElement(self::FIELD_ABATEMENT_AGE);
            $this->abatementAge->xmlSerialize($xw, $config);
            $xw->endElement();
        }
        if (isset($this->abatementPeriod)) {
            $xw->startElement(self::FIELD_ABATEMENT_PERIOD);
            $this->abatementPeriod->xmlSerialize($xw, $config);
            $xw->endElement();
        }
        if (isset($this->abatementRange)) {
            $xw->startElement(self::FIELD_ABATEMENT_RANGE);
            $this->abatementRange->xmlSerialize($xw, $config);
            $xw->endElement();
        }
        if (isset($this->abatementString)
            && (ValueXMLLocationEnum::PARENT_ATTRIBUTE !== $this->_valueXMLLocations[self::FIELD_ABATEMENT_STRING]
                || $this->abatementString->_nonValueFieldDefined())) {
            $xw->startElement(self::FIELD_ABATEMENT_STRING);
            $this->abatementString->xmlSerialize($xw, $config, $this->_valueXMLLocations[self::FIELD_ABATEMENT_STRING]);
            $xw->endElement();
        }
        if (isset($this->recordedDate)
            && (ValueXMLLocationEnum::PARENT_ATTRIBUTE !== $this->_valueXMLLocations[self::FIELD_RECORDED_DATE]
                || $this->recordedDate->_nonValueFieldDefined())) {
            $xw->startElement(self::FIELD_RECORDED_DATE);
            $this->recordedDate->xmlSerialize($xw, $config, $this->_valueXMLLocations[self::FIELD_RECORDED_DATE]);
            $xw->endElement();
        }
        if (isset($this->recorder)) {
            $xw->startElement(self::FIELD_RECORDER);
            $this->recorder->xmlSerialize($xw, $config);
            $xw->endElement();
        }
        if (isset($this->asserter)) {
            $xw->startElement(self::FIELD_ASSERTER);
            $this->asserter->xmlSerialize($xw, $config);
            $xw->endElement();
        }
        if (isset($this->stage)) {
            foreach ($this->stage as $v) {
                $xw->startElement(self::FIELD_STAGE);
                $v->xmlSerialize($xw, $config);
                $xw->endElement();
            }
        }
        if (isset($this->evidence)) {
            foreach ($this->evidence as $v) {
                $xw->startElement(self::FIELD_EVIDENCE);
                $v->xmlSerialize($xw, $config);
                $xw->endElement();
            }
        }
        if (isset($this->note)) {
            foreach ($this->note as $v) {
                $xw->startElement(self::FIELD_NOTE);
                $v->xmlSerialize($xw, $config);
                $xw->endElement();
            }
        }
        if ($rootOpened ?? false) {
            $xw->endElement();
        }
        if ($docStarted ?? false) {
            $xw->endDocument();
        }
        return $xw;
    }

    /**
     * @param string|\stdClass $decoded
     * @param null|\OpenEMR\FHIR\Encoding\UnserializeConfig $config
     * @param null|\OpenEMR\FHIR\Versions\R4\Types\FHIRResource\FHIRDomainResource\FHIRCondition $type
     * @return \OpenEMR\FHIR\Versions\R4\Types\FHIRResource\FHIRDomainResource\FHIRCondition
     * @throws \Exception
     */
    public static function jsonUnserialize(string|\stdClass $decoded,
                                           null|UnserializeConfig $config = null,
                                           null|ResourceTypeInterface $type = null): self
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
        } else if (!($type instanceof FHIRCondition)) {
            throw new \RuntimeException(sprintf(
                '%s::jsonUnserialize - $type must be instance of \\%s or null, %s seen.',
                ltrim(substr(self::class, (int)strrpos(self::class, '\\')), '\\'),
                static::class,
                $type::class
            ));
        }
        if (null === $config) {
            $config = (new Version())->getConfig()->getUnserializeConfig();
        }
        if (is_string($decoded)) {
            $decoded = json_decode(json: $decoded,
                                associative: false,
                                depth: $config->getJSONDecodeMaxDepth(),
                                flags: $config->getJSONDecodeOpts());
        }
        parent::jsonUnserialize($decoded, $config, $type);
        if (isset($decoded->identifier) || property_exists($decoded, self::FIELD_IDENTIFIER)) {
            if (is_object($decoded->identifier)) {
                $vals = [$decoded->identifier];
                $type->_setJSONFieldElideSingletonArray(self::FIELD_IDENTIFIER, true);
            } else {
                $vals = $decoded->identifier;
            }
            foreach($vals as $v) {
                $type->addIdentifier(FHIRIdentifier::jsonUnserialize($v, $config));
            }
        }
        if (isset($decoded->clinicalStatus) || property_exists($decoded, self::FIELD_CLINICAL_STATUS)) {
            if (is_array($decoded->clinicalStatus)) {
                $type->setClinicalStatus(FHIRCodeableConcept::jsonUnserialize(reset($decoded->clinicalStatus), $config));
            } else {
                $type->setClinicalStatus(FHIRCodeableConcept::jsonUnserialize($decoded->clinicalStatus, $config));
            }
        }
        if (isset($decoded->verificationStatus) || property_exists($decoded, self::FIELD_VERIFICATION_STATUS)) {
            if (is_array($decoded->verificationStatus)) {
                $type->setVerificationStatus(FHIRCodeableConcept::jsonUnserialize(reset($decoded->verificationStatus), $config));
            } else {
                $type->setVerificationStatus(FHIRCodeableConcept::jsonUnserialize($decoded->verificationStatus, $config));
            }
        }
        if (isset($decoded->category) || property_exists($decoded, self::FIELD_CATEGORY)) {
            if (is_object($decoded->category)) {
                $vals = [$decoded->category];
                $type->_setJSONFieldElideSingletonArray(self::FIELD_CATEGORY, true);
            } else {
                $vals = $decoded->category;
            }
            foreach($vals as $v) {
                $type->addCategory(FHIRCodeableConcept::jsonUnserialize($v, $config));
            }
        }
        if (isset($decoded->severity) || property_exists($decoded, self::FIELD_SEVERITY)) {
            if (is_array($decoded->severity)) {
                $type->setSeverity(FHIRCodeableConcept::jsonUnserialize(reset($decoded->severity), $config));
            } else {
                $type->setSeverity(FHIRCodeableConcept::jsonUnserialize($decoded->severity, $config));
            }
        }
        if (isset($decoded->code) || property_exists($decoded, self::FIELD_CODE)) {
            if (is_array($decoded->code)) {
                $type->setCode(FHIRCodeableConcept::jsonUnserialize(reset($decoded->code), $config));
            } else {
                $type->setCode(FHIRCodeableConcept::jsonUnserialize($decoded->code, $config));
            }
        }
        if (isset($decoded->bodySite) || property_exists($decoded, self::FIELD_BODY_SITE)) {
            if (is_object($decoded->bodySite)) {
                $vals = [$decoded->bodySite];
                $type->_setJSONFieldElideSingletonArray(self::FIELD_BODY_SITE, true);
            } else {
                $vals = $decoded->bodySite;
            }
            foreach($vals as $v) {
                $type->addBodySite(FHIRCodeableConcept::jsonUnserialize($v, $config));
            }
        }
        if (isset($decoded->subject) || property_exists($decoded, self::FIELD_SUBJECT)) {
            if (is_array($decoded->subject)) {
                $type->setSubject(FHIRReference::jsonUnserialize(reset($decoded->subject), $config));
            } else {
                $type->setSubject(FHIRReference::jsonUnserialize($decoded->subject, $config));
            }
        }
        if (isset($decoded->encounter) || property_exists($decoded, self::FIELD_ENCOUNTER)) {
            if (is_array($decoded->encounter)) {
                $type->setEncounter(FHIRReference::jsonUnserialize(reset($decoded->encounter), $config));
            } else {
                $type->setEncounter(FHIRReference::jsonUnserialize($decoded->encounter, $config));
            }
        }
        if (isset($decoded->onsetDateTime)
            || isset($decoded->_onsetDateTime)
            || property_exists($decoded, self::FIELD_ONSET_DATE_TIME)
            || property_exists($decoded, self::FIELD_ONSET_DATE_TIME_EXT)) {
            $v = $decoded->_onsetDateTime ?? new \stdClass();
            $v->value = $decoded->onsetDateTime ?? null;
            $type->setOnsetDateTime(FHIRDateTime::jsonUnserialize($v, $config));
        }
        if (isset($decoded->onsetAge) || property_exists($decoded, self::FIELD_ONSET_AGE)) {
            if (is_array($decoded->onsetAge)) {
                $type->setOnsetAge(FHIRAge::jsonUnserialize(reset($decoded->onsetAge), $config));
            } else {
                $type->setOnsetAge(FHIRAge::jsonUnserialize($decoded->onsetAge, $config));
            }
        }
        if (isset($decoded->onsetPeriod) || property_exists($decoded, self::FIELD_ONSET_PERIOD)) {
            if (is_array($decoded->onsetPeriod)) {
                $type->setOnsetPeriod(FHIRPeriod::jsonUnserialize(reset($decoded->onsetPeriod), $config));
            } else {
                $type->setOnsetPeriod(FHIRPeriod::jsonUnserialize($decoded->onsetPeriod, $config));
            }
        }
        if (isset($decoded->onsetRange) || property_exists($decoded, self::FIELD_ONSET_RANGE)) {
            if (is_array($decoded->onsetRange)) {
                $type->setOnsetRange(FHIRRange::jsonUnserialize(reset($decoded->onsetRange), $config));
            } else {
                $type->setOnsetRange(FHIRRange::jsonUnserialize($decoded->onsetRange, $config));
            }
        }
        if (isset($decoded->onsetString)
            || isset($decoded->_onsetString)
            || property_exists($decoded, self::FIELD_ONSET_STRING)
            || property_exists($decoded, self::FIELD_ONSET_STRING_EXT)) {
            $v = $decoded->_onsetString ?? new \stdClass();
            $v->value = $decoded->onsetString ?? null;
            $type->setOnsetString(FHIRString::jsonUnserialize($v, $config));
        }
        if (isset($decoded->abatementDateTime)
            || isset($decoded->_abatementDateTime)
            || property_exists($decoded, self::FIELD_ABATEMENT_DATE_TIME)
            || property_exists($decoded, self::FIELD_ABATEMENT_DATE_TIME_EXT)) {
            $v = $decoded->_abatementDateTime ?? new \stdClass();
            $v->value = $decoded->abatementDateTime ?? null;
            $type->setAbatementDateTime(FHIRDateTime::jsonUnserialize($v, $config));
        }
        if (isset($decoded->abatementAge) || property_exists($decoded, self::FIELD_ABATEMENT_AGE)) {
            if (is_array($decoded->abatementAge)) {
                $type->setAbatementAge(FHIRAge::jsonUnserialize(reset($decoded->abatementAge), $config));
            } else {
                $type->setAbatementAge(FHIRAge::jsonUnserialize($decoded->abatementAge, $config));
            }
        }
        if (isset($decoded->abatementPeriod) || property_exists($decoded, self::FIELD_ABATEMENT_PERIOD)) {
            if (is_array($decoded->abatementPeriod)) {
                $type->setAbatementPeriod(FHIRPeriod::jsonUnserialize(reset($decoded->abatementPeriod), $config));
            } else {
                $type->setAbatementPeriod(FHIRPeriod::jsonUnserialize($decoded->abatementPeriod, $config));
            }
        }
        if (isset($decoded->abatementRange) || property_exists($decoded, self::FIELD_ABATEMENT_RANGE)) {
            if (is_array($decoded->abatementRange)) {
                $type->setAbatementRange(FHIRRange::jsonUnserialize(reset($decoded->abatementRange), $config));
            } else {
                $type->setAbatementRange(FHIRRange::jsonUnserialize($decoded->abatementRange, $config));
            }
        }
        if (isset($decoded->abatementString)
            || isset($decoded->_abatementString)
            || property_exists($decoded, self::FIELD_ABATEMENT_STRING)
            || property_exists($decoded, self::FIELD_ABATEMENT_STRING_EXT)) {
            $v = $decoded->_abatementString ?? new \stdClass();
            $v->value = $decoded->abatementString ?? null;
            $type->setAbatementString(FHIRString::jsonUnserialize($v, $config));
        }
        if (isset($decoded->recordedDate)
            || isset($decoded->_recordedDate)
            || property_exists($decoded, self::FIELD_RECORDED_DATE)
            || property_exists($decoded, self::FIELD_RECORDED_DATE_EXT)) {
            $v = $decoded->_recordedDate ?? new \stdClass();
            $v->value = $decoded->recordedDate ?? null;
            $type->setRecordedDate(FHIRDateTime::jsonUnserialize($v, $config));
        }
        if (isset($decoded->recorder) || property_exists($decoded, self::FIELD_RECORDER)) {
            if (is_array($decoded->recorder)) {
                $type->setRecorder(FHIRReference::jsonUnserialize(reset($decoded->recorder), $config));
            } else {
                $type->setRecorder(FHIRReference::jsonUnserialize($decoded->recorder, $config));
            }
        }
        if (isset($decoded->asserter) || property_exists($decoded, self::FIELD_ASSERTER)) {
            if (is_array($decoded->asserter)) {
                $type->setAsserter(FHIRReference::jsonUnserialize(reset($decoded->asserter), $config));
            } else {
                $type->setAsserter(FHIRReference::jsonUnserialize($decoded->asserter, $config));
            }
        }
        if (isset($decoded->stage) || property_exists($decoded, self::FIELD_STAGE)) {
            if (is_object($decoded->stage)) {
                $vals = [$decoded->stage];
                $type->_setJSONFieldElideSingletonArray(self::FIELD_STAGE, true);
            } else {
                $vals = $decoded->stage;
            }
            foreach($vals as $v) {
                $type->addStage(FHIRConditionStage::jsonUnserialize($v, $config));
            }
        }
        if (isset($decoded->evidence) || property_exists($decoded, self::FIELD_EVIDENCE)) {
            if (is_object($decoded->evidence)) {
                $vals = [$decoded->evidence];
                $type->_setJSONFieldElideSingletonArray(self::FIELD_EVIDENCE, true);
            } else {
                $vals = $decoded->evidence;
            }
            foreach($vals as $v) {
                $type->addEvidence(FHIRConditionEvidence::jsonUnserialize($v, $config));
            }
        }
        if (isset($decoded->note) || property_exists($decoded, self::FIELD_NOTE)) {
            if (is_object($decoded->note)) {
                $vals = [$decoded->note];
                $type->_setJSONFieldElideSingletonArray(self::FIELD_NOTE, true);
            } else {
                $vals = $decoded->note;
            }
            foreach($vals as $v) {
                $type->addNote(FHIRAnnotation::jsonUnserialize($v, $config));
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
        if (isset($this->identifier) && [] !== $this->identifier) {
            if ($this->_getJSONFieldElideSingletonArray(self::FIELD_IDENTIFIER) && 1 === count($this->identifier)) {
                $out->identifier = $this->identifier[0];
            } else {
                $out->identifier = $this->identifier;
            }
        }
        if (isset($this->clinicalStatus)) {
            $out->clinicalStatus = $this->clinicalStatus;
        }
        if (isset($this->verificationStatus)) {
            $out->verificationStatus = $this->verificationStatus;
        }
        if (isset($this->category) && [] !== $this->category) {
            if ($this->_getJSONFieldElideSingletonArray(self::FIELD_CATEGORY) && 1 === count($this->category)) {
                $out->category = $this->category[0];
            } else {
                $out->category = $this->category;
            }
        }
        if (isset($this->severity)) {
            $out->severity = $this->severity;
        }
        if (isset($this->code)) {
            $out->code = $this->code;
        }
        if (isset($this->bodySite) && [] !== $this->bodySite) {
            if ($this->_getJSONFieldElideSingletonArray(self::FIELD_BODY_SITE) && 1 === count($this->bodySite)) {
                $out->bodySite = $this->bodySite[0];
            } else {
                $out->bodySite = $this->bodySite;
            }
        }
        if (isset($this->subject)) {
            $out->subject = $this->subject;
        }
        if (isset($this->encounter)) {
            $out->encounter = $this->encounter;
        }
        if (isset($this->onsetDateTime)) {
            if (null !== ($val = $this->onsetDateTime->getValue())) {
                $out->onsetDateTime = $val;
            }
            if ($this->onsetDateTime->_nonValueFieldDefined()) {
                $ext = $this->onsetDateTime->jsonSerialize();
                unset($ext->value);
                $out->_onsetDateTime = $ext;
            }
        }
        if (isset($this->onsetAge)) {
            $out->onsetAge = $this->onsetAge;
        }
        if (isset($this->onsetPeriod)) {
            $out->onsetPeriod = $this->onsetPeriod;
        }
        if (isset($this->onsetRange)) {
            $out->onsetRange = $this->onsetRange;
        }
        if (isset($this->onsetString)) {
            if (null !== ($val = $this->onsetString->getValue())) {
                $out->onsetString = $val;
            }
            if ($this->onsetString->_nonValueFieldDefined()) {
                $ext = $this->onsetString->jsonSerialize();
                unset($ext->value);
                $out->_onsetString = $ext;
            }
        }
        if (isset($this->abatementDateTime)) {
            if (null !== ($val = $this->abatementDateTime->getValue())) {
                $out->abatementDateTime = $val;
            }
            if ($this->abatementDateTime->_nonValueFieldDefined()) {
                $ext = $this->abatementDateTime->jsonSerialize();
                unset($ext->value);
                $out->_abatementDateTime = $ext;
            }
        }
        if (isset($this->abatementAge)) {
            $out->abatementAge = $this->abatementAge;
        }
        if (isset($this->abatementPeriod)) {
            $out->abatementPeriod = $this->abatementPeriod;
        }
        if (isset($this->abatementRange)) {
            $out->abatementRange = $this->abatementRange;
        }
        if (isset($this->abatementString)) {
            if (null !== ($val = $this->abatementString->getValue())) {
                $out->abatementString = $val;
            }
            if ($this->abatementString->_nonValueFieldDefined()) {
                $ext = $this->abatementString->jsonSerialize();
                unset($ext->value);
                $out->_abatementString = $ext;
            }
        }
        if (isset($this->recordedDate)) {
            if (null !== ($val = $this->recordedDate->getValue())) {
                $out->recordedDate = $val;
            }
            if ($this->recordedDate->_nonValueFieldDefined()) {
                $ext = $this->recordedDate->jsonSerialize();
                unset($ext->value);
                $out->_recordedDate = $ext;
            }
        }
        if (isset($this->recorder)) {
            $out->recorder = $this->recorder;
        }
        if (isset($this->asserter)) {
            $out->asserter = $this->asserter;
        }
        if (isset($this->stage) && [] !== $this->stage) {
            if ($this->_getJSONFieldElideSingletonArray(self::FIELD_STAGE) && 1 === count($this->stage)) {
                $out->stage = $this->stage[0];
            } else {
                $out->stage = $this->stage;
            }
        }
        if (isset($this->evidence) && [] !== $this->evidence) {
            if ($this->_getJSONFieldElideSingletonArray(self::FIELD_EVIDENCE) && 1 === count($this->evidence)) {
                $out->evidence = $this->evidence[0];
            } else {
                $out->evidence = $this->evidence;
            }
        }
        if (isset($this->note) && [] !== $this->note) {
            if ($this->_getJSONFieldElideSingletonArray(self::FIELD_NOTE) && 1 === count($this->note)) {
                $out->note = $this->note[0];
            } else {
                $out->note = $this->note;
            }
        }
        $out->resourceType = $this->_getResourceType();
        return $out;
    }
}
