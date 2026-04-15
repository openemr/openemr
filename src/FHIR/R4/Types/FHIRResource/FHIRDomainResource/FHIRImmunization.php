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
use OpenEMR\FHIR\Versions\R4\Types\FHIRBooleanPrimitive;
use OpenEMR\FHIR\Versions\R4\Types\FHIRCodePrimitive;
use OpenEMR\FHIR\Versions\R4\Types\FHIRCodePrimitive\FHIRImmunizationStatusCodesList;
use OpenEMR\FHIR\Versions\R4\Types\FHIRDatePrimitive;
use OpenEMR\FHIR\Versions\R4\Types\FHIRDateTimePrimitive;
use OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRAnnotation;
use OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRImmunization\FHIRImmunizationEducation;
use OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRImmunization\FHIRImmunizationPerformer;
use OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRImmunization\FHIRImmunizationProtocolApplied;
use OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRImmunization\FHIRImmunizationReaction;
use OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBoolean;
use OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRCode;
use OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRCodeableConcept;
use OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRDate;
use OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRDateTime;
use OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRExtension;
use OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRId;
use OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRIdentifier;
use OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRImmunizationStatusCodes;
use OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRMeta;
use OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRNarrative;
use OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRQuantity;
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
 * Describes the event of a patient being administered a vaccine or a record of an
 * immunization as reported by a patient, a clinician or another party.
 * If the element is present, it must have either a \@value, an \@id, or extensions
 */
#[\Attribute(\Attribute::TARGET_PROPERTY)]
class FHIRImmunization extends FHIRDomainResource implements VersionContainedTypeInterface
{
    use TypeValidationsTrait,
        JSONSerializationOptionsTrait,
        XMLSerializationOptionsTrait;

    // name of FHIR type this class describes
    public const FHIR_TYPE_NAME = VersionConstants::TYPE_NAME_IMMUNIZATION;

    /* class_default.php:56 */
    public const FIELD_IDENTIFIER = 'identifier';
    public const FIELD_STATUS = 'status';
    public const FIELD_STATUS_EXT = '_status';
    public const FIELD_STATUS_REASON = 'statusReason';
    public const FIELD_VACCINE_CODE = 'vaccineCode';
    public const FIELD_PATIENT = 'patient';
    public const FIELD_ENCOUNTER = 'encounter';
    public const FIELD_OCCURRENCE_DATE_TIME = 'occurrenceDateTime';
    public const FIELD_OCCURRENCE_DATE_TIME_EXT = '_occurrenceDateTime';
    public const FIELD_OCCURRENCE_STRING = 'occurrenceString';
    public const FIELD_OCCURRENCE_STRING_EXT = '_occurrenceString';
    public const FIELD_RECORDED = 'recorded';
    public const FIELD_RECORDED_EXT = '_recorded';
    public const FIELD_PRIMARY_SOURCE = 'primarySource';
    public const FIELD_PRIMARY_SOURCE_EXT = '_primarySource';
    public const FIELD_REPORT_ORIGIN = 'reportOrigin';
    public const FIELD_LOCATION = 'location';
    public const FIELD_MANUFACTURER = 'manufacturer';
    public const FIELD_LOT_NUMBER = 'lotNumber';
    public const FIELD_LOT_NUMBER_EXT = '_lotNumber';
    public const FIELD_EXPIRATION_DATE = 'expirationDate';
    public const FIELD_EXPIRATION_DATE_EXT = '_expirationDate';
    public const FIELD_SITE = 'site';
    public const FIELD_ROUTE = 'route';
    public const FIELD_DOSE_QUANTITY = 'doseQuantity';
    public const FIELD_PERFORMER = 'performer';
    public const FIELD_NOTE = 'note';
    public const FIELD_REASON_CODE = 'reasonCode';
    public const FIELD_REASON_REFERENCE = 'reasonReference';
    public const FIELD_IS_SUBPOTENT = 'isSubpotent';
    public const FIELD_IS_SUBPOTENT_EXT = '_isSubpotent';
    public const FIELD_SUBPOTENT_REASON = 'subpotentReason';
    public const FIELD_EDUCATION = 'education';
    public const FIELD_PROGRAM_ELIGIBILITY = 'programEligibility';
    public const FIELD_FUNDING_SOURCE = 'fundingSource';
    public const FIELD_REACTION = 'reaction';
    public const FIELD_PROTOCOL_APPLIED = 'protocolApplied';

    /* class_default.php:75 */
    private const _FHIR_VALIDATION_RULES = [
        self::FIELD_STATUS => [
            MinOccursRule::NAME => 1,
        ],
        self::FIELD_VACCINE_CODE => [
            MinOccursRule::NAME => 1,
        ],
        self::FIELD_PATIENT => [
            MinOccursRule::NAME => 1,
        ],
    ];

    /* class_default.php:96 */
    private array $_valueXMLLocations = [
        self::FIELD_STATUS => ValueXMLLocationEnum::CONTAINER_ATTRIBUTE,
        self::FIELD_OCCURRENCE_DATE_TIME => ValueXMLLocationEnum::CONTAINER_ATTRIBUTE,
        self::FIELD_OCCURRENCE_STRING => ValueXMLLocationEnum::CONTAINER_ATTRIBUTE,
        self::FIELD_RECORDED => ValueXMLLocationEnum::CONTAINER_ATTRIBUTE,
        self::FIELD_PRIMARY_SOURCE => ValueXMLLocationEnum::CONTAINER_ATTRIBUTE,
        self::FIELD_LOT_NUMBER => ValueXMLLocationEnum::CONTAINER_ATTRIBUTE,
        self::FIELD_EXPIRATION_DATE => ValueXMLLocationEnum::CONTAINER_ATTRIBUTE,
        self::FIELD_IS_SUBPOTENT => ValueXMLLocationEnum::CONTAINER_ATTRIBUTE,
    ];

    /* class_default.php:112 */
    /**
     * An identifier - identifies some entity uniquely and unambiguously. Typically
     * this is used for business identifiers.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * A unique identifier assigned to this immunization record.
     *
     * @var iterable<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRIdentifier>
     */
    #[FHIRIdentifier]
    protected array $identifier;
    /**
     * A set of codes indicating the current status of an Immunization.
     * If the element is present, it must have either a \@value, an \@id, or extensions
     *
     * Indicates the current status of the immunization event.
     *
     * @var \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRImmunizationStatusCodes
     */
    #[FHIRImmunizationStatusCodes]
    protected FHIRImmunizationStatusCodes $status;
    /**
     * A concept that may be defined by a formal reference to a terminology or ontology
     * or may be provided by text.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * Indicates the reason the immunization event was not performed.
     *
     * @var \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRCodeableConcept
     */
    #[FHIRCodeableConcept]
    protected FHIRCodeableConcept $statusReason;
    /**
     * A concept that may be defined by a formal reference to a terminology or ontology
     * or may be provided by text.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * Vaccine that was administered or was to be administered.
     *
     * @var \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRCodeableConcept
     */
    #[FHIRCodeableConcept]
    protected FHIRCodeableConcept $vaccineCode;
    /**
     * A reference from one resource to another.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * The patient who either received or did not receive the immunization.
     *
     * @var \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRReference
     */
    #[FHIRReference]
    protected FHIRReference $patient;
    /**
     * A reference from one resource to another.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * The visit or admission or other contact between patient and health care provider
     * the immunization was performed as part of.
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
     * Date vaccine administered or was to be administered. (choose any one of
     * occurrence*, but only one)
     *
     * @var \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRDateTime
     */
    #[FHIRDateTime]
    protected FHIRDateTime $occurrenceDateTime;
    /**
     * A sequence of Unicode characters
     * Note that FHIR strings SHALL NOT exceed 1MB in size
     * If the element is present, it must have either a \@value, an \@id, or extensions
     *
     * Date vaccine administered or was to be administered. (choose any one of
     * occurrence*, but only one)
     *
     * @var \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRString
     */
    #[FHIRString]
    protected FHIRString $occurrenceString;
    /**
     * A date, date-time or partial date (e.g. just year or year + month). If hours and
     * minutes are specified, a time zone SHALL be populated. The format is a union of
     * the schema types gYear, gYearMonth, date and dateTime. Seconds must be provided
     * due to schema type constraints but may be zero-filled and may be ignored. Dates
     * SHALL be valid dates.
     * If the element is present, it must have either a \@value, an \@id, or extensions
     *
     * The date the occurrence of the immunization was first captured in the record -
     * potentially significantly after the occurrence of the event.
     *
     * @var \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRDateTime
     */
    #[FHIRDateTime]
    protected FHIRDateTime $recorded;
    /**
     * Value of "true" or "false"
     * If the element is present, it must have either a \@value, an \@id, or extensions
     *
     * An indication that the content of the record is based on information from the
     * person who administered the vaccine. This reflects the context under which the
     * data was originally recorded.
     *
     * @var \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBoolean
     */
    #[FHIRBoolean]
    protected FHIRBoolean $primarySource;
    /**
     * A concept that may be defined by a formal reference to a terminology or ontology
     * or may be provided by text.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * The source of the data when the report of the immunization event is not based on
     * information from the person who administered the vaccine.
     *
     * @var \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRCodeableConcept
     */
    #[FHIRCodeableConcept]
    protected FHIRCodeableConcept $reportOrigin;
    /**
     * A reference from one resource to another.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * The service delivery location where the vaccine administration occurred.
     *
     * @var \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRReference
     */
    #[FHIRReference]
    protected FHIRReference $location;
    /**
     * A reference from one resource to another.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * Name of vaccine manufacturer.
     *
     * @var \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRReference
     */
    #[FHIRReference]
    protected FHIRReference $manufacturer;
    /**
     * A sequence of Unicode characters
     * Note that FHIR strings SHALL NOT exceed 1MB in size
     * If the element is present, it must have either a \@value, an \@id, or extensions
     *
     * Lot number of the vaccine product.
     *
     * @var \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRString
     */
    #[FHIRString]
    protected FHIRString $lotNumber;
    /**
     * A date or partial date (e.g. just year or year + month). There is no time zone.
     * The format is a union of the schema types gYear, gYearMonth and date. Dates
     * SHALL be valid dates.
     * If the element is present, it must have either a \@value, an \@id, or extensions
     *
     * Date vaccine batch expires.
     *
     * @var \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRDate
     */
    #[FHIRDate]
    protected FHIRDate $expirationDate;
    /**
     * A concept that may be defined by a formal reference to a terminology or ontology
     * or may be provided by text.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * Body site where vaccine was administered.
     *
     * @var \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRCodeableConcept
     */
    #[FHIRCodeableConcept]
    protected FHIRCodeableConcept $site;
    /**
     * A concept that may be defined by a formal reference to a terminology or ontology
     * or may be provided by text.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * The path by which the vaccine product is taken into the body.
     *
     * @var \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRCodeableConcept
     */
    #[FHIRCodeableConcept]
    protected FHIRCodeableConcept $route;
    /**
     * A measured amount (or an amount that can potentially be measured). Note that
     * measured amounts include amounts that are not precisely quantified, including
     * amounts involving arbitrary units and floating currencies.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * The quantity of vaccine product that was administered.
     *
     * @var \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRQuantity
     */
    #[FHIRQuantity]
    protected FHIRQuantity $doseQuantity;
    /**
     * Describes the event of a patient being administered a vaccine or a record of an
     * immunization as reported by a patient, a clinician or another party.
     *
     * Indicates who performed the immunization event.
     *
     * @var iterable<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRImmunization\FHIRImmunizationPerformer>
     */
    #[FHIRImmunizationPerformer]
    protected array $performer;
    /**
     * A text note which also contains information about who made the statement and
     * when.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * Extra information about the immunization that is not conveyed by the other
     * attributes.
     *
     * @var iterable<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRAnnotation>
     */
    #[FHIRAnnotation]
    protected array $note;
    /**
     * A concept that may be defined by a formal reference to a terminology or ontology
     * or may be provided by text.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * Reasons why the vaccine was administered.
     *
     * @var iterable<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRCodeableConcept>
     */
    #[FHIRCodeableConcept]
    protected array $reasonCode;
    /**
     * A reference from one resource to another.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * Condition, Observation or DiagnosticReport that supports why the immunization
     * was administered.
     *
     * @var iterable<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRReference>
     */
    #[FHIRReference]
    protected array $reasonReference;
    /**
     * Value of "true" or "false"
     * If the element is present, it must have either a \@value, an \@id, or extensions
     *
     * Indication if a dose is considered to be subpotent. By default, a dose should be
     * considered to be potent.
     *
     * @var \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBoolean
     */
    #[FHIRBoolean]
    protected FHIRBoolean $isSubpotent;
    /**
     * A concept that may be defined by a formal reference to a terminology or ontology
     * or may be provided by text.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * Reason why a dose is considered to be subpotent.
     *
     * @var iterable<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRCodeableConcept>
     */
    #[FHIRCodeableConcept]
    protected array $subpotentReason;
    /**
     * Describes the event of a patient being administered a vaccine or a record of an
     * immunization as reported by a patient, a clinician or another party.
     *
     * Educational material presented to the patient (or guardian) at the time of
     * vaccine administration.
     *
     * @var iterable<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRImmunization\FHIRImmunizationEducation>
     */
    #[FHIRImmunizationEducation]
    protected array $education;
    /**
     * A concept that may be defined by a formal reference to a terminology or ontology
     * or may be provided by text.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * Indicates a patient's eligibility for a funding program.
     *
     * @var iterable<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRCodeableConcept>
     */
    #[FHIRCodeableConcept]
    protected array $programEligibility;
    /**
     * A concept that may be defined by a formal reference to a terminology or ontology
     * or may be provided by text.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * Indicates the source of the vaccine actually administered. This may be different
     * than the patient eligibility (e.g. the patient may be eligible for a publically
     * purchased vaccine but due to inventory issues, vaccine purchased with private
     * funds was actually administered).
     *
     * @var \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRCodeableConcept
     */
    #[FHIRCodeableConcept]
    protected FHIRCodeableConcept $fundingSource;
    /**
     * Describes the event of a patient being administered a vaccine or a record of an
     * immunization as reported by a patient, a clinician or another party.
     *
     * Categorical data indicating that an adverse event is associated in time to an
     * immunization.
     *
     * @var iterable<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRImmunization\FHIRImmunizationReaction>
     */
    #[FHIRImmunizationReaction]
    protected array $reaction;
    /**
     * Describes the event of a patient being administered a vaccine or a record of an
     * immunization as reported by a patient, a clinician or another party.
     *
     * The protocol (set of recommendations) being followed by the provider who
     * administered the dose.
     *
     * @var iterable<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRImmunization\FHIRImmunizationProtocolApplied>
     */
    #[FHIRImmunizationProtocolApplied]
    protected array $protocolApplied;

