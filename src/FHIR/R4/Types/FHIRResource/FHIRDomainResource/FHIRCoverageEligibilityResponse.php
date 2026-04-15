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
use OpenEMR\FHIR\Versions\R4\Types\FHIRCodePrimitive\FHIREligibilityResponsePurposeList;
use OpenEMR\FHIR\Versions\R4\Types\FHIRCodePrimitive\FHIRFinancialResourceStatusCodesList;
use OpenEMR\FHIR\Versions\R4\Types\FHIRCodePrimitive\FHIRRemittanceOutcomeList;
use OpenEMR\FHIR\Versions\R4\Types\FHIRDatePrimitive;
use OpenEMR\FHIR\Versions\R4\Types\FHIRDateTimePrimitive;
use OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRCoverageEligibilityResponse\FHIRCoverageEligibilityResponseError;
use OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRCoverageEligibilityResponse\FHIRCoverageEligibilityResponseInsurance;
use OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRCode;
use OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRCodeableConcept;
use OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRDate;
use OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRDateTime;
use OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIREligibilityResponsePurpose;
use OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRExtension;
use OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRFinancialResourceStatusCodes;
use OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRId;
use OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRIdentifier;
use OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRMeta;
use OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRNarrative;
use OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRPeriod;
use OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRReference;
use OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRRemittanceOutcome;
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
 * This resource provides eligibility and plan details from the processing of an
 * CoverageEligibilityRequest resource.
 * If the element is present, it must have either a \@value, an \@id, or extensions
 */
#[\Attribute(\Attribute::TARGET_PROPERTY)]
class FHIRCoverageEligibilityResponse extends FHIRDomainResource implements VersionContainedTypeInterface
{
    use TypeValidationsTrait,
        JSONSerializationOptionsTrait,
        XMLSerializationOptionsTrait;

    // name of FHIR type this class describes
    public const FHIR_TYPE_NAME = VersionConstants::TYPE_NAME_COVERAGE_ELIGIBILITY_RESPONSE;

    /* class_default.php:56 */
    public const FIELD_IDENTIFIER = 'identifier';
    public const FIELD_STATUS = 'status';
    public const FIELD_STATUS_EXT = '_status';
    public const FIELD_PURPOSE = 'purpose';
    public const FIELD_PURPOSE_EXT = '_purpose';
    public const FIELD_PATIENT = 'patient';
    public const FIELD_SERVICED_DATE = 'servicedDate';
    public const FIELD_SERVICED_DATE_EXT = '_servicedDate';
    public const FIELD_SERVICED_PERIOD = 'servicedPeriod';
    public const FIELD_CREATED = 'created';
    public const FIELD_CREATED_EXT = '_created';
    public const FIELD_REQUESTOR = 'requestor';
    public const FIELD_REQUEST = 'request';
    public const FIELD_OUTCOME = 'outcome';
    public const FIELD_OUTCOME_EXT = '_outcome';
    public const FIELD_DISPOSITION = 'disposition';
    public const FIELD_DISPOSITION_EXT = '_disposition';
    public const FIELD_INSURER = 'insurer';
    public const FIELD_INSURANCE = 'insurance';
    public const FIELD_PRE_AUTH_REF = 'preAuthRef';
    public const FIELD_PRE_AUTH_REF_EXT = '_preAuthRef';
    public const FIELD_FORM = 'form';
    public const FIELD_ERROR = 'error';

    /* class_default.php:75 */
    private const _FHIR_VALIDATION_RULES = [
        self::FIELD_STATUS => [
            MinOccursRule::NAME => 1,
        ],
        self::FIELD_PURPOSE => [
            MinOccursRule::NAME => 1,
        ],
        self::FIELD_PATIENT => [
            MinOccursRule::NAME => 1,
        ],
        self::FIELD_CREATED => [
            MinOccursRule::NAME => 1,
        ],
        self::FIELD_REQUEST => [
            MinOccursRule::NAME => 1,
        ],
        self::FIELD_OUTCOME => [
            MinOccursRule::NAME => 1,
        ],
        self::FIELD_INSURER => [
            MinOccursRule::NAME => 1,
        ],
    ];

    /* class_default.php:96 */
    private array $_valueXMLLocations = [
        self::FIELD_STATUS => ValueXMLLocationEnum::CONTAINER_ATTRIBUTE,
        self::FIELD_SERVICED_DATE => ValueXMLLocationEnum::CONTAINER_ATTRIBUTE,
        self::FIELD_CREATED => ValueXMLLocationEnum::CONTAINER_ATTRIBUTE,
        self::FIELD_OUTCOME => ValueXMLLocationEnum::CONTAINER_ATTRIBUTE,
        self::FIELD_DISPOSITION => ValueXMLLocationEnum::CONTAINER_ATTRIBUTE,
        self::FIELD_PRE_AUTH_REF => ValueXMLLocationEnum::CONTAINER_ATTRIBUTE,
    ];

