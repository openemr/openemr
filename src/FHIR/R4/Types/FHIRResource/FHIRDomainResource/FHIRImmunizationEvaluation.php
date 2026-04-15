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
use OpenEMR\FHIR\Versions\R4\Types\FHIRCodePrimitive\FHIRImmunizationEvaluationStatusCodesList;
use OpenEMR\FHIR\Versions\R4\Types\FHIRDateTimePrimitive;
use OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRCode;
use OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRCodeableConcept;
use OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRDateTime;
use OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRExtension;
use OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRId;
use OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRIdentifier;
use OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRImmunizationEvaluationStatusCodes;
use OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRMeta;
use OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRNarrative;
use OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRPositiveInt;
use OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRReference;
use OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRString;
use OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRUri;
use OpenEMR\FHIR\Versions\R4\Types\FHIRIdPrimitive;
use OpenEMR\FHIR\Versions\R4\Types\FHIRPositiveIntPrimitive;
use OpenEMR\FHIR\Versions\R4\Types\FHIRResource\FHIRDomainResource;
use OpenEMR\FHIR\Versions\R4\Types\FHIRStringPrimitive;
use OpenEMR\FHIR\Versions\R4\Types\FHIRUriPrimitive;
use OpenEMR\FHIR\Versions\R4\Version;
use OpenEMR\FHIR\Versions\R4\VersionConstants;
use OpenEMR\FHIR\Versions\R4\VersionContainedTypeInterface;
use OpenEMR\FHIR\Versions\R4\VersionTypeMap;

/**
 * Describes a comparison of an immunization event against published
 * recommendations to determine if the administration is "valid" in relation to
 * those recommendations.
 * If the element is present, it must have either a \@value, an \@id, or extensions
 */
#[\Attribute(\Attribute::TARGET_PROPERTY)]
class FHIRImmunizationEvaluation extends FHIRDomainResource implements VersionContainedTypeInterface
{
    use TypeValidationsTrait,
        JSONSerializationOptionsTrait,
        XMLSerializationOptionsTrait;

    // name of FHIR type this class describes
    public const FHIR_TYPE_NAME = VersionConstants::TYPE_NAME_IMMUNIZATION_EVALUATION;

    /* class_default.php:56 */
    public const FIELD_IDENTIFIER = 'identifier';
    public const FIELD_STATUS = 'status';
    public const FIELD_STATUS_EXT = '_status';
    public const FIELD_PATIENT = 'patient';
    public const FIELD_DATE = 'date';
    public const FIELD_DATE_EXT = '_date';
    public const FIELD_AUTHORITY = 'authority';
    public const FIELD_TARGET_DISEASE = 'targetDisease';
    public const FIELD_IMMUNIZATION_EVENT = 'immunizationEvent';
    public const FIELD_DOSE_STATUS = 'doseStatus';
    public const FIELD_DOSE_STATUS_REASON = 'doseStatusReason';
    public const FIELD_DESCRIPTION = 'description';
    public const FIELD_DESCRIPTION_EXT = '_description';
    public const FIELD_SERIES = 'series';
    public const FIELD_SERIES_EXT = '_series';
    public const FIELD_DOSE_NUMBER_POSITIVE_INT = 'doseNumberPositiveInt';
    public const FIELD_DOSE_NUMBER_POSITIVE_INT_EXT = '_doseNumberPositiveInt';
    public const FIELD_DOSE_NUMBER_STRING = 'doseNumberString';
    public const FIELD_DOSE_NUMBER_STRING_EXT = '_doseNumberString';
    public const FIELD_SERIES_DOSES_POSITIVE_INT = 'seriesDosesPositiveInt';
    public const FIELD_SERIES_DOSES_POSITIVE_INT_EXT = '_seriesDosesPositiveInt';
    public const FIELD_SERIES_DOSES_STRING = 'seriesDosesString';
    public const FIELD_SERIES_DOSES_STRING_EXT = '_seriesDosesString';

    /* class_default.php:75 */
    private const _FHIR_VALIDATION_RULES = [
        self::FIELD_STATUS => [
            MinOccursRule::NAME => 1,
        ],
        self::FIELD_PATIENT => [
            MinOccursRule::NAME => 1,
        ],
        self::FIELD_TARGET_DISEASE => [
            MinOccursRule::NAME => 1,
        ],
        self::FIELD_IMMUNIZATION_EVENT => [
            MinOccursRule::NAME => 1,
        ],
        self::FIELD_DOSE_STATUS => [
            MinOccursRule::NAME => 1,
        ],
    ];

    /* class_default.php:96 */
    private array $_valueXMLLocations = [
        self::FIELD_STATUS => ValueXMLLocationEnum::CONTAINER_ATTRIBUTE,
        self::FIELD_DATE => ValueXMLLocationEnum::CONTAINER_ATTRIBUTE,
        self::FIELD_DESCRIPTION => ValueXMLLocationEnum::CONTAINER_ATTRIBUTE,
        self::FIELD_SERIES => ValueXMLLocationEnum::CONTAINER_ATTRIBUTE,
        self::FIELD_DOSE_NUMBER_POSITIVE_INT => ValueXMLLocationEnum::CONTAINER_ATTRIBUTE,
        self::FIELD_DOSE_NUMBER_STRING => ValueXMLLocationEnum::CONTAINER_ATTRIBUTE,
        self::FIELD_SERIES_DOSES_POSITIVE_INT => ValueXMLLocationEnum::CONTAINER_ATTRIBUTE,
        self::FIELD_SERIES_DOSES_STRING => ValueXMLLocationEnum::CONTAINER_ATTRIBUTE,
    ];

