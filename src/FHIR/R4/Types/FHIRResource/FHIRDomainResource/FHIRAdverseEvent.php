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
use OpenEMR\FHIR\Versions\R4\Types\FHIRCodePrimitive\FHIRAdverseEventActualityList;
use OpenEMR\FHIR\Versions\R4\Types\FHIRDateTimePrimitive;
use OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRAdverseEventActuality;
use OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRAdverseEvent\FHIRAdverseEventSuspectEntity;
use OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRCode;
use OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRCodeableConcept;
use OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRDateTime;
use OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRExtension;
use OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRId;
use OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRIdentifier;
use OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRMeta;
use OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRNarrative;
use OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRReference;
use OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRUri;
use OpenEMR\FHIR\Versions\R4\Types\FHIRIdPrimitive;
use OpenEMR\FHIR\Versions\R4\Types\FHIRResource\FHIRDomainResource;
use OpenEMR\FHIR\Versions\R4\Types\FHIRUriPrimitive;
use OpenEMR\FHIR\Versions\R4\Version;
use OpenEMR\FHIR\Versions\R4\VersionConstants;
use OpenEMR\FHIR\Versions\R4\VersionContainedTypeInterface;
use OpenEMR\FHIR\Versions\R4\VersionTypeMap;

/**
 * Actual or potential/avoided event causing unintended physical injury resulting
 * from or contributed to by medical care, a research study or other healthcare
 * setting factors that requires additional monitoring, treatment, or
 * hospitalization, or that results in death.
 * If the element is present, it must have either a \@value, an \@id, or extensions
 */
#[\Attribute(\Attribute::TARGET_PROPERTY)]
class FHIRAdverseEvent extends FHIRDomainResource implements VersionContainedTypeInterface
{
    use TypeValidationsTrait,
        JSONSerializationOptionsTrait,
        XMLSerializationOptionsTrait;

    // name of FHIR type this class describes
    public const FHIR_TYPE_NAME = VersionConstants::TYPE_NAME_ADVERSE_EVENT;

    /* class_default.php:56 */
    public const FIELD_IDENTIFIER = 'identifier';
    public const FIELD_ACTUALITY = 'actuality';
    public const FIELD_ACTUALITY_EXT = '_actuality';
    public const FIELD_CATEGORY = 'category';
    public const FIELD_EVENT = 'event';
    public const FIELD_SUBJECT = 'subject';
    public const FIELD_ENCOUNTER = 'encounter';
    public const FIELD_DATE = 'date';
    public const FIELD_DATE_EXT = '_date';
    public const FIELD_DETECTED = 'detected';
    public const FIELD_DETECTED_EXT = '_detected';
    public const FIELD_RECORDED_DATE = 'recordedDate';
    public const FIELD_RECORDED_DATE_EXT = '_recordedDate';
    public const FIELD_RESULTING_CONDITION = 'resultingCondition';
    public const FIELD_LOCATION = 'location';
    public const FIELD_SERIOUSNESS = 'seriousness';
    public const FIELD_SEVERITY = 'severity';
    public const FIELD_OUTCOME = 'outcome';
    public const FIELD_RECORDER = 'recorder';
    public const FIELD_CONTRIBUTOR = 'contributor';
    public const FIELD_SUSPECT_ENTITY = 'suspectEntity';
    public const FIELD_SUBJECT_MEDICAL_HISTORY = 'subjectMedicalHistory';
    public const FIELD_REFERENCE_DOCUMENT = 'referenceDocument';
    public const FIELD_STUDY = 'study';

    /* class_default.php:75 */
    private const _FHIR_VALIDATION_RULES = [
        self::FIELD_ACTUALITY => [
            MinOccursRule::NAME => 1,
        ],
        self::FIELD_SUBJECT => [
            MinOccursRule::NAME => 1,
        ],
    ];

    /* class_default.php:96 */
    private array $_valueXMLLocations = [
        self::FIELD_ACTUALITY => ValueXMLLocationEnum::CONTAINER_ATTRIBUTE,
        self::FIELD_DATE => ValueXMLLocationEnum::CONTAINER_ATTRIBUTE,
        self::FIELD_DETECTED => ValueXMLLocationEnum::CONTAINER_ATTRIBUTE,
        self::FIELD_RECORDED_DATE => ValueXMLLocationEnum::CONTAINER_ATTRIBUTE,
    ];

    /* class_default.php:112 */
    /**
     * An identifier - identifies some entity uniquely and unambiguously. Typically
     * this is used for business identifiers.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * Business identifiers assigned to this adverse event by the performer or other
     * systems which remain constant as the resource is updated and propagates from
     * server to server.
     *
     * @var \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRIdentifier
     */
    #[FHIRIdentifier]
    protected FHIRIdentifier $identifier;
    /**
     * Overall nature of the adverse event, e.g. real or potential.
     * If the element is present, it must have either a \@value, an \@id, or extensions
     *
     * Whether the event actually happened, or just had the potential to. Note that
     * this is independent of whether anyone was affected or harmed or how severely.
     *
     * @var \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRAdverseEventActuality
     */
    #[FHIRAdverseEventActuality]
    protected FHIRAdverseEventActuality $actuality;
    /**
     * A concept that may be defined by a formal reference to a terminology or ontology
     * or may be provided by text.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * The overall type of event, intended for search and filtering purposes.
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
     * This element defines the specific type of event that occurred or that was
     * prevented from occurring.
     *
     * @var \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRCodeableConcept
     */
    #[FHIRCodeableConcept]
    protected FHIRCodeableConcept $event;
    /**
     * A reference from one resource to another.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * This subject or group impacted by the event.
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
     * The Encounter during which AdverseEvent was created or to which the creation of
     * this record is tightly associated.
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
     * The date (and perhaps time) when the adverse event occurred.
     *
     * @var \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRDateTime
     */
    #[FHIRDateTime]
    protected FHIRDateTime $date;
    /**
     * A date, date-time or partial date (e.g. just year or year + month). If hours and
     * minutes are specified, a time zone SHALL be populated. The format is a union of
     * the schema types gYear, gYearMonth, date and dateTime. Seconds must be provided
     * due to schema type constraints but may be zero-filled and may be ignored. Dates
     * SHALL be valid dates.
     * If the element is present, it must have either a \@value, an \@id, or extensions
     *
     * Estimated or actual date the AdverseEvent began, in the opinion of the reporter.
     *
     * @var \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRDateTime
     */
    #[FHIRDateTime]
    protected FHIRDateTime $detected;
    /**
     * A date, date-time or partial date (e.g. just year or year + month). If hours and
     * minutes are specified, a time zone SHALL be populated. The format is a union of
     * the schema types gYear, gYearMonth, date and dateTime. Seconds must be provided
     * due to schema type constraints but may be zero-filled and may be ignored. Dates
     * SHALL be valid dates.
     * If the element is present, it must have either a \@value, an \@id, or extensions
     *
     * The date on which the existence of the AdverseEvent was first recorded.
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
     * Includes information about the reaction that occurred as a result of exposure to
     * a substance (for example, a drug or a chemical).
     *
     * @var iterable<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRReference>
     */
    #[FHIRReference]
    protected array $resultingCondition;
    /**
     * A reference from one resource to another.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * The information about where the adverse event occurred.
     *
     * @var \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRReference
     */
    #[FHIRReference]
    protected FHIRReference $location;
    /**
     * A concept that may be defined by a formal reference to a terminology or ontology
     * or may be provided by text.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * Assessment whether this event was of real importance.
     *
     * @var \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRCodeableConcept
     */
    #[FHIRCodeableConcept]
    protected FHIRCodeableConcept $seriousness;
    /**
     * A concept that may be defined by a formal reference to a terminology or ontology
     * or may be provided by text.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * Describes the severity of the adverse event, in relation to the subject.
     * Contrast to AdverseEvent.seriousness - a severe rash might not be serious, but a
     * mild heart problem is.
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
     * Describes the type of outcome from the adverse event.
     *
     * @var \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRCodeableConcept
     */
    #[FHIRCodeableConcept]
    protected FHIRCodeableConcept $outcome;
    /**
     * A reference from one resource to another.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * Information on who recorded the adverse event. May be the patient or a
     * practitioner.
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
     * Parties that may or should contribute or have contributed information to the
     * adverse event, which can consist of one or more activities. Such information
     * includes information leading to the decision to perform the activity and how to
     * perform the activity (e.g. consultant), information that the activity itself
     * seeks to reveal (e.g. informant of clinical history), or information about what
     * activity was performed (e.g. informant witness).
     *
     * @var iterable<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRReference>
     */
    #[FHIRReference]
    protected array $contributor;
    /**
     * Actual or potential/avoided event causing unintended physical injury resulting
     * from or contributed to by medical care, a research study or other healthcare
     * setting factors that requires additional monitoring, treatment, or
     * hospitalization, or that results in death.
     *
     * Describes the entity that is suspected to have caused the adverse event.
     *
     * @var iterable<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRAdverseEvent\FHIRAdverseEventSuspectEntity>
     */
    #[FHIRAdverseEventSuspectEntity]
    protected array $suspectEntity;
    /**
     * A reference from one resource to another.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * AdverseEvent.subjectMedicalHistory.
     *
     * @var iterable<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRReference>
     */
    #[FHIRReference]
    protected array $subjectMedicalHistory;
    /**
     * A reference from one resource to another.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * AdverseEvent.referenceDocument.
     *
     * @var iterable<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRReference>
     */
    #[FHIRReference]
    protected array $referenceDocument;
    /**
     * A reference from one resource to another.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * AdverseEvent.study.
     *
     * @var iterable<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRReference>
     */
    #[FHIRReference]
    protected array $study;