    /* class_default.php:112 */
    /**
     * An identifier - identifies some entity uniquely and unambiguously. Typically
     * this is used for business identifiers.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * A unique identifier assigned to this coverage eligiblity request.
     *
     * @var iterable<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRIdentifier>
     */
    #[FHIRIdentifier]
    protected array $identifier;
    /**
     * A code specifying the state of the resource instance.
     * If the element is present, it must have either a \@value, an \@id, or extensions
     *
     * The status of the resource instance.
     *
     * @var \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRFinancialResourceStatusCodes
     */
    #[FHIRFinancialResourceStatusCodes]
    protected FHIRFinancialResourceStatusCodes $status;
    /**
     * A code specifying the types of information being requested.
     * If the element is present, it must have either a \@value, an \@id, or extensions
     *
     * Code to specify whether requesting: prior authorization requirements for some
     * service categories or billing codes; benefits for coverages specified or
     * discovered; discovery and return of coverages for the patient; and/or validation
     * that the specified coverage is in-force at the date/period specified or 'now' if
     * not specified.
     *
     * @var iterable<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIREligibilityResponsePurpose>
     */
    #[FHIREligibilityResponsePurpose]
    protected array $purpose;
    /**
     * A reference from one resource to another.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * The party who is the beneficiary of the supplied coverage and for whom
     * eligibility is sought.
     *
     * @var \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRReference
     */
    #[FHIRReference]
    protected FHIRReference $patient;
    /**
     * A date or partial date (e.g. just year or year + month). There is no time zone.
     * The format is a union of the schema types gYear, gYearMonth and date. Dates
     * SHALL be valid dates.
     * If the element is present, it must have either a \@value, an \@id, or extensions
     *
     * The date or dates when the enclosed suite of services were performed or
     * completed. (choose any one of serviced*, but only one)
     *
     * @var \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRDate
     */
    #[FHIRDate]
    protected FHIRDate $servicedDate;
    /**
     * A time period defined by a start and end date and optionally time.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * The date or dates when the enclosed suite of services were performed or
     * completed. (choose any one of serviced*, but only one)
     *
     * @var \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRPeriod
     */
    #[FHIRPeriod]
    protected FHIRPeriod $servicedPeriod;
    /**
     * A date, date-time or partial date (e.g. just year or year + month). If hours and
     * minutes are specified, a time zone SHALL be populated. The format is a union of
     * the schema types gYear, gYearMonth, date and dateTime. Seconds must be provided
     * due to schema type constraints but may be zero-filled and may be ignored. Dates
     * SHALL be valid dates.
     * If the element is present, it must have either a \@value, an \@id, or extensions
     *
     * The date this resource was created.
     *
     * @var \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRDateTime
     */
    #[FHIRDateTime]
    protected FHIRDateTime $created;
    /**
     * A reference from one resource to another.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * The provider which is responsible for the request.
     *
     * @var \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRReference
     */
    #[FHIRReference]
    protected FHIRReference $requestor;
    /**
     * A reference from one resource to another.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * Reference to the original request resource.
     *
     * @var \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRReference
     */
    #[FHIRReference]
    protected FHIRReference $request;
    /**
     * If the element is present, it must have either a \@value, an \@id, or extensions
     *
     * The outcome of the request processing.
     *
     * @var \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRRemittanceOutcome
     */
    #[FHIRRemittanceOutcome]
    protected FHIRRemittanceOutcome $outcome;
    /**
     * A sequence of Unicode characters
     * Note that FHIR strings SHALL NOT exceed 1MB in size
     * If the element is present, it must have either a \@value, an \@id, or extensions
     *
     * A human readable description of the status of the adjudication.
     *
     * @var \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRString
     */
    #[FHIRString]
    protected FHIRString $disposition;
    /**
     * A reference from one resource to another.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * The Insurer who issued the coverage in question and is the author of the
     * response.
     *
     * @var \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRReference
     */
    #[FHIRReference]
    protected FHIRReference $insurer;
    /**
     * This resource provides eligibility and plan details from the processing of an
     * CoverageEligibilityRequest resource.
     *
     * Financial instruments for reimbursement for the health care products and
     * services.
     *
     * @var iterable<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRCoverageEligibilityResponse\FHIRCoverageEligibilityResponseInsurance>
     */
    #[FHIRCoverageEligibilityResponseInsurance]
    protected array $insurance;
    /**
     * A sequence of Unicode characters
     * Note that FHIR strings SHALL NOT exceed 1MB in size
     * If the element is present, it must have either a \@value, an \@id, or extensions
     *
     * A reference from the Insurer to which these services pertain to be used on
     * further communication and as proof that the request occurred.
     *
     * @var \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRString
     */
    #[FHIRString]
    protected FHIRString $preAuthRef;
    /**
     * A concept that may be defined by a formal reference to a terminology or ontology
     * or may be provided by text.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * A code for the form to be used for printing the content.
     *
     * @var \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRCodeableConcept
     */
    #[FHIRCodeableConcept]
    protected FHIRCodeableConcept $form;
    /**
     * This resource provides eligibility and plan details from the processing of an
     * CoverageEligibilityRequest resource.
     *
     * Errors encountered during the processing of the request.
     *
     * @var iterable<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRCoverageEligibilityResponse\FHIRCoverageEligibilityResponseError>
     */
    #[FHIRCoverageEligibilityResponseError]
    protected array $error;

    /* constructor.php:61 */
    /**
     * FHIRCoverageEligibilityResponse Constructor
     * @param null|string|\OpenEMR\FHIR\Versions\R4\Types\FHIRIdPrimitive|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRId $id
     * @param null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRMeta $meta
     * @param null|string|\OpenEMR\FHIR\Versions\R4\Types\FHIRUriPrimitive|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRUri $implicitRules
     * @param null|string|\OpenEMR\FHIR\Versions\R4\Types\FHIRCodePrimitive|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRCode $language
     * @param null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRNarrative $text
     * @param null|iterable<\OpenEMR\FHIR\Versions\R4\Types\FHIRResourceContainer>|iterable<\OpenEMR\FHIR\Versions\R4\VersionContainedTypeInterface> $contained
     * @param null|iterable<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRExtension> $extension
     * @param null|iterable<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRExtension> $modifierExtension
     * @param null|iterable<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRIdentifier> $identifier
     * @param null|string|\OpenEMR\FHIR\Versions\R4\Types\FHIRCodePrimitive\FHIRFinancialResourceStatusCodesList|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRFinancialResourceStatusCodes $status
     * @param null|iterable<string>|iterable<\OpenEMR\FHIR\Versions\R4\Types\FHIRCodePrimitive\FHIREligibilityResponsePurposeList>|iterable<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIREligibilityResponsePurpose> $purpose
     * @param null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRReference $patient
     * @param null|string|\DateTimeInterface|\OpenEMR\FHIR\Versions\R4\Types\FHIRDatePrimitive|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRDate $servicedDate
     * @param null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRPeriod $servicedPeriod
     * @param null|string|\DateTimeInterface|\OpenEMR\FHIR\Versions\R4\Types\FHIRDateTimePrimitive|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRDateTime $created
     * @param null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRReference $requestor
     * @param null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRReference $request
     * @param null|string|\OpenEMR\FHIR\Versions\R4\Types\FHIRCodePrimitive\FHIRRemittanceOutcomeList|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRRemittanceOutcome $outcome
     * @param null|string|\OpenEMR\FHIR\Versions\R4\Types\FHIRStringPrimitive|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRString $disposition
     * @param null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRReference $insurer
     * @param null|iterable<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRCoverageEligibilityResponse\FHIRCoverageEligibilityResponseInsurance> $insurance
     * @param null|string|\OpenEMR\FHIR\Versions\R4\Types\FHIRStringPrimitive|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRString $preAuthRef
     * @param null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRCodeableConcept $form
     * @param null|iterable<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRCoverageEligibilityResponse\FHIRCoverageEligibilityResponseError> $error
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
                                null|string|FHIRFinancialResourceStatusCodesList|FHIRFinancialResourceStatusCodes $status = null,
                                null|iterable $purpose = null,
                                null|FHIRReference $patient = null,
                                null|string|\DateTimeInterface|FHIRDatePrimitive|FHIRDate $servicedDate = null,
                                null|FHIRPeriod $servicedPeriod = null,
                                null|string|\DateTimeInterface|FHIRDateTimePrimitive|FHIRDateTime $created = null,
                                null|FHIRReference $requestor = null,
                                null|FHIRReference $request = null,
                                null|string|FHIRRemittanceOutcomeList|FHIRRemittanceOutcome $outcome = null,
                                null|string|FHIRStringPrimitive|FHIRString $disposition = null,
                                null|FHIRReference $insurer = null,
                                null|iterable $insurance = null,
                                null|string|FHIRStringPrimitive|FHIRString $preAuthRef = null,
                                null|FHIRCodeableConcept $form = null,
                                null|iterable $error = null,
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
        if (null !== $purpose) {
            $this->setPurpose(...$purpose);
        }
        if (null !== $patient) {
            $this->setPatient($patient);
        }
        if (null !== $servicedDate) {
            $this->setServicedDate($servicedDate);
        }
        if (null !== $servicedPeriod) {
            $this->setServicedPeriod($servicedPeriod);
        }
        if (null !== $created) {
            $this->setCreated($created);
        }
        if (null !== $requestor) {
            $this->setRequestor($requestor);
        }
        if (null !== $request) {
            $this->setRequest($request);
        }
        if (null !== $outcome) {
            $this->setOutcome($outcome);
        }
        if (null !== $disposition) {
            $this->setDisposition($disposition);
        }
        if (null !== $insurer) {
            $this->setInsurer($insurer);
        }
        if (null !== $insurance) {
            $this->setInsurance(...$insurance);
        }
        if (null !== $preAuthRef) {
            $this->setPreAuthRef($preAuthRef);
        }
        if (null !== $form) {
            $this->setForm($form);
        }
        if (null !== $error) {
            $this->setError(...$error);
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
     * A unique identifier assigned to this coverage eligiblity request.
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
     * A unique identifier assigned to this coverage eligiblity request.
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
     * A unique identifier assigned to this coverage eligiblity request.
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
     * A code specifying the state of the resource instance.
     * If the element is present, it must have either a \@value, an \@id, or extensions
     *
     * The status of the resource instance.
     *
     * @return null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRFinancialResourceStatusCodes
     */
    public function getStatus(): null|FHIRFinancialResourceStatusCodes
    {
        return $this->status ?? null;
    }