    /* class_default.php:112 */
    /**
     * An identifier - identifies some entity uniquely and unambiguously. Typically
     * this is used for business identifiers.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * A unique identifier assigned to this immunization evaluation record.
     *
     * @var iterable<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRIdentifier>
     */
    #[FHIRIdentifier]
    protected array $identifier;
    /**
     * The status of the evaluation being done.
     * If the element is present, it must have either a \@value, an \@id, or extensions
     *
     * Indicates the current status of the evaluation of the vaccination administration
     * event.
     *
     * @var \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRImmunizationEvaluationStatusCodes
     */
    #[FHIRImmunizationEvaluationStatusCodes]
    protected FHIRImmunizationEvaluationStatusCodes $status;
    /**
     * A reference from one resource to another.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * The individual for whom the evaluation is being done.
     *
     * @var \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRReference
     */
    #[FHIRReference]
    protected FHIRReference $patient;
    /**
     * A date, date-time or partial date (e.g. just year or year + month). If hours and
     * minutes are specified, a time zone SHALL be populated. The format is a union of
     * the schema types gYear, gYearMonth, date and dateTime. Seconds must be provided
     * due to schema type constraints but may be zero-filled and may be ignored. Dates
     * SHALL be valid dates.
     * If the element is present, it must have either a \@value, an \@id, or extensions
     *
     * The date the evaluation of the vaccine administration event was performed.
     *
     * @var \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRDateTime
     */
    #[FHIRDateTime]
    protected FHIRDateTime $date;
    /**
     * A reference from one resource to another.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * Indicates the authority who published the protocol (e.g. ACIP).
     *
     * @var \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRReference
     */
    #[FHIRReference]
    protected FHIRReference $authority;
    /**
     * A concept that may be defined by a formal reference to a terminology or ontology
     * or may be provided by text.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * The vaccine preventable disease the dose is being evaluated against.
     *
     * @var \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRCodeableConcept
     */
    #[FHIRCodeableConcept]
    protected FHIRCodeableConcept $targetDisease;
    /**
     * A reference from one resource to another.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * The vaccine administration event being evaluated.
     *
     * @var \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRReference
     */
    #[FHIRReference]
    protected FHIRReference $immunizationEvent;
    /**
     * A concept that may be defined by a formal reference to a terminology or ontology
     * or may be provided by text.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * Indicates if the dose is valid or not valid with respect to the published
     * recommendations.
     *
     * @var \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRCodeableConcept
     */
    #[FHIRCodeableConcept]
    protected FHIRCodeableConcept $doseStatus;
    /**
     * A concept that may be defined by a formal reference to a terminology or ontology
     * or may be provided by text.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * Provides an explanation as to why the vaccine administration event is valid or
     * not relative to the published recommendations.
     *
     * @var iterable<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRCodeableConcept>
     */
    #[FHIRCodeableConcept]
    protected array $doseStatusReason;
    /**
     * A sequence of Unicode characters
     * Note that FHIR strings SHALL NOT exceed 1MB in size
     * If the element is present, it must have either a \@value, an \@id, or extensions
     *
     * Additional information about the evaluation.
     *
     * @var \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRString
     */
    #[FHIRString]
    protected FHIRString $description;
    /**
     * A sequence of Unicode characters
     * Note that FHIR strings SHALL NOT exceed 1MB in size
     * If the element is present, it must have either a \@value, an \@id, or extensions
     *
     * One possible path to achieve presumed immunity against a disease - within the
     * context of an authority.
     *
     * @var \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRString
     */
    #[FHIRString]
    protected FHIRString $series;
    /**
     * An integer with a value that is positive (e.g. >0)
     * If the element is present, it must have either a \@value, an \@id referenced from
     * the Narrative, or extensions
     *
     * Nominal position in a series. (choose any one of doseNumber*, but only one)
     *
     * @var \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRPositiveInt
     */
    #[FHIRPositiveInt]
    protected FHIRPositiveInt $doseNumberPositiveInt;
    /**
     * A sequence of Unicode characters
     * Note that FHIR strings SHALL NOT exceed 1MB in size
     * If the element is present, it must have either a \@value, an \@id, or extensions
     *
     * Nominal position in a series. (choose any one of doseNumber*, but only one)
     *
     * @var \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRString
     */
    #[FHIRString]
    protected FHIRString $doseNumberString;
    /**
     * An integer with a value that is positive (e.g. >0)
     * If the element is present, it must have either a \@value, an \@id referenced from
     * the Narrative, or extensions
     *
     * The recommended number of doses to achieve immunity. (choose any one of
     * seriesDoses*, but only one)
     *
     * @var \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRPositiveInt
     */
    #[FHIRPositiveInt]
    protected FHIRPositiveInt $seriesDosesPositiveInt;
    /**
     * A sequence of Unicode characters
     * Note that FHIR strings SHALL NOT exceed 1MB in size
     * If the element is present, it must have either a \@value, an \@id, or extensions
     *
     * The recommended number of doses to achieve immunity. (choose any one of
     * seriesDoses*, but only one)
     *
     * @var \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRString
     */
    #[FHIRString]
    protected FHIRString $seriesDosesString;