    /* constructor.php:61 */
    /**
     * FHIRImmunization Constructor
     * @param null|string|\OpenEMR\FHIR\Versions\R4\Types\FHIRIdPrimitive|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRId $id
     * @param null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRMeta $meta
     * @param null|string|\OpenEMR\FHIR\Versions\R4\Types\FHIRUriPrimitive|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRUri $implicitRules
     * @param null|string|\OpenEMR\FHIR\Versions\R4\Types\FHIRCodePrimitive|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRCode $language
     * @param null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRNarrative $text
     * @param null|iterable<\OpenEMR\FHIR\Versions\R4\Types\FHIRResourceContainer>|iterable<\OpenEMR\FHIR\Versions\R4\VersionContainedTypeInterface> $contained
     * @param null|iterable<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRExtension> $extension
     * @param null|iterable<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRExtension> $modifierExtension
     * @param null|iterable<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRIdentifier> $identifier
     * @param null|string|\OpenEMR\FHIR\Versions\R4\Types\FHIRCodePrimitive\FHIRImmunizationStatusCodesList|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRImmunizationStatusCodes $status
     * @param null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRCodeableConcept $statusReason
     * @param null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRCodeableConcept $vaccineCode
     * @param null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRReference $patient
     * @param null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRReference $encounter
     * @param null|string|\DateTimeInterface|\OpenEMR\FHIR\Versions\R4\Types\FHIRDateTimePrimitive|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRDateTime $occurrenceDateTime
     * @param null|string|\OpenEMR\FHIR\Versions\R4\Types\FHIRStringPrimitive|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRString $occurrenceString
     * @param null|string|\DateTimeInterface|\OpenEMR\FHIR\Versions\R4\Types\FHIRDateTimePrimitive|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRDateTime $recorded
     * @param null|string|bool|\OpenEMR\FHIR\Versions\R4\Types\FHIRBooleanPrimitive|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBoolean $primarySource
     * @param null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRCodeableConcept $reportOrigin
     * @param null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRReference $location
     * @param null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRReference $manufacturer
     * @param null|string|\OpenEMR\FHIR\Versions\R4\Types\FHIRStringPrimitive|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRString $lotNumber
     * @param null|string|\DateTimeInterface|\OpenEMR\FHIR\Versions\R4\Types\FHIRDatePrimitive|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRDate $expirationDate
     * @param null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRCodeableConcept $site
     * @param null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRCodeableConcept $route
     * @param null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRQuantity $doseQuantity
     * @param null|iterable<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRImmunization\FHIRImmunizationPerformer> $performer
     * @param null|iterable<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRAnnotation> $note
     * @param null|iterable<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRCodeableConcept> $reasonCode
     * @param null|iterable<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRReference> $reasonReference
     * @param null|string|bool|\OpenEMR\FHIR\Versions\R4\Types\FHIRBooleanPrimitive|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBoolean $isSubpotent
     * @param null|iterable<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRCodeableConcept> $subpotentReason
     * @param null|iterable<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRImmunization\FHIRImmunizationEducation> $education
     * @param null|iterable<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRCodeableConcept> $programEligibility
     * @param null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRCodeableConcept $fundingSource
     * @param null|iterable<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRImmunization\FHIRImmunizationReaction> $reaction
     * @param null|iterable<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRImmunization\FHIRImmunizationProtocolApplied> $protocolApplied
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
                                null|string|FHIRImmunizationStatusCodesList|FHIRImmunizationStatusCodes $status = null,
                                null|FHIRCodeableConcept $statusReason = null,
                                null|FHIRCodeableConcept $vaccineCode = null,
                                null|FHIRReference $patient = null,
                                null|FHIRReference $encounter = null,
                                null|string|\DateTimeInterface|FHIRDateTimePrimitive|FHIRDateTime $occurrenceDateTime = null,
                                null|string|FHIRStringPrimitive|FHIRString $occurrenceString = null,
                                null|string|\DateTimeInterface|FHIRDateTimePrimitive|FHIRDateTime $recorded = null,
                                null|string|bool|FHIRBooleanPrimitive|FHIRBoolean $primarySource = null,
                                null|FHIRCodeableConcept $reportOrigin = null,
                                null|FHIRReference $location = null,
                                null|FHIRReference $manufacturer = null,
                                null|string|FHIRStringPrimitive|FHIRString $lotNumber = null,
                                null|string|\DateTimeInterface|FHIRDatePrimitive|FHIRDate $expirationDate = null,
                                null|FHIRCodeableConcept $site = null,
                                null|FHIRCodeableConcept $route = null,
                                null|FHIRQuantity $doseQuantity = null,
                                null|iterable $performer = null,
                                null|iterable $note = null,
                                null|iterable $reasonCode = null,
                                null|iterable $reasonReference = null,
                                null|string|bool|FHIRBooleanPrimitive|FHIRBoolean $isSubpotent = null,
                                null|iterable $subpotentReason = null,
                                null|iterable $education = null,
                                null|iterable $programEligibility = null,
                                null|FHIRCodeableConcept $fundingSource = null,
                                null|iterable $reaction = null,
                                null|iterable $protocolApplied = null,
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
        if (null !== $status) {
            $this->setStatus($status);
        }
        if (null !== $statusReason) {
            $this->setStatusReason($statusReason);
        }
        if (null !== $vaccineCode) {
            $this->setVaccineCode($vaccineCode);
        }
        if (null !== $patient) {
            $this->setPatient($patient);
        }
        if (null !== $encounter) {
            $this->setEncounter($encounter);
        }
        if (null !== $occurrenceDateTime) {
            $this->setOccurrenceDateTime($occurrenceDateTime);
        }
        if (null !== $occurrenceString) {
            $this->setOccurrenceString($occurrenceString);
        }
        if (null !== $recorded) {
            $this->setRecorded($recorded);
        }
        if (null !== $primarySource) {
            $this->setPrimarySource($primarySource);
        }
        if (null !== $reportOrigin) {
            $this->setReportOrigin($reportOrigin);
        }
        if (null !== $location) {
            $this->setLocation($location);
        }
        if (null !== $manufacturer) {
            $this->setManufacturer($manufacturer);
        }
        if (null !== $lotNumber) {
            $this->setLotNumber($lotNumber);
        }
        if (null !== $expirationDate) {
            $this->setExpirationDate($expirationDate);
        }
        if (null !== $site) {
            $this->setSite($site);
        }
        if (null !== $route) {
            $this->setRoute($route);
        }
        if (null !== $doseQuantity) {
            $this->setDoseQuantity($doseQuantity);
        }
        if (null !== $performer) {
            $this->setPerformer(...$performer);
        }
        if (null !== $note) {
            $this->setNote(...$note);
        }
        if (null !== $reasonCode) {
            $this->setReasonCode(...$reasonCode);
        }
        if (null !== $reasonReference) {
            $this->setReasonReference(...$reasonReference);
        }
        if (null !== $isSubpotent) {
            $this->setIsSubpotent($isSubpotent);
        }
        if (null !== $subpotentReason) {
            $this->setSubpotentReason(...$subpotentReason);
        }
        if (null !== $education) {
            $this->setEducation(...$education);
        }
        if (null !== $programEligibility) {
            $this->setProgramEligibility(...$programEligibility);
        }
        if (null !== $fundingSource) {
            $this->setFundingSource($fundingSource);
        }
        if (null !== $reaction) {
            $this->setReaction(...$reaction);
        }
        if (null !== $protocolApplied) {
            $this->setProtocolApplied(...$protocolApplied);
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
     * A unique identifier assigned to this immunization record.
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
     * A unique identifier assigned to this immunization record.
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
     * A unique identifier assigned to this immunization record.
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
     * A set of codes indicating the current status of an Immunization.
     * If the element is present, it must have either a \@value, an \@id, or extensions
     *
     * Indicates the current status of the immunization event.
     *
     * @return null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRImmunizationStatusCodes
     */
    public function getStatus(): null|FHIRImmunizationStatusCodes
    {
        return $this->status ?? null;
    }