    /**
     * A code specifying the state of the resource instance.
     * If the element is present, it must have either a \@value, an \@id, or extensions
     *
     * The status of the resource instance.
     *
     * @param null|string|\OpenEMR\FHIR\Versions\R4\Types\FHIRCodePrimitive\FHIRFinancialResourceStatusCodesList|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRFinancialResourceStatusCodes $status
     * @return static
     */
    public function setStatus(null|string|FHIRFinancialResourceStatusCodesList|FHIRFinancialResourceStatusCodes $status): self
    {
        if (null === $status) {
            unset($this->status);
            return $this;
        }
        if (!($status instanceof FHIRFinancialResourceStatusCodes)) {
            $status = new FHIRFinancialResourceStatusCodes(value: $status);
        }
        $this->status = $status;
        return $this;
    }

    /**
     * A code specifying the types of information being requested.
     * If the element is present, it must have either a \@value, an \@id, or extensions
     *
     * Code to specify whether requesting: prior authorization requirements for some
     * service categories or billing codes; benefits for coverages specified or
     * discovered; discovery and return of coverages for the patient; and/or validation
     * that the specified coverage is in-force at the date/period specified or 'now' if
     * not specified.
     *
     * @return iterable<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIREligibilityResponsePurpose>
     */
    public function getPurpose(): array
    {
        return $this->purpose ?? [];
    }

    /**
     * @return \ArrayIterator<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIREligibilityResponsePurpose>
     */
    public function getPurposeIterator(): iterable
    {
        if (!isset($this->purpose)) {
            return new \EmptyIterator();
        }
        return new \ArrayIterator($this->purpose);
    }

    /**
     * A code specifying the types of information being requested.
     * If the element is present, it must have either a \@value, an \@id, or extensions
     *
     * Code to specify whether requesting: prior authorization requirements for some
     * service categories or billing codes; benefits for coverages specified or
     * discovered; discovery and return of coverages for the patient; and/or validation
     * that the specified coverage is in-force at the date/period specified or 'now' if
     * not specified.
     *
     * @param string|\OpenEMR\FHIR\Versions\R4\Types\FHIRCodePrimitive\FHIREligibilityResponsePurposeList|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIREligibilityResponsePurpose $purpose
     * @return static
     */
    public function addPurpose(string|FHIREligibilityResponsePurposeList|FHIREligibilityResponsePurpose $purpose): self
    {
        if (!($purpose instanceof FHIREligibilityResponsePurpose)) {
            $purpose = new FHIREligibilityResponsePurpose(value: $purpose);
        }
        if (!isset($this->purpose)) {
            $this->purpose = [];
        }
        $this->purpose[] = $purpose;
        return $this;
    }

    /**
     * A code specifying the types of information being requested.
     * If the element is present, it must have either a \@value, an \@id, or extensions
     *
     * Code to specify whether requesting: prior authorization requirements for some
     * service categories or billing codes; benefits for coverages specified or
     * discovered; discovery and return of coverages for the patient; and/or validation
     * that the specified coverage is in-force at the date/period specified or 'now' if
     * not specified.
     *
     * @param string|\OpenEMR\FHIR\Versions\R4\Types\FHIRCodePrimitive\FHIREligibilityResponsePurposeList|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIREligibilityResponsePurpose ...$purpose
     * @return static
     */
    public function setPurpose(string|FHIREligibilityResponsePurposeList|FHIREligibilityResponsePurpose ...$purpose): self
    {
        if ([] === $purpose) {
            unset($this->purpose);
            return $this;
        }
        $this->purpose = [];
        foreach($purpose as $v) {
            if ($v instanceof FHIREligibilityResponsePurpose) {
                $this->purpose[] = $v;
            } else {
                $this->purpose[] = new FHIREligibilityResponsePurpose(value: $v);
            }
        }
        return $this;
    }

    /**
     * A reference from one resource to another.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * The party who is the beneficiary of the supplied coverage and for whom
     * eligibility is sought.
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
     * The party who is the beneficiary of the supplied coverage and for whom
     * eligibility is sought.
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
     * A date or partial date (e.g. just year or year + month). There is no time zone.
     * The format is a union of the schema types gYear, gYearMonth and date. Dates
     * SHALL be valid dates.
     * If the element is present, it must have either a \@value, an \@id, or extensions
     *
     * The date or dates when the enclosed suite of services were performed or
     * completed. (choose any one of serviced*, but only one)
     *
     * @return null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRDate
     */
    public function getServicedDate(): null|FHIRDate
    {
        return $this->servicedDate ?? null;
    }

