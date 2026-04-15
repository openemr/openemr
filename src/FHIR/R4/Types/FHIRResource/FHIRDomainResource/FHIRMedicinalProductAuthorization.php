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
use OpenEMR\FHIR\Validation\TypeValidationsTrait;
use OpenEMR\FHIR\Versions\R4\Types\FHIRCodePrimitive;
use OpenEMR\FHIR\Versions\R4\Types\FHIRDateTimePrimitive;
use OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRMedicinalProductAuthorization\FHIRMedicinalProductAuthorizationJurisdictionalAuthorization;
use OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRMedicinalProductAuthorization\FHIRMedicinalProductAuthorizationProcedure;
use OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRCode;
use OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRCodeableConcept;
use OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRDateTime;
use OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRExtension;
use OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRId;
use OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRIdentifier;
use OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRMeta;
use OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRNarrative;
use OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRPeriod;
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
 * The regulatory authorization of a medicinal product.
 * If the element is present, it must have either a \@value, an \@id, or extensions
 */
#[\Attribute(\Attribute::TARGET_PROPERTY)]
class FHIRMedicinalProductAuthorization extends FHIRDomainResource implements VersionContainedTypeInterface
{
    use TypeValidationsTrait,
        JSONSerializationOptionsTrait,
        XMLSerializationOptionsTrait;

    // name of FHIR type this class describes
    public const FHIR_TYPE_NAME = VersionConstants::TYPE_NAME_MEDICINAL_PRODUCT_AUTHORIZATION;

    /* class_default.php:56 */
    public const FIELD_IDENTIFIER = 'identifier';
    public const FIELD_SUBJECT = 'subject';
    public const FIELD_COUNTRY = 'country';
    public const FIELD_JURISDICTION = 'jurisdiction';
    public const FIELD_STATUS = 'status';
    public const FIELD_STATUS_DATE = 'statusDate';
    public const FIELD_STATUS_DATE_EXT = '_statusDate';
    public const FIELD_RESTORE_DATE = 'restoreDate';
    public const FIELD_RESTORE_DATE_EXT = '_restoreDate';
    public const FIELD_VALIDITY_PERIOD = 'validityPeriod';
    public const FIELD_DATA_EXCLUSIVITY_PERIOD = 'dataExclusivityPeriod';
    public const FIELD_DATE_OF_FIRST_AUTHORIZATION = 'dateOfFirstAuthorization';
    public const FIELD_DATE_OF_FIRST_AUTHORIZATION_EXT = '_dateOfFirstAuthorization';
    public const FIELD_INTERNATIONAL_BIRTH_DATE = 'internationalBirthDate';
    public const FIELD_INTERNATIONAL_BIRTH_DATE_EXT = '_internationalBirthDate';
    public const FIELD_LEGAL_BASIS = 'legalBasis';
    public const FIELD_JURISDICTIONAL_AUTHORIZATION = 'jurisdictionalAuthorization';
    public const FIELD_HOLDER = 'holder';
    public const FIELD_REGULATOR = 'regulator';
    public const FIELD_PROCEDURE = 'procedure';

    /* class_default.php:75 */
    private const _FHIR_VALIDATION_RULES = [
    ];

    /* class_default.php:96 */
    private array $_valueXMLLocations = [
        self::FIELD_STATUS_DATE => ValueXMLLocationEnum::CONTAINER_ATTRIBUTE,
        self::FIELD_RESTORE_DATE => ValueXMLLocationEnum::CONTAINER_ATTRIBUTE,
        self::FIELD_DATE_OF_FIRST_AUTHORIZATION => ValueXMLLocationEnum::CONTAINER_ATTRIBUTE,
        self::FIELD_INTERNATIONAL_BIRTH_DATE => ValueXMLLocationEnum::CONTAINER_ATTRIBUTE,
    ];

    /* class_default.php:112 */
    /**
     * An identifier - identifies some entity uniquely and unambiguously. Typically
     * this is used for business identifiers.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * Business identifier for the marketing authorization, as assigned by a regulator.
     *
     * @var iterable<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRIdentifier>
     */
    #[FHIRIdentifier]
    protected array $identifier;
    /**
     * A reference from one resource to another.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * The medicinal product that is being authorized.
     *
     * @var \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRReference
     */
    #[FHIRReference]
    protected FHIRReference $subject;
    /**
     * A concept that may be defined by a formal reference to a terminology or ontology
     * or may be provided by text.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * The country in which the marketing authorization has been granted.
     *
     * @var iterable<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRCodeableConcept>
     */
    #[FHIRCodeableConcept]
    protected array $country;
    /**
     * A concept that may be defined by a formal reference to a terminology or ontology
     * or may be provided by text.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * Jurisdiction within a country.
     *
     * @var iterable<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRCodeableConcept>
     */
    #[FHIRCodeableConcept]
    protected array $jurisdiction;
    /**
     * A concept that may be defined by a formal reference to a terminology or ontology
     * or may be provided by text.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * The status of the marketing authorization.
     *
     * @var \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRCodeableConcept
     */
    #[FHIRCodeableConcept]
    protected FHIRCodeableConcept $status;
    /**
     * A date, date-time or partial date (e.g. just year or year + month). If hours and
     * minutes are specified, a time zone SHALL be populated. The format is a union of
     * the schema types gYear, gYearMonth, date and dateTime. Seconds must be provided
     * due to schema type constraints but may be zero-filled and may be ignored. Dates
     * SHALL be valid dates.
     * If the element is present, it must have either a \@value, an \@id, or extensions
     *
     * The date at which the given status has become applicable.
     *
     * @var \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRDateTime
     */
    #[FHIRDateTime]
    protected FHIRDateTime $statusDate;
    /**
     * A date, date-time or partial date (e.g. just year or year + month). If hours and
     * minutes are specified, a time zone SHALL be populated. The format is a union of
     * the schema types gYear, gYearMonth, date and dateTime. Seconds must be provided
     * due to schema type constraints but may be zero-filled and may be ignored. Dates
     * SHALL be valid dates.
     * If the element is present, it must have either a \@value, an \@id, or extensions
     *
     * The date when a suspended the marketing or the marketing authorization of the
     * product is anticipated to be restored.
     *
     * @var \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRDateTime
     */
    #[FHIRDateTime]
    protected FHIRDateTime $restoreDate;
    /**
     * A time period defined by a start and end date and optionally time.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * The beginning of the time period in which the marketing authorization is in the
     * specific status shall be specified A complete date consisting of day, month and
     * year shall be specified using the ISO 8601 date format.
     *
     * @var \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRPeriod
     */
    #[FHIRPeriod]
    protected FHIRPeriod $validityPeriod;
    /**
     * A time period defined by a start and end date and optionally time.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * A period of time after authorization before generic product applicatiosn can be
     * submitted.
     *
     * @var \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRPeriod
     */
    #[FHIRPeriod]
    protected FHIRPeriod $dataExclusivityPeriod;
    /**
     * A date, date-time or partial date (e.g. just year or year + month). If hours and
     * minutes are specified, a time zone SHALL be populated. The format is a union of
     * the schema types gYear, gYearMonth, date and dateTime. Seconds must be provided
     * due to schema type constraints but may be zero-filled and may be ignored. Dates
     * SHALL be valid dates.
     * If the element is present, it must have either a \@value, an \@id, or extensions
     *
     * The date when the first authorization was granted by a Medicines Regulatory
     * Agency.
     *
     * @var \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRDateTime
     */
    #[FHIRDateTime]
    protected FHIRDateTime $dateOfFirstAuthorization;
    /**
     * A date, date-time or partial date (e.g. just year or year + month). If hours and
     * minutes are specified, a time zone SHALL be populated. The format is a union of
     * the schema types gYear, gYearMonth, date and dateTime. Seconds must be provided
     * due to schema type constraints but may be zero-filled and may be ignored. Dates
     * SHALL be valid dates.
     * If the element is present, it must have either a \@value, an \@id, or extensions
     *
     * Date of first marketing authorization for a company's new medicinal product in
     * any country in the World.
     *
     * @var \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRDateTime
     */
    #[FHIRDateTime]
    protected FHIRDateTime $internationalBirthDate;
    /**
     * A concept that may be defined by a formal reference to a terminology or ontology
     * or may be provided by text.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * The legal framework against which this authorization is granted.
     *
     * @var \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRCodeableConcept
     */
    #[FHIRCodeableConcept]
    protected FHIRCodeableConcept $legalBasis;
    /**
     * The regulatory authorization of a medicinal product.
     *
     * Authorization in areas within a country.
     *
     * @var iterable<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRMedicinalProductAuthorization\FHIRMedicinalProductAuthorizationJurisdictionalAuthorization>
     */
    #[FHIRMedicinalProductAuthorizationJurisdictionalAuthorization]
    protected array $jurisdictionalAuthorization;
    /**
     * A reference from one resource to another.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * Marketing Authorization Holder.
     *
     * @var \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRReference
     */
    #[FHIRReference]
    protected FHIRReference $holder;
    /**
     * A reference from one resource to another.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * Medicines Regulatory Agency.
     *
     * @var \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRReference
     */
    #[FHIRReference]
    protected FHIRReference $regulator;
    /**
     * The regulatory authorization of a medicinal product.
     *
     * The regulatory procedure for granting or amending a marketing authorization.
     *
     * @var \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRMedicinalProductAuthorization\FHIRMedicinalProductAuthorizationProcedure
     */
    #[FHIRMedicinalProductAuthorizationProcedure]
    protected FHIRMedicinalProductAuthorizationProcedure $procedure;