    /* constructor.php:61 */
    /**
     * FHIRImmunizationEvaluation Constructor
     * @param null|string|\OpenEMR\FHIR\Versions\R4\Types\FHIRIdPrimitive|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRId $id
     * @param null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRMeta $meta
     * @param null|string|\OpenEMR\FHIR\Versions\R4\Types\FHIRUriPrimitive|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRUri $implicitRules
     * @param null|string|\OpenEMR\FHIR\Versions\R4\Types\FHIRCodePrimitive|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRCode $language
     * @param null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRNarrative $text
     * @param null|iterable<\OpenEMR\FHIR\Versions\R4\Types\FHIRResourceContainer>|iterable<\OpenEMR\FHIR\Versions\R4\VersionContainedTypeInterface> $contained
     * @param null|iterable<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRExtension> $extension
     * @param null|iterable<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRExtension> $modifierExtension
     * @param null|iterable<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRIdentifier> $identifier
     * @param null|string|\OpenEMR\FHIR\Versions\R4\Types\FHIRCodePrimitive\FHIRImmunizationEvaluationStatusCodesList|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRImmunizationEvaluationStatusCodes $status
     * @param null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRReference $patient
     * @param null|string|\DateTimeInterface|\OpenEMR\FHIR\Versions\R4\Types\FHIRDateTimePrimitive|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRDateTime $date
     * @param null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRReference $authority
     * @param null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRCodeableConcept $targetDisease
     * @param null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRReference $immunizationEvent
     * @param null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRCodeableConcept $doseStatus
     * @param null|iterable<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRCodeableConcept> $doseStatusReason
     * @param null|string|\OpenEMR\FHIR\Versions\R4\Types\FHIRStringPrimitive|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRString $description
     * @param null|string|\OpenEMR\FHIR\Versions\R4\Types\FHIRStringPrimitive|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRString $series
     * @param null|string|float|\OpenEMR\FHIR\Versions\R4\Types\FHIRPositiveIntPrimitive|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRPositiveInt $doseNumberPositiveInt
     * @param null|string|\OpenEMR\FHIR\Versions\R4\Types\FHIRStringPrimitive|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRString $doseNumberString
     * @param null|string|float|\OpenEMR\FHIR\Versions\R4\Types\FHIRPositiveIntPrimitive|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRPositiveInt $seriesDosesPositiveInt
     * @param null|string|\OpenEMR\FHIR\Versions\R4\Types\FHIRStringPrimitive|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRString $seriesDosesString
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
                                null|string|FHIRImmunizationEvaluationStatusCodesList|FHIRImmunizationEvaluationStatusCodes $status = null,
                                null|FHIRReference $patient = null,
                                null|string|\DateTimeInterface|FHIRDateTimePrimitive|FHIRDateTime $date = null,
                                null|FHIRReference $authority = null,
                                null|FHIRCodeableConcept $targetDisease = null,
                                null|FHIRReference $immunizationEvent = null,
                                null|FHIRCodeableConcept $doseStatus = null,
                                null|iterable $doseStatusReason = null,
                                null|string|FHIRStringPrimitive|FHIRString $description = null,
                                null|string|FHIRStringPrimitive|FHIRString $series = null,
                                null|string|float|FHIRPositiveIntPrimitive|FHIRPositiveInt $doseNumberPositiveInt = null,
                                null|string|FHIRStringPrimitive|FHIRString $doseNumberString = null,
                                null|string|float|FHIRPositiveIntPrimitive|FHIRPositiveInt $seriesDosesPositiveInt = null,
                                null|string|FHIRStringPrimitive|FHIRString $seriesDosesString = null,
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
        if (null !== $patient) {
            $this->setPatient($patient);
        }
        if (null !== $date) {
            $this->setDate($date);
        }
        if (null !== $authority) {
            $this->setAuthority($authority);
        }
        if (null !== $targetDisease) {
            $this->setTargetDisease($targetDisease);
        }
        if (null !== $immunizationEvent) {
            $this->setImmunizationEvent($immunizationEvent);
        }
        if (null !== $doseStatus) {
            $this->setDoseStatus($doseStatus);
        }
        if (null !== $doseStatusReason) {
            $this->setDoseStatusReason(...$doseStatusReason);
        }
        if (null !== $description) {
            $this->setDescription($description);
        }
        if (null !== $series) {
            $this->setSeries($series);
        }
        if (null !== $doseNumberPositiveInt) {
            $this->setDoseNumberPositiveInt($doseNumberPositiveInt);
        }
        if (null !== $doseNumberString) {
            $this->setDoseNumberString($doseNumberString);
        }
        if (null !== $seriesDosesPositiveInt) {
            $this->setSeriesDosesPositiveInt($seriesDosesPositiveInt);
        }
        if (null !== $seriesDosesString) {
            $this->setSeriesDosesString($seriesDosesString);
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
     * A unique identifier assigned to this immunization evaluation record.
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
     * A unique identifier assigned to this immunization evaluation record.
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
     * A unique identifier assigned to this immunization evaluation record.
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
     * The status of the evaluation being done.
     * If the element is present, it must have either a \@value, an \@id, or extensions
     *
     * Indicates the current status of the evaluation of the vaccination administration
     * event.
     *
     * @return null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRImmunizationEvaluationStatusCodes
     */
    public function getStatus(): null|FHIRImmunizationEvaluationStatusCodes
    {
        return $this->status ?? null;
    }

    /**
     * The status of the evaluation being done.
     * If the element is present, it must have either a \@value, an \@id, or extensions
     *
     * Indicates the current status of the evaluation of the vaccination administration
     * event.
     *
     * @param null|string|\OpenEMR\FHIR\Versions\R4\Types\FHIRCodePrimitive\FHIRImmunizationEvaluationStatusCodesList|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRImmunizationEvaluationStatusCodes $status
     * @return static
     */
    public function setStatus(null|string|FHIRImmunizationEvaluationStatusCodesList|FHIRImmunizationEvaluationStatusCodes $status): self
    {
        if (null === $status) {
            unset($this->status);
            return $this;
        }
        if (!($status instanceof FHIRImmunizationEvaluationStatusCodes)) {
            $status = new FHIRImmunizationEvaluationStatusCodes(value: $status);
        }
        $this->status = $status;
        return $this;
    }

    /**
     * A reference from one resource to another.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * The individual for whom the evaluation is being done.
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
     * The individual for whom the evaluation is being done.
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
     * A date, date-time or partial date (e.g. just year or year + month). If hours and
     * minutes are specified, a time zone SHALL be populated. The format is a union of
     * the schema types gYear, gYearMonth, date and dateTime. Seconds must be provided
     * due to schema type constraints but may be zero-filled and may be ignored. Dates
     * SHALL be valid dates.
     * If the element is present, it must have either a \@value, an \@id, or extensions
     *
     * The date the evaluation of the vaccine administration event was performed.
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
     * The date the evaluation of the vaccine administration event was performed.
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
     * A reference from one resource to another.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * Indicates the authority who published the protocol (e.g. ACIP).
     *
     * @return null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRReference
     */
    public function getAuthority(): null|FHIRReference
    {
        return $this->authority ?? null;
    }

    /**
     * A reference from one resource to another.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * Indicates the authority who published the protocol (e.g. ACIP).
     *
     * @param null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRReference $authority
     * @return static
     */
    public function setAuthority(null|FHIRReference $authority): self
    {
        if (null === $authority) {
            unset($this->authority);
            return $this;
        }
        $this->authority = $authority;
        return $this;
    }

    /**
     * A concept that may be defined by a formal reference to a terminology or ontology
     * or may be provided by text.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * The vaccine preventable disease the dose is being evaluated against.
     *
     * @return null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRCodeableConcept
     */
    public function getTargetDisease(): null|FHIRCodeableConcept
    {
        return $this->targetDisease ?? null;
    }

    /**
     * A concept that may be defined by a formal reference to a terminology or ontology
     * or may be provided by text.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * The vaccine preventable disease the dose is being evaluated against.
     *
     * @param null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRCodeableConcept $targetDisease
     * @return static
     */
    public function setTargetDisease(null|FHIRCodeableConcept $targetDisease): self
    {
        if (null === $targetDisease) {
            unset($this->targetDisease);
            return $this;
        }
        $this->targetDisease = $targetDisease;
        return $this;
    }

    /**
     * A reference from one resource to another.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * The vaccine administration event being evaluated.
     *
     * @return null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRReference
     */
    public function getImmunizationEvent(): null|FHIRReference
    {
        return $this->immunizationEvent ?? null;
    }

    /**
     * A reference from one resource to another.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * The vaccine administration event being evaluated.
     *
     * @param null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRReference $immunizationEvent
     * @return static
     */
    public function setImmunizationEvent(null|FHIRReference $immunizationEvent): self
    {
        if (null === $immunizationEvent) {
            unset($this->immunizationEvent);
            return $this;
        }
        $this->immunizationEvent = $immunizationEvent;
        return $this;
    }