    /**
     * A date or partial date (e.g. just year or year + month). There is no time zone.
     * The format is a union of the schema types gYear, gYearMonth and date. Dates
     * SHALL be valid dates.
     * If the element is present, it must have either a \@value, an \@id, or extensions
     *
     * The date or dates when the enclosed suite of services were performed or
     * completed. (choose any one of serviced*, but only one)
     *
     * @param null|string|\DateTimeInterface|\OpenEMR\FHIR\Versions\R4\Types\FHIRDatePrimitive|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRDate $servicedDate
     * @return static
     */
    public function setServicedDate(null|string|\DateTimeInterface|FHIRDatePrimitive|FHIRDate $servicedDate): self
    {
        if (null === $servicedDate) {
            unset($this->servicedDate);
            return $this;
        }
        if (!($servicedDate instanceof FHIRDate)) {
            $servicedDate = new FHIRDate(value: $servicedDate);
        }
        $this->servicedDate = $servicedDate;
        return $this;
    }

    /**
     * A time period defined by a start and end date and optionally time.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * The date or dates when the enclosed suite of services were performed or
     * completed. (choose any one of serviced*, but only one)
     *
     * @return null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRPeriod
     */
    public function getServicedPeriod(): null|FHIRPeriod
    {
        return $this->servicedPeriod ?? null;
    }