    /* constructor.php:61 */
    /**
     * FHIRMedicinalProductAuthorization Constructor
     * @param null|string|\OpenEMR\FHIR\Versions\R4\Types\FHIRIdPrimitive|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRId $id
     * @param null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRMeta $meta
     * @param null|string|\OpenEMR\FHIR\Versions\R4\Types\FHIRUriPrimitive|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRUri $implicitRules
     * @param null|string|\OpenEMR\FHIR\Versions\R4\Types\FHIRCodePrimitive|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRCode $language
     * @param null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRNarrative $text
     * @param null|iterable<\OpenEMR\FHIR\Versions\R4\Types\FHIRResourceContainer>|iterable<\OpenEMR\FHIR\Versions\R4\VersionContainedTypeInterface> $contained
     * @param null|iterable<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRExtension> $extension
     * @param null|iterable<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRExtension> $modifierExtension
     * @param null|iterable<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRIdentifier> $identifier
     * @param null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRReference $subject
     * @param null|iterable<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRCodeableConcept> $country
     * @param null|iterable<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRCodeableConcept> $jurisdiction
     * @param null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRCodeableConcept $status
     * @param null|string|\DateTimeInterface|\OpenEMR\FHIR\Versions\R4\Types\FHIRDateTimePrimitive|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRDateTime $statusDate
     * @param null|string|\DateTimeInterface|\OpenEMR\FHIR\Versions\R4\Types\FHIRDateTimePrimitive|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRDateTime $restoreDate
     * @param null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRPeriod $validityPeriod
     * @param null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRPeriod $dataExclusivityPeriod
     * @param null|string|\DateTimeInterface|\OpenEMR\FHIR\Versions\R4\Types\FHIRDateTimePrimitive|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRDateTime $dateOfFirstAuthorization
     * @param null|string|\DateTimeInterface|\OpenEMR\FHIR\Versions\R4\Types\FHIRDateTimePrimitive|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRDateTime $internationalBirthDate
     * @param null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRCodeableConcept $legalBasis
     * @param null|iterable<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRMedicinalProductAuthorization\FHIRMedicinalProductAuthorizationJurisdictionalAuthorization> $jurisdictionalAuthorization
     * @param null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRReference $holder
     * @param null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRReference $regulator
     * @param null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRMedicinalProductAuthorization\FHIRMedicinalProductAuthorizationProcedure $procedure
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
                                null|FHIRReference $subject = null,
                                null|iterable $country = null,
                                null|iterable $jurisdiction = null,
                                null|FHIRCodeableConcept $status = null,
                                null|string|\DateTimeInterface|FHIRDateTimePrimitive|FHIRDateTime $statusDate = null,
                                null|string|\DateTimeInterface|FHIRDateTimePrimitive|FHIRDateTime $restoreDate = null,
                                null|FHIRPeriod $validityPeriod = null,
                                null|FHIRPeriod $dataExclusivityPeriod = null,
                                null|string|\DateTimeInterface|FHIRDateTimePrimitive|FHIRDateTime $dateOfFirstAuthorization = null,
                                null|string|\DateTimeInterface|FHIRDateTimePrimitive|FHIRDateTime $internationalBirthDate = null,
                                null|FHIRCodeableConcept $legalBasis = null,
                                null|iterable $jurisdictionalAuthorization = null,
                                null|FHIRReference $holder = null,
                                null|FHIRReference $regulator = null,
                                null|FHIRMedicinalProductAuthorizationProcedure $procedure = null,
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
        if (null !== $subject) {
            $this->setSubject($subject);
        }
        if (null !== $country) {
            $this->setCountry(...$country);
        }
        if (null !== $jurisdiction) {
            $this->setJurisdiction(...$jurisdiction);
        }
        if (null !== $status) {
            $this->setStatus($status);
        }
        if (null !== $statusDate) {
            $this->setStatusDate($statusDate);
        }
        if (null !== $restoreDate) {
            $this->setRestoreDate($restoreDate);
        }
        if (null !== $validityPeriod) {
            $this->setValidityPeriod($validityPeriod);
        }
        if (null !== $dataExclusivityPeriod) {
            $this->setDataExclusivityPeriod($dataExclusivityPeriod);
        }
        if (null !== $dateOfFirstAuthorization) {
            $this->setDateOfFirstAuthorization($dateOfFirstAuthorization);
        }
        if (null !== $internationalBirthDate) {
            $this->setInternationalBirthDate($internationalBirthDate);
        }
        if (null !== $legalBasis) {
            $this->setLegalBasis($legalBasis);
        }
        if (null !== $jurisdictionalAuthorization) {
            $this->setJurisdictionalAuthorization(...$jurisdictionalAuthorization);
        }
        if (null !== $holder) {
            $this->setHolder($holder);
        }
        if (null !== $regulator) {
            $this->setRegulator($regulator);
        }
        if (null !== $procedure) {
            $this->setProcedure($procedure);
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
     * Business identifier for the marketing authorization, as assigned by a regulator.
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
     * Business identifier for the marketing authorization, as assigned by a regulator.
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
     * Business identifier for the marketing authorization, as assigned by a regulator.
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
     * A reference from one resource to another.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * The medicinal product that is being authorized.
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
     * The medicinal product that is being authorized.
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
     * A concept that may be defined by a formal reference to a terminology or ontology
     * or may be provided by text.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * The country in which the marketing authorization has been granted.
     *
     * @return iterable<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRCodeableConcept>
     */
    public function getCountry(): array
    {
        return $this->country ?? [];
    }