    /* constructor.php:61 */
    /**
     * FHIRAdverseEvent Constructor
     * @param null|string|\OpenEMR\FHIR\Versions\R4\Types\FHIRIdPrimitive|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRId $id
     * @param null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRMeta $meta
     * @param null|string|\OpenEMR\FHIR\Versions\R4\Types\FHIRUriPrimitive|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRUri $implicitRules
     * @param null|string|\OpenEMR\FHIR\Versions\R4\Types\FHIRCodePrimitive|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRCode $language
     * @param null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRNarrative $text
     * @param null|iterable<\OpenEMR\FHIR\Versions\R4\Types\FHIRResourceContainer>|iterable<\OpenEMR\FHIR\Versions\R4\VersionContainedTypeInterface> $contained
     * @param null|iterable<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRExtension> $extension
     * @param null|iterable<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRExtension> $modifierExtension
     * @param null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRIdentifier $identifier
     * @param null|string|\OpenEMR\FHIR\Versions\R4\Types\FHIRCodePrimitive\FHIRAdverseEventActualityList|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRAdverseEventActuality $actuality
     * @param null|iterable<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRCodeableConcept> $category
     * @param null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRCodeableConcept $event
     * @param null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRReference $subject
     * @param null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRReference $encounter
     * @param null|string|\DateTimeInterface|\OpenEMR\FHIR\Versions\R4\Types\FHIRDateTimePrimitive|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRDateTime $date
     * @param null|string|\DateTimeInterface|\OpenEMR\FHIR\Versions\R4\Types\FHIRDateTimePrimitive|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRDateTime $detected
     * @param null|string|\DateTimeInterface|\OpenEMR\FHIR\Versions\R4\Types\FHIRDateTimePrimitive|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRDateTime $recordedDate
     * @param null|iterable<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRReference> $resultingCondition
     * @param null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRReference $location
     * @param null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRCodeableConcept $seriousness
     * @param null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRCodeableConcept $severity
     * @param null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRCodeableConcept $outcome
     * @param null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRReference $recorder
     * @param null|iterable<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRReference> $contributor
     * @param null|iterable<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRAdverseEvent\FHIRAdverseEventSuspectEntity> $suspectEntity
     * @param null|iterable<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRReference> $subjectMedicalHistory
     * @param null|iterable<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRReference> $referenceDocument
     * @param null|iterable<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRReference> $study
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
                                null|FHIRIdentifier $identifier = null,
                                null|string|FHIRAdverseEventActualityList|FHIRAdverseEventActuality $actuality = null,
                                null|iterable $category = null,
                                null|FHIRCodeableConcept $event = null,
                                null|FHIRReference $subject = null,
                                null|FHIRReference $encounter = null,
                                null|string|\DateTimeInterface|FHIRDateTimePrimitive|FHIRDateTime $date = null,
                                null|string|\DateTimeInterface|FHIRDateTimePrimitive|FHIRDateTime $detected = null,
                                null|string|\DateTimeInterface|FHIRDateTimePrimitive|FHIRDateTime $recordedDate = null,
                                null|iterable $resultingCondition = null,
                                null|FHIRReference $location = null,
                                null|FHIRCodeableConcept $seriousness = null,
                                null|FHIRCodeableConcept $severity = null,
                                null|FHIRCodeableConcept $outcome = null,
                                null|FHIRReference $recorder = null,
                                null|iterable $contributor = null,
                                null|iterable $suspectEntity = null,
                                null|iterable $subjectMedicalHistory = null,
                                null|iterable $referenceDocument = null,
                                null|iterable $study = null,
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
            $this->setIdentifier($identifier);
        }
        if (null !== $actuality) {
            $this->setActuality($actuality);
        }
        if (null !== $category) {
            $this->setCategory(...$category);
        }
        if (null !== $event) {
            $this->setEvent($event);
        }
        if (null !== $subject) {
            $this->setSubject($subject);
        }
        if (null !== $encounter) {
            $this->setEncounter($encounter);
        }
        if (null !== $date) {
            $this->setDate($date);
        }
        if (null !== $detected) {
            $this->setDetected($detected);
        }
        if (null !== $recordedDate) {
            $this->setRecordedDate($recordedDate);
        }
        if (null !== $resultingCondition) {
            $this->setResultingCondition(...$resultingCondition);
        }
        if (null !== $location) {
            $this->setLocation($location);
        }
        if (null !== $seriousness) {
            $this->setSeriousness($seriousness);
        }
        if (null !== $severity) {
            $this->setSeverity($severity);
        }
        if (null !== $outcome) {
            $this->setOutcome($outcome);
        }
        if (null !== $recorder) {
            $this->setRecorder($recorder);
        }
        if (null !== $contributor) {
            $this->setContributor(...$contributor);
        }
        if (null !== $suspectEntity) {
            $this->setSuspectEntity(...$suspectEntity);
        }
        if (null !== $subjectMedicalHistory) {
            $this->setSubjectMedicalHistory(...$subjectMedicalHistory);
        }
        if (null !== $referenceDocument) {
            $this->setReferenceDocument(...$referenceDocument);
        }
        if (null !== $study) {
            $this->setStudy(...$study);
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
     * Business identifiers assigned to this adverse event by the performer or other
     * systems which remain constant as the resource is updated and propagates from
     * server to server.
     *
     * @return null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRIdentifier
     */
    public function getIdentifier(): null|FHIRIdentifier
    {
        return $this->identifier ?? null;
    }