    /**
     * A time period defined by a start and end date and optionally time.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * The date or dates when the enclosed suite of services were performed or
     * completed. (choose any one of serviced*, but only one)
     *
     * @param null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRPeriod $servicedPeriod
     * @return static
     */
    public function setServicedPeriod(null|FHIRPeriod $servicedPeriod): self
    {
        if (null === $servicedPeriod) {
            unset($this->servicedPeriod);
            return $this;
        }
        $this->servicedPeriod = $servicedPeriod;
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
     * The date this resource was created.
     *
     * @return null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRDateTime
     */
    public function getCreated(): null|FHIRDateTime
    {
        return $this->created ?? null;
    }

    /**
     * A date, date-time or partial date (e.g. just year or year + month). If hours and
     * minutes are specified, a time zone SHALL be populated. The format is a union of
     * the schema types gYear, gYearMonth, date and dateTime. Seconds must be provided
     * due to schema type constraints but may be zero-filled and may be ignored. Dates
     * SHALL be valid dates.
     * If the element is present, it must have either a \@value, an \@id, or extensions
     *
     * The date this resource was created.
     *
     * @param null|string|\DateTimeInterface|\OpenEMR\FHIR\Versions\R4\Types\FHIRDateTimePrimitive|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRDateTime $created
     * @return static
     */
    public function setCreated(null|string|\DateTimeInterface|FHIRDateTimePrimitive|FHIRDateTime $created): self
    {
        if (null === $created) {
            unset($this->created);
            return $this;
        }
        if (!($created instanceof FHIRDateTime)) {
            $created = new FHIRDateTime(value: $created);
        }
        $this->created = $created;
        return $this;
    }

    /**
     * A reference from one resource to another.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * The provider which is responsible for the request.
     *
     * @return null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRReference
     */
    public function getRequestor(): null|FHIRReference
    {
        return $this->requestor ?? null;
    }

    /**
     * A reference from one resource to another.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * The provider which is responsible for the request.
     *
     * @param null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRReference $requestor
     * @return static
     */
    public function setRequestor(null|FHIRReference $requestor): self
    {
        if (null === $requestor) {
            unset($this->requestor);
            return $this;
        }
        $this->requestor = $requestor;
        return $this;
    }

    /**
     * A reference from one resource to another.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * Reference to the original request resource.
     *
     * @return null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRReference
     */
    public function getRequest(): null|FHIRReference
    {
        return $this->request ?? null;
    }

    /**
     * A reference from one resource to another.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * Reference to the original request resource.
     *
     * @param null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRReference $request
     * @return static
     */
    public function setRequest(null|FHIRReference $request): self
    {
        if (null === $request) {
            unset($this->request);
            return $this;
        }
        $this->request = $request;
        return $this;
    }

    /**
     * If the element is present, it must have either a \@value, an \@id, or extensions
     *
     * The outcome of the request processing.
     *
     * @return null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRRemittanceOutcome
     */
    public function getOutcome(): null|FHIRRemittanceOutcome
    {
        return $this->outcome ?? null;
    }

    /**
     * If the element is present, it must have either a \@value, an \@id, or extensions
     *
     * The outcome of the request processing.
     *
     * @param null|string|\OpenEMR\FHIR\Versions\R4\Types\FHIRCodePrimitive\FHIRRemittanceOutcomeList|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRRemittanceOutcome $outcome
     * @return static
     */
    public function setOutcome(null|string|FHIRRemittanceOutcomeList|FHIRRemittanceOutcome $outcome): self
    {
        if (null === $outcome) {
            unset($this->outcome);
            return $this;
        }
        if (!($outcome instanceof FHIRRemittanceOutcome)) {
            $outcome = new FHIRRemittanceOutcome(value: $outcome);
        }
        $this->outcome = $outcome;
        return $this;
    }

    /**
     * A sequence of Unicode characters
     * Note that FHIR strings SHALL NOT exceed 1MB in size
     * If the element is present, it must have either a \@value, an \@id, or extensions
     *
     * A human readable description of the status of the adjudication.
     *
     * @return null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRString
     */
    public function getDisposition(): null|FHIRString
    {
        return $this->disposition ?? null;
    }

    /**
     * A sequence of Unicode characters
     * Note that FHIR strings SHALL NOT exceed 1MB in size
     * If the element is present, it must have either a \@value, an \@id, or extensions
     *
     * A human readable description of the status of the adjudication.
     *
     * @param null|string|\OpenEMR\FHIR\Versions\R4\Types\FHIRStringPrimitive|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRString $disposition
     * @return static
     */
    public function setDisposition(null|string|FHIRStringPrimitive|FHIRString $disposition): self
    {
        if (null === $disposition) {
            unset($this->disposition);
            return $this;
        }
        if (!($disposition instanceof FHIRString)) {
            $disposition = new FHIRString(value: $disposition);
        }
        $this->disposition = $disposition;
        return $this;
    }

    /**
     * A reference from one resource to another.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * The Insurer who issued the coverage in question and is the author of the
     * response.
     *
     * @return null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRReference
     */
    public function getInsurer(): null|FHIRReference
    {
        return $this->insurer ?? null;
    }

    /**
     * A reference from one resource to another.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * The Insurer who issued the coverage in question and is the author of the
     * response.
     *
     * @param null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRReference $insurer
     * @return static
     */
    public function setInsurer(null|FHIRReference $insurer): self
    {
        if (null === $insurer) {
            unset($this->insurer);
            return $this;
        }
        $this->insurer = $insurer;
        return $this;
    }

    /**
     * This resource provides eligibility and plan details from the processing of an
     * CoverageEligibilityRequest resource.
     *
     * Financial instruments for reimbursement for the health care products and
     * services.
     *
     * @return iterable<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRCoverageEligibilityResponse\FHIRCoverageEligibilityResponseInsurance>
     */
    public function getInsurance(): array
    {
        return $this->insurance ?? [];
    }

    /**
     * @return \ArrayIterator<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRCoverageEligibilityResponse\FHIRCoverageEligibilityResponseInsurance>
     */
    public function getInsuranceIterator(): iterable
    {
        if (!isset($this->insurance)) {
            return new \EmptyIterator();
        }
        return new \ArrayIterator($this->insurance);
    }

    /**
     * This resource provides eligibility and plan details from the processing of an
     * CoverageEligibilityRequest resource.
     *
     * Financial instruments for reimbursement for the health care products and
     * services.
     *
     * @param \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRCoverageEligibilityResponse\FHIRCoverageEligibilityResponseInsurance $insurance
     * @return static
     */
    public function addInsurance(FHIRCoverageEligibilityResponseInsurance $insurance): self
    {
        if (!isset($this->insurance)) {
            $this->insurance = [];
        }
        $this->insurance[] = $insurance;
        return $this;
    }

    /**
     * This resource provides eligibility and plan details from the processing of an
     * CoverageEligibilityRequest resource.
     *
     * Financial instruments for reimbursement for the health care products and
     * services.
     *
     * @param \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRCoverageEligibilityResponse\FHIRCoverageEligibilityResponseInsurance ...$insurance
     * @return static
     */
    public function setInsurance(FHIRCoverageEligibilityResponseInsurance ...$insurance): self
    {
        if ([] === $insurance) {
            unset($this->insurance);
            return $this;
        }
        $this->insurance = $insurance;
        return $this;
    }

    /**
     * A sequence of Unicode characters
     * Note that FHIR strings SHALL NOT exceed 1MB in size
     * If the element is present, it must have either a \@value, an \@id, or extensions
     *
     * A reference from the Insurer to which these services pertain to be used on
     * further communication and as proof that the request occurred.
     *
     * @return null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRString
     */
    public function getPreAuthRef(): null|FHIRString
    {
        return $this->preAuthRef ?? null;
    }

    /**
     * A sequence of Unicode characters
     * Note that FHIR strings SHALL NOT exceed 1MB in size
     * If the element is present, it must have either a \@value, an \@id, or extensions
     *
     * A reference from the Insurer to which these services pertain to be used on
     * further communication and as proof that the request occurred.
     *
     * @param null|string|\OpenEMR\FHIR\Versions\R4\Types\FHIRStringPrimitive|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRString $preAuthRef
     * @return static
     */
    public function setPreAuthRef(null|string|FHIRStringPrimitive|FHIRString $preAuthRef): self
    {
        if (null === $preAuthRef) {
            unset($this->preAuthRef);
            return $this;
        }
        if (!($preAuthRef instanceof FHIRString)) {
            $preAuthRef = new FHIRString(value: $preAuthRef);
        }
        $this->preAuthRef = $preAuthRef;
        return $this;
    }

    /**
     * A concept that may be defined by a formal reference to a terminology or ontology
     * or may be provided by text.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * A code for the form to be used for printing the content.
     *
     * @return null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRCodeableConcept
     */
    public function getForm(): null|FHIRCodeableConcept
    {
        return $this->form ?? null;
    }

    /**
     * A concept that may be defined by a formal reference to a terminology or ontology
     * or may be provided by text.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * A code for the form to be used for printing the content.
     *
     * @param null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRCodeableConcept $form
     * @return static
     */
    public function setForm(null|FHIRCodeableConcept $form): self
    {
        if (null === $form) {
            unset($this->form);
            return $this;
        }
        $this->form = $form;
        return $this;
    }

    /**
     * This resource provides eligibility and plan details from the processing of an
     * CoverageEligibilityRequest resource.
     *
     * Errors encountered during the processing of the request.
     *
     * @return iterable<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRCoverageEligibilityResponse\FHIRCoverageEligibilityResponseError>
     */
    public function getError(): array
    {
        return $this->error ?? [];
    }

    /**
     * @return \ArrayIterator<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRCoverageEligibilityResponse\FHIRCoverageEligibilityResponseError>
     */
    public function getErrorIterator(): iterable
    {
        if (!isset($this->error)) {
            return new \EmptyIterator();
        }
        return new \ArrayIterator($this->error);
    }

    /**
     * This resource provides eligibility and plan details from the processing of an
     * CoverageEligibilityRequest resource.
     *
     * Errors encountered during the processing of the request.
     *
     * @param \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRCoverageEligibilityResponse\FHIRCoverageEligibilityResponseError $error
     * @return static
     */
    public function addError(FHIRCoverageEligibilityResponseError $error): self
    {
        if (!isset($this->error)) {
            $this->error = [];
        }
        $this->error[] = $error;
        return $this;
    }

    /**
     * This resource provides eligibility and plan details from the processing of an
     * CoverageEligibilityRequest resource.
     *
     * Errors encountered during the processing of the request.
     *
     * @param \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRCoverageEligibilityResponse\FHIRCoverageEligibilityResponseError ...$error
     * @return static
     */
    public function setError(FHIRCoverageEligibilityResponseError ...$error): self
    {
        if ([] === $error) {
            unset($this->error);
            return $this;
        }
        $this->error = $error;
        return $this;
    }

    /* class_default.php:201 */
    /**
     * @param string|\SimpleXMLElement $element
     * @param null|\OpenEMR\FHIR\Encoding\UnserializeConfig $config
     * @param null|\OpenEMR\FHIR\Versions\R4\Types\FHIRResource\FHIRDomainResource\FHIRCoverageEligibilityResponse $type
     * @return \OpenEMR\FHIR\Versions\R4\Types\FHIRResource\FHIRDomainResource\FHIRCoverageEligibilityResponse
     * @throws \Exception
     */
    public static function xmlUnserialize(string|\SimpleXMLElement $element,
                                          null|UnserializeConfig $config = null,
                                          null|ResourceTypeInterface $type = null): self
    {
        if (null === $type) {
            $type = new static();
        } else if (!($type instanceof FHIRCoverageEligibilityResponse)) {
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
                $type->setStatus(FHIRFinancialResourceStatusCodes::xmlUnserialize($ce, $config));
            } else if (self::FIELD_PURPOSE === $cen) {
                $type->addPurpose(FHIREligibilityResponsePurpose::xmlUnserialize($ce, $config));
            } else if (self::FIELD_PATIENT === $cen) {
                $type->setPatient(FHIRReference::xmlUnserialize($ce, $config));
            } else if (self::FIELD_SERVICED_DATE === $cen) {
                $type->setServicedDate(FHIRDate::xmlUnserialize($ce, $config));
            } else if (self::FIELD_SERVICED_PERIOD === $cen) {
                $type->setServicedPeriod(FHIRPeriod::xmlUnserialize($ce, $config));
            } else if (self::FIELD_CREATED === $cen) {
                $type->setCreated(FHIRDateTime::xmlUnserialize($ce, $config));
            } else if (self::FIELD_REQUESTOR === $cen) {
                $type->setRequestor(FHIRReference::xmlUnserialize($ce, $config));
            } else if (self::FIELD_REQUEST === $cen) {
                $type->setRequest(FHIRReference::xmlUnserialize($ce, $config));
            } else if (self::FIELD_OUTCOME === $cen) {
                $type->setOutcome(FHIRRemittanceOutcome::xmlUnserialize($ce, $config));
            } else if (self::FIELD_DISPOSITION === $cen) {
                $type->setDisposition(FHIRString::xmlUnserialize($ce, $config));
            } else if (self::FIELD_INSURER === $cen) {
                $type->setInsurer(FHIRReference::xmlUnserialize($ce, $config));
            } else if (self::FIELD_INSURANCE === $cen) {
                $type->addInsurance(FHIRCoverageEligibilityResponseInsurance::xmlUnserialize($ce, $config));
            } else if (self::FIELD_PRE_AUTH_REF === $cen) {
                $type->setPreAuthRef(FHIRString::xmlUnserialize($ce, $config));
            } else if (self::FIELD_FORM === $cen) {
                $type->setForm(FHIRCodeableConcept::xmlUnserialize($ce, $config));
            } else if (self::FIELD_ERROR === $cen) {
                $type->addError(FHIRCoverageEligibilityResponseError::xmlUnserialize($ce, $config));
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
        if (isset($attributes[self::FIELD_SERVICED_DATE])) {
            if (isset($type->servicedDate)) {
                $type->servicedDate->setValue((string)$attributes[self::FIELD_SERVICED_DATE]);
            } else {
                $type->setServicedDate((string)$attributes[self::FIELD_SERVICED_DATE]);
            }
            $type->_setXMLFieldValueLocation(self::FIELD_SERVICED_DATE, ValueXMLLocationEnum::PARENT_ATTRIBUTE);
        }
        if (isset($attributes[self::FIELD_CREATED])) {
            if (isset($type->created)) {
                $type->created->setValue((string)$attributes[self::FIELD_CREATED]);
            } else {
                $type->setCreated((string)$attributes[self::FIELD_CREATED]);
            }
            $type->_setXMLFieldValueLocation(self::FIELD_CREATED, ValueXMLLocationEnum::PARENT_ATTRIBUTE);
        }
        if (isset($attributes[self::FIELD_OUTCOME])) {
            if (isset($type->outcome)) {
                $type->outcome->setValue((string)$attributes[self::FIELD_OUTCOME]);
            } else {
                $type->setOutcome((string)$attributes[self::FIELD_OUTCOME]);
            }
            $type->_setXMLFieldValueLocation(self::FIELD_OUTCOME, ValueXMLLocationEnum::PARENT_ATTRIBUTE);
        }
        if (isset($attributes[self::FIELD_DISPOSITION])) {
            if (isset($type->disposition)) {
                $type->disposition->setValue((string)$attributes[self::FIELD_DISPOSITION]);
            } else {
                $type->setDisposition((string)$attributes[self::FIELD_DISPOSITION]);
            }
            $type->_setXMLFieldValueLocation(self::FIELD_DISPOSITION, ValueXMLLocationEnum::PARENT_ATTRIBUTE);
        }
        if (isset($attributes[self::FIELD_PRE_AUTH_REF])) {
            if (isset($type->preAuthRef)) {
                $type->preAuthRef->setValue((string)$attributes[self::FIELD_PRE_AUTH_REF]);
            } else {
                $type->setPreAuthRef((string)$attributes[self::FIELD_PRE_AUTH_REF]);
            }
            $type->_setXMLFieldValueLocation(self::FIELD_PRE_AUTH_REF, ValueXMLLocationEnum::PARENT_ATTRIBUTE);
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
            $xw->openRootNode('CoverageEligibilityResponse', $this->_getSourceXMLNS());
        }
        if (isset($this->status) && ValueXMLLocationEnum::PARENT_ATTRIBUTE === $this->_valueXMLLocations[self::FIELD_STATUS]) {
            $xw->writeAttribute(self::FIELD_STATUS, $this->status->_getValueAsString());
        }
        if (isset($this->servicedDate) && ValueXMLLocationEnum::PARENT_ATTRIBUTE === $this->_valueXMLLocations[self::FIELD_SERVICED_DATE]) {
            $xw->writeAttribute(self::FIELD_SERVICED_DATE, $this->servicedDate->_getValueAsString());
        }
        if (isset($this->created) && ValueXMLLocationEnum::PARENT_ATTRIBUTE === $this->_valueXMLLocations[self::FIELD_CREATED]) {
            $xw->writeAttribute(self::FIELD_CREATED, $this->created->_getValueAsString());
        }
        if (isset($this->outcome) && ValueXMLLocationEnum::PARENT_ATTRIBUTE === $this->_valueXMLLocations[self::FIELD_OUTCOME]) {
            $xw->writeAttribute(self::FIELD_OUTCOME, $this->outcome->_getValueAsString());
        }
        if (isset($this->disposition) && ValueXMLLocationEnum::PARENT_ATTRIBUTE === $this->_valueXMLLocations[self::FIELD_DISPOSITION]) {
            $xw->writeAttribute(self::FIELD_DISPOSITION, $this->disposition->_getValueAsString());
        }
        if (isset($this->preAuthRef) && ValueXMLLocationEnum::PARENT_ATTRIBUTE === $this->_valueXMLLocations[self::FIELD_PRE_AUTH_REF]) {
            $xw->writeAttribute(self::FIELD_PRE_AUTH_REF, $this->preAuthRef->_getValueAsString());
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
        if (isset($this->purpose) && [] !== $this->purpose) {
            foreach($this->purpose as $v) {
                $xw->startElement(self::FIELD_PURPOSE);
                $v->xmlSerialize($xw, $config);
                $xw->endElement();
            }
        }
        if (isset($this->patient)) {
            $xw->startElement(self::FIELD_PATIENT);
            $this->patient->xmlSerialize($xw, $config);
            $xw->endElement();
        }
        if (isset($this->servicedDate)
            && (ValueXMLLocationEnum::PARENT_ATTRIBUTE !== $this->_valueXMLLocations[self::FIELD_SERVICED_DATE]
                || $this->servicedDate->_nonValueFieldDefined())) {
            $xw->startElement(self::FIELD_SERVICED_DATE);
            $this->servicedDate->xmlSerialize($xw, $config, $this->_valueXMLLocations[self::FIELD_SERVICED_DATE]);
            $xw->endElement();
        }
        if (isset($this->servicedPeriod)) {
            $xw->startElement(self::FIELD_SERVICED_PERIOD);
            $this->servicedPeriod->xmlSerialize($xw, $config);
            $xw->endElement();
        }
        if (isset($this->created)
            && (ValueXMLLocationEnum::PARENT_ATTRIBUTE !== $this->_valueXMLLocations[self::FIELD_CREATED]
                || $this->created->_nonValueFieldDefined())) {
            $xw->startElement(self::FIELD_CREATED);
            $this->created->xmlSerialize($xw, $config, $this->_valueXMLLocations[self::FIELD_CREATED]);
            $xw->endElement();
        }
        if (isset($this->requestor)) {
            $xw->startElement(self::FIELD_REQUESTOR);
            $this->requestor->xmlSerialize($xw, $config);
            $xw->endElement();
        }
        if (isset($this->request)) {
            $xw->startElement(self::FIELD_REQUEST);
            $this->request->xmlSerialize($xw, $config);
            $xw->endElement();
        }
        if (isset($this->outcome)
            && (ValueXMLLocationEnum::PARENT_ATTRIBUTE !== $this->_valueXMLLocations[self::FIELD_OUTCOME]
                || $this->outcome->_nonValueFieldDefined())) {
            $xw->startElement(self::FIELD_OUTCOME);
            $this->outcome->xmlSerialize($xw, $config, $this->_valueXMLLocations[self::FIELD_OUTCOME]);
            $xw->endElement();
        }
        if (isset($this->disposition)
            && (ValueXMLLocationEnum::PARENT_ATTRIBUTE !== $this->_valueXMLLocations[self::FIELD_DISPOSITION]
                || $this->disposition->_nonValueFieldDefined())) {
            $xw->startElement(self::FIELD_DISPOSITION);
            $this->disposition->xmlSerialize($xw, $config, $this->_valueXMLLocations[self::FIELD_DISPOSITION]);
            $xw->endElement();
        }
        if (isset($this->insurer)) {
            $xw->startElement(self::FIELD_INSURER);
            $this->insurer->xmlSerialize($xw, $config);
            $xw->endElement();
        }
        if (isset($this->insurance)) {
            foreach ($this->insurance as $v) {
                $xw->startElement(self::FIELD_INSURANCE);
                $v->xmlSerialize($xw, $config);
                $xw->endElement();
            }
        }
        if (isset($this->preAuthRef)
            && (ValueXMLLocationEnum::PARENT_ATTRIBUTE !== $this->_valueXMLLocations[self::FIELD_PRE_AUTH_REF]
                || $this->preAuthRef->_nonValueFieldDefined())) {
            $xw->startElement(self::FIELD_PRE_AUTH_REF);
            $this->preAuthRef->xmlSerialize($xw, $config, $this->_valueXMLLocations[self::FIELD_PRE_AUTH_REF]);
            $xw->endElement();
        }
        if (isset($this->form)) {
            $xw->startElement(self::FIELD_FORM);
            $this->form->xmlSerialize($xw, $config);
            $xw->endElement();
        }
        if (isset($this->error)) {
            foreach ($this->error as $v) {
                $xw->startElement(self::FIELD_ERROR);
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
     * @param null|\OpenEMR\FHIR\Versions\R4\Types\FHIRResource\FHIRDomainResource\FHIRCoverageEligibilityResponse $type
     * @return \OpenEMR\FHIR\Versions\R4\Types\FHIRResource\FHIRDomainResource\FHIRCoverageEligibilityResponse
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
        } else if (!($type instanceof FHIRCoverageEligibilityResponse)) {
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
            $type->setStatus(FHIRFinancialResourceStatusCodes::jsonUnserialize($v, $config));
        }
        if (isset($decoded->purpose)
            || isset($decoded->_purpose)
            || property_exists($decoded, self::FIELD_PURPOSE)
            || property_exists($decoded, self::FIELD_PURPOSE_EXT)) {
            $vals = (array)($decoded->purpose ?? []);
            $exts = (array)($decoded->_purpose ?? []);
            $valCnt = count($vals);
            $extCnt = count($exts);
            if ($extCnt > $valCnt) {
                $valCnt = $extCnt;
            }
            for ($i = 0; $i < $valCnt; $i++) {
                $v = $exts[$i] ?? new \stdClass();
                $v->value = $vals[$i] ?? null;
                $type->addPurpose(FHIREligibilityResponsePurpose::jsonUnserialize($v, $config));
            }
        }
        if (isset($decoded->patient) || property_exists($decoded, self::FIELD_PATIENT)) {
            if (is_array($decoded->patient)) {
                $type->setPatient(FHIRReference::jsonUnserialize(reset($decoded->patient), $config));
            } else {
                $type->setPatient(FHIRReference::jsonUnserialize($decoded->patient, $config));
            }
        }
        if (isset($decoded->servicedDate)
            || isset($decoded->_servicedDate)
            || property_exists($decoded, self::FIELD_SERVICED_DATE)
            || property_exists($decoded, self::FIELD_SERVICED_DATE_EXT)) {
            $v = $decoded->_servicedDate ?? new \stdClass();
            $v->value = $decoded->servicedDate ?? null;
            $type->setServicedDate(FHIRDate::jsonUnserialize($v, $config));
        }
        if (isset($decoded->servicedPeriod) || property_exists($decoded, self::FIELD_SERVICED_PERIOD)) {
            if (is_array($decoded->servicedPeriod)) {
                $type->setServicedPeriod(FHIRPeriod::jsonUnserialize(reset($decoded->servicedPeriod), $config));
            } else {
                $type->setServicedPeriod(FHIRPeriod::jsonUnserialize($decoded->servicedPeriod, $config));
            }
        }
        if (isset($decoded->created)
            || isset($decoded->_created)
            || property_exists($decoded, self::FIELD_CREATED)
            || property_exists($decoded, self::FIELD_CREATED_EXT)) {
            $v = $decoded->_created ?? new \stdClass();
            $v->value = $decoded->created ?? null;
            $type->setCreated(FHIRDateTime::jsonUnserialize($v, $config));
        }
        if (isset($decoded->requestor) || property_exists($decoded, self::FIELD_REQUESTOR)) {
            if (is_array($decoded->requestor)) {
                $type->setRequestor(FHIRReference::jsonUnserialize(reset($decoded->requestor), $config));
            } else {
                $type->setRequestor(FHIRReference::jsonUnserialize($decoded->requestor, $config));
            }
        }
        if (isset($decoded->request) || property_exists($decoded, self::FIELD_REQUEST)) {
            if (is_array($decoded->request)) {
                $type->setRequest(FHIRReference::jsonUnserialize(reset($decoded->request), $config));
            } else {
                $type->setRequest(FHIRReference::jsonUnserialize($decoded->request, $config));
            }
        }
        if (isset($decoded->outcome)
            || isset($decoded->_outcome)
            || property_exists($decoded, self::FIELD_OUTCOME)
            || property_exists($decoded, self::FIELD_OUTCOME_EXT)) {
            $v = $decoded->_outcome ?? new \stdClass();
            $v->value = $decoded->outcome ?? null;
            $type->setOutcome(FHIRRemittanceOutcome::jsonUnserialize($v, $config));
        }
        if (isset($decoded->disposition)
            || isset($decoded->_disposition)
            || property_exists($decoded, self::FIELD_DISPOSITION)
            || property_exists($decoded, self::FIELD_DISPOSITION_EXT)) {
            $v = $decoded->_disposition ?? new \stdClass();
            $v->value = $decoded->disposition ?? null;
            $type->setDisposition(FHIRString::jsonUnserialize($v, $config));
        }
        if (isset($decoded->insurer) || property_exists($decoded, self::FIELD_INSURER)) {
            if (is_array($decoded->insurer)) {
                $type->setInsurer(FHIRReference::jsonUnserialize(reset($decoded->insurer), $config));
            } else {
                $type->setInsurer(FHIRReference::jsonUnserialize($decoded->insurer, $config));
            }
        }
        if (isset($decoded->insurance) || property_exists($decoded, self::FIELD_INSURANCE)) {
            if (is_object($decoded->insurance)) {
                $vals = [$decoded->insurance];
                $type->_setJSONFieldElideSingletonArray(self::FIELD_INSURANCE, true);
            } else {
                $vals = $decoded->insurance;
            }
            foreach($vals as $v) {
                $type->addInsurance(FHIRCoverageEligibilityResponseInsurance::jsonUnserialize($v, $config));
            }
        }
        if (isset($decoded->preAuthRef)
            || isset($decoded->_preAuthRef)
            || property_exists($decoded, self::FIELD_PRE_AUTH_REF)
            || property_exists($decoded, self::FIELD_PRE_AUTH_REF_EXT)) {
            $v = $decoded->_preAuthRef ?? new \stdClass();
            $v->value = $decoded->preAuthRef ?? null;
            $type->setPreAuthRef(FHIRString::jsonUnserialize($v, $config));
        }
        if (isset($decoded->form) || property_exists($decoded, self::FIELD_FORM)) {
            if (is_array($decoded->form)) {
                $type->setForm(FHIRCodeableConcept::jsonUnserialize(reset($decoded->form), $config));
            } else {
                $type->setForm(FHIRCodeableConcept::jsonUnserialize($decoded->form, $config));
            }
        }
        if (isset($decoded->error) || property_exists($decoded, self::FIELD_ERROR)) {
            if (is_object($decoded->error)) {
                $vals = [$decoded->error];
                $type->_setJSONFieldElideSingletonArray(self::FIELD_ERROR, true);
            } else {
                $vals = $decoded->error;
            }
            foreach($vals as $v) {
                $type->addError(FHIRCoverageEligibilityResponseError::jsonUnserialize($v, $config));
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
        if (isset($this->purpose) && [] !== $this->purpose) {
            $vals = [];
            $exts = [];
            $hasVals = false;
            $hasExts = false;
            foreach ($this->purpose as $v) {
                $val = $v->getValue();
                if (null !== $val) {
                    $hasVals = true;
                    $vals[] = $val;
                } else {
                    $vals[] = null;
                }
                if ($v->_nonValueFieldDefined()) {
                    $hasExts = true;
                    $ext = $v->jsonSerialize();
                    unset($ext->value);
                    $exts[] = $ext;
                } else {
                    $exts[] = null;
                }
            }
            if ($hasVals) {
                $out->purpose = $vals;
            }
            if ($hasExts) {
                $out->_purpose = $exts;
            }
        }
        if (isset($this->patient)) {
            $out->patient = $this->patient;
        }
        if (isset($this->servicedDate)) {
            if (null !== ($val = $this->servicedDate->getValue())) {
                $out->servicedDate = $val;
            }
            if ($this->servicedDate->_nonValueFieldDefined()) {
                $ext = $this->servicedDate->jsonSerialize();
                unset($ext->value);
                $out->_servicedDate = $ext;
            }
        }
        if (isset($this->servicedPeriod)) {
            $out->servicedPeriod = $this->servicedPeriod;
        }
        if (isset($this->created)) {
            if (null !== ($val = $this->created->getValue())) {
                $out->created = $val;
            }
            if ($this->created->_nonValueFieldDefined()) {
                $ext = $this->created->jsonSerialize();
                unset($ext->value);
                $out->_created = $ext;
            }
        }
        if (isset($this->requestor)) {
            $out->requestor = $this->requestor;
        }
        if (isset($this->request)) {
            $out->request = $this->request;
        }
        if (isset($this->outcome)) {
            if (null !== ($val = $this->outcome->getValue())) {
                $out->outcome = $val;
            }
            if ($this->outcome->_nonValueFieldDefined()) {
                $ext = $this->outcome->jsonSerialize();
                unset($ext->value);
                $out->_outcome = $ext;
            }
        }
        if (isset($this->disposition)) {
            if (null !== ($val = $this->disposition->getValue())) {
                $out->disposition = $val;
            }
            if ($this->disposition->_nonValueFieldDefined()) {
                $ext = $this->disposition->jsonSerialize();
                unset($ext->value);
                $out->_disposition = $ext;
            }
        }
        if (isset($this->insurer)) {
            $out->insurer = $this->insurer;
        }
        if (isset($this->insurance) && [] !== $this->insurance) {
            if ($this->_getJSONFieldElideSingletonArray(self::FIELD_INSURANCE) && 1 === count($this->insurance)) {
                $out->insurance = $this->insurance[0];
            } else {
                $out->insurance = $this->insurance;
            }
        }
        if (isset($this->preAuthRef)) {
            if (null !== ($val = $this->preAuthRef->getValue())) {
                $out->preAuthRef = $val;
            }
            if ($this->preAuthRef->_nonValueFieldDefined()) {
                $ext = $this->preAuthRef->jsonSerialize();
                unset($ext->value);
                $out->_preAuthRef = $ext;
            }
        }
        if (isset($this->form)) {
            $out->form = $this->form;
        }
        if (isset($this->error) && [] !== $this->error) {
            if ($this->_getJSONFieldElideSingletonArray(self::FIELD_ERROR) && 1 === count($this->error)) {
                $out->error = $this->error[0];
            } else {
                $out->error = $this->error;
            }
        }
        $out->resourceType = $this->_getResourceType();
        return $out;
    }
}