    /**
     * @return \ArrayIterator<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRCodeableConcept>
     */
    public function getCountryIterator(): iterable
    {
        if (!isset($this->country)) {
            return new \EmptyIterator();
        }
        return new \ArrayIterator($this->country);
    }

    /**
     * A concept that may be defined by a formal reference to a terminology or ontology
     * or may be provided by text.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * The country in which the marketing authorization has been granted.
     *
     * @param \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRCodeableConcept $country
     * @return static
     */
    public function addCountry(FHIRCodeableConcept $country): self
    {
        if (!isset($this->country)) {
            $this->country = [];
        }
        $this->country[] = $country;
        return $this;
    }

    /**
     * A concept that may be defined by a formal reference to a terminology or ontology
     * or may be provided by text.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * The country in which the marketing authorization has been granted.
     *
     * @param \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRCodeableConcept ...$country
     * @return static
     */
    public function setCountry(FHIRCodeableConcept ...$country): self
    {
        if ([] === $country) {
            unset($this->country);
            return $this;
        }
        $this->country = $country;
        return $this;
    }

    /**
     * A concept that may be defined by a formal reference to a terminology or ontology
     * or may be provided by text.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * Jurisdiction within a country.
     *
     * @return iterable<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRCodeableConcept>
     */
    public function getJurisdiction(): array
    {
        return $this->jurisdiction ?? [];
    }

    /**
     * @return \ArrayIterator<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRCodeableConcept>
     */
    public function getJurisdictionIterator(): iterable
    {
        if (!isset($this->jurisdiction)) {
            return new \EmptyIterator();
        }
        return new \ArrayIterator($this->jurisdiction);
    }

    /**
     * A concept that may be defined by a formal reference to a terminology or ontology
     * or may be provided by text.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * Jurisdiction within a country.
     *
     * @param \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRCodeableConcept $jurisdiction
     * @return static
     */
    public function addJurisdiction(FHIRCodeableConcept $jurisdiction): self
    {
        if (!isset($this->jurisdiction)) {
            $this->jurisdiction = [];
        }
        $this->jurisdiction[] = $jurisdiction;
        return $this;
    }

    /**
     * A concept that may be defined by a formal reference to a terminology or ontology
     * or may be provided by text.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * Jurisdiction within a country.
     *
     * @param \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRCodeableConcept ...$jurisdiction
     * @return static
     */
    public function setJurisdiction(FHIRCodeableConcept ...$jurisdiction): self
    {
        if ([] === $jurisdiction) {
            unset($this->jurisdiction);
            return $this;
        }
        $this->jurisdiction = $jurisdiction;
        return $this;
    }

    /**
     * A concept that may be defined by a formal reference to a terminology or ontology
     * or may be provided by text.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * The status of the marketing authorization.
     *
     * @return null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRCodeableConcept
     */
    public function getStatus(): null|FHIRCodeableConcept
    {
        return $this->status ?? null;
    }