    /**
     * A concept that may be defined by a formal reference to a terminology or ontology
     * or may be provided by text.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * Indicates if the dose is valid or not valid with respect to the published
     * recommendations.
     *
     * @return null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRCodeableConcept
     */
    public function getDoseStatus(): null|FHIRCodeableConcept
    {
        return $this->doseStatus ?? null;
    }

    /**
     * A concept that may be defined by a formal reference to a terminology or ontology
     * or may be provided by text.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * Indicates if the dose is valid or not valid with respect to the published
     * recommendations.
     *
     * @param null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRCodeableConcept $doseStatus
     * @return static
     */
    public function setDoseStatus(null|FHIRCodeableConcept $doseStatus): self
    {
        if (null === $doseStatus) {
            unset($this->doseStatus);
            return $this;
        }
        $this->doseStatus = $doseStatus;
        return $this;
    }

    /**
     * A concept that may be defined by a formal reference to a terminology or ontology
     * or may be provided by text.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * Provides an explanation as to why the vaccine administration event is valid or
     * not relative to the published recommendations.
     *
     * @return iterable<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRCodeableConcept>
     */
    public function getDoseStatusReason(): array
    {
        return $this->doseStatusReason ?? [];
    }

    /**
     * @return \ArrayIterator<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRCodeableConcept>
     */
    public function getDoseStatusReasonIterator(): iterable
    {
        if (!isset($this->doseStatusReason)) {
            return new \EmptyIterator();
        }
        return new \ArrayIterator($this->doseStatusReason);
    }

    /**
     * A concept that may be defined by a formal reference to a terminology or ontology
     * or may be provided by text.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * Provides an explanation as to why the vaccine administration event is valid or
     * not relative to the published recommendations.
     *
     * @param \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRCodeableConcept $doseStatusReason
     * @return static
     */
    public function addDoseStatusReason(FHIRCodeableConcept $doseStatusReason): self
    {
        if (!isset($this->doseStatusReason)) {
            $this->doseStatusReason = [];
        }
        $this->doseStatusReason[] = $doseStatusReason;
        return $this;
    }

    /**
     * A concept that may be defined by a formal reference to a terminology or ontology
     * or may be provided by text.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * Provides an explanation as to why the vaccine administration event is valid or
     * not relative to the published recommendations.
     *
     * @param \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRCodeableConcept ...$doseStatusReason
     * @return static
     */
    public function setDoseStatusReason(FHIRCodeableConcept ...$doseStatusReason): self
    {
        if ([] === $doseStatusReason) {
            unset($this->doseStatusReason);
            return $this;
        }
        $this->doseStatusReason = $doseStatusReason;
        return $this;
    }

    /**
     * A sequence of Unicode characters
     * Note that FHIR strings SHALL NOT exceed 1MB in size
     * If the element is present, it must have either a \@value, an \@id, or extensions
     *
     * Additional information about the evaluation.
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
     * Additional information about the evaluation.
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
     * A sequence of Unicode characters
     * Note that FHIR strings SHALL NOT exceed 1MB in size
     * If the element is present, it must have either a \@value, an \@id, or extensions
     *
     * One possible path to achieve presumed immunity against a disease - within the
     * context of an authority.
     *
     * @return null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRString
     */
    public function getSeries(): null|FHIRString
    {
        return $this->series ?? null;
    }

    /**
     * A sequence of Unicode characters
     * Note that FHIR strings SHALL NOT exceed 1MB in size
     * If the element is present, it must have either a \@value, an \@id, or extensions
     *
     * One possible path to achieve presumed immunity against a disease - within the
     * context of an authority.
     *
     * @param null|string|\OpenEMR\FHIR\Versions\R4\Types\FHIRStringPrimitive|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRString $series
     * @return static
     */
    public function setSeries(null|string|FHIRStringPrimitive|FHIRString $series): self
    {
        if (null === $series) {
            unset($this->series);
            return $this;
        }
        if (!($series instanceof FHIRString)) {
            $series = new FHIRString(value: $series);
        }
        $this->series = $series;
        return $this;
    }

    /**
     * An integer with a value that is positive (e.g. >0)
     * If the element is present, it must have either a \@value, an \@id referenced from
     * the Narrative, or extensions
     *
     * Nominal position in a series. (choose any one of doseNumber*, but only one)
     *
     * @return null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRPositiveInt
     */
    public function getDoseNumberPositiveInt(): null|FHIRPositiveInt
    {
        return $this->doseNumberPositiveInt ?? null;
    }

    /**
     * An integer with a value that is positive (e.g. >0)
     * If the element is present, it must have either a \@value, an \@id referenced from
     * the Narrative, or extensions
     *
     * Nominal position in a series. (choose any one of doseNumber*, but only one)
     *
     * @param null|string|float|\OpenEMR\FHIR\Versions\R4\Types\FHIRPositiveIntPrimitive|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRPositiveInt $doseNumberPositiveInt
     * @return static
     */
    public function setDoseNumberPositiveInt(null|string|float|FHIRPositiveIntPrimitive|FHIRPositiveInt $doseNumberPositiveInt): self
    {
        if (null === $doseNumberPositiveInt) {
            unset($this->doseNumberPositiveInt);
            return $this;
        }
        if (!($doseNumberPositiveInt instanceof FHIRPositiveInt)) {
            $doseNumberPositiveInt = new FHIRPositiveInt(value: $doseNumberPositiveInt);
        }
        $this->doseNumberPositiveInt = $doseNumberPositiveInt;
        return $this;
    }

    /**
     * A sequence of Unicode characters
     * Note that FHIR strings SHALL NOT exceed 1MB in size
     * If the element is present, it must have either a \@value, an \@id, or extensions
     *
     * Nominal position in a series. (choose any one of doseNumber*, but only one)
     *
     * @return null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRString
     */
    public function getDoseNumberString(): null|FHIRString
    {
        return $this->doseNumberString ?? null;
    }

    /**
     * A sequence of Unicode characters
     * Note that FHIR strings SHALL NOT exceed 1MB in size
     * If the element is present, it must have either a \@value, an \@id, or extensions
     *
     * Nominal position in a series. (choose any one of doseNumber*, but only one)
     *
     * @param null|string|\OpenEMR\FHIR\Versions\R4\Types\FHIRStringPrimitive|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRString $doseNumberString
     * @return static
     */
    public function setDoseNumberString(null|string|FHIRStringPrimitive|FHIRString $doseNumberString): self
    {
        if (null === $doseNumberString) {
            unset($this->doseNumberString);
            return $this;
        }
        if (!($doseNumberString instanceof FHIRString)) {
            $doseNumberString = new FHIRString(value: $doseNumberString);
        }
        $this->doseNumberString = $doseNumberString;
        return $this;
    }