    /**
     * A set of codes indicating the current status of an Immunization.
     * If the element is present, it must have either a \@value, an \@id, or extensions
     *
     * Indicates the current status of the immunization event.
     *
     * @param null|string|\OpenEMR\FHIR\Versions\R4\Types\FHIRCodePrimitive\FHIRImmunizationStatusCodesList|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRImmunizationStatusCodes $status
     * @return static
     */
    public function setStatus(null|string|FHIRImmunizationStatusCodesList|FHIRImmunizationStatusCodes $status): self
    {
        if (null === $status) {
            unset($this->status);
            return $this;
        }
        if (!($status instanceof FHIRImmunizationStatusCodes)) {
            $status = new FHIRImmunizationStatusCodes(value: $status);
        }
        $this->status = $status;
        return $this;
    }

    /**
     * A concept that may be defined by a formal reference to a terminology or ontology
     * or may be provided by text.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * Indicates the reason the immunization event was not performed.
     *
     * @return null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRCodeableConcept
     */
    public function getStatusReason(): null|FHIRCodeableConcept
    {
        return $this->statusReason ?? null;
    }

    /**
     * A concept that may be defined by a formal reference to a terminology or ontology
     * or may be provided by text.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * Indicates the reason the immunization event was not performed.
     *
     * @param null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRCodeableConcept $statusReason
     * @return static
     */
    public function setStatusReason(null|FHIRCodeableConcept $statusReason): self
    {
        if (null === $statusReason) {
            unset($this->statusReason);
            return $this;
        }
        $this->statusReason = $statusReason;
        return $this;
    }

    /**
     * A concept that may be defined by a formal reference to a terminology or ontology
     * or may be provided by text.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * Vaccine that was administered or was to be administered.
     *
     * @return null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRCodeableConcept
     */
    public function getVaccineCode(): null|FHIRCodeableConcept
    {
        return $this->vaccineCode ?? null;
    }

    /**
     * A concept that may be defined by a formal reference to a terminology or ontology
     * or may be provided by text.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * Vaccine that was administered or was to be administered.
     *
     * @param null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRCodeableConcept $vaccineCode
     * @return static
     */
    public function setVaccineCode(null|FHIRCodeableConcept $vaccineCode): self
    {
        if (null === $vaccineCode) {
            unset($this->vaccineCode);
            return $this;
        }
        $this->vaccineCode = $vaccineCode;
        return $this;
    }

    /**
     * A reference from one resource to another.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * The patient who either received or did not receive the immunization.
     *
     * @return null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRReference
     */
    public function getPatient(): null|FHIRReference
    {
        return $this->patient ?? null;
    }

    /**
     * A reference from one resource to another.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * The patient who either received or did not receive the immunization.
     *
     * @param null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRReference $patient
     * @return static
     */
    public function setPatient(null|FHIRReference $patient): self
    {
        if (null === $patient) {
            unset($this->patient);
            return $this;
        }
        $this->patient = $patient;
        return $this;
    }

    /**
     * A reference from one resource to another.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * The visit or admission or other contact between patient and health care provider
     * the immunization was performed as part of.
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
     * The visit or admission or other contact between patient and health care provider
     * the immunization was performed as part of.
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
     * Date vaccine administered or was to be administered. (choose any one of
     * occurrence*, but only one)
     *
     * @return null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRDateTime
     */
    public function getOccurrenceDateTime(): null|FHIRDateTime
    {
        return $this->occurrenceDateTime ?? null;
    }

    /**
     * A date, date-time or partial date (e.g. just year or year + month). If hours and
     * minutes are specified, a time zone SHALL be populated. The format is a union of
     * the schema types gYear, gYearMonth, date and dateTime. Seconds must be provided
     * due to schema type constraints but may be zero-filled and may be ignored. Dates
     * SHALL be valid dates.
     * If the element is present, it must have either a \@value, an \@id, or extensions
     *
     * Date vaccine administered or was to be administered. (choose any one of
     * occurrence*, but only one)
     *
     * @param null|string|\DateTimeInterface|\OpenEMR\FHIR\Versions\R4\Types\FHIRDateTimePrimitive|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRDateTime $occurrenceDateTime
     * @return static
     */
    public function setOccurrenceDateTime(null|string|\DateTimeInterface|FHIRDateTimePrimitive|FHIRDateTime $occurrenceDateTime): self
    {
        if (null === $occurrenceDateTime) {
            unset($this->occurrenceDateTime);
            return $this;
        }
        if (!($occurrenceDateTime instanceof FHIRDateTime)) {
            $occurrenceDateTime = new FHIRDateTime(value: $occurrenceDateTime);
        }
        $this->occurrenceDateTime = $occurrenceDateTime;
        return $this;
    }

    /**
     * A sequence of Unicode characters
     * Note that FHIR strings SHALL NOT exceed 1MB in size
     * If the element is present, it must have either a \@value, an \@id, or extensions
     *
     * Date vaccine administered or was to be administered. (choose any one of
     * occurrence*, but only one)
     *
     * @return null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRString
     */
    public function getOccurrenceString(): null|FHIRString
    {
        return $this->occurrenceString ?? null;
    }