    /**
     * A concept that may be defined by a formal reference to a terminology or ontology
     * or may be provided by text.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * The status of the marketing authorization.
     *
     * @param null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRCodeableConcept $status
     * @return static
     */
    public function setStatus(null|FHIRCodeableConcept $status): self
    {
        if (null === $status) {
            unset($this->status);
            return $this;
        }
        $this->status = $status;
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
     * The date at which the given status has become applicable.
     *
     * @return null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRDateTime
     */
    public function getStatusDate(): null|FHIRDateTime
    {
        return $this->statusDate ?? null;
    }

    /**
     * A date, date-time or partial date (e.g. just year or year + month). If hours and
     * minutes are specified, a time zone SHALL be populated. The format is a union of
     * the schema types gYear, gYearMonth, date and dateTime. Seconds must be provided
     * due to schema type constraints but may be zero-filled and may be ignored. Dates
     * SHALL be valid dates.
     * If the element is present, it must have either a \@value, an \@id, or extensions
     *
     * The date at which the given status has become applicable.
     *
     * @param null|string|\DateTimeInterface|\OpenEMR\FHIR\Versions\R4\Types\FHIRDateTimePrimitive|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRDateTime $statusDate
     * @return static
     */
    public function setStatusDate(null|string|\DateTimeInterface|FHIRDateTimePrimitive|FHIRDateTime $statusDate): self
    {
        if (null === $statusDate) {
            unset($this->statusDate);
            return $this;
        }
        if (!($statusDate instanceof FHIRDateTime)) {
            $statusDate = new FHIRDateTime(value: $statusDate);
        }
        $this->statusDate = $statusDate;
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
     * The date when a suspended the marketing or the marketing authorization of the
     * product is anticipated to be restored.
     *
     * @return null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRDateTime
     */
    public function getRestoreDate(): null|FHIRDateTime
    {
        return $this->restoreDate ?? null;
    }

    /**
     * A date, date-time or partial date (e.g. just year or year + month). If hours and
     * minutes are specified, a time zone SHALL be populated. The format is a union of
     * the schema types gYear, gYearMonth, date and dateTime. Seconds must be provided
     * due to schema type constraints but may be zero-filled and may be ignored. Dates
     * SHALL be valid dates.
     * If the element is present, it must have either a \@value, an \@id, or extensions
     *
     * The date when a suspended the marketing or the marketing authorization of the
     * product is anticipated to be restored.
     *
     * @param null|string|\DateTimeInterface|\OpenEMR\FHIR\Versions\R4\Types\FHIRDateTimePrimitive|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRDateTime $restoreDate
     * @return static
     */
    public function setRestoreDate(null|string|\DateTimeInterface|FHIRDateTimePrimitive|FHIRDateTime $restoreDate): self
    {
        if (null === $restoreDate) {
            unset($this->restoreDate);
            return $this;
        }
        if (!($restoreDate instanceof FHIRDateTime)) {
            $restoreDate = new FHIRDateTime(value: $restoreDate);
        }
        $this->restoreDate = $restoreDate;
        return $this;
    }

    /**
     * A time period defined by a start and end date and optionally time.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * The beginning of the time period in which the marketing authorization is in the
     * specific status shall be specified A complete date consisting of day, month and
     * year shall be specified using the ISO 8601 date format.
     *
     * @return null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRPeriod
     */
    public function getValidityPeriod(): null|FHIRPeriod
    {
        return $this->validityPeriod ?? null;
    }

    /**
     * A time period defined by a start and end date and optionally time.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * The beginning of the time period in which the marketing authorization is in the
     * specific status shall be specified A complete date consisting of day, month and
     * year shall be specified using the ISO 8601 date format.
     *
     * @param null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRPeriod $validityPeriod
     * @return static
     */
    public function setValidityPeriod(null|FHIRPeriod $validityPeriod): self
    {
        if (null === $validityPeriod) {
            unset($this->validityPeriod);
            return $this;
        }
        $this->validityPeriod = $validityPeriod;
        return $this;
    }

    /**
     * A time period defined by a start and end date and optionally time.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * A period of time after authorization before generic product applicatiosn can be
     * submitted.
     *
     * @return null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRPeriod
     */
    public function getDataExclusivityPeriod(): null|FHIRPeriod
    {
        return $this->dataExclusivityPeriod ?? null;
    }

    /**
     * A time period defined by a start and end date and optionally time.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * A period of time after authorization before generic product applicatiosn can be
     * submitted.
     *
     * @param null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRPeriod $dataExclusivityPeriod
     * @return static
     */
    public function setDataExclusivityPeriod(null|FHIRPeriod $dataExclusivityPeriod): self
    {
        if (null === $dataExclusivityPeriod) {
            unset($this->dataExclusivityPeriod);
            return $this;
        }
        $this->dataExclusivityPeriod = $dataExclusivityPeriod;
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
     * The date when the first authorization was granted by a Medicines Regulatory
     * Agency.
     *
     * @return null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRDateTime
     */
    public function getDateOfFirstAuthorization(): null|FHIRDateTime
    {
        return $this->dateOfFirstAuthorization ?? null;
    }

    /**
     * A date, date-time or partial date (e.g. just year or year + month). If hours and
     * minutes are specified, a time zone SHALL be populated. The format is a union of
     * the schema types gYear, gYearMonth, date and dateTime. Seconds must be provided
     * due to schema type constraints but may be zero-filled and may be ignored. Dates
     * SHALL be valid dates.
     * If the element is present, it must have either a \@value, an \@id, or extensions
     *
     * The date when the first authorization was granted by a Medicines Regulatory
     * Agency.
     *
     * @param null|string|\DateTimeInterface|\OpenEMR\FHIR\Versions\R4\Types\FHIRDateTimePrimitive|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRDateTime $dateOfFirstAuthorization
     * @return static
     */
    public function setDateOfFirstAuthorization(null|string|\DateTimeInterface|FHIRDateTimePrimitive|FHIRDateTime $dateOfFirstAuthorization): self
    {
        if (null === $dateOfFirstAuthorization) {
            unset($this->dateOfFirstAuthorization);
            return $this;
        }
        if (!($dateOfFirstAuthorization instanceof FHIRDateTime)) {
            $dateOfFirstAuthorization = new FHIRDateTime(value: $dateOfFirstAuthorization);
        }
        $this->dateOfFirstAuthorization = $dateOfFirstAuthorization;
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
     * Date of first marketing authorization for a company's new medicinal product in
     * any country in the World.
     *
     * @return null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRDateTime
     */
    public function getInternationalBirthDate(): null|FHIRDateTime
    {
        return $this->internationalBirthDate ?? null;
    }

    /**
     * A date, date-time or partial date (e.g. just year or year + month). If hours and
     * minutes are specified, a time zone SHALL be populated. The format is a union of
     * the schema types gYear, gYearMonth, date and dateTime. Seconds must be provided
     * due to schema type constraints but may be zero-filled and may be ignored. Dates
     * SHALL be valid dates.
     * If the element is present, it must have either a \@value, an \@id, or extensions
     *
     * Date of first marketing authorization for a company's new medicinal product in
     * any country in the World.
     *
     * @param null|string|\DateTimeInterface|\OpenEMR\FHIR\Versions\R4\Types\FHIRDateTimePrimitive|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRDateTime $internationalBirthDate
     * @return static
     */
    public function setInternationalBirthDate(null|string|\DateTimeInterface|FHIRDateTimePrimitive|FHIRDateTime $internationalBirthDate): self
    {
        if (null === $internationalBirthDate) {
            unset($this->internationalBirthDate);
            return $this;
        }
        if (!($internationalBirthDate instanceof FHIRDateTime)) {
            $internationalBirthDate = new FHIRDateTime(value: $internationalBirthDate);
        }
        $this->internationalBirthDate = $internationalBirthDate;
        return $this;
    }

    /**
     * A concept that may be defined by a formal reference to a terminology or ontology
     * or may be provided by text.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * The legal framework against which this authorization is granted.
     *
     * @return null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRCodeableConcept
     */
    public function getLegalBasis(): null|FHIRCodeableConcept
    {
        return $this->legalBasis ?? null;
    }

    /**
     * A concept that may be defined by a formal reference to a terminology or ontology
     * or may be provided by text.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * The legal framework against which this authorization is granted.
     *
     * @param null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRCodeableConcept $legalBasis
     * @return static
     */
    public function setLegalBasis(null|FHIRCodeableConcept $legalBasis): self
    {
        if (null === $legalBasis) {
            unset($this->legalBasis);
            return $this;
        }
        $this->legalBasis = $legalBasis;
        return $this;
    }

    /**
     * The regulatory authorization of a medicinal product.
     *
     * Authorization in areas within a country.
     *
     * @return iterable<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRMedicinalProductAuthorization\FHIRMedicinalProductAuthorizationJurisdictionalAuthorization>
     */
    public function getJurisdictionalAuthorization(): array
    {
        return $this->jurisdictionalAuthorization ?? [];
    }

    /**
     * @return \ArrayIterator<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRMedicinalProductAuthorization\FHIRMedicinalProductAuthorizationJurisdictionalAuthorization>
     */
    public function getJurisdictionalAuthorizationIterator(): iterable
    {
        if (!isset($this->jurisdictionalAuthorization)) {
            return new \EmptyIterator();
        }
        return new \ArrayIterator($this->jurisdictionalAuthorization);
    }

    /**
     * The regulatory authorization of a medicinal product.
     *
     * Authorization in areas within a country.
     *
     * @param \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRMedicinalProductAuthorization\FHIRMedicinalProductAuthorizationJurisdictionalAuthorization $jurisdictionalAuthorization
     * @return static
     */
    public function addJurisdictionalAuthorization(FHIRMedicinalProductAuthorizationJurisdictionalAuthorization $jurisdictionalAuthorization): self
    {
        if (!isset($this->jurisdictionalAuthorization)) {
            $this->jurisdictionalAuthorization = [];
        }
        $this->jurisdictionalAuthorization[] = $jurisdictionalAuthorization;
        return $this;
    }

    /**
     * The regulatory authorization of a medicinal product.
     *
     * Authorization in areas within a country.
     *
     * @param \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRMedicinalProductAuthorization\FHIRMedicinalProductAuthorizationJurisdictionalAuthorization ...$jurisdictionalAuthorization
     * @return static
     */
    public function setJurisdictionalAuthorization(FHIRMedicinalProductAuthorizationJurisdictionalAuthorization ...$jurisdictionalAuthorization): self
    {
        if ([] === $jurisdictionalAuthorization) {
            unset($this->jurisdictionalAuthorization);
            return $this;
        }
        $this->jurisdictionalAuthorization = $jurisdictionalAuthorization;
        return $this;
    }

    /**
     * A reference from one resource to another.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * Marketing Authorization Holder.
     *
     * @return null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRReference
     */
    public function getHolder(): null|FHIRReference
    {
        return $this->holder ?? null;
    }

    /**
     * A reference from one resource to another.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * Marketing Authorization Holder.
     *
     * @param null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRReference $holder
     * @return static
     */
    public function setHolder(null|FHIRReference $holder): self
    {
        if (null === $holder) {
            unset($this->holder);
            return $this;
        }
        $this->holder = $holder;
        return $this;
    }

    /**
     * A reference from one resource to another.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * Medicines Regulatory Agency.
     *
     * @return null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRReference
     */
    public function getRegulator(): null|FHIRReference
    {
        return $this->regulator ?? null;
    }

    /**
     * A reference from one resource to another.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * Medicines Regulatory Agency.
     *
     * @param null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRReference $regulator
     * @return static
     */
    public function setRegulator(null|FHIRReference $regulator): self
    {
        if (null === $regulator) {
            unset($this->regulator);
            return $this;
        }
        $this->regulator = $regulator;
        return $this;
    }

    /**
     * The regulatory authorization of a medicinal product.
     *
     * The regulatory procedure for granting or amending a marketing authorization.
     *
     * @return null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRMedicinalProductAuthorization\FHIRMedicinalProductAuthorizationProcedure
     */
    public function getProcedure(): null|FHIRMedicinalProductAuthorizationProcedure
    {
        return $this->procedure ?? null;
    }

    /**
     * The regulatory authorization of a medicinal product.
     *
     * The regulatory procedure for granting or amending a marketing authorization.
     *
     * @param null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRMedicinalProductAuthorization\FHIRMedicinalProductAuthorizationProcedure $procedure
     * @return static
     */
    public function setProcedure(null|FHIRMedicinalProductAuthorizationProcedure $procedure): self
    {
        if (null === $procedure) {
            unset($this->procedure);
            return $this;
        }
        $this->procedure = $procedure;
        return $this;
    }

    /* class_default.php:201 */
    /**
     * @param string|\SimpleXMLElement $element
     * @param null|\OpenEMR\FHIR\Encoding\UnserializeConfig $config
     * @param null|\OpenEMR\FHIR\Versions\R4\Types\FHIRResource\FHIRDomainResource\FHIRMedicinalProductAuthorization $type
     * @return \OpenEMR\FHIR\Versions\R4\Types\FHIRResource\FHIRDomainResource\FHIRMedicinalProductAuthorization
     * @throws \Exception
     */
    public static function xmlUnserialize(string|\SimpleXMLElement $element,
                                          null|UnserializeConfig $config = null,
                                          null|ResourceTypeInterface $type = null): self
    {
        if (null === $type) {
            $type = new static();
        } else if (!($type instanceof FHIRMedicinalProductAuthorization)) {
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
            } else if (self::FIELD_SUBJECT === $cen) {
                $type->setSubject(FHIRReference::xmlUnserialize($ce, $config));
            } else if (self::FIELD_COUNTRY === $cen) {
                $type->addCountry(FHIRCodeableConcept::xmlUnserialize($ce, $config));
            } else if (self::FIELD_JURISDICTION === $cen) {
                $type->addJurisdiction(FHIRCodeableConcept::xmlUnserialize($ce, $config));
            } else if (self::FIELD_STATUS === $cen) {
                $type->setStatus(FHIRCodeableConcept::xmlUnserialize($ce, $config));
            } else if (self::FIELD_STATUS_DATE === $cen) {
                $type->setStatusDate(FHIRDateTime::xmlUnserialize($ce, $config));
            } else if (self::FIELD_RESTORE_DATE === $cen) {
                $type->setRestoreDate(FHIRDateTime::xmlUnserialize($ce, $config));
            } else if (self::FIELD_VALIDITY_PERIOD === $cen) {
                $type->setValidityPeriod(FHIRPeriod::xmlUnserialize($ce, $config));
            } else if (self::FIELD_DATA_EXCLUSIVITY_PERIOD === $cen) {
                $type->setDataExclusivityPeriod(FHIRPeriod::xmlUnserialize($ce, $config));
            } else if (self::FIELD_DATE_OF_FIRST_AUTHORIZATION === $cen) {
                $type->setDateOfFirstAuthorization(FHIRDateTime::xmlUnserialize($ce, $config));
            } else if (self::FIELD_INTERNATIONAL_BIRTH_DATE === $cen) {
                $type->setInternationalBirthDate(FHIRDateTime::xmlUnserialize($ce, $config));
            } else if (self::FIELD_LEGAL_BASIS === $cen) {
                $type->setLegalBasis(FHIRCodeableConcept::xmlUnserialize($ce, $config));
            } else if (self::FIELD_JURISDICTIONAL_AUTHORIZATION === $cen) {
                $type->addJurisdictionalAuthorization(FHIRMedicinalProductAuthorizationJurisdictionalAuthorization::xmlUnserialize($ce, $config));
            } else if (self::FIELD_HOLDER === $cen) {
                $type->setHolder(FHIRReference::xmlUnserialize($ce, $config));
            } else if (self::FIELD_REGULATOR === $cen) {
                $type->setRegulator(FHIRReference::xmlUnserialize($ce, $config));
            } else if (self::FIELD_PROCEDURE === $cen) {
                $type->setProcedure(FHIRMedicinalProductAuthorizationProcedure::xmlUnserialize($ce, $config));
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
        if (isset($attributes[self::FIELD_STATUS_DATE])) {
            if (isset($type->statusDate)) {
                $type->statusDate->setValue((string)$attributes[self::FIELD_STATUS_DATE]);
            } else {
                $type->setStatusDate((string)$attributes[self::FIELD_STATUS_DATE]);
            }
            $type->_setXMLFieldValueLocation(self::FIELD_STATUS_DATE, ValueXMLLocationEnum::PARENT_ATTRIBUTE);
        }
        if (isset($attributes[self::FIELD_RESTORE_DATE])) {
            if (isset($type->restoreDate)) {
                $type->restoreDate->setValue((string)$attributes[self::FIELD_RESTORE_DATE]);
            } else {
                $type->setRestoreDate((string)$attributes[self::FIELD_RESTORE_DATE]);
            }
            $type->_setXMLFieldValueLocation(self::FIELD_RESTORE_DATE, ValueXMLLocationEnum::PARENT_ATTRIBUTE);
        }
        if (isset($attributes[self::FIELD_DATE_OF_FIRST_AUTHORIZATION])) {
            if (isset($type->dateOfFirstAuthorization)) {
                $type->dateOfFirstAuthorization->setValue((string)$attributes[self::FIELD_DATE_OF_FIRST_AUTHORIZATION]);
            } else {
                $type->setDateOfFirstAuthorization((string)$attributes[self::FIELD_DATE_OF_FIRST_AUTHORIZATION]);
            }
            $type->_setXMLFieldValueLocation(self::FIELD_DATE_OF_FIRST_AUTHORIZATION, ValueXMLLocationEnum::PARENT_ATTRIBUTE);
        }
        if (isset($attributes[self::FIELD_INTERNATIONAL_BIRTH_DATE])) {
            if (isset($type->internationalBirthDate)) {
                $type->internationalBirthDate->setValue((string)$attributes[self::FIELD_INTERNATIONAL_BIRTH_DATE]);
            } else {
                $type->setInternationalBirthDate((string)$attributes[self::FIELD_INTERNATIONAL_BIRTH_DATE]);
            }
            $type->_setXMLFieldValueLocation(self::FIELD_INTERNATIONAL_BIRTH_DATE, ValueXMLLocationEnum::PARENT_ATTRIBUTE);
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
            $xw->openRootNode('MedicinalProductAuthorization', $this->_getSourceXMLNS());
        }
        if (isset($this->statusDate) && ValueXMLLocationEnum::PARENT_ATTRIBUTE === $this->_valueXMLLocations[self::FIELD_STATUS_DATE]) {
            $xw->writeAttribute(self::FIELD_STATUS_DATE, $this->statusDate->_getValueAsString());
        }
        if (isset($this->restoreDate) && ValueXMLLocationEnum::PARENT_ATTRIBUTE === $this->_valueXMLLocations[self::FIELD_RESTORE_DATE]) {
            $xw->writeAttribute(self::FIELD_RESTORE_DATE, $this->restoreDate->_getValueAsString());
        }
        if (isset($this->dateOfFirstAuthorization) && ValueXMLLocationEnum::PARENT_ATTRIBUTE === $this->_valueXMLLocations[self::FIELD_DATE_OF_FIRST_AUTHORIZATION]) {
            $xw->writeAttribute(self::FIELD_DATE_OF_FIRST_AUTHORIZATION, $this->dateOfFirstAuthorization->_getValueAsString());
        }
        if (isset($this->internationalBirthDate) && ValueXMLLocationEnum::PARENT_ATTRIBUTE === $this->_valueXMLLocations[self::FIELD_INTERNATIONAL_BIRTH_DATE]) {
            $xw->writeAttribute(self::FIELD_INTERNATIONAL_BIRTH_DATE, $this->internationalBirthDate->_getValueAsString());
        }
        parent::xmlSerialize($xw, $config);
        if (isset($this->identifier)) {
            foreach ($this->identifier as $v) {
                $xw->startElement(self::FIELD_IDENTIFIER);
                $v->xmlSerialize($xw, $config);
                $xw->endElement();
            }
        }
        if (isset($this->subject)) {
            $xw->startElement(self::FIELD_SUBJECT);
            $this->subject->xmlSerialize($xw, $config);
            $xw->endElement();
        }
        if (isset($this->country)) {
            foreach ($this->country as $v) {
                $xw->startElement(self::FIELD_COUNTRY);
                $v->xmlSerialize($xw, $config);
                $xw->endElement();
            }
        }
        if (isset($this->jurisdiction)) {
            foreach ($this->jurisdiction as $v) {
                $xw->startElement(self::FIELD_JURISDICTION);
                $v->xmlSerialize($xw, $config);
                $xw->endElement();
            }
        }
        if (isset($this->status)) {
            $xw->startElement(self::FIELD_STATUS);
            $this->status->xmlSerialize($xw, $config);
            $xw->endElement();
        }
        if (isset($this->statusDate)
            && (ValueXMLLocationEnum::PARENT_ATTRIBUTE !== $this->_valueXMLLocations[self::FIELD_STATUS_DATE]
                || $this->statusDate->_nonValueFieldDefined())) {
            $xw->startElement(self::FIELD_STATUS_DATE);
            $this->statusDate->xmlSerialize($xw, $config, $this->_valueXMLLocations[self::FIELD_STATUS_DATE]);
            $xw->endElement();
        }
        if (isset($this->restoreDate)
            && (ValueXMLLocationEnum::PARENT_ATTRIBUTE !== $this->_valueXMLLocations[self::FIELD_RESTORE_DATE]
                || $this->restoreDate->_nonValueFieldDefined())) {
            $xw->startElement(self::FIELD_RESTORE_DATE);
            $this->restoreDate->xmlSerialize($xw, $config, $this->_valueXMLLocations[self::FIELD_RESTORE_DATE]);
            $xw->endElement();
        }
        if (isset($this->validityPeriod)) {
            $xw->startElement(self::FIELD_VALIDITY_PERIOD);
            $this->validityPeriod->xmlSerialize($xw, $config);
            $xw->endElement();
        }
        if (isset($this->dataExclusivityPeriod)) {
            $xw->startElement(self::FIELD_DATA_EXCLUSIVITY_PERIOD);
            $this->dataExclusivityPeriod->xmlSerialize($xw, $config);
            $xw->endElement();
        }
        if (isset($this->dateOfFirstAuthorization)
            && (ValueXMLLocationEnum::PARENT_ATTRIBUTE !== $this->_valueXMLLocations[self::FIELD_DATE_OF_FIRST_AUTHORIZATION]
                || $this->dateOfFirstAuthorization->_nonValueFieldDefined())) {
            $xw->startElement(self::FIELD_DATE_OF_FIRST_AUTHORIZATION);
            $this->dateOfFirstAuthorization->xmlSerialize($xw, $config, $this->_valueXMLLocations[self::FIELD_DATE_OF_FIRST_AUTHORIZATION]);
            $xw->endElement();
        }
        if (isset($this->internationalBirthDate)
            && (ValueXMLLocationEnum::PARENT_ATTRIBUTE !== $this->_valueXMLLocations[self::FIELD_INTERNATIONAL_BIRTH_DATE]
                || $this->internationalBirthDate->_nonValueFieldDefined())) {
            $xw->startElement(self::FIELD_INTERNATIONAL_BIRTH_DATE);
            $this->internationalBirthDate->xmlSerialize($xw, $config, $this->_valueXMLLocations[self::FIELD_INTERNATIONAL_BIRTH_DATE]);
            $xw->endElement();
        }
        if (isset($this->legalBasis)) {
            $xw->startElement(self::FIELD_LEGAL_BASIS);
            $this->legalBasis->xmlSerialize($xw, $config);
            $xw->endElement();
        }
        if (isset($this->jurisdictionalAuthorization)) {
            foreach ($this->jurisdictionalAuthorization as $v) {
                $xw->startElement(self::FIELD_JURISDICTIONAL_AUTHORIZATION);
                $v->xmlSerialize($xw, $config);
                $xw->endElement();
            }
        }
        if (isset($this->holder)) {
            $xw->startElement(self::FIELD_HOLDER);
            $this->holder->xmlSerialize($xw, $config);
            $xw->endElement();
        }
        if (isset($this->regulator)) {
            $xw->startElement(self::FIELD_REGULATOR);
            $this->regulator->xmlSerialize($xw, $config);
            $xw->endElement();
        }
        if (isset($this->procedure)) {
            $xw->startElement(self::FIELD_PROCEDURE);
            $this->procedure->xmlSerialize($xw, $config);
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
     * @param null|\OpenEMR\FHIR\Versions\R4\Types\FHIRResource\FHIRDomainResource\FHIRMedicinalProductAuthorization $type
     * @return \OpenEMR\FHIR\Versions\R4\Types\FHIRResource\FHIRDomainResource\FHIRMedicinalProductAuthorization
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
        } else if (!($type instanceof FHIRMedicinalProductAuthorization)) {
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
        if (isset($decoded->subject) || property_exists($decoded, self::FIELD_SUBJECT)) {
            if (is_array($decoded->subject)) {
                $type->setSubject(FHIRReference::jsonUnserialize(reset($decoded->subject), $config));
            } else {
                $type->setSubject(FHIRReference::jsonUnserialize($decoded->subject, $config));
            }
        }
        if (isset($decoded->country) || property_exists($decoded, self::FIELD_COUNTRY)) {
            if (is_object($decoded->country)) {
                $vals = [$decoded->country];
                $type->_setJSONFieldElideSingletonArray(self::FIELD_COUNTRY, true);
            } else {
                $vals = $decoded->country;
            }
            foreach($vals as $v) {
                $type->addCountry(FHIRCodeableConcept::jsonUnserialize($v, $config));
            }
        }
        if (isset($decoded->jurisdiction) || property_exists($decoded, self::FIELD_JURISDICTION)) {
            if (is_object($decoded->jurisdiction)) {
                $vals = [$decoded->jurisdiction];
                $type->_setJSONFieldElideSingletonArray(self::FIELD_JURISDICTION, true);
            } else {
                $vals = $decoded->jurisdiction;
            }
            foreach($vals as $v) {
                $type->addJurisdiction(FHIRCodeableConcept::jsonUnserialize($v, $config));
            }
        }
        if (isset($decoded->status) || property_exists($decoded, self::FIELD_STATUS)) {
            if (is_array($decoded->status)) {
                $type->setStatus(FHIRCodeableConcept::jsonUnserialize(reset($decoded->status), $config));
            } else {
                $type->setStatus(FHIRCodeableConcept::jsonUnserialize($decoded->status, $config));
            }
        }
        if (isset($decoded->statusDate)
            || isset($decoded->_statusDate)
            || property_exists($decoded, self::FIELD_STATUS_DATE)
            || property_exists($decoded, self::FIELD_STATUS_DATE_EXT)) {
            $v = $decoded->_statusDate ?? new \stdClass();
            $v->value = $decoded->statusDate ?? null;
            $type->setStatusDate(FHIRDateTime::jsonUnserialize($v, $config));
        }
        if (isset($decoded->restoreDate)
            || isset($decoded->_restoreDate)
            || property_exists($decoded, self::FIELD_RESTORE_DATE)
            || property_exists($decoded, self::FIELD_RESTORE_DATE_EXT)) {
            $v = $decoded->_restoreDate ?? new \stdClass();
            $v->value = $decoded->restoreDate ?? null;
            $type->setRestoreDate(FHIRDateTime::jsonUnserialize($v, $config));
        }
        if (isset($decoded->validityPeriod) || property_exists($decoded, self::FIELD_VALIDITY_PERIOD)) {
            if (is_array($decoded->validityPeriod)) {
                $type->setValidityPeriod(FHIRPeriod::jsonUnserialize(reset($decoded->validityPeriod), $config));
            } else {
                $type->setValidityPeriod(FHIRPeriod::jsonUnserialize($decoded->validityPeriod, $config));
            }
        }
        if (isset($decoded->dataExclusivityPeriod) || property_exists($decoded, self::FIELD_DATA_EXCLUSIVITY_PERIOD)) {
            if (is_array($decoded->dataExclusivityPeriod)) {
                $type->setDataExclusivityPeriod(FHIRPeriod::jsonUnserialize(reset($decoded->dataExclusivityPeriod), $config));
            } else {
                $type->setDataExclusivityPeriod(FHIRPeriod::jsonUnserialize($decoded->dataExclusivityPeriod, $config));
            }
        }
        if (isset($decoded->dateOfFirstAuthorization)
            || isset($decoded->_dateOfFirstAuthorization)
            || property_exists($decoded, self::FIELD_DATE_OF_FIRST_AUTHORIZATION)
            || property_exists($decoded, self::FIELD_DATE_OF_FIRST_AUTHORIZATION_EXT)) {
            $v = $decoded->_dateOfFirstAuthorization ?? new \stdClass();
            $v->value = $decoded->dateOfFirstAuthorization ?? null;
            $type->setDateOfFirstAuthorization(FHIRDateTime::jsonUnserialize($v, $config));
        }
        if (isset($decoded->internationalBirthDate)
            || isset($decoded->_internationalBirthDate)
            || property_exists($decoded, self::FIELD_INTERNATIONAL_BIRTH_DATE)
            || property_exists($decoded, self::FIELD_INTERNATIONAL_BIRTH_DATE_EXT)) {
            $v = $decoded->_internationalBirthDate ?? new \stdClass();
            $v->value = $decoded->internationalBirthDate ?? null;
            $type->setInternationalBirthDate(FHIRDateTime::jsonUnserialize($v, $config));
        }
        if (isset($decoded->legalBasis) || property_exists($decoded, self::FIELD_LEGAL_BASIS)) {
            if (is_array($decoded->legalBasis)) {
                $type->setLegalBasis(FHIRCodeableConcept::jsonUnserialize(reset($decoded->legalBasis), $config));
            } else {
                $type->setLegalBasis(FHIRCodeableConcept::jsonUnserialize($decoded->legalBasis, $config));
            }
        }
        if (isset($decoded->jurisdictionalAuthorization) || property_exists($decoded, self::FIELD_JURISDICTIONAL_AUTHORIZATION)) {
            if (is_object($decoded->jurisdictionalAuthorization)) {
                $vals = [$decoded->jurisdictionalAuthorization];
                $type->_setJSONFieldElideSingletonArray(self::FIELD_JURISDICTIONAL_AUTHORIZATION, true);
            } else {
                $vals = $decoded->jurisdictionalAuthorization;
            }
            foreach($vals as $v) {
                $type->addJurisdictionalAuthorization(FHIRMedicinalProductAuthorizationJurisdictionalAuthorization::jsonUnserialize($v, $config));
            }
        }
        if (isset($decoded->holder) || property_exists($decoded, self::FIELD_HOLDER)) {
            if (is_array($decoded->holder)) {
                $type->setHolder(FHIRReference::jsonUnserialize(reset($decoded->holder), $config));
            } else {
                $type->setHolder(FHIRReference::jsonUnserialize($decoded->holder, $config));
            }
        }
        if (isset($decoded->regulator) || property_exists($decoded, self::FIELD_REGULATOR)) {
            if (is_array($decoded->regulator)) {
                $type->setRegulator(FHIRReference::jsonUnserialize(reset($decoded->regulator), $config));
            } else {
                $type->setRegulator(FHIRReference::jsonUnserialize($decoded->regulator, $config));
            }
        }
        if (isset($decoded->procedure) || property_exists($decoded, self::FIELD_PROCEDURE)) {
            if (is_array($decoded->procedure)) {
                $type->setProcedure(FHIRMedicinalProductAuthorizationProcedure::jsonUnserialize(reset($decoded->procedure), $config));
            } else {
                $type->setProcedure(FHIRMedicinalProductAuthorizationProcedure::jsonUnserialize($decoded->procedure, $config));
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
        if (isset($this->subject)) {
            $out->subject = $this->subject;
        }
        if (isset($this->country) && [] !== $this->country) {
            if ($this->_getJSONFieldElideSingletonArray(self::FIELD_COUNTRY) && 1 === count($this->country)) {
                $out->country = $this->country[0];
            } else {
                $out->country = $this->country;
            }
        }
        if (isset($this->jurisdiction) && [] !== $this->jurisdiction) {
            if ($this->_getJSONFieldElideSingletonArray(self::FIELD_JURISDICTION) && 1 === count($this->jurisdiction)) {
                $out->jurisdiction = $this->jurisdiction[0];
            } else {
                $out->jurisdiction = $this->jurisdiction;
            }
        }
        if (isset($this->status)) {
            $out->status = $this->status;
        }
        if (isset($this->statusDate)) {
            if (null !== ($val = $this->statusDate->getValue())) {
                $out->statusDate = $val;
            }
            if ($this->statusDate->_nonValueFieldDefined()) {
                $ext = $this->statusDate->jsonSerialize();
                unset($ext->value);
                $out->_statusDate = $ext;
            }
        }
        if (isset($this->restoreDate)) {
            if (null !== ($val = $this->restoreDate->getValue())) {
                $out->restoreDate = $val;
            }
            if ($this->restoreDate->_nonValueFieldDefined()) {
                $ext = $this->restoreDate->jsonSerialize();
                unset($ext->value);
                $out->_restoreDate = $ext;
            }
        }
        if (isset($this->validityPeriod)) {
            $out->validityPeriod = $this->validityPeriod;
        }
        if (isset($this->dataExclusivityPeriod)) {
            $out->dataExclusivityPeriod = $this->dataExclusivityPeriod;
        }
        if (isset($this->dateOfFirstAuthorization)) {
            if (null !== ($val = $this->dateOfFirstAuthorization->getValue())) {
                $out->dateOfFirstAuthorization = $val;
            }
            if ($this->dateOfFirstAuthorization->_nonValueFieldDefined()) {
                $ext = $this->dateOfFirstAuthorization->jsonSerialize();
                unset($ext->value);
                $out->_dateOfFirstAuthorization = $ext;
            }
        }
        if (isset($this->internationalBirthDate)) {
            if (null !== ($val = $this->internationalBirthDate->getValue())) {
                $out->internationalBirthDate = $val;
            }
            if ($this->internationalBirthDate->_nonValueFieldDefined()) {
                $ext = $this->internationalBirthDate->jsonSerialize();
                unset($ext->value);
                $out->_internationalBirthDate = $ext;
            }
        }
        if (isset($this->legalBasis)) {
            $out->legalBasis = $this->legalBasis;
        }
        if (isset($this->jurisdictionalAuthorization) && [] !== $this->jurisdictionalAuthorization) {
            if ($this->_getJSONFieldElideSingletonArray(self::FIELD_JURISDICTIONAL_AUTHORIZATION) && 1 === count($this->jurisdictionalAuthorization)) {
                $out->jurisdictionalAuthorization = $this->jurisdictionalAuthorization[0];
            } else {
                $out->jurisdictionalAuthorization = $this->jurisdictionalAuthorization;
            }
        }
        if (isset($this->holder)) {
            $out->holder = $this->holder;
        }
        if (isset($this->regulator)) {
            $out->regulator = $this->regulator;
        }
        if (isset($this->procedure)) {
            $out->procedure = $this->procedure;
        }
        $out->resourceType = $this->_getResourceType();
        return $out;
    }
}