    /**
     * An integer with a value that is positive (e.g. >0)
     * If the element is present, it must have either a \@value, an \@id referenced from
     * the Narrative, or extensions
     *
     * The recommended number of doses to achieve immunity. (choose any one of
     * seriesDoses*, but only one)
     *
     * @return null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRPositiveInt
     */
    public function getSeriesDosesPositiveInt(): null|FHIRPositiveInt
    {
        return $this->seriesDosesPositiveInt ?? null;
    }

    /**
     * An integer with a value that is positive (e.g. >0)
     * If the element is present, it must have either a \@value, an \@id referenced from
     * the Narrative, or extensions
     *
     * The recommended number of doses to achieve immunity. (choose any one of
     * seriesDoses*, but only one)
     *
     * @param null|string|float|\OpenEMR\FHIR\Versions\R4\Types\FHIRPositiveIntPrimitive|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRPositiveInt $seriesDosesPositiveInt
     * @return static
     */
    public function setSeriesDosesPositiveInt(null|string|float|FHIRPositiveIntPrimitive|FHIRPositiveInt $seriesDosesPositiveInt): self
    {
        if (null === $seriesDosesPositiveInt) {
            unset($this->seriesDosesPositiveInt);
            return $this;
        }
        if (!($seriesDosesPositiveInt instanceof FHIRPositiveInt)) {
            $seriesDosesPositiveInt = new FHIRPositiveInt(value: $seriesDosesPositiveInt);
        }
        $this->seriesDosesPositiveInt = $seriesDosesPositiveInt;
        return $this;
    }

    /**
     * A sequence of Unicode characters
     * Note that FHIR strings SHALL NOT exceed 1MB in size
     * If the element is present, it must have either a \@value, an \@id, or extensions
     *
     * The recommended number of doses to achieve immunity. (choose any one of
     * seriesDoses*, but only one)
     *
     * @return null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRString
     */
    public function getSeriesDosesString(): null|FHIRString
    {
        return $this->seriesDosesString ?? null;
    }

    /**
     * A sequence of Unicode characters
     * Note that FHIR strings SHALL NOT exceed 1MB in size
     * If the element is present, it must have either a \@value, an \@id, or extensions
     *
     * The recommended number of doses to achieve immunity. (choose any one of
     * seriesDoses*, but only one)
     *
     * @param null|string|\OpenEMR\FHIR\Versions\R4\Types\FHIRStringPrimitive|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRString $seriesDosesString
     * @return static
     */
    public function setSeriesDosesString(null|string|FHIRStringPrimitive|FHIRString $seriesDosesString): self
    {
        if (null === $seriesDosesString) {
            unset($this->seriesDosesString);
            return $this;
        }
        if (!($seriesDosesString instanceof FHIRString)) {
            $seriesDosesString = new FHIRString(value: $seriesDosesString);
        }
        $this->seriesDosesString = $seriesDosesString;
        return $this;
    }