    /**
     * An identifier - identifies some entity uniquely and unambiguously. Typically
     * this is used for business identifiers.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * Business identifiers assigned to this adverse event by the performer or other
     * systems which remain constant as the resource is updated and propagates from
     * server to server.
     *
     * @param null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRIdentifier $identifier
     * @return static
     */
    public function setIdentifier(null|FHIRIdentifier $identifier): self
    {
        if (null === $identifier) {
            unset($this->identifier);
            return $this;
        }
        $this->identifier = $identifier;
        return $this;
    }

    /**
     * Overall nature of the adverse event, e.g. real or potential.
     * If the element is present, it must have either a \@value, an \@id, or extensions
     *
     * Whether the event actually happened, or just had the potential to. Note that
     * this is independent of whether anyone was affected or harmed or how severely.
     *
     * @return null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRAdverseEventActuality
     */
    public function getActuality(): null|FHIRAdverseEventActuality
    {
        return $this->actuality ?? null;
    }

    /**
     * Overall nature of the adverse event, e.g. real or potential.
     * If the element is present, it must have either a \@value, an \@id, or extensions
     *
     * Whether the event actually happened, or just had the potential to. Note that
     * this is independent of whether anyone was affected or harmed or how severely.
     *
     * @param null|string|\OpenEMR\FHIR\Versions\R4\Types\FHIRCodePrimitive\FHIRAdverseEventActualityList|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRAdverseEventActuality $actuality
     * @return static
     */
    public function setActuality(null|string|FHIRAdverseEventActualityList|FHIRAdverseEventActuality $actuality): self
    {
        if (null === $actuality) {
            unset($this->actuality);
            return $this;
        }
        if (!($actuality instanceof FHIRAdverseEventActuality)) {
            $actuality = new FHIRAdverseEventActuality(value: $actuality);
        }
        $this->actuality = $actuality;
        return $this;
    }

    /**
     * A concept that may be defined by a formal reference to a terminology or ontology
     * or may be provided by text.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * The overall type of event, intended for search and filtering purposes.
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
     * The overall type of event, intended for search and filtering purposes.
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
     * The overall type of event, intended for search and filtering purposes.
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
     * This element defines the specific type of event that occurred or that was
     * prevented from occurring.
     *
     * @return null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRCodeableConcept
     */
    public function getEvent(): null|FHIRCodeableConcept
    {
        return $this->event ?? null;
    }

    /**
     * A concept that may be defined by a formal reference to a terminology or ontology
     * or may be provided by text.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * This element defines the specific type of event that occurred or that was
     * prevented from occurring.
     *
     * @param null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRCodeableConcept $event
     * @return static
     */
    public function setEvent(null|FHIRCodeableConcept $event): self
    {
        if (null === $event) {
            unset($this->event);
            return $this;
        }
        $this->event = $event;
        return $this;
    }

    /**
     * A reference from one resource to another.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * This subject or group impacted by the event.
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
     * This subject or group impacted by the event.
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
     * The Encounter during which AdverseEvent was created or to which the creation of
     * this record is tightly associated.
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
     * The Encounter during which AdverseEvent was created or to which the creation of
     * this record is tightly associated.
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
     * The date (and perhaps time) when the adverse event occurred.
     *
     * @return null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRDateTime
     */
    public function getDate(): null|FHIRDateTime
    {
        return $this->date ?? null;
    }