    /**
     * A sequence of Unicode characters
     * Note that FHIR strings SHALL NOT exceed 1MB in size
     * If the element is present, it must have either a \@value, an \@id, or extensions
     *
     * Date vaccine administered or was to be administered. (choose any one of
     * occurrence*, but only one)
     *
     * @param null|string|\OpenEMR\FHIR\Versions\R4\Types\FHIRStringPrimitive|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRString $occurrenceString
     * @return static
     */
    public function setOccurrenceString(null|string|FHIRStringPrimitive|FHIRString $occurrenceString): self
    {
        if (null === $occurrenceString) {
            unset($this->occurrenceString);
            return $this;
        }
        if (!($occurrenceString instanceof FHIRString)) {
            $occurrenceString = new FHIRString(value: $occurrenceString);
        }
        $this->occurrenceString = $occurrenceString;
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
     * The date the occurrence of the immunization was first captured in the record -
     * potentially significantly after the occurrence of the event.
     *
     * @return null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRDateTime
     */
    public function getRecorded(): null|FHIRDateTime
    {
        return $this->recorded ?? null;
    }

    /**
     * A date, date-time or partial date (e.g. just year or year + month). If hours and
     * minutes are specified, a time zone SHALL be populated. The format is a union of
     * the schema types gYear, gYearMonth, date and dateTime. Seconds must be provided
     * due to schema type constraints but may be zero-filled and may be ignored. Dates
     * SHALL be valid dates.
     * If the element is present, it must have either a \@value, an \@id, or extensions
     *
     * The date the occurrence of the immunization was first captured in the record -
     * potentially significantly after the occurrence of the event.
     *
     * @param null|string|\DateTimeInterface|\OpenEMR\FHIR\Versions\R4\Types\FHIRDateTimePrimitive|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRDateTime $recorded
     * @return static
     */
    public function setRecorded(null|string|\DateTimeInterface|FHIRDateTimePrimitive|FHIRDateTime $recorded): self
    {
        if (null === $recorded) {
            unset($this->recorded);
            return $this;
        }
        if (!($recorded instanceof FHIRDateTime)) {
            $recorded = new FHIRDateTime(value: $recorded);
        }
        $this->recorded = $recorded;
        return $this;
    }

    /**
     * Value of "true" or "false"
     * If the element is present, it must have either a \@value, an \@id, or extensions
     *
     * An indication that the content of the record is based on information from the
     * person who administered the vaccine. This reflects the context under which the
     * data was originally recorded.
     *
     * @return null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBoolean
     */
    public function getPrimarySource(): null|FHIRBoolean
    {
        return $this->primarySource ?? null;
    }

    /**
     * Value of "true" or "false"
     * If the element is present, it must have either a \@value, an \@id, or extensions
     *
     * An indication that the content of the record is based on information from the
     * person who administered the vaccine. This reflects the context under which the
     * data was originally recorded.
     *
     * @param null|string|bool|\OpenEMR\FHIR\Versions\R4\Types\FHIRBooleanPrimitive|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBoolean $primarySource
     * @return static
     */
    public function setPrimarySource(null|string|bool|FHIRBooleanPrimitive|FHIRBoolean $primarySource): self
    {
        if (null === $primarySource) {
            unset($this->primarySource);
            return $this;
        }
        if (!($primarySource instanceof FHIRBoolean)) {
            $primarySource = new FHIRBoolean(value: $primarySource);
        }
        $this->primarySource = $primarySource;
        return $this;
    }

    /**
     * A concept that may be defined by a formal reference to a terminology or ontology
     * or may be provided by text.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * The source of the data when the report of the immunization event is not based on
     * information from the person who administered the vaccine.
     *
     * @return null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRCodeableConcept
     */
    public function getReportOrigin(): null|FHIRCodeableConcept
    {
        return $this->reportOrigin ?? null;
    }

    /**
     * A concept that may be defined by a formal reference to a terminology or ontology
     * or may be provided by text.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * The source of the data when the report of the immunization event is not based on
     * information from the person who administered the vaccine.
     *
     * @param null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRCodeableConcept $reportOrigin
     * @return static
     */
    public function setReportOrigin(null|FHIRCodeableConcept $reportOrigin): self
    {
        if (null === $reportOrigin) {
            unset($this->reportOrigin);
            return $this;
        }
        $this->reportOrigin = $reportOrigin;
        return $this;
    }

    /**
     * A reference from one resource to another.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * The service delivery location where the vaccine administration occurred.
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
     * The service delivery location where the vaccine administration occurred.
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
     * A reference from one resource to another.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * Name of vaccine manufacturer.
     *
     * @return null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRReference
     */
    public function getManufacturer(): null|FHIRReference
    {
        return $this->manufacturer ?? null;
    }

    /**
     * A reference from one resource to another.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * Name of vaccine manufacturer.
     *
     * @param null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRReference $manufacturer
     * @return static
     */
    public function setManufacturer(null|FHIRReference $manufacturer): self
    {
        if (null === $manufacturer) {
            unset($this->manufacturer);
            return $this;
        }
        $this->manufacturer = $manufacturer;
        return $this;
    }

    /**
     * A sequence of Unicode characters
     * Note that FHIR strings SHALL NOT exceed 1MB in size
     * If the element is present, it must have either a \@value, an \@id, or extensions
     *
     * Lot number of the vaccine product.
     *
     * @return null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRString
     */
    public function getLotNumber(): null|FHIRString
    {
        return $this->lotNumber ?? null;
    }

    /**
     * A sequence of Unicode characters
     * Note that FHIR strings SHALL NOT exceed 1MB in size
     * If the element is present, it must have either a \@value, an \@id, or extensions
     *
     * Lot number of the vaccine product.
     *
     * @param null|string|\OpenEMR\FHIR\Versions\R4\Types\FHIRStringPrimitive|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRString $lotNumber
     * @return static
     */
    public function setLotNumber(null|string|FHIRStringPrimitive|FHIRString $lotNumber): self
    {
        if (null === $lotNumber) {
            unset($this->lotNumber);
            return $this;
        }
        if (!($lotNumber instanceof FHIRString)) {
            $lotNumber = new FHIRString(value: $lotNumber);
        }
        $this->lotNumber = $lotNumber;
        return $this;
    }

    /**
     * A date or partial date (e.g. just year or year + month). There is no time zone.
     * The format is a union of the schema types gYear, gYearMonth and date. Dates
     * SHALL be valid dates.
     * If the element is present, it must have either a \@value, an \@id, or extensions
     *
     * Date vaccine batch expires.
     *
     * @return null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRDate
     */
    public function getExpirationDate(): null|FHIRDate
    {
        return $this->expirationDate ?? null;
    }

    /**
     * A date or partial date (e.g. just year or year + month). There is no time zone.
     * The format is a union of the schema types gYear, gYearMonth and date. Dates
     * SHALL be valid dates.
     * If the element is present, it must have either a \@value, an \@id, or extensions
     *
     * Date vaccine batch expires.
     *
     * @param null|string|\DateTimeInterface|\OpenEMR\FHIR\Versions\R4\Types\FHIRDatePrimitive|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRDate $expirationDate
     * @return static
     */
    public function setExpirationDate(null|string|\DateTimeInterface|FHIRDatePrimitive|FHIRDate $expirationDate): self
    {
        if (null === $expirationDate) {
            unset($this->expirationDate);
            return $this;
        }
        if (!($expirationDate instanceof FHIRDate)) {
            $expirationDate = new FHIRDate(value: $expirationDate);
        }
        $this->expirationDate = $expirationDate;
        return $this;
    }

    /**
     * A concept that may be defined by a formal reference to a terminology or ontology
     * or may be provided by text.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * Body site where vaccine was administered.
     *
     * @return null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRCodeableConcept
     */
    public function getSite(): null|FHIRCodeableConcept
    {
        return $this->site ?? null;
    }

    /**
     * A concept that may be defined by a formal reference to a terminology or ontology
     * or may be provided by text.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * Body site where vaccine was administered.
     *
     * @param null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRCodeableConcept $site
     * @return static
     */
    public function setSite(null|FHIRCodeableConcept $site): self
    {
        if (null === $site) {
            unset($this->site);
            return $this;
        }
        $this->site = $site;
        return $this;
    }

    /**
     * A concept that may be defined by a formal reference to a terminology or ontology
     * or may be provided by text.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * The path by which the vaccine product is taken into the body.
     *
     * @return null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRCodeableConcept
     */
    public function getRoute(): null|FHIRCodeableConcept
    {
        return $this->route ?? null;
    }

    /**
     * A concept that may be defined by a formal reference to a terminology or ontology
     * or may be provided by text.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * The path by which the vaccine product is taken into the body.
     *
     * @param null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRCodeableConcept $route
     * @return static
     */
    public function setRoute(null|FHIRCodeableConcept $route): self
    {
        if (null === $route) {
            unset($this->route);
            return $this;
        }
        $this->route = $route;
        return $this;
    }

    /**
     * A measured amount (or an amount that can potentially be measured). Note that
     * measured amounts include amounts that are not precisely quantified, including
     * amounts involving arbitrary units and floating currencies.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * The quantity of vaccine product that was administered.
     *
     * @return null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRQuantity
     */
    public function getDoseQuantity(): null|FHIRQuantity
    {
        return $this->doseQuantity ?? null;
    }

    /**
     * A measured amount (or an amount that can potentially be measured). Note that
     * measured amounts include amounts that are not precisely quantified, including
     * amounts involving arbitrary units and floating currencies.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * The quantity of vaccine product that was administered.
     *
     * @param null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRQuantity $doseQuantity
     * @return static
     */
    public function setDoseQuantity(null|FHIRQuantity $doseQuantity): self
    {
        if (null === $doseQuantity) {
            unset($this->doseQuantity);
            return $this;
        }
        $this->doseQuantity = $doseQuantity;
        return $this;
    }

    /**
     * Describes the event of a patient being administered a vaccine or a record of an
     * immunization as reported by a patient, a clinician or another party.
     *
     * Indicates who performed the immunization event.
     *
     * @return iterable<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRImmunization\FHIRImmunizationPerformer>
     */
    public function getPerformer(): array
    {
        return $this->performer ?? [];
    }

    /**
     * @return \ArrayIterator<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRImmunization\FHIRImmunizationPerformer>
     */
    public function getPerformerIterator(): iterable
    {
        if (!isset($this->performer)) {
            return new \EmptyIterator();
        }
        return new \ArrayIterator($this->performer);
    }

    /**
     * Describes the event of a patient being administered a vaccine or a record of an
     * immunization as reported by a patient, a clinician or another party.
     *
     * Indicates who performed the immunization event.
     *
     * @param \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRImmunization\FHIRImmunizationPerformer $performer
     * @return static
     */
    public function addPerformer(FHIRImmunizationPerformer $performer): self
    {
        if (!isset($this->performer)) {
            $this->performer = [];
        }
        $this->performer[] = $performer;
        return $this;
    }

    /**
     * Describes the event of a patient being administered a vaccine or a record of an
     * immunization as reported by a patient, a clinician or another party.
     *
     * Indicates who performed the immunization event.
     *
     * @param \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRImmunization\FHIRImmunizationPerformer ...$performer
     * @return static
     */
    public function setPerformer(FHIRImmunizationPerformer ...$performer): self
    {
        if ([] === $performer) {
            unset($this->performer);
            return $this;
        }
        $this->performer = $performer;
        return $this;
    }

    /**
     * A text note which also contains information about who made the statement and
     * when.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * Extra information about the immunization that is not conveyed by the other
     * attributes.
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
     * Extra information about the immunization that is not conveyed by the other
     * attributes.
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
     * Extra information about the immunization that is not conveyed by the other
     * attributes.
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

    /**
     * A concept that may be defined by a formal reference to a terminology or ontology
     * or may be provided by text.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * Reasons why the vaccine was administered.
     *
     * @return iterable<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRCodeableConcept>
     */
    public function getReasonCode(): array
    {
        return $this->reasonCode ?? [];
    }

    /**
     * @return \ArrayIterator<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRCodeableConcept>
     */
    public function getReasonCodeIterator(): iterable
    {
        if (!isset($this->reasonCode)) {
            return new \EmptyIterator();
        }
        return new \ArrayIterator($this->reasonCode);
    }

    /**
     * A concept that may be defined by a formal reference to a terminology or ontology
     * or may be provided by text.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * Reasons why the vaccine was administered.
     *
     * @param \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRCodeableConcept $reasonCode
     * @return static
     */
    public function addReasonCode(FHIRCodeableConcept $reasonCode): self
    {
        if (!isset($this->reasonCode)) {
            $this->reasonCode = [];
        }
        $this->reasonCode[] = $reasonCode;
        return $this;
    }

    /**
     * A concept that may be defined by a formal reference to a terminology or ontology
     * or may be provided by text.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * Reasons why the vaccine was administered.
     *
     * @param \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRCodeableConcept ...$reasonCode
     * @return static
     */
    public function setReasonCode(FHIRCodeableConcept ...$reasonCode): self
    {
        if ([] === $reasonCode) {
            unset($this->reasonCode);
            return $this;
        }
        $this->reasonCode = $reasonCode;
        return $this;
    }

    /**
     * A reference from one resource to another.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * Condition, Observation or DiagnosticReport that supports why the immunization
     * was administered.
     *
     * @return iterable<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRReference>
     */
    public function getReasonReference(): array
    {
        return $this->reasonReference ?? [];
    }

    /**
     * @return \ArrayIterator<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRReference>
     */
    public function getReasonReferenceIterator(): iterable
    {
        if (!isset($this->reasonReference)) {
            return new \EmptyIterator();
        }
        return new \ArrayIterator($this->reasonReference);
    }

    /**
     * A reference from one resource to another.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * Condition, Observation or DiagnosticReport that supports why the immunization
     * was administered.
     *
     * @param \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRReference $reasonReference
     * @return static
     */
    public function addReasonReference(FHIRReference $reasonReference): self
    {
        if (!isset($this->reasonReference)) {
            $this->reasonReference = [];
        }
        $this->reasonReference[] = $reasonReference;
        return $this;
    }

    /**
     * A reference from one resource to another.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * Condition, Observation or DiagnosticReport that supports why the immunization
     * was administered.
     *
     * @param \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRReference ...$reasonReference
     * @return static
     */
    public function setReasonReference(FHIRReference ...$reasonReference): self
    {
        if ([] === $reasonReference) {
            unset($this->reasonReference);
            return $this;
        }
        $this->reasonReference = $reasonReference;
        return $this;
    }

    /**
     * Value of "true" or "false"
     * If the element is present, it must have either a \@value, an \@id, or extensions
     *
     * Indication if a dose is considered to be subpotent. By default, a dose should be
     * considered to be potent.
     *
     * @return null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBoolean
     */
    public function getIsSubpotent(): null|FHIRBoolean
    {
        return $this->isSubpotent ?? null;
    }

    /**
     * Value of "true" or "false"
     * If the element is present, it must have either a \@value, an \@id, or extensions
     *
     * Indication if a dose is considered to be subpotent. By default, a dose should be
     * considered to be potent.
     *
     * @param null|string|bool|\OpenEMR\FHIR\Versions\R4\Types\FHIRBooleanPrimitive|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBoolean $isSubpotent
     * @return static
     */
    public function setIsSubpotent(null|string|bool|FHIRBooleanPrimitive|FHIRBoolean $isSubpotent): self
    {
        if (null === $isSubpotent) {
            unset($this->isSubpotent);
            return $this;
        }
        if (!($isSubpotent instanceof FHIRBoolean)) {
            $isSubpotent = new FHIRBoolean(value: $isSubpotent);
        }
        $this->isSubpotent = $isSubpotent;
        return $this;
    }

    /**
     * A concept that may be defined by a formal reference to a terminology or ontology
     * or may be provided by text.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * Reason why a dose is considered to be subpotent.
     *
     * @return iterable<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRCodeableConcept>
     */
    public function getSubpotentReason(): array
    {
        return $this->subpotentReason ?? [];
    }

    /**
     * @return \ArrayIterator<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRCodeableConcept>
     */
    public function getSubpotentReasonIterator(): iterable
    {
        if (!isset($this->subpotentReason)) {
            return new \EmptyIterator();
        }
        return new \ArrayIterator($this->subpotentReason);
    }

    /**
     * A concept that may be defined by a formal reference to a terminology or ontology
     * or may be provided by text.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * Reason why a dose is considered to be subpotent.
     *
     * @param \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRCodeableConcept $subpotentReason
     * @return static
     */
    public function addSubpotentReason(FHIRCodeableConcept $subpotentReason): self
    {
        if (!isset($this->subpotentReason)) {
            $this->subpotentReason = [];
        }
        $this->subpotentReason[] = $subpotentReason;
        return $this;
    }

    /**
     * A concept that may be defined by a formal reference to a terminology or ontology
     * or may be provided by text.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * Reason why a dose is considered to be subpotent.
     *
     * @param \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRCodeableConcept ...$subpotentReason
     * @return static
     */
    public function setSubpotentReason(FHIRCodeableConcept ...$subpotentReason): self
    {
        if ([] === $subpotentReason) {
            unset($this->subpotentReason);
            return $this;
        }
        $this->subpotentReason = $subpotentReason;
        return $this;
    }

    /**
     * Describes the event of a patient being administered a vaccine or a record of an
     * immunization as reported by a patient, a clinician or another party.
     *
     * Educational material presented to the patient (or guardian) at the time of
     * vaccine administration.
     *
     * @return iterable<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRImmunization\FHIRImmunizationEducation>
     */
    public function getEducation(): array
    {
        return $this->education ?? [];
    }

    /**
     * @return \ArrayIterator<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRImmunization\FHIRImmunizationEducation>
     */
    public function getEducationIterator(): iterable
    {
        if (!isset($this->education)) {
            return new \EmptyIterator();
        }
        return new \ArrayIterator($this->education);
    }

    /**
     * Describes the event of a patient being administered a vaccine or a record of an
     * immunization as reported by a patient, a clinician or another party.
     *
     * Educational material presented to the patient (or guardian) at the time of
     * vaccine administration.
     *
     * @param \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRImmunization\FHIRImmunizationEducation $education
     * @return static
     */
    public function addEducation(FHIRImmunizationEducation $education): self
    {
        if (!isset($this->education)) {
            $this->education = [];
        }
        $this->education[] = $education;
        return $this;
    }

    /**
     * Describes the event of a patient being administered a vaccine or a record of an
     * immunization as reported by a patient, a clinician or another party.
     *
     * Educational material presented to the patient (or guardian) at the time of
     * vaccine administration.
     *
     * @param \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRImmunization\FHIRImmunizationEducation ...$education
     * @return static
     */
    public function setEducation(FHIRImmunizationEducation ...$education): self
    {
        if ([] === $education) {
            unset($this->education);
            return $this;
        }
        $this->education = $education;
        return $this;
    }

    /**
     * A concept that may be defined by a formal reference to a terminology or ontology
     * or may be provided by text.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * Indicates a patient's eligibility for a funding program.
     *
     * @return iterable<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRCodeableConcept>
     */
    public function getProgramEligibility(): array
    {
        return $this->programEligibility ?? [];
    }

    /**
     * @return \ArrayIterator<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRCodeableConcept>
     */
    public function getProgramEligibilityIterator(): iterable
    {
        if (!isset($this->programEligibility)) {
            return new \EmptyIterator();
        }
        return new \ArrayIterator($this->programEligibility);
    }

    /**
     * A concept that may be defined by a formal reference to a terminology or ontology
     * or may be provided by text.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * Indicates a patient's eligibility for a funding program.
     *
     * @param \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRCodeableConcept $programEligibility
     * @return static
     */
    public function addProgramEligibility(FHIRCodeableConcept $programEligibility): self
    {
        if (!isset($this->programEligibility)) {
            $this->programEligibility = [];
        }
        $this->programEligibility[] = $programEligibility;
        return $this;
    }

    /**
     * A concept that may be defined by a formal reference to a terminology or ontology
     * or may be provided by text.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * Indicates a patient's eligibility for a funding program.
     *
     * @param \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRCodeableConcept ...$programEligibility
     * @return static
     */
    public function setProgramEligibility(FHIRCodeableConcept ...$programEligibility): self
    {
        if ([] === $programEligibility) {
            unset($this->programEligibility);
            return $this;
        }
        $this->programEligibility = $programEligibility;
        return $this;
    }

    /**
     * A concept that may be defined by a formal reference to a terminology or ontology
     * or may be provided by text.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * Indicates the source of the vaccine actually administered. This may be different
     * than the patient eligibility (e.g. the patient may be eligible for a publically
     * purchased vaccine but due to inventory issues, vaccine purchased with private
     * funds was actually administered).
     *
     * @return null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRCodeableConcept
     */
    public function getFundingSource(): null|FHIRCodeableConcept
    {
        return $this->fundingSource ?? null;
    }

    /**
     * A concept that may be defined by a formal reference to a terminology or ontology
     * or may be provided by text.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * Indicates the source of the vaccine actually administered. This may be different
     * than the patient eligibility (e.g. the patient may be eligible for a publically
     * purchased vaccine but due to inventory issues, vaccine purchased with private
     * funds was actually administered).
     *
     * @param null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRCodeableConcept $fundingSource
     * @return static
     */
    public function setFundingSource(null|FHIRCodeableConcept $fundingSource): self
    {
        if (null === $fundingSource) {
            unset($this->fundingSource);
            return $this;
        }
        $this->fundingSource = $fundingSource;
        return $this;
    }

    /**
     * Describes the event of a patient being administered a vaccine or a record of an
     * immunization as reported by a patient, a clinician or another party.
     *
     * Categorical data indicating that an adverse event is associated in time to an
     * immunization.
     *
     * @return iterable<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRImmunization\FHIRImmunizationReaction>
     */
    public function getReaction(): array
    {
        return $this->reaction ?? [];
    }

    /**
     * @return \ArrayIterator<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRImmunization\FHIRImmunizationReaction>
     */
    public function getReactionIterator(): iterable
    {
        if (!isset($this->reaction)) {
            return new \EmptyIterator();
        }
        return new \ArrayIterator($this->reaction);
    }

    /**
     * Describes the event of a patient being administered a vaccine or a record of an
     * immunization as reported by a patient, a clinician or another party.
     *
     * Categorical data indicating that an adverse event is associated in time to an
     * immunization.
     *
     * @param \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRImmunization\FHIRImmunizationReaction $reaction
     * @return static
     */
    public function addReaction(FHIRImmunizationReaction $reaction): self
    {
        if (!isset($this->reaction)) {
            $this->reaction = [];
        }
        $this->reaction[] = $reaction;
        return $this;
    }

    /**
     * Describes the event of a patient being administered a vaccine or a record of an
     * immunization as reported by a patient, a clinician or another party.
     *
     * Categorical data indicating that an adverse event is associated in time to an
     * immunization.
     *
     * @param \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRImmunization\FHIRImmunizationReaction ...$reaction
     * @return static
     */
    public function setReaction(FHIRImmunizationReaction ...$reaction): self
    {
        if ([] === $reaction) {
            unset($this->reaction);
            return $this;
        }
        $this->reaction = $reaction;
        return $this;
    }

    /**
     * Describes the event of a patient being administered a vaccine or a record of an
     * immunization as reported by a patient, a clinician or another party.
     *
     * The protocol (set of recommendations) being followed by the provider who
     * administered the dose.
     *
     * @return iterable<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRImmunization\FHIRImmunizationProtocolApplied>
     */
    public function getProtocolApplied(): array
    {
        return $this->protocolApplied ?? [];
    }

    /**
     * @return \ArrayIterator<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRImmunization\FHIRImmunizationProtocolApplied>
     */
    public function getProtocolAppliedIterator(): iterable
    {
        if (!isset($this->protocolApplied)) {
            return new \EmptyIterator();
        }
        return new \ArrayIterator($this->protocolApplied);
    }

    /**
     * Describes the event of a patient being administered a vaccine or a record of an
     * immunization as reported by a patient, a clinician or another party.
     *
     * The protocol (set of recommendations) being followed by the provider who
     * administered the dose.
     *
     * @param \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRImmunization\FHIRImmunizationProtocolApplied $protocolApplied
     * @return static
     */
    public function addProtocolApplied(FHIRImmunizationProtocolApplied $protocolApplied): self
    {
        if (!isset($this->protocolApplied)) {
            $this->protocolApplied = [];
        }
        $this->protocolApplied[] = $protocolApplied;
        return $this;
    }

    /**
     * Describes the event of a patient being administered a vaccine or a record of an
     * immunization as reported by a patient, a clinician or another party.
     *
     * The protocol (set of recommendations) being followed by the provider who
     * administered the dose.
     *
     * @param \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRImmunization\FHIRImmunizationProtocolApplied ...$protocolApplied
     * @return static
     */
    public function setProtocolApplied(FHIRImmunizationProtocolApplied ...$protocolApplied): self
    {
        if ([] === $protocolApplied) {
            unset($this->protocolApplied);
            return $this;
        }
        $this->protocolApplied = $protocolApplied;
        return $this;
    }

    /* class_default.php:201 */
    /**
     * @param string|\SimpleXMLElement $element
     * @param null|\OpenEMR\FHIR\Encoding\UnserializeConfig $config
     * @param null|\OpenEMR\FHIR\Versions\R4\Types\FHIRResource\FHIRDomainResource\FHIRImmunization $type
     * @return \OpenEMR\FHIR\Versions\R4\Types\FHIRResource\FHIRDomainResource\FHIRImmunization
     * @throws \Exception
     */
    public static function xmlUnserialize(string|\SimpleXMLElement $element,
                                          null|UnserializeConfig $config = null,
                                          null|ResourceTypeInterface $type = null): self
    {
        if (null === $type) {
            $type = new static();
        } else if (!($type instanceof FHIRImmunization)) {
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
            } else if (self::FIELD_STATUS === $cen) {
                $type->setStatus(FHIRImmunizationStatusCodes::xmlUnserialize($ce, $config));
            } else if (self::FIELD_STATUS_REASON === $cen) {
                $type->setStatusReason(FHIRCodeableConcept::xmlUnserialize($ce, $config));
            } else if (self::FIELD_VACCINE_CODE === $cen) {
                $type->setVaccineCode(FHIRCodeableConcept::xmlUnserialize($ce, $config));
            } else if (self::FIELD_PATIENT === $cen) {
                $type->setPatient(FHIRReference::xmlUnserialize($ce, $config));
            } else if (self::FIELD_ENCOUNTER === $cen) {
                $type->setEncounter(FHIRReference::xmlUnserialize($ce, $config));
            } else if (self::FIELD_OCCURRENCE_DATE_TIME === $cen) {
                $type->setOccurrenceDateTime(FHIRDateTime::xmlUnserialize($ce, $config));
            } else if (self::FIELD_OCCURRENCE_STRING === $cen) {
                $type->setOccurrenceString(FHIRString::xmlUnserialize($ce, $config));
            } else if (self::FIELD_RECORDED === $cen) {
                $type->setRecorded(FHIRDateTime::xmlUnserialize($ce, $config));
            } else if (self::FIELD_PRIMARY_SOURCE === $cen) {
                $type->setPrimarySource(FHIRBoolean::xmlUnserialize($ce, $config));
            } else if (self::FIELD_REPORT_ORIGIN === $cen) {
                $type->setReportOrigin(FHIRCodeableConcept::xmlUnserialize($ce, $config));
            } else if (self::FIELD_LOCATION === $cen) {
                $type->setLocation(FHIRReference::xmlUnserialize($ce, $config));
            } else if (self::FIELD_MANUFACTURER === $cen) {
                $type->setManufacturer(FHIRReference::xmlUnserialize($ce, $config));
            } else if (self::FIELD_LOT_NUMBER === $cen) {
                $type->setLotNumber(FHIRString::xmlUnserialize($ce, $config));
            } else if (self::FIELD_EXPIRATION_DATE === $cen) {
                $type->setExpirationDate(FHIRDate::xmlUnserialize($ce, $config));
            } else if (self::FIELD_SITE === $cen) {
                $type->setSite(FHIRCodeableConcept::xmlUnserialize($ce, $config));
            } else if (self::FIELD_ROUTE === $cen) {
                $type->setRoute(FHIRCodeableConcept::xmlUnserialize($ce, $config));
            } else if (self::FIELD_DOSE_QUANTITY === $cen) {
                $type->setDoseQuantity(FHIRQuantity::xmlUnserialize($ce, $config));
            } else if (self::FIELD_PERFORMER === $cen) {
                $type->addPerformer(FHIRImmunizationPerformer::xmlUnserialize($ce, $config));
            } else if (self::FIELD_NOTE === $cen) {
                $type->addNote(FHIRAnnotation::xmlUnserialize($ce, $config));
            } else if (self::FIELD_REASON_CODE === $cen) {
                $type->addReasonCode(FHIRCodeableConcept::xmlUnserialize($ce, $config));
            } else if (self::FIELD_REASON_REFERENCE === $cen) {
                $type->addReasonReference(FHIRReference::xmlUnserialize($ce, $config));
            } else if (self::FIELD_IS_SUBPOTENT === $cen) {
                $type->setIsSubpotent(FHIRBoolean::xmlUnserialize($ce, $config));
            } else if (self::FIELD_SUBPOTENT_REASON === $cen) {
                $type->addSubpotentReason(FHIRCodeableConcept::xmlUnserialize($ce, $config));
            } else if (self::FIELD_EDUCATION === $cen) {
                $type->addEducation(FHIRImmunizationEducation::xmlUnserialize($ce, $config));
            } else if (self::FIELD_PROGRAM_ELIGIBILITY === $cen) {
                $type->addProgramEligibility(FHIRCodeableConcept::xmlUnserialize($ce, $config));
            } else if (self::FIELD_FUNDING_SOURCE === $cen) {
                $type->setFundingSource(FHIRCodeableConcept::xmlUnserialize($ce, $config));
            } else if (self::FIELD_REACTION === $cen) {
                $type->addReaction(FHIRImmunizationReaction::xmlUnserialize($ce, $config));
            } else if (self::FIELD_PROTOCOL_APPLIED === $cen) {
                $type->addProtocolApplied(FHIRImmunizationProtocolApplied::xmlUnserialize($ce, $config));
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
        if (isset($attributes[self::FIELD_STATUS])) {
            if (isset($type->status)) {
                $type->status->setValue((string)$attributes[self::FIELD_STATUS]);
            } else {
                $type->setStatus((string)$attributes[self::FIELD_STATUS]);
            }
            $type->_setXMLFieldValueLocation(self::FIELD_STATUS, ValueXMLLocationEnum::PARENT_ATTRIBUTE);
        }
        if (isset($attributes[self::FIELD_OCCURRENCE_DATE_TIME])) {
            if (isset($type->occurrenceDateTime)) {
                $type->occurrenceDateTime->setValue((string)$attributes[self::FIELD_OCCURRENCE_DATE_TIME]);
            } else {
                $type->setOccurrenceDateTime((string)$attributes[self::FIELD_OCCURRENCE_DATE_TIME]);
            }
            $type->_setXMLFieldValueLocation(self::FIELD_OCCURRENCE_DATE_TIME, ValueXMLLocationEnum::PARENT_ATTRIBUTE);
        }
        if (isset($attributes[self::FIELD_OCCURRENCE_STRING])) {
            if (isset($type->occurrenceString)) {
                $type->occurrenceString->setValue((string)$attributes[self::FIELD_OCCURRENCE_STRING]);
            } else {
                $type->setOccurrenceString((string)$attributes[self::FIELD_OCCURRENCE_STRING]);
            }
            $type->_setXMLFieldValueLocation(self::FIELD_OCCURRENCE_STRING, ValueXMLLocationEnum::PARENT_ATTRIBUTE);
        }
        if (isset($attributes[self::FIELD_RECORDED])) {
            if (isset($type->recorded)) {
                $type->recorded->setValue((string)$attributes[self::FIELD_RECORDED]);
            } else {
                $type->setRecorded((string)$attributes[self::FIELD_RECORDED]);
            }
            $type->_setXMLFieldValueLocation(self::FIELD_RECORDED, ValueXMLLocationEnum::PARENT_ATTRIBUTE);
        }
        if (isset($attributes[self::FIELD_PRIMARY_SOURCE])) {
            if (isset($type->primarySource)) {
                $type->primarySource->setValue((string)$attributes[self::FIELD_PRIMARY_SOURCE]);
            } else {
                $type->setPrimarySource((string)$attributes[self::FIELD_PRIMARY_SOURCE]);
            }
            $type->_setXMLFieldValueLocation(self::FIELD_PRIMARY_SOURCE, ValueXMLLocationEnum::PARENT_ATTRIBUTE);
        }
        if (isset($attributes[self::FIELD_LOT_NUMBER])) {
            if (isset($type->lotNumber)) {
                $type->lotNumber->setValue((string)$attributes[self::FIELD_LOT_NUMBER]);
            } else {
                $type->setLotNumber((string)$attributes[self::FIELD_LOT_NUMBER]);
            }
            $type->_setXMLFieldValueLocation(self::FIELD_LOT_NUMBER, ValueXMLLocationEnum::PARENT_ATTRIBUTE);
        }
        if (isset($attributes[self::FIELD_EXPIRATION_DATE])) {
            if (isset($type->expirationDate)) {
                $type->expirationDate->setValue((string)$attributes[self::FIELD_EXPIRATION_DATE]);
            } else {
                $type->setExpirationDate((string)$attributes[self::FIELD_EXPIRATION_DATE]);
            }
            $type->_setXMLFieldValueLocation(self::FIELD_EXPIRATION_DATE, ValueXMLLocationEnum::PARENT_ATTRIBUTE);
        }
        if (isset($attributes[self::FIELD_IS_SUBPOTENT])) {
            if (isset($type->isSubpotent)) {
                $type->isSubpotent->setValue((string)$attributes[self::FIELD_IS_SUBPOTENT]);
            } else {
                $type->setIsSubpotent((string)$attributes[self::FIELD_IS_SUBPOTENT]);
            }
            $type->_setXMLFieldValueLocation(self::FIELD_IS_SUBPOTENT, ValueXMLLocationEnum::PARENT_ATTRIBUTE);
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
            $xw->openRootNode('Immunization', $this->_getSourceXMLNS());
        }
        if (isset($this->status) && ValueXMLLocationEnum::PARENT_ATTRIBUTE === $this->_valueXMLLocations[self::FIELD_STATUS]) {
            $xw->writeAttribute(self::FIELD_STATUS, $this->status->_getValueAsString());
        }
        if (isset($this->occurrenceDateTime) && ValueXMLLocationEnum::PARENT_ATTRIBUTE === $this->_valueXMLLocations[self::FIELD_OCCURRENCE_DATE_TIME]) {
            $xw->writeAttribute(self::FIELD_OCCURRENCE_DATE_TIME, $this->occurrenceDateTime->_getValueAsString());
        }
        if (isset($this->occurrenceString) && ValueXMLLocationEnum::PARENT_ATTRIBUTE === $this->_valueXMLLocations[self::FIELD_OCCURRENCE_STRING]) {
            $xw->writeAttribute(self::FIELD_OCCURRENCE_STRING, $this->occurrenceString->_getValueAsString());
        }
        if (isset($this->recorded) && ValueXMLLocationEnum::PARENT_ATTRIBUTE === $this->_valueXMLLocations[self::FIELD_RECORDED]) {
            $xw->writeAttribute(self::FIELD_RECORDED, $this->recorded->_getValueAsString());
        }
        if (isset($this->primarySource) && ValueXMLLocationEnum::PARENT_ATTRIBUTE === $this->_valueXMLLocations[self::FIELD_PRIMARY_SOURCE]) {
            $xw->writeAttribute(self::FIELD_PRIMARY_SOURCE, $this->primarySource->_getValueAsString());
        }
        if (isset($this->lotNumber) && ValueXMLLocationEnum::PARENT_ATTRIBUTE === $this->_valueXMLLocations[self::FIELD_LOT_NUMBER]) {
            $xw->writeAttribute(self::FIELD_LOT_NUMBER, $this->lotNumber->_getValueAsString());
        }
        if (isset($this->expirationDate) && ValueXMLLocationEnum::PARENT_ATTRIBUTE === $this->_valueXMLLocations[self::FIELD_EXPIRATION_DATE]) {
            $xw->writeAttribute(self::FIELD_EXPIRATION_DATE, $this->expirationDate->_getValueAsString());
        }
        if (isset($this->isSubpotent) && ValueXMLLocationEnum::PARENT_ATTRIBUTE === $this->_valueXMLLocations[self::FIELD_IS_SUBPOTENT]) {
            $xw->writeAttribute(self::FIELD_IS_SUBPOTENT, $this->isSubpotent->_getValueAsString());
        }
        parent::xmlSerialize($xw, $config);
        if (isset($this->identifier)) {
            foreach ($this->identifier as $v) {
                $xw->startElement(self::FIELD_IDENTIFIER);
                $v->xmlSerialize($xw, $config);
                $xw->endElement();
            }
        }
        if (isset($this->status)
            && (ValueXMLLocationEnum::PARENT_ATTRIBUTE !== $this->_valueXMLLocations[self::FIELD_STATUS]
                || $this->status->_nonValueFieldDefined())) {
            $xw->startElement(self::FIELD_STATUS);
            $this->status->xmlSerialize($xw, $config, $this->_valueXMLLocations[self::FIELD_STATUS]);
            $xw->endElement();
        }
        if (isset($this->statusReason)) {
            $xw->startElement(self::FIELD_STATUS_REASON);
            $this->statusReason->xmlSerialize($xw, $config);
            $xw->endElement();
        }
        if (isset($this->vaccineCode)) {
            $xw->startElement(self::FIELD_VACCINE_CODE);
            $this->vaccineCode->xmlSerialize($xw, $config);
            $xw->endElement();
        }
        if (isset($this->patient)) {
            $xw->startElement(self::FIELD_PATIENT);
            $this->patient->xmlSerialize($xw, $config);
            $xw->endElement();
        }
        if (isset($this->encounter)) {
            $xw->startElement(self::FIELD_ENCOUNTER);
            $this->encounter->xmlSerialize($xw, $config);
            $xw->endElement();
        }
        if (isset($this->occurrenceDateTime)
            && (ValueXMLLocationEnum::PARENT_ATTRIBUTE !== $this->_valueXMLLocations[self::FIELD_OCCURRENCE_DATE_TIME]
                || $this->occurrenceDateTime->_nonValueFieldDefined())) {
            $xw->startElement(self::FIELD_OCCURRENCE_DATE_TIME);
            $this->occurrenceDateTime->xmlSerialize($xw, $config, $this->_valueXMLLocations[self::FIELD_OCCURRENCE_DATE_TIME]);
            $xw->endElement();
        }
        if (isset($this->occurrenceString)
            && (ValueXMLLocationEnum::PARENT_ATTRIBUTE !== $this->_valueXMLLocations[self::FIELD_OCCURRENCE_STRING]
                || $this->occurrenceString->_nonValueFieldDefined())) {
            $xw->startElement(self::FIELD_OCCURRENCE_STRING);
            $this->occurrenceString->xmlSerialize($xw, $config, $this->_valueXMLLocations[self::FIELD_OCCURRENCE_STRING]);
            $xw->endElement();
        }
        if (isset($this->recorded)
            && (ValueXMLLocationEnum::PARENT_ATTRIBUTE !== $this->_valueXMLLocations[self::FIELD_RECORDED]
                || $this->recorded->_nonValueFieldDefined())) {
            $xw->startElement(self::FIELD_RECORDED);
            $this->recorded->xmlSerialize($xw, $config, $this->_valueXMLLocations[self::FIELD_RECORDED]);
            $xw->endElement();
        }
        if (isset($this->primarySource)
            && (ValueXMLLocationEnum::PARENT_ATTRIBUTE !== $this->_valueXMLLocations[self::FIELD_PRIMARY_SOURCE]
                || $this->primarySource->_nonValueFieldDefined())) {
            $xw->startElement(self::FIELD_PRIMARY_SOURCE);
            $this->primarySource->xmlSerialize($xw, $config, $this->_valueXMLLocations[self::FIELD_PRIMARY_SOURCE]);
            $xw->endElement();
        }
        if (isset($this->reportOrigin)) {
            $xw->startElement(self::FIELD_REPORT_ORIGIN);
            $this->reportOrigin->xmlSerialize($xw, $config);
            $xw->endElement();
        }
        if (isset($this->location)) {
            $xw->startElement(self::FIELD_LOCATION);
            $this->location->xmlSerialize($xw, $config);
            $xw->endElement();
        }
        if (isset($this->manufacturer)) {
            $xw->startElement(self::FIELD_MANUFACTURER);
            $this->manufacturer->xmlSerialize($xw, $config);
            $xw->endElement();
        }
        if (isset($this->lotNumber)
            && (ValueXMLLocationEnum::PARENT_ATTRIBUTE !== $this->_valueXMLLocations[self::FIELD_LOT_NUMBER]
                || $this->lotNumber->_nonValueFieldDefined())) {
            $xw->startElement(self::FIELD_LOT_NUMBER);
            $this->lotNumber->xmlSerialize($xw, $config, $this->_valueXMLLocations[self::FIELD_LOT_NUMBER]);
            $xw->endElement();
        }
        if (isset($this->expirationDate)
            && (ValueXMLLocationEnum::PARENT_ATTRIBUTE !== $this->_valueXMLLocations[self::FIELD_EXPIRATION_DATE]
                || $this->expirationDate->_nonValueFieldDefined())) {
            $xw->startElement(self::FIELD_EXPIRATION_DATE);
            $this->expirationDate->xmlSerialize($xw, $config, $this->_valueXMLLocations[self::FIELD_EXPIRATION_DATE]);
            $xw->endElement();
        }
        if (isset($this->site)) {
            $xw->startElement(self::FIELD_SITE);
            $this->site->xmlSerialize($xw, $config);
            $xw->endElement();
        }
        if (isset($this->route)) {
            $xw->startElement(self::FIELD_ROUTE);
            $this->route->xmlSerialize($xw, $config);
            $xw->endElement();
        }
        if (isset($this->doseQuantity)) {
            $xw->startElement(self::FIELD_DOSE_QUANTITY);
            $this->doseQuantity->xmlSerialize($xw, $config);
            $xw->endElement();
        }
        if (isset($this->performer)) {
            foreach ($this->performer as $v) {
                $xw->startElement(self::FIELD_PERFORMER);
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
        if (isset($this->reasonCode)) {
            foreach ($this->reasonCode as $v) {
                $xw->startElement(self::FIELD_REASON_CODE);
                $v->xmlSerialize($xw, $config);
                $xw->endElement();
            }
        }
        if (isset($this->reasonReference)) {
            foreach ($this->reasonReference as $v) {
                $xw->startElement(self::FIELD_REASON_REFERENCE);
                $v->xmlSerialize($xw, $config);
                $xw->endElement();
            }
        }
        if (isset($this->isSubpotent)
            && (ValueXMLLocationEnum::PARENT_ATTRIBUTE !== $this->_valueXMLLocations[self::FIELD_IS_SUBPOTENT]
                || $this->isSubpotent->_nonValueFieldDefined())) {
            $xw->startElement(self::FIELD_IS_SUBPOTENT);
            $this->isSubpotent->xmlSerialize($xw, $config, $this->_valueXMLLocations[self::FIELD_IS_SUBPOTENT]);
            $xw->endElement();
        }
        if (isset($this->subpotentReason)) {
            foreach ($this->subpotentReason as $v) {
                $xw->startElement(self::FIELD_SUBPOTENT_REASON);
                $v->xmlSerialize($xw, $config);
                $xw->endElement();
            }
        }
        if (isset($this->education)) {
            foreach ($this->education as $v) {
                $xw->startElement(self::FIELD_EDUCATION);
                $v->xmlSerialize($xw, $config);
                $xw->endElement();
            }
        }
        if (isset($this->programEligibility)) {
            foreach ($this->programEligibility as $v) {
                $xw->startElement(self::FIELD_PROGRAM_ELIGIBILITY);
                $v->xmlSerialize($xw, $config);
                $xw->endElement();
            }
        }
        if (isset($this->fundingSource)) {
            $xw->startElement(self::FIELD_FUNDING_SOURCE);
            $this->fundingSource->xmlSerialize($xw, $config);
            $xw->endElement();
        }
        if (isset($this->reaction)) {
            foreach ($this->reaction as $v) {
                $xw->startElement(self::FIELD_REACTION);
                $v->xmlSerialize($xw, $config);
                $xw->endElement();
            }
        }
        if (isset($this->protocolApplied)) {
            foreach ($this->protocolApplied as $v) {
                $xw->startElement(self::FIELD_PROTOCOL_APPLIED);
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
     * @param null|\OpenEMR\FHIR\Versions\R4\Types\FHIRResource\FHIRDomainResource\FHIRImmunization $type
     * @return \OpenEMR\FHIR\Versions\R4\Types\FHIRResource\FHIRDomainResource\FHIRImmunization
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
        } else if (!($type instanceof FHIRImmunization)) {
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
        if (isset($decoded->status)
            || isset($decoded->_status)
            || property_exists($decoded, self::FIELD_STATUS)
            || property_exists($decoded, self::FIELD_STATUS_EXT)) {
            $v = $decoded->_status ?? new \stdClass();
            $v->value = $decoded->status ?? null;
            $type->setStatus(FHIRImmunizationStatusCodes::jsonUnserialize($v, $config));
        }
        if (isset($decoded->statusReason) || property_exists($decoded, self::FIELD_STATUS_REASON)) {
            if (is_array($decoded->statusReason)) {
                $type->setStatusReason(FHIRCodeableConcept::jsonUnserialize(reset($decoded->statusReason), $config));
            } else {
                $type->setStatusReason(FHIRCodeableConcept::jsonUnserialize($decoded->statusReason, $config));
            }
        }
        if (isset($decoded->vaccineCode) || property_exists($decoded, self::FIELD_VACCINE_CODE)) {
            if (is_array($decoded->vaccineCode)) {
                $type->setVaccineCode(FHIRCodeableConcept::jsonUnserialize(reset($decoded->vaccineCode), $config));
            } else {
                $type->setVaccineCode(FHIRCodeableConcept::jsonUnserialize($decoded->vaccineCode, $config));
            }
        }
        if (isset($decoded->patient) || property_exists($decoded, self::FIELD_PATIENT)) {
            if (is_array($decoded->patient)) {
                $type->setPatient(FHIRReference::jsonUnserialize(reset($decoded->patient), $config));
            } else {
                $type->setPatient(FHIRReference::jsonUnserialize($decoded->patient, $config));
            }
        }
        if (isset($decoded->encounter) || property_exists($decoded, self::FIELD_ENCOUNTER)) {
            if (is_array($decoded->encounter)) {
                $type->setEncounter(FHIRReference::jsonUnserialize(reset($decoded->encounter), $config));
            } else {
                $type->setEncounter(FHIRReference::jsonUnserialize($decoded->encounter, $config));
            }
        }
        if (isset($decoded->occurrenceDateTime)
            || isset($decoded->_occurrenceDateTime)
            || property_exists($decoded, self::FIELD_OCCURRENCE_DATE_TIME)
            || property_exists($decoded, self::FIELD_OCCURRENCE_DATE_TIME_EXT)) {
            $v = $decoded->_occurrenceDateTime ?? new \stdClass();
            $v->value = $decoded->occurrenceDateTime ?? null;
            $type->setOccurrenceDateTime(FHIRDateTime::jsonUnserialize($v, $config));
        }
        if (isset($decoded->occurrenceString)
            || isset($decoded->_occurrenceString)
            || property_exists($decoded, self::FIELD_OCCURRENCE_STRING)
            || property_exists($decoded, self::FIELD_OCCURRENCE_STRING_EXT)) {
            $v = $decoded->_occurrenceString ?? new \stdClass();
            $v->value = $decoded->occurrenceString ?? null;
            $type->setOccurrenceString(FHIRString::jsonUnserialize($v, $config));
        }
        if (isset($decoded->recorded)
            || isset($decoded->_recorded)
            || property_exists($decoded, self::FIELD_RECORDED)
            || property_exists($decoded, self::FIELD_RECORDED_EXT)) {
            $v = $decoded->_recorded ?? new \stdClass();
            $v->value = $decoded->recorded ?? null;
            $type->setRecorded(FHIRDateTime::jsonUnserialize($v, $config));
        }
        if (isset($decoded->primarySource)
            || isset($decoded->_primarySource)
            || property_exists($decoded, self::FIELD_PRIMARY_SOURCE)
            || property_exists($decoded, self::FIELD_PRIMARY_SOURCE_EXT)) {
            $v = $decoded->_primarySource ?? new \stdClass();
            $v->value = $decoded->primarySource ?? null;
            $type->setPrimarySource(FHIRBoolean::jsonUnserialize($v, $config));
        }
        if (isset($decoded->reportOrigin) || property_exists($decoded, self::FIELD_REPORT_ORIGIN)) {
            if (is_array($decoded->reportOrigin)) {
                $type->setReportOrigin(FHIRCodeableConcept::jsonUnserialize(reset($decoded->reportOrigin), $config));
            } else {
                $type->setReportOrigin(FHIRCodeableConcept::jsonUnserialize($decoded->reportOrigin, $config));
            }
        }
        if (isset($decoded->location) || property_exists($decoded, self::FIELD_LOCATION)) {
            if (is_array($decoded->location)) {
                $type->setLocation(FHIRReference::jsonUnserialize(reset($decoded->location), $config));
            } else {
                $type->setLocation(FHIRReference::jsonUnserialize($decoded->location, $config));
            }
        }
        if (isset($decoded->manufacturer) || property_exists($decoded, self::FIELD_MANUFACTURER)) {
            if (is_array($decoded->manufacturer)) {
                $type->setManufacturer(FHIRReference::jsonUnserialize(reset($decoded->manufacturer), $config));
            } else {
                $type->setManufacturer(FHIRReference::jsonUnserialize($decoded->manufacturer, $config));
            }
        }
        if (isset($decoded->lotNumber)
            || isset($decoded->_lotNumber)
            || property_exists($decoded, self::FIELD_LOT_NUMBER)
            || property_exists($decoded, self::FIELD_LOT_NUMBER_EXT)) {
            $v = $decoded->_lotNumber ?? new \stdClass();
            $v->value = $decoded->lotNumber ?? null;
            $type->setLotNumber(FHIRString::jsonUnserialize($v, $config));
        }
        if (isset($decoded->expirationDate)
            || isset($decoded->_expirationDate)
            || property_exists($decoded, self::FIELD_EXPIRATION_DATE)
            || property_exists($decoded, self::FIELD_EXPIRATION_DATE_EXT)) {
            $v = $decoded->_expirationDate ?? new \stdClass();
            $v->value = $decoded->expirationDate ?? null;
            $type->setExpirationDate(FHIRDate::jsonUnserialize($v, $config));
        }
        if (isset($decoded->site) || property_exists($decoded, self::FIELD_SITE)) {
            if (is_array($decoded->site)) {
                $type->setSite(FHIRCodeableConcept::jsonUnserialize(reset($decoded->site), $config));
            } else {
                $type->setSite(FHIRCodeableConcept::jsonUnserialize($decoded->site, $config));
            }
        }
        if (isset($decoded->route) || property_exists($decoded, self::FIELD_ROUTE)) {
            if (is_array($decoded->route)) {
                $type->setRoute(FHIRCodeableConcept::jsonUnserialize(reset($decoded->route), $config));
            } else {
                $type->setRoute(FHIRCodeableConcept::jsonUnserialize($decoded->route, $config));
            }
        }
        if (isset($decoded->doseQuantity) || property_exists($decoded, self::FIELD_DOSE_QUANTITY)) {
            if (is_array($decoded->doseQuantity)) {
                $type->setDoseQuantity(FHIRQuantity::jsonUnserialize(reset($decoded->doseQuantity), $config));
            } else {
                $type->setDoseQuantity(FHIRQuantity::jsonUnserialize($decoded->doseQuantity, $config));
            }
        }
        if (isset($decoded->performer) || property_exists($decoded, self::FIELD_PERFORMER)) {
            if (is_object($decoded->performer)) {
                $vals = [$decoded->performer];
                $type->_setJSONFieldElideSingletonArray(self::FIELD_PERFORMER, true);
            } else {
                $vals = $decoded->performer;
            }
            foreach($vals as $v) {
                $type->addPerformer(FHIRImmunizationPerformer::jsonUnserialize($v, $config));
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
        if (isset($decoded->reasonCode) || property_exists($decoded, self::FIELD_REASON_CODE)) {
            if (is_object($decoded->reasonCode)) {
                $vals = [$decoded->reasonCode];
                $type->_setJSONFieldElideSingletonArray(self::FIELD_REASON_CODE, true);
            } else {
                $vals = $decoded->reasonCode;
            }
            foreach($vals as $v) {
                $type->addReasonCode(FHIRCodeableConcept::jsonUnserialize($v, $config));
            }
        }
        if (isset($decoded->reasonReference) || property_exists($decoded, self::FIELD_REASON_REFERENCE)) {
            if (is_object($decoded->reasonReference)) {
                $vals = [$decoded->reasonReference];
                $type->_setJSONFieldElideSingletonArray(self::FIELD_REASON_REFERENCE, true);
            } else {
                $vals = $decoded->reasonReference;
            }
            foreach($vals as $v) {
                $type->addReasonReference(FHIRReference::jsonUnserialize($v, $config));
            }
        }
        if (isset($decoded->isSubpotent)
            || isset($decoded->_isSubpotent)
            || property_exists($decoded, self::FIELD_IS_SUBPOTENT)
            || property_exists($decoded, self::FIELD_IS_SUBPOTENT_EXT)) {
            $v = $decoded->_isSubpotent ?? new \stdClass();
            $v->value = $decoded->isSubpotent ?? null;
            $type->setIsSubpotent(FHIRBoolean::jsonUnserialize($v, $config));
        }
        if (isset($decoded->subpotentReason) || property_exists($decoded, self::FIELD_SUBPOTENT_REASON)) {
            if (is_object($decoded->subpotentReason)) {
                $vals = [$decoded->subpotentReason];
                $type->_setJSONFieldElideSingletonArray(self::FIELD_SUBPOTENT_REASON, true);
            } else {
                $vals = $decoded->subpotentReason;
            }
            foreach($vals as $v) {
                $type->addSubpotentReason(FHIRCodeableConcept::jsonUnserialize($v, $config));
            }
        }
        if (isset($decoded->education) || property_exists($decoded, self::FIELD_EDUCATION)) {
            if (is_object($decoded->education)) {
                $vals = [$decoded->education];
                $type->_setJSONFieldElideSingletonArray(self::FIELD_EDUCATION, true);
            } else {
                $vals = $decoded->education;
            }
            foreach($vals as $v) {
                $type->addEducation(FHIRImmunizationEducation::jsonUnserialize($v, $config));
            }
        }
        if (isset($decoded->programEligibility) || property_exists($decoded, self::FIELD_PROGRAM_ELIGIBILITY)) {
            if (is_object($decoded->programEligibility)) {
                $vals = [$decoded->programEligibility];
                $type->_setJSONFieldElideSingletonArray(self::FIELD_PROGRAM_ELIGIBILITY, true);
            } else {
                $vals = $decoded->programEligibility;
            }
            foreach($vals as $v) {
                $type->addProgramEligibility(FHIRCodeableConcept::jsonUnserialize($v, $config));
            }
        }
        if (isset($decoded->fundingSource) || property_exists($decoded, self::FIELD_FUNDING_SOURCE)) {
            if (is_array($decoded->fundingSource)) {
                $type->setFundingSource(FHIRCodeableConcept::jsonUnserialize(reset($decoded->fundingSource), $config));
            } else {
                $type->setFundingSource(FHIRCodeableConcept::jsonUnserialize($decoded->fundingSource, $config));
            }
        }
        if (isset($decoded->reaction) || property_exists($decoded, self::FIELD_REACTION)) {
            if (is_object($decoded->reaction)) {
                $vals = [$decoded->reaction];
                $type->_setJSONFieldElideSingletonArray(self::FIELD_REACTION, true);
            } else {
                $vals = $decoded->reaction;
            }
            foreach($vals as $v) {
                $type->addReaction(FHIRImmunizationReaction::jsonUnserialize($v, $config));
            }
        }
        if (isset($decoded->protocolApplied) || property_exists($decoded, self::FIELD_PROTOCOL_APPLIED)) {
            if (is_object($decoded->protocolApplied)) {
                $vals = [$decoded->protocolApplied];
                $type->_setJSONFieldElideSingletonArray(self::FIELD_PROTOCOL_APPLIED, true);
            } else {
                $vals = $decoded->protocolApplied;
            }
            foreach($vals as $v) {
                $type->addProtocolApplied(FHIRImmunizationProtocolApplied::jsonUnserialize($v, $config));
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
        if (isset($this->status)) {
            if (null !== ($val = $this->status->getValue())) {
                $out->status = $val;
            }
            if ($this->status->_nonValueFieldDefined()) {
                $ext = $this->status->jsonSerialize();
                unset($ext->value);
                $out->_status = $ext;
            }
        }
        if (isset($this->statusReason)) {
            $out->statusReason = $this->statusReason;
        }
        if (isset($this->vaccineCode)) {
            $out->vaccineCode = $this->vaccineCode;
        }
        if (isset($this->patient)) {
            $out->patient = $this->patient;
        }
        if (isset($this->encounter)) {
            $out->encounter = $this->encounter;
        }
        if (isset($this->occurrenceDateTime)) {
            if (null !== ($val = $this->occurrenceDateTime->getValue())) {
                $out->occurrenceDateTime = $val;
            }
            if ($this->occurrenceDateTime->_nonValueFieldDefined()) {
                $ext = $this->occurrenceDateTime->jsonSerialize();
                unset($ext->value);
                $out->_occurrenceDateTime = $ext;
            }
        }
        if (isset($this->occurrenceString)) {
            if (null !== ($val = $this->occurrenceString->getValue())) {
                $out->occurrenceString = $val;
            }
            if ($this->occurrenceString->_nonValueFieldDefined()) {
                $ext = $this->occurrenceString->jsonSerialize();
                unset($ext->value);
                $out->_occurrenceString = $ext;
            }
        }
        if (isset($this->recorded)) {
            if (null !== ($val = $this->recorded->getValue())) {
                $out->recorded = $val;
            }
            if ($this->recorded->_nonValueFieldDefined()) {
                $ext = $this->recorded->jsonSerialize();
                unset($ext->value);
                $out->_recorded = $ext;
            }
        }
        if (isset($this->primarySource)) {
            if (null !== ($val = $this->primarySource->getValue())) {
                $out->primarySource = $val;
            }
            if ($this->primarySource->_nonValueFieldDefined()) {
                $ext = $this->primarySource->jsonSerialize();
                unset($ext->value);
                $out->_primarySource = $ext;
            }
        }
        if (isset($this->reportOrigin)) {
            $out->reportOrigin = $this->reportOrigin;
        }
        if (isset($this->location)) {
            $out->location = $this->location;
        }
        if (isset($this->manufacturer)) {
            $out->manufacturer = $this->manufacturer;
        }
        if (isset($this->lotNumber)) {
            if (null !== ($val = $this->lotNumber->getValue())) {
                $out->lotNumber = $val;
            }
            if ($this->lotNumber->_nonValueFieldDefined()) {
                $ext = $this->lotNumber->jsonSerialize();
                unset($ext->value);
                $out->_lotNumber = $ext;
            }
        }
        if (isset($this->expirationDate)) {
            if (null !== ($val = $this->expirationDate->getValue())) {
                $out->expirationDate = $val;
            }
            if ($this->expirationDate->_nonValueFieldDefined()) {
                $ext = $this->expirationDate->jsonSerialize();
                unset($ext->value);
                $out->_expirationDate = $ext;
            }
        }
        if (isset($this->site)) {
            $out->site = $this->site;
        }
        if (isset($this->route)) {
            $out->route = $this->route;
        }
        if (isset($this->doseQuantity)) {
            $out->doseQuantity = $this->doseQuantity;
        }
        if (isset($this->performer) && [] !== $this->performer) {
            if ($this->_getJSONFieldElideSingletonArray(self::FIELD_PERFORMER) && 1 === count($this->performer)) {
                $out->performer = $this->performer[0];
            } else {
                $out->performer = $this->performer;
            }
        }
        if (isset($this->note) && [] !== $this->note) {
            if ($this->_getJSONFieldElideSingletonArray(self::FIELD_NOTE) && 1 === count($this->note)) {
                $out->note = $this->note[0];
            } else {
                $out->note = $this->note;
            }
        }
        if (isset($this->reasonCode) && [] !== $this->reasonCode) {
            if ($this->_getJSONFieldElideSingletonArray(self::FIELD_REASON_CODE) && 1 === count($this->reasonCode)) {
                $out->reasonCode = $this->reasonCode[0];
            } else {
                $out->reasonCode = $this->reasonCode;
            }
        }
        if (isset($this->reasonReference) && [] !== $this->reasonReference) {
            if ($this->_getJSONFieldElideSingletonArray(self::FIELD_REASON_REFERENCE) && 1 === count($this->reasonReference)) {
                $out->reasonReference = $this->reasonReference[0];
            } else {
                $out->reasonReference = $this->reasonReference;
            }
        }
        if (isset($this->isSubpotent)) {
            if (null !== ($val = $this->isSubpotent->getValue())) {
                $out->isSubpotent = $val;
            }
            if ($this->isSubpotent->_nonValueFieldDefined()) {
                $ext = $this->isSubpotent->jsonSerialize();
                unset($ext->value);
                $out->_isSubpotent = $ext;
            }
        }
        if (isset($this->subpotentReason) && [] !== $this->subpotentReason) {
            if ($this->_getJSONFieldElideSingletonArray(self::FIELD_SUBPOTENT_REASON) && 1 === count($this->subpotentReason)) {
                $out->subpotentReason = $this->subpotentReason[0];
            } else {
                $out->subpotentReason = $this->subpotentReason;
            }
        }
        if (isset($this->education) && [] !== $this->education) {
            if ($this->_getJSONFieldElideSingletonArray(self::FIELD_EDUCATION) && 1 === count($this->education)) {
                $out->education = $this->education[0];
            } else {
                $out->education = $this->education;
            }
        }
        if (isset($this->programEligibility) && [] !== $this->programEligibility) {
            if ($this->_getJSONFieldElideSingletonArray(self::FIELD_PROGRAM_ELIGIBILITY) && 1 === count($this->programEligibility)) {
                $out->programEligibility = $this->programEligibility[0];
            } else {
                $out->programEligibility = $this->programEligibility;
            }
        }
        if (isset($this->fundingSource)) {
            $out->fundingSource = $this->fundingSource;
        }
        if (isset($this->reaction) && [] !== $this->reaction) {
            if ($this->_getJSONFieldElideSingletonArray(self::FIELD_REACTION) && 1 === count($this->reaction)) {
                $out->reaction = $this->reaction[0];
            } else {
                $out->reaction = $this->reaction;
            }
        }
        if (isset($this->protocolApplied) && [] !== $this->protocolApplied) {
            if ($this->_getJSONFieldElideSingletonArray(self::FIELD_PROTOCOL_APPLIED) && 1 === count($this->protocolApplied)) {
                $out->protocolApplied = $this->protocolApplied[0];
            } else {
                $out->protocolApplied = $this->protocolApplied;
            }
        }
        $out->resourceType = $this->_getResourceType();
        return $out;
    }
}