    /* class_default.php:201 */
    /**
     * @param string|\SimpleXMLElement $element
     * @param null|\OpenEMR\FHIR\Encoding\UnserializeConfig $config
     * @param null|\OpenEMR\FHIR\Versions\R4\Types\FHIRResource\FHIRDomainResource\FHIRImmunizationEvaluation $type
     * @return \OpenEMR\FHIR\Versions\R4\Types\FHIRResource\FHIRDomainResource\FHIRImmunizationEvaluation
     * @throws \Exception
     */
    public static function xmlUnserialize(string|\SimpleXMLElement $element,
                                          null|UnserializeConfig $config = null,
                                          null|ResourceTypeInterface $type = null): self
    {
        if (null === $type) {
            $type = new static();
        } else if (!($type instanceof FHIRImmunizationEvaluation)) {
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
                $type->setStatus(FHIRImmunizationEvaluationStatusCodes::xmlUnserialize($ce, $config));
            } else if (self::FIELD_PATIENT === $cen) {
                $type->setPatient(FHIRReference::xmlUnserialize($ce, $config));
            } else if (self::FIELD_DATE === $cen) {
                $type->setDate(FHIRDateTime::xmlUnserialize($ce, $config));
            } else if (self::FIELD_AUTHORITY === $cen) {
                $type->setAuthority(FHIRReference::xmlUnserialize($ce, $config));
            } else if (self::FIELD_TARGET_DISEASE === $cen) {
                $type->setTargetDisease(FHIRCodeableConcept::xmlUnserialize($ce, $config));
            } else if (self::FIELD_IMMUNIZATION_EVENT === $cen) {
                $type->setImmunizationEvent(FHIRReference::xmlUnserialize($ce, $config));
            } else if (self::FIELD_DOSE_STATUS === $cen) {
                $type->setDoseStatus(FHIRCodeableConcept::xmlUnserialize($ce, $config));
            } else if (self::FIELD_DOSE_STATUS_REASON === $cen) {
                $type->addDoseStatusReason(FHIRCodeableConcept::xmlUnserialize($ce, $config));
            } else if (self::FIELD_DESCRIPTION === $cen) {
                $type->setDescription(FHIRString::xmlUnserialize($ce, $config));
            } else if (self::FIELD_SERIES === $cen) {
                $type->setSeries(FHIRString::xmlUnserialize($ce, $config));
            } else if (self::FIELD_DOSE_NUMBER_POSITIVE_INT === $cen) {
                $type->setDoseNumberPositiveInt(FHIRPositiveInt::xmlUnserialize($ce, $config));
            } else if (self::FIELD_DOSE_NUMBER_STRING === $cen) {
                $type->setDoseNumberString(FHIRString::xmlUnserialize($ce, $config));
            } else if (self::FIELD_SERIES_DOSES_POSITIVE_INT === $cen) {
                $type->setSeriesDosesPositiveInt(FHIRPositiveInt::xmlUnserialize($ce, $config));
            } else if (self::FIELD_SERIES_DOSES_STRING === $cen) {
                $type->setSeriesDosesString(FHIRString::xmlUnserialize($ce, $config));
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
        if (isset($attributes[self::FIELD_DATE])) {
            if (isset($type->date)) {
                $type->date->setValue((string)$attributes[self::FIELD_DATE]);
            } else {
                $type->setDate((string)$attributes[self::FIELD_DATE]);
            }
            $type->_setXMLFieldValueLocation(self::FIELD_DATE, ValueXMLLocationEnum::PARENT_ATTRIBUTE);
        }
        if (isset($attributes[self::FIELD_DESCRIPTION])) {
            if (isset($type->description)) {
                $type->description->setValue((string)$attributes[self::FIELD_DESCRIPTION]);
            } else {
                $type->setDescription((string)$attributes[self::FIELD_DESCRIPTION]);
            }
            $type->_setXMLFieldValueLocation(self::FIELD_DESCRIPTION, ValueXMLLocationEnum::PARENT_ATTRIBUTE);
        }
        if (isset($attributes[self::FIELD_SERIES])) {
            if (isset($type->series)) {
                $type->series->setValue((string)$attributes[self::FIELD_SERIES]);
            } else {
                $type->setSeries((string)$attributes[self::FIELD_SERIES]);
            }
            $type->_setXMLFieldValueLocation(self::FIELD_SERIES, ValueXMLLocationEnum::PARENT_ATTRIBUTE);
        }
        if (isset($attributes[self::FIELD_DOSE_NUMBER_POSITIVE_INT])) {
            if (isset($type->doseNumberPositiveInt)) {
                $type->doseNumberPositiveInt->setValue((string)$attributes[self::FIELD_DOSE_NUMBER_POSITIVE_INT]);
            } else {
                $type->setDoseNumberPositiveInt((string)$attributes[self::FIELD_DOSE_NUMBER_POSITIVE_INT]);
            }
            $type->_setXMLFieldValueLocation(self::FIELD_DOSE_NUMBER_POSITIVE_INT, ValueXMLLocationEnum::PARENT_ATTRIBUTE);
        }
        if (isset($attributes[self::FIELD_DOSE_NUMBER_STRING])) {
            if (isset($type->doseNumberString)) {
                $type->doseNumberString->setValue((string)$attributes[self::FIELD_DOSE_NUMBER_STRING]);
            } else {
                $type->setDoseNumberString((string)$attributes[self::FIELD_DOSE_NUMBER_STRING]);
            }
            $type->_setXMLFieldValueLocation(self::FIELD_DOSE_NUMBER_STRING, ValueXMLLocationEnum::PARENT_ATTRIBUTE);
        }
        if (isset($attributes[self::FIELD_SERIES_DOSES_POSITIVE_INT])) {
            if (isset($type->seriesDosesPositiveInt)) {
                $type->seriesDosesPositiveInt->setValue((string)$attributes[self::FIELD_SERIES_DOSES_POSITIVE_INT]);
            } else {
                $type->setSeriesDosesPositiveInt((string)$attributes[self::FIELD_SERIES_DOSES_POSITIVE_INT]);
            }
            $type->_setXMLFieldValueLocation(self::FIELD_SERIES_DOSES_POSITIVE_INT, ValueXMLLocationEnum::PARENT_ATTRIBUTE);
        }
        if (isset($attributes[self::FIELD_SERIES_DOSES_STRING])) {
            if (isset($type->seriesDosesString)) {
                $type->seriesDosesString->setValue((string)$attributes[self::FIELD_SERIES_DOSES_STRING]);
            } else {
                $type->setSeriesDosesString((string)$attributes[self::FIELD_SERIES_DOSES_STRING]);
            }
            $type->_setXMLFieldValueLocation(self::FIELD_SERIES_DOSES_STRING, ValueXMLLocationEnum::PARENT_ATTRIBUTE);
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
            $xw->openRootNode('ImmunizationEvaluation', $this->_getSourceXMLNS());
        }
        if (isset($this->status) && ValueXMLLocationEnum::PARENT_ATTRIBUTE === $this->_valueXMLLocations[self::FIELD_STATUS]) {
            $xw->writeAttribute(self::FIELD_STATUS, $this->status->_getValueAsString());
        }
        if (isset($this->date) && ValueXMLLocationEnum::PARENT_ATTRIBUTE === $this->_valueXMLLocations[self::FIELD_DATE]) {
            $xw->writeAttribute(self::FIELD_DATE, $this->date->_getValueAsString());
        }
        if (isset($this->description) && ValueXMLLocationEnum::PARENT_ATTRIBUTE === $this->_valueXMLLocations[self::FIELD_DESCRIPTION]) {
            $xw->writeAttribute(self::FIELD_DESCRIPTION, $this->description->_getValueAsString());
        }
        if (isset($this->series) && ValueXMLLocationEnum::PARENT_ATTRIBUTE === $this->_valueXMLLocations[self::FIELD_SERIES]) {
            $xw->writeAttribute(self::FIELD_SERIES, $this->series->_getValueAsString());
        }
        if (isset($this->doseNumberPositiveInt) && ValueXMLLocationEnum::PARENT_ATTRIBUTE === $this->_valueXMLLocations[self::FIELD_DOSE_NUMBER_POSITIVE_INT]) {
            $xw->writeAttribute(self::FIELD_DOSE_NUMBER_POSITIVE_INT, $this->doseNumberPositiveInt->_getValueAsString());
        }
        if (isset($this->doseNumberString) && ValueXMLLocationEnum::PARENT_ATTRIBUTE === $this->_valueXMLLocations[self::FIELD_DOSE_NUMBER_STRING]) {
            $xw->writeAttribute(self::FIELD_DOSE_NUMBER_STRING, $this->doseNumberString->_getValueAsString());
        }
        if (isset($this->seriesDosesPositiveInt) && ValueXMLLocationEnum::PARENT_ATTRIBUTE === $this->_valueXMLLocations[self::FIELD_SERIES_DOSES_POSITIVE_INT]) {
            $xw->writeAttribute(self::FIELD_SERIES_DOSES_POSITIVE_INT, $this->seriesDosesPositiveInt->_getValueAsString());
        }
        if (isset($this->seriesDosesString) && ValueXMLLocationEnum::PARENT_ATTRIBUTE === $this->_valueXMLLocations[self::FIELD_SERIES_DOSES_STRING]) {
            $xw->writeAttribute(self::FIELD_SERIES_DOSES_STRING, $this->seriesDosesString->_getValueAsString());
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
        if (isset($this->patient)) {
            $xw->startElement(self::FIELD_PATIENT);
            $this->patient->xmlSerialize($xw, $config);
            $xw->endElement();
        }
        if (isset($this->date)
            && (ValueXMLLocationEnum::PARENT_ATTRIBUTE !== $this->_valueXMLLocations[self::FIELD_DATE]
                || $this->date->_nonValueFieldDefined())) {
            $xw->startElement(self::FIELD_DATE);
            $this->date->xmlSerialize($xw, $config, $this->_valueXMLLocations[self::FIELD_DATE]);
            $xw->endElement();
        }
        if (isset($this->authority)) {
            $xw->startElement(self::FIELD_AUTHORITY);
            $this->authority->xmlSerialize($xw, $config);
            $xw->endElement();
        }
        if (isset($this->targetDisease)) {
            $xw->startElement(self::FIELD_TARGET_DISEASE);
            $this->targetDisease->xmlSerialize($xw, $config);
            $xw->endElement();
        }
        if (isset($this->immunizationEvent)) {
            $xw->startElement(self::FIELD_IMMUNIZATION_EVENT);
            $this->immunizationEvent->xmlSerialize($xw, $config);
            $xw->endElement();
        }
        if (isset($this->doseStatus)) {
            $xw->startElement(self::FIELD_DOSE_STATUS);
            $this->doseStatus->xmlSerialize($xw, $config);
            $xw->endElement();
        }
        if (isset($this->doseStatusReason)) {
            foreach ($this->doseStatusReason as $v) {
                $xw->startElement(self::FIELD_DOSE_STATUS_REASON);
                $v->xmlSerialize($xw, $config);
                $xw->endElement();
            }
        }
        if (isset($this->description)
            && (ValueXMLLocationEnum::PARENT_ATTRIBUTE !== $this->_valueXMLLocations[self::FIELD_DESCRIPTION]
                || $this->description->_nonValueFieldDefined())) {
            $xw->startElement(self::FIELD_DESCRIPTION);
            $this->description->xmlSerialize($xw, $config, $this->_valueXMLLocations[self::FIELD_DESCRIPTION]);
            $xw->endElement();
        }
        if (isset($this->series)
            && (ValueXMLLocationEnum::PARENT_ATTRIBUTE !== $this->_valueXMLLocations[self::FIELD_SERIES]
                || $this->series->_nonValueFieldDefined())) {
            $xw->startElement(self::FIELD_SERIES);
            $this->series->xmlSerialize($xw, $config, $this->_valueXMLLocations[self::FIELD_SERIES]);
            $xw->endElement();
        }
        if (isset($this->doseNumberPositiveInt)
            && (ValueXMLLocationEnum::PARENT_ATTRIBUTE !== $this->_valueXMLLocations[self::FIELD_DOSE_NUMBER_POSITIVE_INT]
                || $this->doseNumberPositiveInt->_nonValueFieldDefined())) {
            $xw->startElement(self::FIELD_DOSE_NUMBER_POSITIVE_INT);
            $this->doseNumberPositiveInt->xmlSerialize($xw, $config, $this->_valueXMLLocations[self::FIELD_DOSE_NUMBER_POSITIVE_INT]);
            $xw->endElement();
        }
        if (isset($this->doseNumberString)
            && (ValueXMLLocationEnum::PARENT_ATTRIBUTE !== $this->_valueXMLLocations[self::FIELD_DOSE_NUMBER_STRING]
                || $this->doseNumberString->_nonValueFieldDefined())) {
            $xw->startElement(self::FIELD_DOSE_NUMBER_STRING);
            $this->doseNumberString->xmlSerialize($xw, $config, $this->_valueXMLLocations[self::FIELD_DOSE_NUMBER_STRING]);
            $xw->endElement();
        }
        if (isset($this->seriesDosesPositiveInt)
            && (ValueXMLLocationEnum::PARENT_ATTRIBUTE !== $this->_valueXMLLocations[self::FIELD_SERIES_DOSES_POSITIVE_INT]
                || $this->seriesDosesPositiveInt->_nonValueFieldDefined())) {
            $xw->startElement(self::FIELD_SERIES_DOSES_POSITIVE_INT);
            $this->seriesDosesPositiveInt->xmlSerialize($xw, $config, $this->_valueXMLLocations[self::FIELD_SERIES_DOSES_POSITIVE_INT]);
            $xw->endElement();
        }
        if (isset($this->seriesDosesString)
            && (ValueXMLLocationEnum::PARENT_ATTRIBUTE !== $this->_valueXMLLocations[self::FIELD_SERIES_DOSES_STRING]
                || $this->seriesDosesString->_nonValueFieldDefined())) {
            $xw->startElement(self::FIELD_SERIES_DOSES_STRING);
            $this->seriesDosesString->xmlSerialize($xw, $config, $this->_valueXMLLocations[self::FIELD_SERIES_DOSES_STRING]);
            $xw->endElement();
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
     * @param null|\OpenEMR\FHIR\Versions\R4\Types\FHIRResource\FHIRDomainResource\FHIRImmunizationEvaluation $type
     * @return \OpenEMR\FHIR\Versions\R4\Types\FHIRResource\FHIRDomainResource\FHIRImmunizationEvaluation
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
        } else if (!($type instanceof FHIRImmunizationEvaluation)) {
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
            $type->setStatus(FHIRImmunizationEvaluationStatusCodes::jsonUnserialize($v, $config));
        }
        if (isset($decoded->patient) || property_exists($decoded, self::FIELD_PATIENT)) {
            if (is_array($decoded->patient)) {
                $type->setPatient(FHIRReference::jsonUnserialize(reset($decoded->patient), $config));
            } else {
                $type->setPatient(FHIRReference::jsonUnserialize($decoded->patient, $config));
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
        if (isset($decoded->authority) || property_exists($decoded, self::FIELD_AUTHORITY)) {
            if (is_array($decoded->authority)) {
                $type->setAuthority(FHIRReference::jsonUnserialize(reset($decoded->authority), $config));
            } else {
                $type->setAuthority(FHIRReference::jsonUnserialize($decoded->authority, $config));
            }
        }
        if (isset($decoded->targetDisease) || property_exists($decoded, self::FIELD_TARGET_DISEASE)) {
            if (is_array($decoded->targetDisease)) {
                $type->setTargetDisease(FHIRCodeableConcept::jsonUnserialize(reset($decoded->targetDisease), $config));
            } else {
                $type->setTargetDisease(FHIRCodeableConcept::jsonUnserialize($decoded->targetDisease, $config));
            }
        }
        if (isset($decoded->immunizationEvent) || property_exists($decoded, self::FIELD_IMMUNIZATION_EVENT)) {
            if (is_array($decoded->immunizationEvent)) {
                $type->setImmunizationEvent(FHIRReference::jsonUnserialize(reset($decoded->immunizationEvent), $config));
            } else {
                $type->setImmunizationEvent(FHIRReference::jsonUnserialize($decoded->immunizationEvent, $config));
            }
        }
        if (isset($decoded->doseStatus) || property_exists($decoded, self::FIELD_DOSE_STATUS)) {
            if (is_array($decoded->doseStatus)) {
                $type->setDoseStatus(FHIRCodeableConcept::jsonUnserialize(reset($decoded->doseStatus), $config));
            } else {
                $type->setDoseStatus(FHIRCodeableConcept::jsonUnserialize($decoded->doseStatus, $config));
            }
        }
        if (isset($decoded->doseStatusReason) || property_exists($decoded, self::FIELD_DOSE_STATUS_REASON)) {
            if (is_object($decoded->doseStatusReason)) {
                $vals = [$decoded->doseStatusReason];
                $type->_setJSONFieldElideSingletonArray(self::FIELD_DOSE_STATUS_REASON, true);
            } else {
                $vals = $decoded->doseStatusReason;
            }
            foreach($vals as $v) {
                $type->addDoseStatusReason(FHIRCodeableConcept::jsonUnserialize($v, $config));
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
        if (isset($decoded->series)
            || isset($decoded->_series)
            || property_exists($decoded, self::FIELD_SERIES)
            || property_exists($decoded, self::FIELD_SERIES_EXT)) {
            $v = $decoded->_series ?? new \stdClass();
            $v->value = $decoded->series ?? null;
            $type->setSeries(FHIRString::jsonUnserialize($v, $config));
        }
        if (isset($decoded->doseNumberPositiveInt)
            || isset($decoded->_doseNumberPositiveInt)
            || property_exists($decoded, self::FIELD_DOSE_NUMBER_POSITIVE_INT)
            || property_exists($decoded, self::FIELD_DOSE_NUMBER_POSITIVE_INT_EXT)) {
            $v = $decoded->_doseNumberPositiveInt ?? new \stdClass();
            $v->value = $decoded->doseNumberPositiveInt ?? null;
            $type->setDoseNumberPositiveInt(FHIRPositiveInt::jsonUnserialize($v, $config));
        }
        if (isset($decoded->doseNumberString)
            || isset($decoded->_doseNumberString)
            || property_exists($decoded, self::FIELD_DOSE_NUMBER_STRING)
            || property_exists($decoded, self::FIELD_DOSE_NUMBER_STRING_EXT)) {
            $v = $decoded->_doseNumberString ?? new \stdClass();
            $v->value = $decoded->doseNumberString ?? null;
            $type->setDoseNumberString(FHIRString::jsonUnserialize($v, $config));
        }
        if (isset($decoded->seriesDosesPositiveInt)
            || isset($decoded->_seriesDosesPositiveInt)
            || property_exists($decoded, self::FIELD_SERIES_DOSES_POSITIVE_INT)
            || property_exists($decoded, self::FIELD_SERIES_DOSES_POSITIVE_INT_EXT)) {
            $v = $decoded->_seriesDosesPositiveInt ?? new \stdClass();
            $v->value = $decoded->seriesDosesPositiveInt ?? null;
            $type->setSeriesDosesPositiveInt(FHIRPositiveInt::jsonUnserialize($v, $config));
        }
        if (isset($decoded->seriesDosesString)
            || isset($decoded->_seriesDosesString)
            || property_exists($decoded, self::FIELD_SERIES_DOSES_STRING)
            || property_exists($decoded, self::FIELD_SERIES_DOSES_STRING_EXT)) {
            $v = $decoded->_seriesDosesString ?? new \stdClass();
            $v->value = $decoded->seriesDosesString ?? null;
            $type->setSeriesDosesString(FHIRString::jsonUnserialize($v, $config));
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
        if (isset($this->patient)) {
            $out->patient = $this->patient;
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
        if (isset($this->authority)) {
            $out->authority = $this->authority;
        }
        if (isset($this->targetDisease)) {
            $out->targetDisease = $this->targetDisease;
        }
        if (isset($this->immunizationEvent)) {
            $out->immunizationEvent = $this->immunizationEvent;
        }
        if (isset($this->doseStatus)) {
            $out->doseStatus = $this->doseStatus;
        }
        if (isset($this->doseStatusReason) && [] !== $this->doseStatusReason) {
            if ($this->_getJSONFieldElideSingletonArray(self::FIELD_DOSE_STATUS_REASON) && 1 === count($this->doseStatusReason)) {
                $out->doseStatusReason = $this->doseStatusReason[0];
            } else {
                $out->doseStatusReason = $this->doseStatusReason;
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
        if (isset($this->series)) {
            if (null !== ($val = $this->series->getValue())) {
                $out->series = $val;
            }
            if ($this->series->_nonValueFieldDefined()) {
                $ext = $this->series->jsonSerialize();
                unset($ext->value);
                $out->_series = $ext;
            }
        }
        if (isset($this->doseNumberPositiveInt)) {
            if (null !== ($val = $this->doseNumberPositiveInt->getValue())) {
                $out->doseNumberPositiveInt = $val;
            }
            if ($this->doseNumberPositiveInt->_nonValueFieldDefined()) {
                $ext = $this->doseNumberPositiveInt->jsonSerialize();
                unset($ext->value);
                $out->_doseNumberPositiveInt = $ext;
            }
        }
        if (isset($this->doseNumberString)) {
            if (null !== ($val = $this->doseNumberString->getValue())) {
                $out->doseNumberString = $val;
            }
            if ($this->doseNumberString->_nonValueFieldDefined()) {
                $ext = $this->doseNumberString->jsonSerialize();
                unset($ext->value);
                $out->_doseNumberString = $ext;
            }
        }
        if (isset($this->seriesDosesPositiveInt)) {
            if (null !== ($val = $this->seriesDosesPositiveInt->getValue())) {
                $out->seriesDosesPositiveInt = $val;
            }
            if ($this->seriesDosesPositiveInt->_nonValueFieldDefined()) {
                $ext = $this->seriesDosesPositiveInt->jsonSerialize();
                unset($ext->value);
                $out->_seriesDosesPositiveInt = $ext;
            }
        }
        if (isset($this->seriesDosesString)) {
            if (null !== ($val = $this->seriesDosesString->getValue())) {
                $out->seriesDosesString = $val;
            }
            if ($this->seriesDosesString->_nonValueFieldDefined()) {
                $ext = $this->seriesDosesString->jsonSerialize();
                unset($ext->value);
                $out->_seriesDosesString = $ext;
            }
        }
        $out->resourceType = $this->_getResourceType();
        return $out;
    }
}