    /**
     * A date, date-time or partial date (e.g. just year or year + month). If hours and
     * minutes are specified, a time zone SHALL be populated. The format is a union of
     * the schema types gYear, gYearMonth, date and dateTime. Seconds must be provided
     * due to schema type constraints but may be zero-filled and may be ignored. Dates
     * SHALL be valid dates.
     * If the element is present, it must have either a \@value, an \@id, or extensions
     *
     * The date (and perhaps time) when the adverse event occurred.
     *
     * @param null|string|\DateTimeInterface|\OpenEMR\FHIR\Versions\R4\Types\FHIRDateTimePrimitive|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRDateTime $date
     * @return static
     */
    public function setDate(null|string|\DateTimeInterface|FHIRDateTimePrimitive|FHIRDateTime $date): self
    {
        if (null === $date) {
            unset($this->date);
            return $this;
        }
        if (!($date instanceof FHIRDateTime)) {
            $date = new FHIRDateTime(value: $date);
        }
        $this->date = $date;
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
     * Estimated or actual date the AdverseEvent began, in the opinion of the reporter.
     *
     * @return null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRDateTime
     */
    public function getDetected(): null|FHIRDateTime
    {
        return $this->detected ?? null;
    }

    /**
     * A date, date-time or partial date (e.g. just year or year + month). If hours and
     * minutes are specified, a time zone SHALL be populated. The format is a union of
     * the schema types gYear, gYearMonth, date and dateTime. Seconds must be provided
     * due to schema type constraints but may be zero-filled and may be ignored. Dates
     * SHALL be valid dates.
     * If the element is present, it must have either a \@value, an \@id, or extensions
     *
     * Estimated or actual date the AdverseEvent began, in the opinion of the reporter.
     *
     * @param null|string|\DateTimeInterface|\OpenEMR\FHIR\Versions\R4\Types\FHIRDateTimePrimitive|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRDateTime $detected
     * @return static
     */
    public function setDetected(null|string|\DateTimeInterface|FHIRDateTimePrimitive|FHIRDateTime $detected): self
    {
        if (null === $detected) {
            unset($this->detected);
            return $this;
        }
        if (!($detected instanceof FHIRDateTime)) {
            $detected = new FHIRDateTime(value: $detected);
        }
        $this->detected = $detected;
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
     * The date on which the existence of the AdverseEvent was first recorded.
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
     * The date on which the existence of the AdverseEvent was first recorded.
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
     * Includes information about the reaction that occurred as a result of exposure to
     * a substance (for example, a drug or a chemical).
     *
     * @return iterable<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRReference>
     */
    public function getResultingCondition(): array
    {
        return $this->resultingCondition ?? [];
    }

    /**
     * @return \ArrayIterator<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRReference>
     */
    public function getResultingConditionIterator(): iterable
    {
        if (!isset($this->resultingCondition)) {
            return new \EmptyIterator();
        }
        return new \ArrayIterator($this->resultingCondition);
    }

    /**
     * A reference from one resource to another.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * Includes information about the reaction that occurred as a result of exposure to
     * a substance (for example, a drug or a chemical).
     *
     * @param \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRReference $resultingCondition
     * @return static
     */
    public function addResultingCondition(FHIRReference $resultingCondition): self
    {
        if (!isset($this->resultingCondition)) {
            $this->resultingCondition = [];
        }
        $this->resultingCondition[] = $resultingCondition;
        return $this;
    }

    /**
     * A reference from one resource to another.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * Includes information about the reaction that occurred as a result of exposure to
     * a substance (for example, a drug or a chemical).
     *
     * @param \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRReference ...$resultingCondition
     * @return static
     */
    public function setResultingCondition(FHIRReference ...$resultingCondition): self
    {
        if ([] === $resultingCondition) {
            unset($this->resultingCondition);
            return $this;
        }
        $this->resultingCondition = $resultingCondition;
        return $this;
    }

    /**
     * A reference from one resource to another.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * The information about where the adverse event occurred.
     *
     * @return null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRReference
     */
    public function getLocation(): null|FHIRReference
    {
        return $this->location ?? null;
    }

    /**
     * A reference from one resource to another.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * The information about where the adverse event occurred.
     *
     * @param null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRReference $location
     * @return static
     */
    public function setLocation(null|FHIRReference $location): self
    {
        if (null === $location) {
            unset($this->location);
            return $this;
        }
        $this->location = $location;
        return $this;
    }

    /**
     * A concept that may be defined by a formal reference to a terminology or ontology
     * or may be provided by text.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * Assessment whether this event was of real importance.
     *
     * @return null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRCodeableConcept
     */
    public function getSeriousness(): null|FHIRCodeableConcept
    {
        return $this->seriousness ?? null;
    }

    /**
     * A concept that may be defined by a formal reference to a terminology or ontology
     * or may be provided by text.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * Assessment whether this event was of real importance.
     *
     * @param null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRCodeableConcept $seriousness
     * @return static
     */
    public function setSeriousness(null|FHIRCodeableConcept $seriousness): self
    {
        if (null === $seriousness) {
            unset($this->seriousness);
            return $this;
        }
        $this->seriousness = $seriousness;
        return $this;
    }

    /**
     * A concept that may be defined by a formal reference to a terminology or ontology
     * or may be provided by text.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * Describes the severity of the adverse event, in relation to the subject.
     * Contrast to AdverseEvent.seriousness - a severe rash might not be serious, but a
     * mild heart problem is.
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
     * Describes the severity of the adverse event, in relation to the subject.
     * Contrast to AdverseEvent.seriousness - a severe rash might not be serious, but a
     * mild heart problem is.
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
     * Describes the type of outcome from the adverse event.
     *
     * @return null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRCodeableConcept
     */
    public function getOutcome(): null|FHIRCodeableConcept
    {
        return $this->outcome ?? null;
    }

    /**
     * A concept that may be defined by a formal reference to a terminology or ontology
     * or may be provided by text.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * Describes the type of outcome from the adverse event.
     *
     * @param null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRCodeableConcept $outcome
     * @return static
     */
    public function setOutcome(null|FHIRCodeableConcept $outcome): self
    {
        if (null === $outcome) {
            unset($this->outcome);
            return $this;
        }
        $this->outcome = $outcome;
        return $this;
    }

    /**
     * A reference from one resource to another.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * Information on who recorded the adverse event. May be the patient or a
     * practitioner.
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
     * Information on who recorded the adverse event. May be the patient or a
     * practitioner.
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
     * Parties that may or should contribute or have contributed information to the
     * adverse event, which can consist of one or more activities. Such information
     * includes information leading to the decision to perform the activity and how to
     * perform the activity (e.g. consultant), information that the activity itself
     * seeks to reveal (e.g. informant of clinical history), or information about what
     * activity was performed (e.g. informant witness).
     *
     * @return iterable<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRReference>
     */
    public function getContributor(): array
    {
        return $this->contributor ?? [];
    }

    /**
     * @return \ArrayIterator<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRReference>
     */
    public function getContributorIterator(): iterable
    {
        if (!isset($this->contributor)) {
            return new \EmptyIterator();
        }
        return new \ArrayIterator($this->contributor);
    }

    /**
     * A reference from one resource to another.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * Parties that may or should contribute or have contributed information to the
     * adverse event, which can consist of one or more activities. Such information
     * includes information leading to the decision to perform the activity and how to
     * perform the activity (e.g. consultant), information that the activity itself
     * seeks to reveal (e.g. informant of clinical history), or information about what
     * activity was performed (e.g. informant witness).
     *
     * @param \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRReference $contributor
     * @return static
     */
    public function addContributor(FHIRReference $contributor): self
    {
        if (!isset($this->contributor)) {
            $this->contributor = [];
        }
        $this->contributor[] = $contributor;
        return $this;
    }

    /**
     * A reference from one resource to another.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * Parties that may or should contribute or have contributed information to the
     * adverse event, which can consist of one or more activities. Such information
     * includes information leading to the decision to perform the activity and how to
     * perform the activity (e.g. consultant), information that the activity itself
     * seeks to reveal (e.g. informant of clinical history), or information about what
     * activity was performed (e.g. informant witness).
     *
     * @param \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRReference ...$contributor
     * @return static
     */
    public function setContributor(FHIRReference ...$contributor): self
    {
        if ([] === $contributor) {
            unset($this->contributor);
            return $this;
        }
        $this->contributor = $contributor;
        return $this;
    }

    /**
     * Actual or potential/avoided event causing unintended physical injury resulting
     * from or contributed to by medical care, a research study or other healthcare
     * setting factors that requires additional monitoring, treatment, or
     * hospitalization, or that results in death.
     *
     * Describes the entity that is suspected to have caused the adverse event.
     *
     * @return iterable<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRAdverseEvent\FHIRAdverseEventSuspectEntity>
     */
    public function getSuspectEntity(): array
    {
        return $this->suspectEntity ?? [];
    }

    /**
     * @return \ArrayIterator<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRAdverseEvent\FHIRAdverseEventSuspectEntity>
     */
    public function getSuspectEntityIterator(): iterable
    {
        if (!isset($this->suspectEntity)) {
            return new \EmptyIterator();
        }
        return new \ArrayIterator($this->suspectEntity);
    }

    /**
     * Actual or potential/avoided event causing unintended physical injury resulting
     * from or contributed to by medical care, a research study or other healthcare
     * setting factors that requires additional monitoring, treatment, or
     * hospitalization, or that results in death.
     *
     * Describes the entity that is suspected to have caused the adverse event.
     *
     * @param \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRAdverseEvent\FHIRAdverseEventSuspectEntity $suspectEntity
     * @return static
     */
    public function addSuspectEntity(FHIRAdverseEventSuspectEntity $suspectEntity): self
    {
        if (!isset($this->suspectEntity)) {
            $this->suspectEntity = [];
        }
        $this->suspectEntity[] = $suspectEntity;
        return $this;
    }

    /**
     * Actual or potential/avoided event causing unintended physical injury resulting
     * from or contributed to by medical care, a research study or other healthcare
     * setting factors that requires additional monitoring, treatment, or
     * hospitalization, or that results in death.
     *
     * Describes the entity that is suspected to have caused the adverse event.
     *
     * @param \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRAdverseEvent\FHIRAdverseEventSuspectEntity ...$suspectEntity
     * @return static
     */
    public function setSuspectEntity(FHIRAdverseEventSuspectEntity ...$suspectEntity): self
    {
        if ([] === $suspectEntity) {
            unset($this->suspectEntity);
            return $this;
        }
        $this->suspectEntity = $suspectEntity;
        return $this;
    }

    /**
     * A reference from one resource to another.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * AdverseEvent.subjectMedicalHistory.
     *
     * @return iterable<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRReference>
     */
    public function getSubjectMedicalHistory(): array
    {
        return $this->subjectMedicalHistory ?? [];
    }

    /**
     * @return \ArrayIterator<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRReference>
     */
    public function getSubjectMedicalHistoryIterator(): iterable
    {
        if (!isset($this->subjectMedicalHistory)) {
            return new \EmptyIterator();
        }
        return new \ArrayIterator($this->subjectMedicalHistory);
    }

    /**
     * A reference from one resource to another.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * AdverseEvent.subjectMedicalHistory.
     *
     * @param \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRReference $subjectMedicalHistory
     * @return static
     */
    public function addSubjectMedicalHistory(FHIRReference $subjectMedicalHistory): self
    {
        if (!isset($this->subjectMedicalHistory)) {
            $this->subjectMedicalHistory = [];
        }
        $this->subjectMedicalHistory[] = $subjectMedicalHistory;
        return $this;
    }

    /**
     * A reference from one resource to another.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * AdverseEvent.subjectMedicalHistory.
     *
     * @param \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRReference ...$subjectMedicalHistory
     * @return static
     */
    public function setSubjectMedicalHistory(FHIRReference ...$subjectMedicalHistory): self
    {
        if ([] === $subjectMedicalHistory) {
            unset($this->subjectMedicalHistory);
            return $this;
        }
        $this->subjectMedicalHistory = $subjectMedicalHistory;
        return $this;
    }

    /**
     * A reference from one resource to another.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * AdverseEvent.referenceDocument.
     *
     * @return iterable<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRReference>
     */
    public function getReferenceDocument(): array
    {
        return $this->referenceDocument ?? [];
    }

    /**
     * @return \ArrayIterator<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRReference>
     */
    public function getReferenceDocumentIterator(): iterable
    {
        if (!isset($this->referenceDocument)) {
            return new \EmptyIterator();
        }
        return new \ArrayIterator($this->referenceDocument);
    }

    /**
     * A reference from one resource to another.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * AdverseEvent.referenceDocument.
     *
     * @param \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRReference $referenceDocument
     * @return static
     */
    public function addReferenceDocument(FHIRReference $referenceDocument): self
    {
        if (!isset($this->referenceDocument)) {
            $this->referenceDocument = [];
        }
        $this->referenceDocument[] = $referenceDocument;
        return $this;
    }

    /**
     * A reference from one resource to another.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * AdverseEvent.referenceDocument.
     *
     * @param \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRReference ...$referenceDocument
     * @return static
     */
    public function setReferenceDocument(FHIRReference ...$referenceDocument): self
    {
        if ([] === $referenceDocument) {
            unset($this->referenceDocument);
            return $this;
        }
        $this->referenceDocument = $referenceDocument;
        return $this;
    }

    /**
     * A reference from one resource to another.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * AdverseEvent.study.
     *
     * @return iterable<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRReference>
     */
    public function getStudy(): array
    {
        return $this->study ?? [];
    }

    /**
     * @return \ArrayIterator<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRReference>
     */
    public function getStudyIterator(): iterable
    {
        if (!isset($this->study)) {
            return new \EmptyIterator();
        }
        return new \ArrayIterator($this->study);
    }

    /**
     * A reference from one resource to another.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * AdverseEvent.study.
     *
     * @param \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRReference $study
     * @return static
     */
    public function addStudy(FHIRReference $study): self
    {
        if (!isset($this->study)) {
            $this->study = [];
        }
        $this->study[] = $study;
        return $this;
    }

    /**
     * A reference from one resource to another.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * AdverseEvent.study.
     *
     * @param \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRReference ...$study
     * @return static
     */
    public function setStudy(FHIRReference ...$study): self
    {
        if ([] === $study) {
            unset($this->study);
            return $this;
        }
        $this->study = $study;
        return $this;
    }

    /* class_default.php:201 */
    /**
     * @param string|\SimpleXMLElement $element
     * @param null|\OpenEMR\FHIR\Encoding\UnserializeConfig $config
     * @param null|\OpenEMR\FHIR\Versions\R4\Types\FHIRResource\FHIRDomainResource\FHIRAdverseEvent $type
     * @return \OpenEMR\FHIR\Versions\R4\Types\FHIRResource\FHIRDomainResource\FHIRAdverseEvent
     * @throws \Exception
     */
    public static function xmlUnserialize(string|\SimpleXMLElement $element,
                                          null|UnserializeConfig $config = null,
                                          null|ResourceTypeInterface $type = null): self
    {
        if (null === $type) {
            $type = new static();
        } else if (!($type instanceof FHIRAdverseEvent)) {
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
                $type->setIdentifier(FHIRIdentifier::xmlUnserialize($ce, $config));
            } else if (self::FIELD_ACTUALITY === $cen) {
                $type->setActuality(FHIRAdverseEventActuality::xmlUnserialize($ce, $config));
            } else if (self::FIELD_CATEGORY === $cen) {
                $type->addCategory(FHIRCodeableConcept::xmlUnserialize($ce, $config));
            } else if (self::FIELD_EVENT === $cen) {
                $type->setEvent(FHIRCodeableConcept::xmlUnserialize($ce, $config));
            } else if (self::FIELD_SUBJECT === $cen) {
                $type->setSubject(FHIRReference::xmlUnserialize($ce, $config));
            } else if (self::FIELD_ENCOUNTER === $cen) {
                $type->setEncounter(FHIRReference::xmlUnserialize($ce, $config));
            } else if (self::FIELD_DATE === $cen) {
                $type->setDate(FHIRDateTime::xmlUnserialize($ce, $config));
            } else if (self::FIELD_DETECTED === $cen) {
                $type->setDetected(FHIRDateTime::xmlUnserialize($ce, $config));
            } else if (self::FIELD_RECORDED_DATE === $cen) {
                $type->setRecordedDate(FHIRDateTime::xmlUnserialize($ce, $config));
            } else if (self::FIELD_RESULTING_CONDITION === $cen) {
                $type->addResultingCondition(FHIRReference::xmlUnserialize($ce, $config));
            } else if (self::FIELD_LOCATION === $cen) {
                $type->setLocation(FHIRReference::xmlUnserialize($ce, $config));
            } else if (self::FIELD_SERIOUSNESS === $cen) {
                $type->setSeriousness(FHIRCodeableConcept::xmlUnserialize($ce, $config));
            } else if (self::FIELD_SEVERITY === $cen) {
                $type->setSeverity(FHIRCodeableConcept::xmlUnserialize($ce, $config));
            } else if (self::FIELD_OUTCOME === $cen) {
                $type->setOutcome(FHIRCodeableConcept::xmlUnserialize($ce, $config));
            } else if (self::FIELD_RECORDER === $cen) {
                $type->setRecorder(FHIRReference::xmlUnserialize($ce, $config));
            } else if (self::FIELD_CONTRIBUTOR === $cen) {
                $type->addContributor(FHIRReference::xmlUnserialize($ce, $config));
            } else if (self::FIELD_SUSPECT_ENTITY === $cen) {
                $type->addSuspectEntity(FHIRAdverseEventSuspectEntity::xmlUnserialize($ce, $config));
            } else if (self::FIELD_SUBJECT_MEDICAL_HISTORY === $cen) {
                $type->addSubjectMedicalHistory(FHIRReference::xmlUnserialize($ce, $config));
            } else if (self::FIELD_REFERENCE_DOCUMENT === $cen) {
                $type->addReferenceDocument(FHIRReference::xmlUnserialize($ce, $config));
            } else if (self::FIELD_STUDY === $cen) {
                $type->addStudy(FHIRReference::xmlUnserialize($ce, $config));
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
        if (isset($attributes[self::FIELD_ACTUALITY])) {
            if (isset($type->actuality)) {
                $type->actuality->setValue((string)$attributes[self::FIELD_ACTUALITY]);
            } else {
                $type->setActuality((string)$attributes[self::FIELD_ACTUALITY]);
            }
            $type->_setXMLFieldValueLocation(self::FIELD_ACTUALITY, ValueXMLLocationEnum::PARENT_ATTRIBUTE);
        }
        if (isset($attributes[self::FIELD_DATE])) {
            if (isset($type->date)) {
                $type->date->setValue((string)$attributes[self::FIELD_DATE]);
            } else {
                $type->setDate((string)$attributes[self::FIELD_DATE]);
            }
            $type->_setXMLFieldValueLocation(self::FIELD_DATE, ValueXMLLocationEnum::PARENT_ATTRIBUTE);
        }
        if (isset($attributes[self::FIELD_DETECTED])) {
            if (isset($type->detected)) {
                $type->detected->setValue((string)$attributes[self::FIELD_DETECTED]);
            } else {
                $type->setDetected((string)$attributes[self::FIELD_DETECTED]);
            }
            $type->_setXMLFieldValueLocation(self::FIELD_DETECTED, ValueXMLLocationEnum::PARENT_ATTRIBUTE);
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
            $xw->openRootNode('AdverseEvent', $this->_getSourceXMLNS());
        }
        if (isset($this->actuality) && ValueXMLLocationEnum::PARENT_ATTRIBUTE === $this->_valueXMLLocations[self::FIELD_ACTUALITY]) {
            $xw->writeAttribute(self::FIELD_ACTUALITY, $this->actuality->_getValueAsString());
        }
        if (isset($this->date) && ValueXMLLocationEnum::PARENT_ATTRIBUTE === $this->_valueXMLLocations[self::FIELD_DATE]) {
            $xw->writeAttribute(self::FIELD_DATE, $this->date->_getValueAsString());
        }
        if (isset($this->detected) && ValueXMLLocationEnum::PARENT_ATTRIBUTE === $this->_valueXMLLocations[self::FIELD_DETECTED]) {
            $xw->writeAttribute(self::FIELD_DETECTED, $this->detected->_getValueAsString());
        }
        if (isset($this->recordedDate) && ValueXMLLocationEnum::PARENT_ATTRIBUTE === $this->_valueXMLLocations[self::FIELD_RECORDED_DATE]) {
            $xw->writeAttribute(self::FIELD_RECORDED_DATE, $this->recordedDate->_getValueAsString());
        }
        parent::xmlSerialize($xw, $config);
        if (isset($this->identifier)) {
            $xw->startElement(self::FIELD_IDENTIFIER);
            $this->identifier->xmlSerialize($xw, $config);
            $xw->endElement();
        }
        if (isset($this->actuality)
            && (ValueXMLLocationEnum::PARENT_ATTRIBUTE !== $this->_valueXMLLocations[self::FIELD_ACTUALITY]
                || $this->actuality->_nonValueFieldDefined())) {
            $xw->startElement(self::FIELD_ACTUALITY);
            $this->actuality->xmlSerialize($xw, $config, $this->_valueXMLLocations[self::FIELD_ACTUALITY]);
            $xw->endElement();
        }
        if (isset($this->category)) {
            foreach ($this->category as $v) {
                $xw->startElement(self::FIELD_CATEGORY);
                $v->xmlSerialize($xw, $config);
                $xw->endElement();
            }
        }
        if (isset($this->event)) {
            $xw->startElement(self::FIELD_EVENT);
            $this->event->xmlSerialize($xw, $config);
            $xw->endElement();
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
        if (isset($this->date)
            && (ValueXMLLocationEnum::PARENT_ATTRIBUTE !== $this->_valueXMLLocations[self::FIELD_DATE]
                || $this->date->_nonValueFieldDefined())) {
            $xw->startElement(self::FIELD_DATE);
            $this->date->xmlSerialize($xw, $config, $this->_valueXMLLocations[self::FIELD_DATE]);
            $xw->endElement();
        }
        if (isset($this->detected)
            && (ValueXMLLocationEnum::PARENT_ATTRIBUTE !== $this->_valueXMLLocations[self::FIELD_DETECTED]
                || $this->detected->_nonValueFieldDefined())) {
            $xw->startElement(self::FIELD_DETECTED);
            $this->detected->xmlSerialize($xw, $config, $this->_valueXMLLocations[self::FIELD_DETECTED]);
            $xw->endElement();
        }
        if (isset($this->recordedDate)
            && (ValueXMLLocationEnum::PARENT_ATTRIBUTE !== $this->_valueXMLLocations[self::FIELD_RECORDED_DATE]
                || $this->recordedDate->_nonValueFieldDefined())) {
            $xw->startElement(self::FIELD_RECORDED_DATE);
            $this->recordedDate->xmlSerialize($xw, $config, $this->_valueXMLLocations[self::FIELD_RECORDED_DATE]);
            $xw->endElement();
        }
        if (isset($this->resultingCondition)) {
            foreach ($this->resultingCondition as $v) {
                $xw->startElement(self::FIELD_RESULTING_CONDITION);
                $v->xmlSerialize($xw, $config);
                $xw->endElement();
            }
        }
        if (isset($this->location)) {
            $xw->startElement(self::FIELD_LOCATION);
            $this->location->xmlSerialize($xw, $config);
            $xw->endElement();
        }
        if (isset($this->seriousness)) {
            $xw->startElement(self::FIELD_SERIOUSNESS);
            $this->seriousness->xmlSerialize($xw, $config);
            $xw->endElement();
        }
        if (isset($this->severity)) {
            $xw->startElement(self::FIELD_SEVERITY);
            $this->severity->xmlSerialize($xw, $config);
            $xw->endElement();
        }
        if (isset($this->outcome)) {
            $xw->startElement(self::FIELD_OUTCOME);
            $this->outcome->xmlSerialize($xw, $config);
            $xw->endElement();
        }
        if (isset($this->recorder)) {
            $xw->startElement(self::FIELD_RECORDER);
            $this->recorder->xmlSerialize($xw, $config);
            $xw->endElement();
        }
        if (isset($this->contributor)) {
            foreach ($this->contributor as $v) {
                $xw->startElement(self::FIELD_CONTRIBUTOR);
                $v->xmlSerialize($xw, $config);
                $xw->endElement();
            }
        }
        if (isset($this->suspectEntity)) {
            foreach ($this->suspectEntity as $v) {
                $xw->startElement(self::FIELD_SUSPECT_ENTITY);
                $v->xmlSerialize($xw, $config);
                $xw->endElement();
            }
        }
        if (isset($this->subjectMedicalHistory)) {
            foreach ($this->subjectMedicalHistory as $v) {
                $xw->startElement(self::FIELD_SUBJECT_MEDICAL_HISTORY);
                $v->xmlSerialize($xw, $config);
                $xw->endElement();
            }
        }
        if (isset($this->referenceDocument)) {
            foreach ($this->referenceDocument as $v) {
                $xw->startElement(self::FIELD_REFERENCE_DOCUMENT);
                $v->xmlSerialize($xw, $config);
                $xw->endElement();
            }
        }
        if (isset($this->study)) {
            foreach ($this->study as $v) {
                $xw->startElement(self::FIELD_STUDY);
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
     * @param null|\OpenEMR\FHIR\Versions\R4\Types\FHIRResource\FHIRDomainResource\FHIRAdverseEvent $type
     * @return \OpenEMR\FHIR\Versions\R4\Types\FHIRResource\FHIRDomainResource\FHIRAdverseEvent
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
        } else if (!($type instanceof FHIRAdverseEvent)) {
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
            if (is_array($decoded->identifier)) {
                $type->setIdentifier(FHIRIdentifier::jsonUnserialize(reset($decoded->identifier), $config));
            } else {
                $type->setIdentifier(FHIRIdentifier::jsonUnserialize($decoded->identifier, $config));
            }
        }
        if (isset($decoded->actuality)
            || isset($decoded->_actuality)
            || property_exists($decoded, self::FIELD_ACTUALITY)
            || property_exists($decoded, self::FIELD_ACTUALITY_EXT)) {
            $v = $decoded->_actuality ?? new \stdClass();
            $v->value = $decoded->actuality ?? null;
            $type->setActuality(FHIRAdverseEventActuality::jsonUnserialize($v, $config));
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
        if (isset($decoded->event) || property_exists($decoded, self::FIELD_EVENT)) {
            if (is_array($decoded->event)) {
                $type->setEvent(FHIRCodeableConcept::jsonUnserialize(reset($decoded->event), $config));
            } else {
                $type->setEvent(FHIRCodeableConcept::jsonUnserialize($decoded->event, $config));
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
        if (isset($decoded->date)
            || isset($decoded->_date)
            || property_exists($decoded, self::FIELD_DATE)
            || property_exists($decoded, self::FIELD_DATE_EXT)) {
            $v = $decoded->_date ?? new \stdClass();
            $v->value = $decoded->date ?? null;
            $type->setDate(FHIRDateTime::jsonUnserialize($v, $config));
        }
        if (isset($decoded->detected)
            || isset($decoded->_detected)
            || property_exists($decoded, self::FIELD_DETECTED)
            || property_exists($decoded, self::FIELD_DETECTED_EXT)) {
            $v = $decoded->_detected ?? new \stdClass();
            $v->value = $decoded->detected ?? null;
            $type->setDetected(FHIRDateTime::jsonUnserialize($v, $config));
        }
        if (isset($decoded->recordedDate)
            || isset($decoded->_recordedDate)
            || property_exists($decoded, self::FIELD_RECORDED_DATE)
            || property_exists($decoded, self::FIELD_RECORDED_DATE_EXT)) {
            $v = $decoded->_recordedDate ?? new \stdClass();
            $v->value = $decoded->recordedDate ?? null;
            $type->setRecordedDate(FHIRDateTime::jsonUnserialize($v, $config));
        }
        if (isset($decoded->resultingCondition) || property_exists($decoded, self::FIELD_RESULTING_CONDITION)) {
            if (is_object($decoded->resultingCondition)) {
                $vals = [$decoded->resultingCondition];
                $type->_setJSONFieldElideSingletonArray(self::FIELD_RESULTING_CONDITION, true);
            } else {
                $vals = $decoded->resultingCondition;
            }
            foreach($vals as $v) {
                $type->addResultingCondition(FHIRReference::jsonUnserialize($v, $config));
            }
        }
        if (isset($decoded->location) || property_exists($decoded, self::FIELD_LOCATION)) {
            if (is_array($decoded->location)) {
                $type->setLocation(FHIRReference::jsonUnserialize(reset($decoded->location), $config));
            } else {
                $type->setLocation(FHIRReference::jsonUnserialize($decoded->location, $config));
            }
        }
        if (isset($decoded->seriousness) || property_exists($decoded, self::FIELD_SERIOUSNESS)) {
            if (is_array($decoded->seriousness)) {
                $type->setSeriousness(FHIRCodeableConcept::jsonUnserialize(reset($decoded->seriousness), $config));
            } else {
                $type->setSeriousness(FHIRCodeableConcept::jsonUnserialize($decoded->seriousness, $config));
            }
        }
        if (isset($decoded->severity) || property_exists($decoded, self::FIELD_SEVERITY)) {
            if (is_array($decoded->severity)) {
                $type->setSeverity(FHIRCodeableConcept::jsonUnserialize(reset($decoded->severity), $config));
            } else {
                $type->setSeverity(FHIRCodeableConcept::jsonUnserialize($decoded->severity, $config));
            }
        }
        if (isset($decoded->outcome) || property_exists($decoded, self::FIELD_OUTCOME)) {
            if (is_array($decoded->outcome)) {
                $type->setOutcome(FHIRCodeableConcept::jsonUnserialize(reset($decoded->outcome), $config));
            } else {
                $type->setOutcome(FHIRCodeableConcept::jsonUnserialize($decoded->outcome, $config));
            }
        }
        if (isset($decoded->recorder) || property_exists($decoded, self::FIELD_RECORDER)) {
            if (is_array($decoded->recorder)) {
                $type->setRecorder(FHIRReference::jsonUnserialize(reset($decoded->recorder), $config));
            } else {
                $type->setRecorder(FHIRReference::jsonUnserialize($decoded->recorder, $config));
            }
        }
        if (isset($decoded->contributor) || property_exists($decoded, self::FIELD_CONTRIBUTOR)) {
            if (is_object($decoded->contributor)) {
                $vals = [$decoded->contributor];
                $type->_setJSONFieldElideSingletonArray(self::FIELD_CONTRIBUTOR, true);
            } else {
                $vals = $decoded->contributor;
            }
            foreach($vals as $v) {
                $type->addContributor(FHIRReference::jsonUnserialize($v, $config));
            }
        }
        if (isset($decoded->suspectEntity) || property_exists($decoded, self::FIELD_SUSPECT_ENTITY)) {
            if (is_object($decoded->suspectEntity)) {
                $vals = [$decoded->suspectEntity];
                $type->_setJSONFieldElideSingletonArray(self::FIELD_SUSPECT_ENTITY, true);
            } else {
                $vals = $decoded->suspectEntity;
            }
            foreach($vals as $v) {
                $type->addSuspectEntity(FHIRAdverseEventSuspectEntity::jsonUnserialize($v, $config));
            }
        }
        if (isset($decoded->subjectMedicalHistory) || property_exists($decoded, self::FIELD_SUBJECT_MEDICAL_HISTORY)) {
            if (is_object($decoded->subjectMedicalHistory)) {
                $vals = [$decoded->subjectMedicalHistory];
                $type->_setJSONFieldElideSingletonArray(self::FIELD_SUBJECT_MEDICAL_HISTORY, true);
            } else {
                $vals = $decoded->subjectMedicalHistory;
            }
            foreach($vals as $v) {
                $type->addSubjectMedicalHistory(FHIRReference::jsonUnserialize($v, $config));
            }
        }
        if (isset($decoded->referenceDocument) || property_exists($decoded, self::FIELD_REFERENCE_DOCUMENT)) {
            if (is_object($decoded->referenceDocument)) {
                $vals = [$decoded->referenceDocument];
                $type->_setJSONFieldElideSingletonArray(self::FIELD_REFERENCE_DOCUMENT, true);
            } else {
                $vals = $decoded->referenceDocument;
            }
            foreach($vals as $v) {
                $type->addReferenceDocument(FHIRReference::jsonUnserialize($v, $config));
            }
        }
        if (isset($decoded->study) || property_exists($decoded, self::FIELD_STUDY)) {
            if (is_object($decoded->study)) {
                $vals = [$decoded->study];
                $type->_setJSONFieldElideSingletonArray(self::FIELD_STUDY, true);
            } else {
                $vals = $decoded->study;
            }
            foreach($vals as $v) {
                $type->addStudy(FHIRReference::jsonUnserialize($v, $config));
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
        if (isset($this->identifier)) {
            $out->identifier = $this->identifier;
        }
        if (isset($this->actuality)) {
            if (null !== ($val = $this->actuality->getValue())) {
                $out->actuality = $val;
            }
            if ($this->actuality->_nonValueFieldDefined()) {
                $ext = $this->actuality->jsonSerialize();
                unset($ext->value);
                $out->_actuality = $ext;
            }
        }
        if (isset($this->category) && [] !== $this->category) {
            if ($this->_getJSONFieldElideSingletonArray(self::FIELD_CATEGORY) && 1 === count($this->category)) {
                $out->category = $this->category[0];
            } else {
                $out->category = $this->category;
            }
        }
        if (isset($this->event)) {
            $out->event = $this->event;
        }
        if (isset($this->subject)) {
            $out->subject = $this->subject;
        }
        if (isset($this->encounter)) {
            $out->encounter = $this->encounter;
        }
        if (isset($this->date)) {
            if (null !== ($val = $this->date->getValue())) {
                $out->date = $val;
            }
            if ($this->date->_nonValueFieldDefined()) {
                $ext = $this->date->jsonSerialize();
                unset($ext->value);
                $out->_date = $ext;
            }
        }
        if (isset($this->detected)) {
            if (null !== ($val = $this->detected->getValue())) {
                $out->detected = $val;
            }
            if ($this->detected->_nonValueFieldDefined()) {
                $ext = $this->detected->jsonSerialize();
                unset($ext->value);
                $out->_detected = $ext;
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
        if (isset($this->resultingCondition) && [] !== $this->resultingCondition) {
            if ($this->_getJSONFieldElideSingletonArray(self::FIELD_RESULTING_CONDITION) && 1 === count($this->resultingCondition)) {
                $out->resultingCondition = $this->resultingCondition[0];
            } else {
                $out->resultingCondition = $this->resultingCondition;
            }
        }
        if (isset($this->location)) {
            $out->location = $this->location;
        }
        if (isset($this->seriousness)) {
            $out->seriousness = $this->seriousness;
        }
        if (isset($this->severity)) {
            $out->severity = $this->severity;
        }
        if (isset($this->outcome)) {
            $out->outcome = $this->outcome;
        }
        if (isset($this->recorder)) {
            $out->recorder = $this->recorder;
        }
        if (isset($this->contributor) && [] !== $this->contributor) {
            if ($this->_getJSONFieldElideSingletonArray(self::FIELD_CONTRIBUTOR) && 1 === count($this->contributor)) {
                $out->contributor = $this->contributor[0];
            } else {
                $out->contributor = $this->contributor;
            }
        }
        if (isset($this->suspectEntity) && [] !== $this->suspectEntity) {
            if ($this->_getJSONFieldElideSingletonArray(self::FIELD_SUSPECT_ENTITY) && 1 === count($this->suspectEntity)) {
                $out->suspectEntity = $this->suspectEntity[0];
            } else {
                $out->suspectEntity = $this->suspectEntity;
            }
        }
        if (isset($this->subjectMedicalHistory) && [] !== $this->subjectMedicalHistory) {
            if ($this->_getJSONFieldElideSingletonArray(self::FIELD_SUBJECT_MEDICAL_HISTORY) && 1 === count($this->subjectMedicalHistory)) {
                $out->subjectMedicalHistory = $this->subjectMedicalHistory[0];
            } else {
                $out->subjectMedicalHistory = $this->subjectMedicalHistory;
            }
        }
        if (isset($this->referenceDocument) && [] !== $this->referenceDocument) {
            if ($this->_getJSONFieldElideSingletonArray(self::FIELD_REFERENCE_DOCUMENT) && 1 === count($this->referenceDocument)) {
                $out->referenceDocument = $this->referenceDocument[0];
            } else {
                $out->referenceDocument = $this->referenceDocument;
            }
        }
        if (isset($this->study) && [] !== $this->study) {
            if ($this->_getJSONFieldElideSingletonArray(self::FIELD_STUDY) && 1 === count($this->study)) {
                $out->study = $this->study[0];
            } else {
                $out->study = $this->study;
            }
        }
        $out->resourceType = $this->_getResourceType();
        return $out;
    }
}
