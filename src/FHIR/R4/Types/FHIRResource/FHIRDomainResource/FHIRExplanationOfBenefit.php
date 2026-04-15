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
use OpenEMR\FHIR\Versions\R4\Types\FHIRCodePrimitive\FHIRClaimProcessingCodesList;
use OpenEMR\FHIR\Versions\R4\Types\FHIRCodePrimitive\FHIRExplanationOfBenefitStatusList;
use OpenEMR\FHIR\Versions\R4\Types\FHIRCodePrimitive\FHIRUseList;
use OpenEMR\FHIR\Versions\R4\Types\FHIRDateTimePrimitive;
use OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRAttachment;
use OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRExplanationOfBenefit\FHIRExplanationOfBenefitAccident;
use OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRExplanationOfBenefit\FHIRExplanationOfBenefitAddItem;
use OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRExplanationOfBenefit\FHIRExplanationOfBenefitAdjudication;
use OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRExplanationOfBenefit\FHIRExplanationOfBenefitBenefitBalance;
use OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRExplanationOfBenefit\FHIRExplanationOfBenefitCareTeam;
use OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRExplanationOfBenefit\FHIRExplanationOfBenefitDiagnosis;
use OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRExplanationOfBenefit\FHIRExplanationOfBenefitInsurance;
use OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRExplanationOfBenefit\FHIRExplanationOfBenefitItem;
use OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRExplanationOfBenefit\FHIRExplanationOfBenefitPayee;
use OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRExplanationOfBenefit\FHIRExplanationOfBenefitPayment;
use OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRExplanationOfBenefit\FHIRExplanationOfBenefitProcedure;
use OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRExplanationOfBenefit\FHIRExplanationOfBenefitProcessNote;
use OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRExplanationOfBenefit\FHIRExplanationOfBenefitRelated;
use OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRExplanationOfBenefit\FHIRExplanationOfBenefitSupportingInfo;
use OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRExplanationOfBenefit\FHIRExplanationOfBenefitTotal;
use OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRClaimProcessingCodes;
use OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRCode;
use OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRCodeableConcept;
use OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRDateTime;
use OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRExplanationOfBenefitStatus;
use OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRExtension;
use OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRId;
use OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRIdentifier;
use OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRMeta;
use OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRNarrative;
use OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRPeriod;
use OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRPositiveInt;
use OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRReference;
use OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRString;
use OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRUri;
use OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRUse;
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
 * This resource provides: the claim details; adjudication details from the
 * processing of a Claim; and optionally account balance information, for informing
 * the subscriber of the benefits provided.
 * If the element is present, it must have either a \@value, an \@id, or extensions
 */
#[\Attribute(\Attribute::TARGET_PROPERTY)]
class FHIRExplanationOfBenefit extends FHIRDomainResource implements VersionContainedTypeInterface
{
    use TypeValidationsTrait,
        JSONSerializationOptionsTrait,
        XMLSerializationOptionsTrait;

    // name of FHIR type this class describes
    public const FHIR_TYPE_NAME = VersionConstants::TYPE_NAME_EXPLANATION_OF_BENEFIT;

    /* class_default.php:56 */
    public const FIELD_IDENTIFIER = 'identifier';
    public const FIELD_STATUS = 'status';
    public const FIELD_STATUS_EXT = '_status';
    public const FIELD_TYPE = 'type';
    public const FIELD_SUB_TYPE = 'subType';
    public const FIELD_USE = 'use';
    public const FIELD_USE_EXT = '_use';
    public const FIELD_PATIENT = 'patient';
    public const FIELD_BILLABLE_PERIOD = 'billablePeriod';
    public const FIELD_CREATED = 'created';
    public const FIELD_CREATED_EXT = '_created';
    public const FIELD_ENTERER = 'enterer';
    public const FIELD_INSURER = 'insurer';
    public const FIELD_PROVIDER = 'provider';
    public const FIELD_PRIORITY = 'priority';
    public const FIELD_FUNDS_RESERVE_REQUESTED = 'fundsReserveRequested';
    public const FIELD_FUNDS_RESERVE = 'fundsReserve';
    public const FIELD_RELATED = 'related';
    public const FIELD_PRESCRIPTION = 'prescription';
    public const FIELD_ORIGINAL_PRESCRIPTION = 'originalPrescription';
    public const FIELD_PAYEE = 'payee';
    public const FIELD_REFERRAL = 'referral';
    public const FIELD_FACILITY = 'facility';
    public const FIELD_CLAIM = 'claim';
    public const FIELD_CLAIM_RESPONSE = 'claimResponse';
    public const FIELD_OUTCOME = 'outcome';
    public const FIELD_OUTCOME_EXT = '_outcome';
    public const FIELD_DISPOSITION = 'disposition';
    public const FIELD_DISPOSITION_EXT = '_disposition';
    public const FIELD_PRE_AUTH_REF = 'preAuthRef';
    public const FIELD_PRE_AUTH_REF_EXT = '_preAuthRef';
    public const FIELD_PRE_AUTH_REF_PERIOD = 'preAuthRefPeriod';
    public const FIELD_CARE_TEAM = 'careTeam';
    public const FIELD_SUPPORTING_INFO = 'supportingInfo';
    public const FIELD_DIAGNOSIS = 'diagnosis';
    public const FIELD_PROCEDURE = 'procedure';
    public const FIELD_PRECEDENCE = 'precedence';
    public const FIELD_PRECEDENCE_EXT = '_precedence';
    public const FIELD_INSURANCE = 'insurance';
    public const FIELD_ACCIDENT = 'accident';
    public const FIELD_ITEM = 'item';
    public const FIELD_ADD_ITEM = 'addItem';
    public const FIELD_ADJUDICATION = 'adjudication';
    public const FIELD_TOTAL = 'total';
    public const FIELD_PAYMENT = 'payment';
    public const FIELD_FORM_CODE = 'formCode';
    public const FIELD_FORM = 'form';
    public const FIELD_PROCESS_NOTE = 'processNote';
    public const FIELD_BENEFIT_PERIOD = 'benefitPeriod';
    public const FIELD_BENEFIT_BALANCE = 'benefitBalance';

    /* class_default.php:75 */
    private const _FHIR_VALIDATION_RULES = [
        self::FIELD_STATUS => [
            MinOccursRule::NAME => 1,
        ],
        self::FIELD_TYPE => [
            MinOccursRule::NAME => 1,
        ],
        self::FIELD_USE => [
            MinOccursRule::NAME => 1,
        ],
        self::FIELD_PATIENT => [
            MinOccursRule::NAME => 1,
        ],
        self::FIELD_CREATED => [
            MinOccursRule::NAME => 1,
        ],
        self::FIELD_INSURER => [
            MinOccursRule::NAME => 1,
        ],
        self::FIELD_PROVIDER => [
            MinOccursRule::NAME => 1,
        ],
        self::FIELD_OUTCOME => [
            MinOccursRule::NAME => 1,
        ],
        self::FIELD_INSURANCE => [
            MinOccursRule::NAME => 1,
        ],
    ];

    /* class_default.php:96 */
    private array $_valueXMLLocations = [
        self::FIELD_STATUS => ValueXMLLocationEnum::CONTAINER_ATTRIBUTE,
        self::FIELD_USE => ValueXMLLocationEnum::CONTAINER_ATTRIBUTE,
        self::FIELD_CREATED => ValueXMLLocationEnum::CONTAINER_ATTRIBUTE,
        self::FIELD_OUTCOME => ValueXMLLocationEnum::CONTAINER_ATTRIBUTE,
        self::FIELD_DISPOSITION => ValueXMLLocationEnum::CONTAINER_ATTRIBUTE,
        self::FIELD_PRECEDENCE => ValueXMLLocationEnum::CONTAINER_ATTRIBUTE,
    ];

    /* class_default.php:112 */
    /**
     * An identifier - identifies some entity uniquely and unambiguously. Typically
     * this is used for business identifiers.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * A unique identifier assigned to this explanation of benefit.
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
     * @var \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRExplanationOfBenefitStatus
     */
    #[FHIRExplanationOfBenefitStatus]
    protected FHIRExplanationOfBenefitStatus $status;
    /**
     * A concept that may be defined by a formal reference to a terminology or ontology
     * or may be provided by text.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * The category of claim, e.g. oral, pharmacy, vision, institutional, professional.
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
     * A finer grained suite of claim type codes which may convey additional
     * information such as Inpatient vs Outpatient and/or a specialty service.
     *
     * @var \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRCodeableConcept
     */
    #[FHIRCodeableConcept]
    protected FHIRCodeableConcept $subType;
    /**
     * The purpose of the Claim: predetermination, preauthorization, claim.
     * If the element is present, it must have either a \@value, an \@id, or extensions
     *
     * A code to indicate whether the nature of the request is: to request adjudication
     * of products and services previously rendered; or requesting authorization and
     * adjudication for provision in the future; or requesting the non-binding
     * adjudication of the listed products and services which could be provided in the
     * future.
     *
     * @var \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRUse
     */
    #[FHIRUse]
    protected FHIRUse $use;
    /**
     * A reference from one resource to another.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * The party to whom the professional services and/or products have been supplied
     * or are being considered and for whom actual for forecast reimbursement is
     * sought.
     *
     * @var \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRReference
     */
    #[FHIRReference]
    protected FHIRReference $patient;
    /**
     * A time period defined by a start and end date and optionally time.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * The period for which charges are being submitted.
     *
     * @var \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRPeriod
     */
    #[FHIRPeriod]
    protected FHIRPeriod $billablePeriod;
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
     * Individual who created the claim, predetermination or preauthorization.
     *
     * @var \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRReference
     */
    #[FHIRReference]
    protected FHIRReference $enterer;
    /**
     * A reference from one resource to another.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * The party responsible for authorization, adjudication and reimbursement.
     *
     * @var \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRReference
     */
    #[FHIRReference]
    protected FHIRReference $insurer;
    /**
     * A reference from one resource to another.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * The provider which is responsible for the claim, predetermination or
     * preauthorization.
     *
     * @var \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRReference
     */
    #[FHIRReference]
    protected FHIRReference $provider;
    /**
     * A concept that may be defined by a formal reference to a terminology or ontology
     * or may be provided by text.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * The provider-required urgency of processing the request. Typical values include:
     * stat, routine deferred.
     *
     * @var \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRCodeableConcept
     */
    #[FHIRCodeableConcept]
    protected FHIRCodeableConcept $priority;
    /**
     * A concept that may be defined by a formal reference to a terminology or ontology
     * or may be provided by text.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * A code to indicate whether and for whom funds are to be reserved for future
     * claims.
     *
     * @var \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRCodeableConcept
     */
    #[FHIRCodeableConcept]
    protected FHIRCodeableConcept $fundsReserveRequested;
    /**
     * A concept that may be defined by a formal reference to a terminology or ontology
     * or may be provided by text.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * A code, used only on a response to a preauthorization, to indicate whether the
     * benefits payable have been reserved and for whom.
     *
     * @var \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRCodeableConcept
     */
    #[FHIRCodeableConcept]
    protected FHIRCodeableConcept $fundsReserve;
    /**
     * This resource provides: the claim details; adjudication details from the
     * processing of a Claim; and optionally account balance information, for informing
     * the subscriber of the benefits provided.
     *
     * Other claims which are related to this claim such as prior submissions or claims
     * for related services or for the same event.
     *
     * @var iterable<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRExplanationOfBenefit\FHIRExplanationOfBenefitRelated>
     */
    #[FHIRExplanationOfBenefitRelated]
    protected array $related;
    /**
     * A reference from one resource to another.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * Prescription to support the dispensing of pharmacy, device or vision products.
     *
     * @var \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRReference
     */
    #[FHIRReference]
    protected FHIRReference $prescription;
    /**
     * A reference from one resource to another.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * Original prescription which has been superseded by this prescription to support
     * the dispensing of pharmacy services, medications or products.
     *
     * @var \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRReference
     */
    #[FHIRReference]
    protected FHIRReference $originalPrescription;
    /**
     * This resource provides: the claim details; adjudication details from the
     * processing of a Claim; and optionally account balance information, for informing
     * the subscriber of the benefits provided.
     *
     * The party to be reimbursed for cost of the products and services according to
     * the terms of the policy.
     *
     * @var \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRExplanationOfBenefit\FHIRExplanationOfBenefitPayee
     */
    #[FHIRExplanationOfBenefitPayee]
    protected FHIRExplanationOfBenefitPayee $payee;
    /**
     * A reference from one resource to another.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * A reference to a referral resource.
     *
     * @var \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRReference
     */
    #[FHIRReference]
    protected FHIRReference $referral;
    /**
     * A reference from one resource to another.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * Facility where the services were provided.
     *
     * @var \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRReference
     */
    #[FHIRReference]
    protected FHIRReference $facility;
    /**
     * A reference from one resource to another.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * The business identifier for the instance of the adjudication request: claim
     * predetermination or preauthorization.
     *
     * @var \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRReference
     */
    #[FHIRReference]
    protected FHIRReference $claim;
    /**
     * A reference from one resource to another.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * The business identifier for the instance of the adjudication response: claim,
     * predetermination or preauthorization response.
     *
     * @var \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRReference
     */
    #[FHIRReference]
    protected FHIRReference $claimResponse;
    /**
     * The result of the claim processing.
     * If the element is present, it must have either a \@value, an \@id, or extensions
     *
     * The outcome of the claim, predetermination, or preauthorization processing.
     *
     * @var \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRClaimProcessingCodes
     */
    #[FHIRClaimProcessingCodes]
    protected FHIRClaimProcessingCodes $outcome;
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
     * A sequence of Unicode characters
     * Note that FHIR strings SHALL NOT exceed 1MB in size
     * If the element is present, it must have either a \@value, an \@id, or extensions
     *
     * Reference from the Insurer which is used in later communications which refers to
     * this adjudication.
     *
     * @var iterable<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRString>
     */
    #[FHIRString]
    protected array $preAuthRef;
    /**
     * A time period defined by a start and end date and optionally time.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * The timeframe during which the supplied preauthorization reference may be quoted
     * on claims to obtain the adjudication as provided.
     *
     * @var iterable<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRPeriod>
     */
    #[FHIRPeriod]
    protected array $preAuthRefPeriod;
    /**
     * This resource provides: the claim details; adjudication details from the
     * processing of a Claim; and optionally account balance information, for informing
     * the subscriber of the benefits provided.
     *
     * The members of the team who provided the products and services.
     *
     * @var iterable<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRExplanationOfBenefit\FHIRExplanationOfBenefitCareTeam>
     */
    #[FHIRExplanationOfBenefitCareTeam]
    protected array $careTeam;
    /**
     * This resource provides: the claim details; adjudication details from the
     * processing of a Claim; and optionally account balance information, for informing
     * the subscriber of the benefits provided.
     *
     * Additional information codes regarding exceptions, special considerations, the
     * condition, situation, prior or concurrent issues.
     *
     * @var iterable<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRExplanationOfBenefit\FHIRExplanationOfBenefitSupportingInfo>
     */
    #[FHIRExplanationOfBenefitSupportingInfo]
    protected array $supportingInfo;
    /**
     * This resource provides: the claim details; adjudication details from the
     * processing of a Claim; and optionally account balance information, for informing
     * the subscriber of the benefits provided.
     *
     * Information about diagnoses relevant to the claim items.
     *
     * @var iterable<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRExplanationOfBenefit\FHIRExplanationOfBenefitDiagnosis>
     */
    #[FHIRExplanationOfBenefitDiagnosis]
    protected array $diagnosis;
    /**
     * This resource provides: the claim details; adjudication details from the
     * processing of a Claim; and optionally account balance information, for informing
     * the subscriber of the benefits provided.
     *
     * Procedures performed on the patient relevant to the billing items with the
     * claim.
     *
     * @var iterable<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRExplanationOfBenefit\FHIRExplanationOfBenefitProcedure>
     */
    #[FHIRExplanationOfBenefitProcedure]
    protected array $procedure;
    /**
     * An integer with a value that is positive (e.g. >0)
     * If the element is present, it must have either a \@value, an \@id referenced from
     * the Narrative, or extensions
     *
     * This indicates the relative order of a series of EOBs related to different
     * coverages for the same suite of services.
     *
     * @var \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRPositiveInt
     */
    #[FHIRPositiveInt]
    protected FHIRPositiveInt $precedence;
    /**
     * This resource provides: the claim details; adjudication details from the
     * processing of a Claim; and optionally account balance information, for informing
     * the subscriber of the benefits provided.
     *
     * Financial instruments for reimbursement for the health care products and
     * services specified on the claim.
     *
     * @var iterable<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRExplanationOfBenefit\FHIRExplanationOfBenefitInsurance>
     */
    #[FHIRExplanationOfBenefitInsurance]
    protected array $insurance;
    /**
     * This resource provides: the claim details; adjudication details from the
     * processing of a Claim; and optionally account balance information, for informing
     * the subscriber of the benefits provided.
     *
     * Details of a accident which resulted in injuries which required the products and
     * services listed in the claim.
     *
     * @var \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRExplanationOfBenefit\FHIRExplanationOfBenefitAccident
     */
    #[FHIRExplanationOfBenefitAccident]
    protected FHIRExplanationOfBenefitAccident $accident;
    /**
     * This resource provides: the claim details; adjudication details from the
     * processing of a Claim; and optionally account balance information, for informing
     * the subscriber of the benefits provided.
     *
     * A claim line. Either a simple (a product or service) or a 'group' of details
     * which can also be a simple items or groups of sub-details.
     *
     * @var iterable<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRExplanationOfBenefit\FHIRExplanationOfBenefitItem>
     */
    #[FHIRExplanationOfBenefitItem]
    protected array $item;
    /**
     * This resource provides: the claim details; adjudication details from the
     * processing of a Claim; and optionally account balance information, for informing
     * the subscriber of the benefits provided.
     *
     * The first-tier service adjudications for payor added product or service lines.
     *
     * @var iterable<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRExplanationOfBenefit\FHIRExplanationOfBenefitAddItem>
     */
    #[FHIRExplanationOfBenefitAddItem]
    protected array $addItem;
    /**
     * This resource provides: the claim details; adjudication details from the
     * processing of a Claim; and optionally account balance information, for informing
     * the subscriber of the benefits provided.
     *
     * The adjudication results which are presented at the header level rather than at
     * the line-item or add-item levels.
     *
     * @var iterable<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRExplanationOfBenefit\FHIRExplanationOfBenefitAdjudication>
     */
    #[FHIRExplanationOfBenefitAdjudication]
    protected array $adjudication;
    /**
     * This resource provides: the claim details; adjudication details from the
     * processing of a Claim; and optionally account balance information, for informing
     * the subscriber of the benefits provided.
     *
     * Categorized monetary totals for the adjudication.
     *
     * @var iterable<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRExplanationOfBenefit\FHIRExplanationOfBenefitTotal>
     */
    #[FHIRExplanationOfBenefitTotal]
    protected array $total;
    /**
     * This resource provides: the claim details; adjudication details from the
     * processing of a Claim; and optionally account balance information, for informing
     * the subscriber of the benefits provided.
     *
     * Payment details for the adjudication of the claim.
     *
     * @var \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRExplanationOfBenefit\FHIRExplanationOfBenefitPayment
     */
    #[FHIRExplanationOfBenefitPayment]
    protected FHIRExplanationOfBenefitPayment $payment;
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
    protected FHIRCodeableConcept $formCode;
    /**
     * For referring to data content defined in other formats.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * The actual form, by reference or inclusion, for printing the content or an EOB.
     *
     * @var \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRAttachment
     */
    #[FHIRAttachment]
    protected FHIRAttachment $form;
    /**
     * This resource provides: the claim details; adjudication details from the
     * processing of a Claim; and optionally account balance information, for informing
     * the subscriber of the benefits provided.
     *
     * A note that describes or explains adjudication results in a human readable form.
     *
     * @var iterable<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRExplanationOfBenefit\FHIRExplanationOfBenefitProcessNote>
     */
    #[FHIRExplanationOfBenefitProcessNote]
    protected array $processNote;
    /**
     * A time period defined by a start and end date and optionally time.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * The term of the benefits documented in this response.
     *
     * @var \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRPeriod
     */
    #[FHIRPeriod]
    protected FHIRPeriod $benefitPeriod;
    /**
     * This resource provides: the claim details; adjudication details from the
     * processing of a Claim; and optionally account balance information, for informing
     * the subscriber of the benefits provided.
     *
     * Balance by Benefit Category.
     *
     * @var iterable<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRExplanationOfBenefit\FHIRExplanationOfBenefitBenefitBalance>
     */
    #[FHIRExplanationOfBenefitBenefitBalance]
    protected array $benefitBalance;

    /* constructor.php:61 */
    /**
     * FHIRExplanationOfBenefit Constructor
     * @param null|string|\OpenEMR\FHIR\Versions\R4\Types\FHIRIdPrimitive|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRId $id
     * @param null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRMeta $meta
     * @param null|string|\OpenEMR\FHIR\Versions\R4\Types\FHIRUriPrimitive|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRUri $implicitRules
     * @param null|string|\OpenEMR\FHIR\Versions\R4\Types\FHIRCodePrimitive|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRCode $language
     * @param null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRNarrative $text
     * @param null|iterable<\OpenEMR\FHIR\Versions\R4\Types\FHIRResourceContainer>|iterable<\OpenEMR\FHIR\Versions\R4\VersionContainedTypeInterface> $contained
     * @param null|iterable<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRExtension> $extension
     * @param null|iterable<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRExtension> $modifierExtension
     * @param null|iterable<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRIdentifier> $identifier
     * @param null|string|\OpenEMR\FHIR\Versions\R4\Types\FHIRCodePrimitive\FHIRExplanationOfBenefitStatusList|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRExplanationOfBenefitStatus $status
     * @param null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRCodeableConcept $type
     * @param null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRCodeableConcept $subType
     * @param null|string|\OpenEMR\FHIR\Versions\R4\Types\FHIRCodePrimitive\FHIRUseList|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRUse $use
     * @param null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRReference $patient
     * @param null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRPeriod $billablePeriod
     * @param null|string|\DateTimeInterface|\OpenEMR\FHIR\Versions\R4\Types\FHIRDateTimePrimitive|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRDateTime $created
     * @param null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRReference $enterer
     * @param null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRReference $insurer
     * @param null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRReference $provider
     * @param null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRCodeableConcept $priority
     * @param null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRCodeableConcept $fundsReserveRequested
     * @param null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRCodeableConcept $fundsReserve
     * @param null|iterable<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRExplanationOfBenefit\FHIRExplanationOfBenefitRelated> $related
     * @param null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRReference $prescription
     * @param null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRReference $originalPrescription
     * @param null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRExplanationOfBenefit\FHIRExplanationOfBenefitPayee $payee
     * @param null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRReference $referral
     * @param null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRReference $facility
     * @param null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRReference $claim
     * @param null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRReference $claimResponse
     * @param null|string|\OpenEMR\FHIR\Versions\R4\Types\FHIRCodePrimitive\FHIRClaimProcessingCodesList|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRClaimProcessingCodes $outcome
     * @param null|string|\OpenEMR\FHIR\Versions\R4\Types\FHIRStringPrimitive|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRString $disposition
     * @param null|iterable<string>|iterable<\OpenEMR\FHIR\Versions\R4\Types\FHIRStringPrimitive>|iterable<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRString> $preAuthRef
     * @param null|iterable<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRPeriod> $preAuthRefPeriod
     * @param null|iterable<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRExplanationOfBenefit\FHIRExplanationOfBenefitCareTeam> $careTeam
     * @param null|iterable<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRExplanationOfBenefit\FHIRExplanationOfBenefitSupportingInfo> $supportingInfo
     * @param null|iterable<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRExplanationOfBenefit\FHIRExplanationOfBenefitDiagnosis> $diagnosis
     * @param null|iterable<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRExplanationOfBenefit\FHIRExplanationOfBenefitProcedure> $procedure
     * @param null|string|float|\OpenEMR\FHIR\Versions\R4\Types\FHIRPositiveIntPrimitive|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRPositiveInt $precedence
     * @param null|iterable<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRExplanationOfBenefit\FHIRExplanationOfBenefitInsurance> $insurance
     * @param null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRExplanationOfBenefit\FHIRExplanationOfBenefitAccident $accident
     * @param null|iterable<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRExplanationOfBenefit\FHIRExplanationOfBenefitItem> $item
     * @param null|iterable<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRExplanationOfBenefit\FHIRExplanationOfBenefitAddItem> $addItem
     * @param null|iterable<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRExplanationOfBenefit\FHIRExplanationOfBenefitAdjudication> $adjudication
     * @param null|iterable<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRExplanationOfBenefit\FHIRExplanationOfBenefitTotal> $total
     * @param null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRExplanationOfBenefit\FHIRExplanationOfBenefitPayment $payment
     * @param null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRCodeableConcept $formCode
     * @param null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRAttachment $form
     * @param null|iterable<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRExplanationOfBenefit\FHIRExplanationOfBenefitProcessNote> $processNote
     * @param null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRPeriod $benefitPeriod
     * @param null|iterable<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRExplanationOfBenefit\FHIRExplanationOfBenefitBenefitBalance> $benefitBalance
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
                                null|string|FHIRExplanationOfBenefitStatusList|FHIRExplanationOfBenefitStatus $status = null,
                                null|FHIRCodeableConcept $type = null,
                                null|FHIRCodeableConcept $subType = null,
                                null|string|FHIRUseList|FHIRUse $use = null,
                                null|FHIRReference $patient = null,
                                null|FHIRPeriod $billablePeriod = null,
                                null|string|\DateTimeInterface|FHIRDateTimePrimitive|FHIRDateTime $created = null,
                                null|FHIRReference $enterer = null,
                                null|FHIRReference $insurer = null,
                                null|FHIRReference $provider = null,
                                null|FHIRCodeableConcept $priority = null,
                                null|FHIRCodeableConcept $fundsReserveRequested = null,
                                null|FHIRCodeableConcept $fundsReserve = null,
                                null|iterable $related = null,
                                null|FHIRReference $prescription = null,
                                null|FHIRReference $originalPrescription = null,
                                null|FHIRExplanationOfBenefitPayee $payee = null,
                                null|FHIRReference $referral = null,
                                null|FHIRReference $facility = null,
                                null|FHIRReference $claim = null,
                                null|FHIRReference $claimResponse = null,
                                null|string|FHIRClaimProcessingCodesList|FHIRClaimProcessingCodes $outcome = null,
                                null|string|FHIRStringPrimitive|FHIRString $disposition = null,
                                null|iterable $preAuthRef = null,
                                null|iterable $preAuthRefPeriod = null,
                                null|iterable $careTeam = null,
                                null|iterable $supportingInfo = null,
                                null|iterable $diagnosis = null,
                                null|iterable $procedure = null,
                                null|string|float|FHIRPositiveIntPrimitive|FHIRPositiveInt $precedence = null,
                                null|iterable $insurance = null,
                                null|FHIRExplanationOfBenefitAccident $accident = null,
                                null|iterable $item = null,
                                null|iterable $addItem = null,
                                null|iterable $adjudication = null,
                                null|iterable $total = null,
                                null|FHIRExplanationOfBenefitPayment $payment = null,
                                null|FHIRCodeableConcept $formCode = null,
                                null|FHIRAttachment $form = null,
                                null|iterable $processNote = null,
                                null|FHIRPeriod $benefitPeriod = null,
                                null|iterable $benefitBalance = null,
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
        if (null !== $type) {
            $this->setType($type);
        }
        if (null !== $subType) {
            $this->setSubType($subType);
        }
        if (null !== $use) {
            $this->setUse($use);
        }
        if (null !== $patient) {
            $this->setPatient($patient);
        }
        if (null !== $billablePeriod) {
            $this->setBillablePeriod($billablePeriod);
        }
        if (null !== $created) {
            $this->setCreated($created);
        }
        if (null !== $enterer) {
            $this->setEnterer($enterer);
        }
        if (null !== $insurer) {
            $this->setInsurer($insurer);
        }
        if (null !== $provider) {
            $this->setProvider($provider);
        }
        if (null !== $priority) {
            $this->setPriority($priority);
        }
        if (null !== $fundsReserveRequested) {
            $this->setFundsReserveRequested($fundsReserveRequested);
        }
        if (null !== $fundsReserve) {
            $this->setFundsReserve($fundsReserve);
        }
        if (null !== $related) {
            $this->setRelated(...$related);
        }
        if (null !== $prescription) {
            $this->setPrescription($prescription);
        }
        if (null !== $originalPrescription) {
            $this->setOriginalPrescription($originalPrescription);
        }
        if (null !== $payee) {
            $this->setPayee($payee);
        }
        if (null !== $referral) {
            $this->setReferral($referral);
        }
        if (null !== $facility) {
            $this->setFacility($facility);
        }
        if (null !== $claim) {
            $this->setClaim($claim);
        }
        if (null !== $claimResponse) {
            $this->setClaimResponse($claimResponse);
        }
        if (null !== $outcome) {
            $this->setOutcome($outcome);
        }
        if (null !== $disposition) {
            $this->setDisposition($disposition);
        }
        if (null !== $preAuthRef) {
            $this->setPreAuthRef(...$preAuthRef);
        }
        if (null !== $preAuthRefPeriod) {
            $this->setPreAuthRefPeriod(...$preAuthRefPeriod);
        }
        if (null !== $careTeam) {
            $this->setCareTeam(...$careTeam);
        }
        if (null !== $supportingInfo) {
            $this->setSupportingInfo(...$supportingInfo);
        }
        if (null !== $diagnosis) {
            $this->setDiagnosis(...$diagnosis);
        }
        if (null !== $procedure) {
            $this->setProcedure(...$procedure);
        }
        if (null !== $precedence) {
            $this->setPrecedence($precedence);
        }
        if (null !== $insurance) {
            $this->setInsurance(...$insurance);
        }
        if (null !== $accident) {
            $this->setAccident($accident);
        }
        if (null !== $item) {
            $this->setItem(...$item);
        }
        if (null !== $addItem) {
            $this->setAddItem(...$addItem);
        }
        if (null !== $adjudication) {
            $this->setAdjudication(...$adjudication);
        }
        if (null !== $total) {
            $this->setTotal(...$total);
        }
        if (null !== $payment) {
            $this->setPayment($payment);
        }
        if (null !== $formCode) {
            $this->setFormCode($formCode);
        }
        if (null !== $form) {
            $this->setForm($form);
        }
        if (null !== $processNote) {
            $this->setProcessNote(...$processNote);
        }
        if (null !== $benefitPeriod) {
            $this->setBenefitPeriod($benefitPeriod);
        }
        if (null !== $benefitBalance) {
            $this->setBenefitBalance(...$benefitBalance);
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
     * A unique identifier assigned to this explanation of benefit.
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
     * A unique identifier assigned to this explanation of benefit.
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
     * A unique identifier assigned to this explanation of benefit.
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
     * @return null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRExplanationOfBenefitStatus
     */
    public function getStatus(): null|FHIRExplanationOfBenefitStatus
    {
        return $this->status ?? null;
    }

    /**
     * A code specifying the state of the resource instance.
     * If the element is present, it must have either a \@value, an \@id, or extensions
     *
     * The status of the resource instance.
     *
     * @param null|string|\OpenEMR\FHIR\Versions\R4\Types\FHIRCodePrimitive\FHIRExplanationOfBenefitStatusList|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRExplanationOfBenefitStatus $status
     * @return static
     */
    public function setStatus(null|string|FHIRExplanationOfBenefitStatusList|FHIRExplanationOfBenefitStatus $status): self
    {
        if (null === $status) {
            unset($this->status);
            return $this;
        }
        if (!($status instanceof FHIRExplanationOfBenefitStatus)) {
            $status = new FHIRExplanationOfBenefitStatus(value: $status);
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
     * The category of claim, e.g. oral, pharmacy, vision, institutional, professional.
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
     * The category of claim, e.g. oral, pharmacy, vision, institutional, professional.
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
     * A finer grained suite of claim type codes which may convey additional
     * information such as Inpatient vs Outpatient and/or a specialty service.
     *
     * @return null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRCodeableConcept
     */
    public function getSubType(): null|FHIRCodeableConcept
    {
        return $this->subType ?? null;
    }

    /**
     * A concept that may be defined by a formal reference to a terminology or ontology
     * or may be provided by text.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * A finer grained suite of claim type codes which may convey additional
     * information such as Inpatient vs Outpatient and/or a specialty service.
     *
     * @param null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRCodeableConcept $subType
     * @return static
     */
    public function setSubType(null|FHIRCodeableConcept $subType): self
    {
        if (null === $subType) {
            unset($this->subType);
            return $this;
        }
        $this->subType = $subType;
        return $this;
    }

    /**
     * The purpose of the Claim: predetermination, preauthorization, claim.
     * If the element is present, it must have either a \@value, an \@id, or extensions
     *
     * A code to indicate whether the nature of the request is: to request adjudication
     * of products and services previously rendered; or requesting authorization and
     * adjudication for provision in the future; or requesting the non-binding
     * adjudication of the listed products and services which could be provided in the
     * future.
     *
     * @return null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRUse
     */
    public function getUse(): null|FHIRUse
    {
        return $this->use ?? null;
    }

    /**
     * The purpose of the Claim: predetermination, preauthorization, claim.
     * If the element is present, it must have either a \@value, an \@id, or extensions
     *
     * A code to indicate whether the nature of the request is: to request adjudication
     * of products and services previously rendered; or requesting authorization and
     * adjudication for provision in the future; or requesting the non-binding
     * adjudication of the listed products and services which could be provided in the
     * future.
     *
     * @param null|string|\OpenEMR\FHIR\Versions\R4\Types\FHIRCodePrimitive\FHIRUseList|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRUse $use
     * @return static
     */
    public function setUse(null|string|FHIRUseList|FHIRUse $use): self
    {
        if (null === $use) {
            unset($this->use);
            return $this;
        }
        if (!($use instanceof FHIRUse)) {
            $use = new FHIRUse(value: $use);
        }
        $this->use = $use;
        return $this;
    }

    /**
     * A reference from one resource to another.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * The party to whom the professional services and/or products have been supplied
     * or are being considered and for whom actual for forecast reimbursement is
     * sought.
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
     * The party to whom the professional services and/or products have been supplied
     * or are being considered and for whom actual for forecast reimbursement is
     * sought.
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
     * A time period defined by a start and end date and optionally time.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * The period for which charges are being submitted.
     *
     * @return null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRPeriod
     */
    public function getBillablePeriod(): null|FHIRPeriod
    {
        return $this->billablePeriod ?? null;
    }

    /**
     * A time period defined by a start and end date and optionally time.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * The period for which charges are being submitted.
     *
     * @param null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRPeriod $billablePeriod
     * @return static
     */
    public function setBillablePeriod(null|FHIRPeriod $billablePeriod): self
    {
        if (null === $billablePeriod) {
            unset($this->billablePeriod);
            return $this;
        }
        $this->billablePeriod = $billablePeriod;
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
     * Individual who created the claim, predetermination or preauthorization.
     *
     * @return null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRReference
     */
    public function getEnterer(): null|FHIRReference
    {
        return $this->enterer ?? null;
    }

    /**
     * A reference from one resource to another.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * Individual who created the claim, predetermination or preauthorization.
     *
     * @param null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRReference $enterer
     * @return static
     */
    public function setEnterer(null|FHIRReference $enterer): self
    {
        if (null === $enterer) {
            unset($this->enterer);
            return $this;
        }
        $this->enterer = $enterer;
        return $this;
    }

    /**
     * A reference from one resource to another.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * The party responsible for authorization, adjudication and reimbursement.
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
     * The party responsible for authorization, adjudication and reimbursement.
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
     * A reference from one resource to another.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * The provider which is responsible for the claim, predetermination or
     * preauthorization.
     *
     * @return null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRReference
     */
    public function getProvider(): null|FHIRReference
    {
        return $this->provider ?? null;
    }

    /**
     * A reference from one resource to another.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * The provider which is responsible for the claim, predetermination or
     * preauthorization.
     *
     * @param null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRReference $provider
     * @return static
     */
    public function setProvider(null|FHIRReference $provider): self
    {
        if (null === $provider) {
            unset($this->provider);
            return $this;
        }
        $this->provider = $provider;
        return $this;
    }

    /**
     * A concept that may be defined by a formal reference to a terminology or ontology
     * or may be provided by text.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * The provider-required urgency of processing the request. Typical values include:
     * stat, routine deferred.
     *
     * @return null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRCodeableConcept
     */
    public function getPriority(): null|FHIRCodeableConcept
    {
        return $this->priority ?? null;
    }

    /**
     * A concept that may be defined by a formal reference to a terminology or ontology
     * or may be provided by text.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * The provider-required urgency of processing the request. Typical values include:
     * stat, routine deferred.
     *
     * @param null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRCodeableConcept $priority
     * @return static
     */
    public function setPriority(null|FHIRCodeableConcept $priority): self
    {
        if (null === $priority) {
            unset($this->priority);
            return $this;
        }
        $this->priority = $priority;
        return $this;
    }

    /**
     * A concept that may be defined by a formal reference to a terminology or ontology
     * or may be provided by text.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * A code to indicate whether and for whom funds are to be reserved for future
     * claims.
     *
     * @return null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRCodeableConcept
     */
    public function getFundsReserveRequested(): null|FHIRCodeableConcept
    {
        return $this->fundsReserveRequested ?? null;
    }

    /**
     * A concept that may be defined by a formal reference to a terminology or ontology
     * or may be provided by text.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * A code to indicate whether and for whom funds are to be reserved for future
     * claims.
     *
     * @param null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRCodeableConcept $fundsReserveRequested
     * @return static
     */
    public function setFundsReserveRequested(null|FHIRCodeableConcept $fundsReserveRequested): self
    {
        if (null === $fundsReserveRequested) {
            unset($this->fundsReserveRequested);
            return $this;
        }
        $this->fundsReserveRequested = $fundsReserveRequested;
        return $this;
    }

    /**
     * A concept that may be defined by a formal reference to a terminology or ontology
     * or may be provided by text.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * A code, used only on a response to a preauthorization, to indicate whether the
     * benefits payable have been reserved and for whom.
     *
     * @return null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRCodeableConcept
     */
    public function getFundsReserve(): null|FHIRCodeableConcept
    {
        return $this->fundsReserve ?? null;
    }

    /**
     * A concept that may be defined by a formal reference to a terminology or ontology
     * or may be provided by text.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * A code, used only on a response to a preauthorization, to indicate whether the
     * benefits payable have been reserved and for whom.
     *
     * @param null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRCodeableConcept $fundsReserve
     * @return static
     */
    public function setFundsReserve(null|FHIRCodeableConcept $fundsReserve): self
    {
        if (null === $fundsReserve) {
            unset($this->fundsReserve);
            return $this;
        }
        $this->fundsReserve = $fundsReserve;
        return $this;
    }

    /**
     * This resource provides: the claim details; adjudication details from the
     * processing of a Claim; and optionally account balance information, for informing
     * the subscriber of the benefits provided.
     *
     * Other claims which are related to this claim such as prior submissions or claims
     * for related services or for the same event.
     *
     * @return iterable<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRExplanationOfBenefit\FHIRExplanationOfBenefitRelated>
     */
    public function getRelated(): array
    {
        return $this->related ?? [];
    }

    /**
     * @return \ArrayIterator<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRExplanationOfBenefit\FHIRExplanationOfBenefitRelated>
     */
    public function getRelatedIterator(): iterable
    {
        if (!isset($this->related)) {
            return new \EmptyIterator();
        }
        return new \ArrayIterator($this->related);
    }

    /**
     * This resource provides: the claim details; adjudication details from the
     * processing of a Claim; and optionally account balance information, for informing
     * the subscriber of the benefits provided.
     *
     * Other claims which are related to this claim such as prior submissions or claims
     * for related services or for the same event.
     *
     * @param \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRExplanationOfBenefit\FHIRExplanationOfBenefitRelated $related
     * @return static
     */
    public function addRelated(FHIRExplanationOfBenefitRelated $related): self
    {
        if (!isset($this->related)) {
            $this->related = [];
        }
        $this->related[] = $related;
        return $this;
    }

    /**
     * This resource provides: the claim details; adjudication details from the
     * processing of a Claim; and optionally account balance information, for informing
     * the subscriber of the benefits provided.
     *
     * Other claims which are related to this claim such as prior submissions or claims
     * for related services or for the same event.
     *
     * @param \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRExplanationOfBenefit\FHIRExplanationOfBenefitRelated ...$related
     * @return static
     */
    public function setRelated(FHIRExplanationOfBenefitRelated ...$related): self
    {
        if ([] === $related) {
            unset($this->related);
            return $this;
        }
        $this->related = $related;
        return $this;
    }

    /**
     * A reference from one resource to another.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * Prescription to support the dispensing of pharmacy, device or vision products.
     *
     * @return null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRReference
     */
    public function getPrescription(): null|FHIRReference
    {
        return $this->prescription ?? null;
    }

    /**
     * A reference from one resource to another.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * Prescription to support the dispensing of pharmacy, device or vision products.
     *
     * @param null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRReference $prescription
     * @return static
     */
    public function setPrescription(null|FHIRReference $prescription): self
    {
        if (null === $prescription) {
            unset($this->prescription);
            return $this;
        }
        $this->prescription = $prescription;
        return $this;
    }

    /**
     * A reference from one resource to another.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * Original prescription which has been superseded by this prescription to support
     * the dispensing of pharmacy services, medications or products.
     *
     * @return null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRReference
     */
    public function getOriginalPrescription(): null|FHIRReference
    {
        return $this->originalPrescription ?? null;
    }

    /**
     * A reference from one resource to another.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * Original prescription which has been superseded by this prescription to support
     * the dispensing of pharmacy services, medications or products.
     *
     * @param null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRReference $originalPrescription
     * @return static
     */
    public function setOriginalPrescription(null|FHIRReference $originalPrescription): self
    {
        if (null === $originalPrescription) {
            unset($this->originalPrescription);
            return $this;
        }
        $this->originalPrescription = $originalPrescription;
        return $this;
    }

    /**
     * This resource provides: the claim details; adjudication details from the
     * processing of a Claim; and optionally account balance information, for informing
     * the subscriber of the benefits provided.
     *
     * The party to be reimbursed for cost of the products and services according to
     * the terms of the policy.
     *
     * @return null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRExplanationOfBenefit\FHIRExplanationOfBenefitPayee
     */
    public function getPayee(): null|FHIRExplanationOfBenefitPayee
    {
        return $this->payee ?? null;
    }

    /**
     * This resource provides: the claim details; adjudication details from the
     * processing of a Claim; and optionally account balance information, for informing
     * the subscriber of the benefits provided.
     *
     * The party to be reimbursed for cost of the products and services according to
     * the terms of the policy.
     *
     * @param null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRExplanationOfBenefit\FHIRExplanationOfBenefitPayee $payee
     * @return static
     */
    public function setPayee(null|FHIRExplanationOfBenefitPayee $payee): self
    {
        if (null === $payee) {
            unset($this->payee);
            return $this;
        }
        $this->payee = $payee;
        return $this;
    }

    /**
     * A reference from one resource to another.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * A reference to a referral resource.
     *
     * @return null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRReference
     */
    public function getReferral(): null|FHIRReference
    {
        return $this->referral ?? null;
    }

    /**
     * A reference from one resource to another.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * A reference to a referral resource.
     *
     * @param null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRReference $referral
     * @return static
     */
    public function setReferral(null|FHIRReference $referral): self
    {
        if (null === $referral) {
            unset($this->referral);
            return $this;
        }
        $this->referral = $referral;
        return $this;
    }

    /**
     * A reference from one resource to another.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * Facility where the services were provided.
     *
     * @return null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRReference
     */
    public function getFacility(): null|FHIRReference
    {
        return $this->facility ?? null;
    }

    /**
     * A reference from one resource to another.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * Facility where the services were provided.
     *
     * @param null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRReference $facility
     * @return static
     */
    public function setFacility(null|FHIRReference $facility): self
    {
        if (null === $facility) {
            unset($this->facility);
            return $this;
        }
        $this->facility = $facility;
        return $this;
    }

    /**
     * A reference from one resource to another.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * The business identifier for the instance of the adjudication request: claim
     * predetermination or preauthorization.
     *
     * @return null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRReference
     */
    public function getClaim(): null|FHIRReference
    {
        return $this->claim ?? null;
    }

    /**
     * A reference from one resource to another.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * The business identifier for the instance of the adjudication request: claim
     * predetermination or preauthorization.
     *
     * @param null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRReference $claim
     * @return static
     */
    public function setClaim(null|FHIRReference $claim): self
    {
        if (null === $claim) {
            unset($this->claim);
            return $this;
        }
        $this->claim = $claim;
        return $this;
    }

    /**
     * A reference from one resource to another.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * The business identifier for the instance of the adjudication response: claim,
     * predetermination or preauthorization response.
     *
     * @return null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRReference
     */
    public function getClaimResponse(): null|FHIRReference
    {
        return $this->claimResponse ?? null;
    }

    /**
     * A reference from one resource to another.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * The business identifier for the instance of the adjudication response: claim,
     * predetermination or preauthorization response.
     *
     * @param null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRReference $claimResponse
     * @return static
     */
    public function setClaimResponse(null|FHIRReference $claimResponse): self
    {
        if (null === $claimResponse) {
            unset($this->claimResponse);
            return $this;
        }
        $this->claimResponse = $claimResponse;
        return $this;
    }

    /**
     * The result of the claim processing.
     * If the element is present, it must have either a \@value, an \@id, or extensions
     *
     * The outcome of the claim, predetermination, or preauthorization processing.
     *
     * @return null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRClaimProcessingCodes
     */
    public function getOutcome(): null|FHIRClaimProcessingCodes
    {
        return $this->outcome ?? null;
    }

    /**
     * The result of the claim processing.
     * If the element is present, it must have either a \@value, an \@id, or extensions
     *
     * The outcome of the claim, predetermination, or preauthorization processing.
     *
     * @param null|string|\OpenEMR\FHIR\Versions\R4\Types\FHIRCodePrimitive\FHIRClaimProcessingCodesList|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRClaimProcessingCodes $outcome
     * @return static
     */
    public function setOutcome(null|string|FHIRClaimProcessingCodesList|FHIRClaimProcessingCodes $outcome): self
    {
        if (null === $outcome) {
            unset($this->outcome);
            return $this;
        }
        if (!($outcome instanceof FHIRClaimProcessingCodes)) {
            $outcome = new FHIRClaimProcessingCodes(value: $outcome);
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
     * A sequence of Unicode characters
     * Note that FHIR strings SHALL NOT exceed 1MB in size
     * If the element is present, it must have either a \@value, an \@id, or extensions
     *
     * Reference from the Insurer which is used in later communications which refers to
     * this adjudication.
     *
     * @return iterable<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRString>
     */
    public function getPreAuthRef(): array
    {
        return $this->preAuthRef ?? [];
    }

    /**
     * @return \ArrayIterator<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRString>
     */
    public function getPreAuthRefIterator(): iterable
    {
        if (!isset($this->preAuthRef)) {
            return new \EmptyIterator();
        }
        return new \ArrayIterator($this->preAuthRef);
    }

    /**
     * A sequence of Unicode characters
     * Note that FHIR strings SHALL NOT exceed 1MB in size
     * If the element is present, it must have either a \@value, an \@id, or extensions
     *
     * Reference from the Insurer which is used in later communications which refers to
     * this adjudication.
     *
     * @param string|\OpenEMR\FHIR\Versions\R4\Types\FHIRStringPrimitive|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRString $preAuthRef
     * @return static
     */
    public function addPreAuthRef(string|FHIRStringPrimitive|FHIRString $preAuthRef): self
    {
        if (!($preAuthRef instanceof FHIRString)) {
            $preAuthRef = new FHIRString(value: $preAuthRef);
        }
        if (!isset($this->preAuthRef)) {
            $this->preAuthRef = [];
        }
        $this->preAuthRef[] = $preAuthRef;
        return $this;
    }

    /**
     * A sequence of Unicode characters
     * Note that FHIR strings SHALL NOT exceed 1MB in size
     * If the element is present, it must have either a \@value, an \@id, or extensions
     *
     * Reference from the Insurer which is used in later communications which refers to
     * this adjudication.
     *
     * @param string|\OpenEMR\FHIR\Versions\R4\Types\FHIRStringPrimitive|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRString ...$preAuthRef
     * @return static
     */
    public function setPreAuthRef(string|FHIRStringPrimitive|FHIRString ...$preAuthRef): self
    {
        if ([] === $preAuthRef) {
            unset($this->preAuthRef);
            return $this;
        }
        $this->preAuthRef = [];
        foreach($preAuthRef as $v) {
            if ($v instanceof FHIRString) {
                $this->preAuthRef[] = $v;
            } else {
                $this->preAuthRef[] = new FHIRString(value: $v);
            }
        }
        return $this;
    }

    /**
     * A time period defined by a start and end date and optionally time.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * The timeframe during which the supplied preauthorization reference may be quoted
     * on claims to obtain the adjudication as provided.
     *
     * @return iterable<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRPeriod>
     */
    public function getPreAuthRefPeriod(): array
    {
        return $this->preAuthRefPeriod ?? [];
    }

    /**
     * @return \ArrayIterator<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRPeriod>
     */
    public function getPreAuthRefPeriodIterator(): iterable
    {
        if (!isset($this->preAuthRefPeriod)) {
            return new \EmptyIterator();
        }
        return new \ArrayIterator($this->preAuthRefPeriod);
    }

    /**
     * A time period defined by a start and end date and optionally time.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * The timeframe during which the supplied preauthorization reference may be quoted
     * on claims to obtain the adjudication as provided.
     *
     * @param \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRPeriod $preAuthRefPeriod
     * @return static
     */
    public function addPreAuthRefPeriod(FHIRPeriod $preAuthRefPeriod): self
    {
        if (!isset($this->preAuthRefPeriod)) {
            $this->preAuthRefPeriod = [];
        }
        $this->preAuthRefPeriod[] = $preAuthRefPeriod;
        return $this;
    }

    /**
     * A time period defined by a start and end date and optionally time.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * The timeframe during which the supplied preauthorization reference may be quoted
     * on claims to obtain the adjudication as provided.
     *
     * @param \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRPeriod ...$preAuthRefPeriod
     * @return static
     */
    public function setPreAuthRefPeriod(FHIRPeriod ...$preAuthRefPeriod): self
    {
        if ([] === $preAuthRefPeriod) {
            unset($this->preAuthRefPeriod);
            return $this;
        }
        $this->preAuthRefPeriod = $preAuthRefPeriod;
        return $this;
    }

    /**
     * This resource provides: the claim details; adjudication details from the
     * processing of a Claim; and optionally account balance information, for informing
     * the subscriber of the benefits provided.
     *
     * The members of the team who provided the products and services.
     *
     * @return iterable<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRExplanationOfBenefit\FHIRExplanationOfBenefitCareTeam>
     */
    public function getCareTeam(): array
    {
        return $this->careTeam ?? [];
    }

    /**
     * @return \ArrayIterator<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRExplanationOfBenefit\FHIRExplanationOfBenefitCareTeam>
     */
    public function getCareTeamIterator(): iterable
    {
        if (!isset($this->careTeam)) {
            return new \EmptyIterator();
        }
        return new \ArrayIterator($this->careTeam);
    }

    /**
     * This resource provides: the claim details; adjudication details from the
     * processing of a Claim; and optionally account balance information, for informing
     * the subscriber of the benefits provided.
     *
     * The members of the team who provided the products and services.
     *
     * @param \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRExplanationOfBenefit\FHIRExplanationOfBenefitCareTeam $careTeam
     * @return static
     */
    public function addCareTeam(FHIRExplanationOfBenefitCareTeam $careTeam): self
    {
        if (!isset($this->careTeam)) {
            $this->careTeam = [];
        }
        $this->careTeam[] = $careTeam;
        return $this;
    }

    /**
     * This resource provides: the claim details; adjudication details from the
     * processing of a Claim; and optionally account balance information, for informing
     * the subscriber of the benefits provided.
     *
     * The members of the team who provided the products and services.
     *
     * @param \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRExplanationOfBenefit\FHIRExplanationOfBenefitCareTeam ...$careTeam
     * @return static
     */
    public function setCareTeam(FHIRExplanationOfBenefitCareTeam ...$careTeam): self
    {
        if ([] === $careTeam) {
            unset($this->careTeam);
            return $this;
        }
        $this->careTeam = $careTeam;
        return $this;
    }

    /**
     * This resource provides: the claim details; adjudication details from the
     * processing of a Claim; and optionally account balance information, for informing
     * the subscriber of the benefits provided.
     *
     * Additional information codes regarding exceptions, special considerations, the
     * condition, situation, prior or concurrent issues.
     *
     * @return iterable<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRExplanationOfBenefit\FHIRExplanationOfBenefitSupportingInfo>
     */
    public function getSupportingInfo(): array
    {
        return $this->supportingInfo ?? [];
    }

    /**
     * @return \ArrayIterator<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRExplanationOfBenefit\FHIRExplanationOfBenefitSupportingInfo>
     */
    public function getSupportingInfoIterator(): iterable
    {
        if (!isset($this->supportingInfo)) {
            return new \EmptyIterator();
        }
        return new \ArrayIterator($this->supportingInfo);
    }

    /**
     * This resource provides: the claim details; adjudication details from the
     * processing of a Claim; and optionally account balance information, for informing
     * the subscriber of the benefits provided.
     *
     * Additional information codes regarding exceptions, special considerations, the
     * condition, situation, prior or concurrent issues.
     *
     * @param \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRExplanationOfBenefit\FHIRExplanationOfBenefitSupportingInfo $supportingInfo
     * @return static
     */
    public function addSupportingInfo(FHIRExplanationOfBenefitSupportingInfo $supportingInfo): self
    {
        if (!isset($this->supportingInfo)) {
            $this->supportingInfo = [];
        }
        $this->supportingInfo[] = $supportingInfo;
        return $this;
    }

    /**
     * This resource provides: the claim details; adjudication details from the
     * processing of a Claim; and optionally account balance information, for informing
     * the subscriber of the benefits provided.
     *
     * Additional information codes regarding exceptions, special considerations, the
     * condition, situation, prior or concurrent issues.
     *
     * @param \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRExplanationOfBenefit\FHIRExplanationOfBenefitSupportingInfo ...$supportingInfo
     * @return static
     */
    public function setSupportingInfo(FHIRExplanationOfBenefitSupportingInfo ...$supportingInfo): self
    {
        if ([] === $supportingInfo) {
            unset($this->supportingInfo);
            return $this;
        }
        $this->supportingInfo = $supportingInfo;
        return $this;
    }

    /**
     * This resource provides: the claim details; adjudication details from the
     * processing of a Claim; and optionally account balance information, for informing
     * the subscriber of the benefits provided.
     *
     * Information about diagnoses relevant to the claim items.
     *
     * @return iterable<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRExplanationOfBenefit\FHIRExplanationOfBenefitDiagnosis>
     */
    public function getDiagnosis(): array
    {
        return $this->diagnosis ?? [];
    }

    /**
     * @return \ArrayIterator<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRExplanationOfBenefit\FHIRExplanationOfBenefitDiagnosis>
     */
    public function getDiagnosisIterator(): iterable
    {
        if (!isset($this->diagnosis)) {
            return new \EmptyIterator();
        }
        return new \ArrayIterator($this->diagnosis);
    }

    /**
     * This resource provides: the claim details; adjudication details from the
     * processing of a Claim; and optionally account balance information, for informing
     * the subscriber of the benefits provided.
     *
     * Information about diagnoses relevant to the claim items.
     *
     * @param \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRExplanationOfBenefit\FHIRExplanationOfBenefitDiagnosis $diagnosis
     * @return static
     */
    public function addDiagnosis(FHIRExplanationOfBenefitDiagnosis $diagnosis): self
    {
        if (!isset($this->diagnosis)) {
            $this->diagnosis = [];
        }
        $this->diagnosis[] = $diagnosis;
        return $this;
    }

    /**
     * This resource provides: the claim details; adjudication details from the
     * processing of a Claim; and optionally account balance information, for informing
     * the subscriber of the benefits provided.
     *
     * Information about diagnoses relevant to the claim items.
     *
     * @param \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRExplanationOfBenefit\FHIRExplanationOfBenefitDiagnosis ...$diagnosis
     * @return static
     */
    public function setDiagnosis(FHIRExplanationOfBenefitDiagnosis ...$diagnosis): self
    {
        if ([] === $diagnosis) {
            unset($this->diagnosis);
            return $this;
        }
        $this->diagnosis = $diagnosis;
        return $this;
    }

    /**
     * This resource provides: the claim details; adjudication details from the
     * processing of a Claim; and optionally account balance information, for informing
     * the subscriber of the benefits provided.
     *
     * Procedures performed on the patient relevant to the billing items with the
     * claim.
     *
     * @return iterable<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRExplanationOfBenefit\FHIRExplanationOfBenefitProcedure>
     */
    public function getProcedure(): array
    {
        return $this->procedure ?? [];
    }

    /**
     * @return \ArrayIterator<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRExplanationOfBenefit\FHIRExplanationOfBenefitProcedure>
     */
    public function getProcedureIterator(): iterable
    {
        if (!isset($this->procedure)) {
            return new \EmptyIterator();
        }
        return new \ArrayIterator($this->procedure);
    }

    /**
     * This resource provides: the claim details; adjudication details from the
     * processing of a Claim; and optionally account balance information, for informing
     * the subscriber of the benefits provided.
     *
     * Procedures performed on the patient relevant to the billing items with the
     * claim.
     *
     * @param \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRExplanationOfBenefit\FHIRExplanationOfBenefitProcedure $procedure
     * @return static
     */
    public function addProcedure(FHIRExplanationOfBenefitProcedure $procedure): self
    {
        if (!isset($this->procedure)) {
            $this->procedure = [];
        }
        $this->procedure[] = $procedure;
        return $this;
    }

    /**
     * This resource provides: the claim details; adjudication details from the
     * processing of a Claim; and optionally account balance information, for informing
     * the subscriber of the benefits provided.
     *
     * Procedures performed on the patient relevant to the billing items with the
     * claim.
     *
     * @param \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRExplanationOfBenefit\FHIRExplanationOfBenefitProcedure ...$procedure
     * @return static
     */
    public function setProcedure(FHIRExplanationOfBenefitProcedure ...$procedure): self
    {
        if ([] === $procedure) {
            unset($this->procedure);
            return $this;
        }
        $this->procedure = $procedure;
        return $this;
    }

    /**
     * An integer with a value that is positive (e.g. >0)
     * If the element is present, it must have either a \@value, an \@id referenced from
     * the Narrative, or extensions
     *
     * This indicates the relative order of a series of EOBs related to different
     * coverages for the same suite of services.
     *
     * @return null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRPositiveInt
     */
    public function getPrecedence(): null|FHIRPositiveInt
    {
        return $this->precedence ?? null;
    }

    /**
     * An integer with a value that is positive (e.g. >0)
     * If the element is present, it must have either a \@value, an \@id referenced from
     * the Narrative, or extensions
     *
     * This indicates the relative order of a series of EOBs related to different
     * coverages for the same suite of services.
     *
     * @param null|string|float|\OpenEMR\FHIR\Versions\R4\Types\FHIRPositiveIntPrimitive|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRPositiveInt $precedence
     * @return static
     */
    public function setPrecedence(null|string|float|FHIRPositiveIntPrimitive|FHIRPositiveInt $precedence): self
    {
        if (null === $precedence) {
            unset($this->precedence);
            return $this;
        }
        if (!($precedence instanceof FHIRPositiveInt)) {
            $precedence = new FHIRPositiveInt(value: $precedence);
        }
        $this->precedence = $precedence;
        return $this;
    }

    /**
     * This resource provides: the claim details; adjudication details from the
     * processing of a Claim; and optionally account balance information, for informing
     * the subscriber of the benefits provided.
     *
     * Financial instruments for reimbursement for the health care products and
     * services specified on the claim.
     *
     * @return iterable<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRExplanationOfBenefit\FHIRExplanationOfBenefitInsurance>
     */
    public function getInsurance(): array
    {
        return $this->insurance ?? [];
    }

    /**
     * @return \ArrayIterator<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRExplanationOfBenefit\FHIRExplanationOfBenefitInsurance>
     */
    public function getInsuranceIterator(): iterable
    {
        if (!isset($this->insurance)) {
            return new \EmptyIterator();
        }
        return new \ArrayIterator($this->insurance);
    }

    /**
     * This resource provides: the claim details; adjudication details from the
     * processing of a Claim; and optionally account balance information, for informing
     * the subscriber of the benefits provided.
     *
     * Financial instruments for reimbursement for the health care products and
     * services specified on the claim.
     *
     * @param \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRExplanationOfBenefit\FHIRExplanationOfBenefitInsurance $insurance
     * @return static
     */
    public function addInsurance(FHIRExplanationOfBenefitInsurance $insurance): self
    {
        if (!isset($this->insurance)) {
            $this->insurance = [];
        }
        $this->insurance[] = $insurance;
        return $this;
    }

    /**
     * This resource provides: the claim details; adjudication details from the
     * processing of a Claim; and optionally account balance information, for informing
     * the subscriber of the benefits provided.
     *
     * Financial instruments for reimbursement for the health care products and
     * services specified on the claim.
     *
     * @param \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRExplanationOfBenefit\FHIRExplanationOfBenefitInsurance ...$insurance
     * @return static
     */
    public function setInsurance(FHIRExplanationOfBenefitInsurance ...$insurance): self
    {
        if ([] === $insurance) {
            unset($this->insurance);
            return $this;
        }
        $this->insurance = $insurance;
        return $this;
    }

    /**
     * This resource provides: the claim details; adjudication details from the
     * processing of a Claim; and optionally account balance information, for informing
     * the subscriber of the benefits provided.
     *
     * Details of a accident which resulted in injuries which required the products and
     * services listed in the claim.
     *
     * @return null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRExplanationOfBenefit\FHIRExplanationOfBenefitAccident
     */
    public function getAccident(): null|FHIRExplanationOfBenefitAccident
    {
        return $this->accident ?? null;
    }

    /**
     * This resource provides: the claim details; adjudication details from the
     * processing of a Claim; and optionally account balance information, for informing
     * the subscriber of the benefits provided.
     *
     * Details of a accident which resulted in injuries which required the products and
     * services listed in the claim.
     *
     * @param null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRExplanationOfBenefit\FHIRExplanationOfBenefitAccident $accident
     * @return static
     */
    public function setAccident(null|FHIRExplanationOfBenefitAccident $accident): self
    {
        if (null === $accident) {
            unset($this->accident);
            return $this;
        }
        $this->accident = $accident;
        return $this;
    }

    /**
     * This resource provides: the claim details; adjudication details from the
     * processing of a Claim; and optionally account balance information, for informing
     * the subscriber of the benefits provided.
     *
     * A claim line. Either a simple (a product or service) or a 'group' of details
     * which can also be a simple items or groups of sub-details.
     *
     * @return iterable<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRExplanationOfBenefit\FHIRExplanationOfBenefitItem>
     */
    public function getItem(): array
    {
        return $this->item ?? [];
    }

    /**
     * @return \ArrayIterator<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRExplanationOfBenefit\FHIRExplanationOfBenefitItem>
     */
    public function getItemIterator(): iterable
    {
        if (!isset($this->item)) {
            return new \EmptyIterator();
        }
        return new \ArrayIterator($this->item);
    }

    /**
     * This resource provides: the claim details; adjudication details from the
     * processing of a Claim; and optionally account balance information, for informing
     * the subscriber of the benefits provided.
     *
     * A claim line. Either a simple (a product or service) or a 'group' of details
     * which can also be a simple items or groups of sub-details.
     *
     * @param \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRExplanationOfBenefit\FHIRExplanationOfBenefitItem $item
     * @return static
     */
    public function addItem(FHIRExplanationOfBenefitItem $item): self
    {
        if (!isset($this->item)) {
            $this->item = [];
        }
        $this->item[] = $item;
        return $this;
    }

    /**
     * This resource provides: the claim details; adjudication details from the
     * processing of a Claim; and optionally account balance information, for informing
     * the subscriber of the benefits provided.
     *
     * A claim line. Either a simple (a product or service) or a 'group' of details
     * which can also be a simple items or groups of sub-details.
     *
     * @param \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRExplanationOfBenefit\FHIRExplanationOfBenefitItem ...$item
     * @return static
     */
    public function setItem(FHIRExplanationOfBenefitItem ...$item): self
    {
        if ([] === $item) {
            unset($this->item);
            return $this;
        }
        $this->item = $item;
        return $this;
    }

    /**
     * This resource provides: the claim details; adjudication details from the
     * processing of a Claim; and optionally account balance information, for informing
     * the subscriber of the benefits provided.
     *
     * The first-tier service adjudications for payor added product or service lines.
     *
     * @return iterable<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRExplanationOfBenefit\FHIRExplanationOfBenefitAddItem>
     */
    public function getAddItem(): array
    {
        return $this->addItem ?? [];
    }

    /**
     * @return \ArrayIterator<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRExplanationOfBenefit\FHIRExplanationOfBenefitAddItem>
     */
    public function getAddItemIterator(): iterable
    {
        if (!isset($this->addItem)) {
            return new \EmptyIterator();
        }
        return new \ArrayIterator($this->addItem);
    }

    /**
     * This resource provides: the claim details; adjudication details from the
     * processing of a Claim; and optionally account balance information, for informing
     * the subscriber of the benefits provided.
     *
     * The first-tier service adjudications for payor added product or service lines.
     *
     * @param \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRExplanationOfBenefit\FHIRExplanationOfBenefitAddItem $addItem
     * @return static
     */
    public function addAddItem(FHIRExplanationOfBenefitAddItem $addItem): self
    {
        if (!isset($this->addItem)) {
            $this->addItem = [];
        }
        $this->addItem[] = $addItem;
        return $this;
    }

    /**
     * This resource provides: the claim details; adjudication details from the
     * processing of a Claim; and optionally account balance information, for informing
     * the subscriber of the benefits provided.
     *
     * The first-tier service adjudications for payor added product or service lines.
     *
     * @param \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRExplanationOfBenefit\FHIRExplanationOfBenefitAddItem ...$addItem
     * @return static
     */
    public function setAddItem(FHIRExplanationOfBenefitAddItem ...$addItem): self
    {
        if ([] === $addItem) {
            unset($this->addItem);
            return $this;
        }
        $this->addItem = $addItem;
        return $this;
    }

    /**
     * This resource provides: the claim details; adjudication details from the
     * processing of a Claim; and optionally account balance information, for informing
     * the subscriber of the benefits provided.
     *
     * The adjudication results which are presented at the header level rather than at
     * the line-item or add-item levels.
     *
     * @return iterable<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRExplanationOfBenefit\FHIRExplanationOfBenefitAdjudication>
     */
    public function getAdjudication(): array
    {
        return $this->adjudication ?? [];
    }

    /**
     * @return \ArrayIterator<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRExplanationOfBenefit\FHIRExplanationOfBenefitAdjudication>
     */
    public function getAdjudicationIterator(): iterable
    {
        if (!isset($this->adjudication)) {
            return new \EmptyIterator();
        }
        return new \ArrayIterator($this->adjudication);
    }

    /**
     * This resource provides: the claim details; adjudication details from the
     * processing of a Claim; and optionally account balance information, for informing
     * the subscriber of the benefits provided.
     *
     * The adjudication results which are presented at the header level rather than at
     * the line-item or add-item levels.
     *
     * @param \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRExplanationOfBenefit\FHIRExplanationOfBenefitAdjudication $adjudication
     * @return static
     */
    public function addAdjudication(FHIRExplanationOfBenefitAdjudication $adjudication): self
    {
        if (!isset($this->adjudication)) {
            $this->adjudication = [];
        }
        $this->adjudication[] = $adjudication;
        return $this;
    }

    /**
     * This resource provides: the claim details; adjudication details from the
     * processing of a Claim; and optionally account balance information, for informing
     * the subscriber of the benefits provided.
     *
     * The adjudication results which are presented at the header level rather than at
     * the line-item or add-item levels.
     *
     * @param \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRExplanationOfBenefit\FHIRExplanationOfBenefitAdjudication ...$adjudication
     * @return static
     */
    public function setAdjudication(FHIRExplanationOfBenefitAdjudication ...$adjudication): self
    {
        if ([] === $adjudication) {
            unset($this->adjudication);
            return $this;
        }
        $this->adjudication = $adjudication;
        return $this;
    }

    /**
     * This resource provides: the claim details; adjudication details from the
     * processing of a Claim; and optionally account balance information, for informing
     * the subscriber of the benefits provided.
     *
     * Categorized monetary totals for the adjudication.
     *
     * @return iterable<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRExplanationOfBenefit\FHIRExplanationOfBenefitTotal>
     */
    public function getTotal(): array
    {
        return $this->total ?? [];
    }

    /**
     * @return \ArrayIterator<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRExplanationOfBenefit\FHIRExplanationOfBenefitTotal>
     */
    public function getTotalIterator(): iterable
    {
        if (!isset($this->total)) {
            return new \EmptyIterator();
        }
        return new \ArrayIterator($this->total);
    }

    /**
     * This resource provides: the claim details; adjudication details from the
     * processing of a Claim; and optionally account balance information, for informing
     * the subscriber of the benefits provided.
     *
     * Categorized monetary totals for the adjudication.
     *
     * @param \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRExplanationOfBenefit\FHIRExplanationOfBenefitTotal $total
     * @return static
     */
    public function addTotal(FHIRExplanationOfBenefitTotal $total): self
    {
        if (!isset($this->total)) {
            $this->total = [];
        }
        $this->total[] = $total;
        return $this;
    }

    /**
     * This resource provides: the claim details; adjudication details from the
     * processing of a Claim; and optionally account balance information, for informing
     * the subscriber of the benefits provided.
     *
     * Categorized monetary totals for the adjudication.
     *
     * @param \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRExplanationOfBenefit\FHIRExplanationOfBenefitTotal ...$total
     * @return static
     */
    public function setTotal(FHIRExplanationOfBenefitTotal ...$total): self
    {
        if ([] === $total) {
            unset($this->total);
            return $this;
        }
        $this->total = $total;
        return $this;
    }

    /**
     * This resource provides: the claim details; adjudication details from the
     * processing of a Claim; and optionally account balance information, for informing
     * the subscriber of the benefits provided.
     *
     * Payment details for the adjudication of the claim.
     *
     * @return null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRExplanationOfBenefit\FHIRExplanationOfBenefitPayment
     */
    public function getPayment(): null|FHIRExplanationOfBenefitPayment
    {
        return $this->payment ?? null;
    }

    /**
     * This resource provides: the claim details; adjudication details from the
     * processing of a Claim; and optionally account balance information, for informing
     * the subscriber of the benefits provided.
     *
     * Payment details for the adjudication of the claim.
     *
     * @param null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRExplanationOfBenefit\FHIRExplanationOfBenefitPayment $payment
     * @return static
     */
    public function setPayment(null|FHIRExplanationOfBenefitPayment $payment): self
    {
        if (null === $payment) {
            unset($this->payment);
            return $this;
        }
        $this->payment = $payment;
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
    public function getFormCode(): null|FHIRCodeableConcept
    {
        return $this->formCode ?? null;
    }

    /**
     * A concept that may be defined by a formal reference to a terminology or ontology
     * or may be provided by text.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * A code for the form to be used for printing the content.
     *
     * @param null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRCodeableConcept $formCode
     * @return static
     */
    public function setFormCode(null|FHIRCodeableConcept $formCode): self
    {
        if (null === $formCode) {
            unset($this->formCode);
            return $this;
        }
        $this->formCode = $formCode;
        return $this;
    }

    /**
     * For referring to data content defined in other formats.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * The actual form, by reference or inclusion, for printing the content or an EOB.
     *
     * @return null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRAttachment
     */
    public function getForm(): null|FHIRAttachment
    {
        return $this->form ?? null;
    }

    /**
     * For referring to data content defined in other formats.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * The actual form, by reference or inclusion, for printing the content or an EOB.
     *
     * @param null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRAttachment $form
     * @return static
     */
    public function setForm(null|FHIRAttachment $form): self
    {
        if (null === $form) {
            unset($this->form);
            return $this;
        }
        $this->form = $form;
        return $this;
    }

    /**
     * This resource provides: the claim details; adjudication details from the
     * processing of a Claim; and optionally account balance information, for informing
     * the subscriber of the benefits provided.
     *
     * A note that describes or explains adjudication results in a human readable form.
     *
     * @return iterable<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRExplanationOfBenefit\FHIRExplanationOfBenefitProcessNote>
     */
    public function getProcessNote(): array
    {
        return $this->processNote ?? [];
    }

    /**
     * @return \ArrayIterator<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRExplanationOfBenefit\FHIRExplanationOfBenefitProcessNote>
     */
    public function getProcessNoteIterator(): iterable
    {
        if (!isset($this->processNote)) {
            return new \EmptyIterator();
        }
        return new \ArrayIterator($this->processNote);
    }

    /**
     * This resource provides: the claim details; adjudication details from the
     * processing of a Claim; and optionally account balance information, for informing
     * the subscriber of the benefits provided.
     *
     * A note that describes or explains adjudication results in a human readable form.
     *
     * @param \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRExplanationOfBenefit\FHIRExplanationOfBenefitProcessNote $processNote
     * @return static
     */
    public function addProcessNote(FHIRExplanationOfBenefitProcessNote $processNote): self
    {
        if (!isset($this->processNote)) {
            $this->processNote = [];
        }
        $this->processNote[] = $processNote;
        return $this;
    }

    /**
     * This resource provides: the claim details; adjudication details from the
     * processing of a Claim; and optionally account balance information, for informing
     * the subscriber of the benefits provided.
     *
     * A note that describes or explains adjudication results in a human readable form.
     *
     * @param \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRExplanationOfBenefit\FHIRExplanationOfBenefitProcessNote ...$processNote
     * @return static
     */
    public function setProcessNote(FHIRExplanationOfBenefitProcessNote ...$processNote): self
    {
        if ([] === $processNote) {
            unset($this->processNote);
            return $this;
        }
        $this->processNote = $processNote;
        return $this;
    }

    /**
     * A time period defined by a start and end date and optionally time.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * The term of the benefits documented in this response.
     *
     * @return null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRPeriod
     */
    public function getBenefitPeriod(): null|FHIRPeriod
    {
        return $this->benefitPeriod ?? null;
    }

    /**
     * A time period defined by a start and end date and optionally time.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * The term of the benefits documented in this response.
     *
     * @param null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRPeriod $benefitPeriod
     * @return static
     */
    public function setBenefitPeriod(null|FHIRPeriod $benefitPeriod): self
    {
        if (null === $benefitPeriod) {
            unset($this->benefitPeriod);
            return $this;
        }
        $this->benefitPeriod = $benefitPeriod;
        return $this;
    }

    /**
     * This resource provides: the claim details; adjudication details from the
     * processing of a Claim; and optionally account balance information, for informing
     * the subscriber of the benefits provided.
     *
     * Balance by Benefit Category.
     *
     * @return iterable<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRExplanationOfBenefit\FHIRExplanationOfBenefitBenefitBalance>
     */
    public function getBenefitBalance(): array
    {
        return $this->benefitBalance ?? [];
    }

    /**
     * @return \ArrayIterator<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRExplanationOfBenefit\FHIRExplanationOfBenefitBenefitBalance>
     */
    public function getBenefitBalanceIterator(): iterable
    {
        if (!isset($this->benefitBalance)) {
            return new \EmptyIterator();
        }
        return new \ArrayIterator($this->benefitBalance);
    }

    /**
     * This resource provides: the claim details; adjudication details from the
     * processing of a Claim; and optionally account balance information, for informing
     * the subscriber of the benefits provided.
     *
     * Balance by Benefit Category.
     *
     * @param \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRExplanationOfBenefit\FHIRExplanationOfBenefitBenefitBalance $benefitBalance
     * @return static
     */
    public function addBenefitBalance(FHIRExplanationOfBenefitBenefitBalance $benefitBalance): self
    {
        if (!isset($this->benefitBalance)) {
            $this->benefitBalance = [];
        }
        $this->benefitBalance[] = $benefitBalance;
        return $this;
    }

    /**
     * This resource provides: the claim details; adjudication details from the
     * processing of a Claim; and optionally account balance information, for informing
     * the subscriber of the benefits provided.
     *
     * Balance by Benefit Category.
     *
     * @param \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRExplanationOfBenefit\FHIRExplanationOfBenefitBenefitBalance ...$benefitBalance
     * @return static
     */
    public function setBenefitBalance(FHIRExplanationOfBenefitBenefitBalance ...$benefitBalance): self
    {
        if ([] === $benefitBalance) {
            unset($this->benefitBalance);
            return $this;
        }
        $this->benefitBalance = $benefitBalance;
        return $this;
    }

    /* class_default.php:201 */
    /**
     * @param string|\SimpleXMLElement $element
     * @param null|\OpenEMR\FHIR\Encoding\UnserializeConfig $config
     * @param null|\OpenEMR\FHIR\Versions\R4\Types\FHIRResource\FHIRDomainResource\FHIRExplanationOfBenefit $type
     * @return \OpenEMR\FHIR\Versions\R4\Types\FHIRResource\FHIRDomainResource\FHIRExplanationOfBenefit
     * @throws \Exception
     */
    public static function xmlUnserialize(string|\SimpleXMLElement $element,
                                          null|UnserializeConfig $config = null,
                                          null|ResourceTypeInterface $type = null): self
    {
        if (null === $type) {
            $type = new static();
        } else if (!($type instanceof FHIRExplanationOfBenefit)) {
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
                $type->setStatus(FHIRExplanationOfBenefitStatus::xmlUnserialize($ce, $config));
            } else if (self::FIELD_TYPE === $cen) {
                $type->setType(FHIRCodeableConcept::xmlUnserialize($ce, $config));
            } else if (self::FIELD_SUB_TYPE === $cen) {
                $type->setSubType(FHIRCodeableConcept::xmlUnserialize($ce, $config));
            } else if (self::FIELD_USE === $cen) {
                $type->setUse(FHIRUse::xmlUnserialize($ce, $config));
            } else if (self::FIELD_PATIENT === $cen) {
                $type->setPatient(FHIRReference::xmlUnserialize($ce, $config));
            } else if (self::FIELD_BILLABLE_PERIOD === $cen) {
                $type->setBillablePeriod(FHIRPeriod::xmlUnserialize($ce, $config));
            } else if (self::FIELD_CREATED === $cen) {
                $type->setCreated(FHIRDateTime::xmlUnserialize($ce, $config));
            } else if (self::FIELD_ENTERER === $cen) {
                $type->setEnterer(FHIRReference::xmlUnserialize($ce, $config));
            } else if (self::FIELD_INSURER === $cen) {
                $type->setInsurer(FHIRReference::xmlUnserialize($ce, $config));
            } else if (self::FIELD_PROVIDER === $cen) {
                $type->setProvider(FHIRReference::xmlUnserialize($ce, $config));
            } else if (self::FIELD_PRIORITY === $cen) {
                $type->setPriority(FHIRCodeableConcept::xmlUnserialize($ce, $config));
            } else if (self::FIELD_FUNDS_RESERVE_REQUESTED === $cen) {
                $type->setFundsReserveRequested(FHIRCodeableConcept::xmlUnserialize($ce, $config));
            } else if (self::FIELD_FUNDS_RESERVE === $cen) {
                $type->setFundsReserve(FHIRCodeableConcept::xmlUnserialize($ce, $config));
            } else if (self::FIELD_RELATED === $cen) {
                $type->addRelated(FHIRExplanationOfBenefitRelated::xmlUnserialize($ce, $config));
            } else if (self::FIELD_PRESCRIPTION === $cen) {
                $type->setPrescription(FHIRReference::xmlUnserialize($ce, $config));
            } else if (self::FIELD_ORIGINAL_PRESCRIPTION === $cen) {
                $type->setOriginalPrescription(FHIRReference::xmlUnserialize($ce, $config));
            } else if (self::FIELD_PAYEE === $cen) {
                $type->setPayee(FHIRExplanationOfBenefitPayee::xmlUnserialize($ce, $config));
            } else if (self::FIELD_REFERRAL === $cen) {
                $type->setReferral(FHIRReference::xmlUnserialize($ce, $config));
            } else if (self::FIELD_FACILITY === $cen) {
                $type->setFacility(FHIRReference::xmlUnserialize($ce, $config));
            } else if (self::FIELD_CLAIM === $cen) {
                $type->setClaim(FHIRReference::xmlUnserialize($ce, $config));
            } else if (self::FIELD_CLAIM_RESPONSE === $cen) {
                $type->setClaimResponse(FHIRReference::xmlUnserialize($ce, $config));
            } else if (self::FIELD_OUTCOME === $cen) {
                $type->setOutcome(FHIRClaimProcessingCodes::xmlUnserialize($ce, $config));
            } else if (self::FIELD_DISPOSITION === $cen) {
                $type->setDisposition(FHIRString::xmlUnserialize($ce, $config));
            } else if (self::FIELD_PRE_AUTH_REF === $cen) {
                $type->addPreAuthRef(FHIRString::xmlUnserialize($ce, $config));
            } else if (self::FIELD_PRE_AUTH_REF_PERIOD === $cen) {
                $type->addPreAuthRefPeriod(FHIRPeriod::xmlUnserialize($ce, $config));
            } else if (self::FIELD_CARE_TEAM === $cen) {
                $type->addCareTeam(FHIRExplanationOfBenefitCareTeam::xmlUnserialize($ce, $config));
            } else if (self::FIELD_SUPPORTING_INFO === $cen) {
                $type->addSupportingInfo(FHIRExplanationOfBenefitSupportingInfo::xmlUnserialize($ce, $config));
            } else if (self::FIELD_DIAGNOSIS === $cen) {
                $type->addDiagnosis(FHIRExplanationOfBenefitDiagnosis::xmlUnserialize($ce, $config));
            } else if (self::FIELD_PROCEDURE === $cen) {
                $type->addProcedure(FHIRExplanationOfBenefitProcedure::xmlUnserialize($ce, $config));
            } else if (self::FIELD_PRECEDENCE === $cen) {
                $type->setPrecedence(FHIRPositiveInt::xmlUnserialize($ce, $config));
            } else if (self::FIELD_INSURANCE === $cen) {
                $type->addInsurance(FHIRExplanationOfBenefitInsurance::xmlUnserialize($ce, $config));
            } else if (self::FIELD_ACCIDENT === $cen) {
                $type->setAccident(FHIRExplanationOfBenefitAccident::xmlUnserialize($ce, $config));
            } else if (self::FIELD_ITEM === $cen) {
                $type->addItem(FHIRExplanationOfBenefitItem::xmlUnserialize($ce, $config));
            } else if (self::FIELD_ADD_ITEM === $cen) {
                $type->addAddItem(FHIRExplanationOfBenefitAddItem::xmlUnserialize($ce, $config));
            } else if (self::FIELD_ADJUDICATION === $cen) {
                $type->addAdjudication(FHIRExplanationOfBenefitAdjudication::xmlUnserialize($ce, $config));
            } else if (self::FIELD_TOTAL === $cen) {
                $type->addTotal(FHIRExplanationOfBenefitTotal::xmlUnserialize($ce, $config));
            } else if (self::FIELD_PAYMENT === $cen) {
                $type->setPayment(FHIRExplanationOfBenefitPayment::xmlUnserialize($ce, $config));
            } else if (self::FIELD_FORM_CODE === $cen) {
                $type->setFormCode(FHIRCodeableConcept::xmlUnserialize($ce, $config));
            } else if (self::FIELD_FORM === $cen) {
                $type->setForm(FHIRAttachment::xmlUnserialize($ce, $config));
            } else if (self::FIELD_PROCESS_NOTE === $cen) {
                $type->addProcessNote(FHIRExplanationOfBenefitProcessNote::xmlUnserialize($ce, $config));
            } else if (self::FIELD_BENEFIT_PERIOD === $cen) {
                $type->setBenefitPeriod(FHIRPeriod::xmlUnserialize($ce, $config));
            } else if (self::FIELD_BENEFIT_BALANCE === $cen) {
                $type->addBenefitBalance(FHIRExplanationOfBenefitBenefitBalance::xmlUnserialize($ce, $config));
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
        if (isset($attributes[self::FIELD_USE])) {
            if (isset($type->use)) {
                $type->use->setValue((string)$attributes[self::FIELD_USE]);
            } else {
                $type->setUse((string)$attributes[self::FIELD_USE]);
            }
            $type->_setXMLFieldValueLocation(self::FIELD_USE, ValueXMLLocationEnum::PARENT_ATTRIBUTE);
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
        if (isset($attributes[self::FIELD_PRECEDENCE])) {
            if (isset($type->precedence)) {
                $type->precedence->setValue((string)$attributes[self::FIELD_PRECEDENCE]);
            } else {
                $type->setPrecedence((string)$attributes[self::FIELD_PRECEDENCE]);
            }
            $type->_setXMLFieldValueLocation(self::FIELD_PRECEDENCE, ValueXMLLocationEnum::PARENT_ATTRIBUTE);
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
            $xw->openRootNode('ExplanationOfBenefit', $this->_getSourceXMLNS());
        }
        if (isset($this->status) && ValueXMLLocationEnum::PARENT_ATTRIBUTE === $this->_valueXMLLocations[self::FIELD_STATUS]) {
            $xw->writeAttribute(self::FIELD_STATUS, $this->status->_getValueAsString());
        }
        if (isset($this->use) && ValueXMLLocationEnum::PARENT_ATTRIBUTE === $this->_valueXMLLocations[self::FIELD_USE]) {
            $xw->writeAttribute(self::FIELD_USE, $this->use->_getValueAsString());
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
        if (isset($this->precedence) && ValueXMLLocationEnum::PARENT_ATTRIBUTE === $this->_valueXMLLocations[self::FIELD_PRECEDENCE]) {
            $xw->writeAttribute(self::FIELD_PRECEDENCE, $this->precedence->_getValueAsString());
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
        if (isset($this->type)) {
            $xw->startElement(self::FIELD_TYPE);
            $this->type->xmlSerialize($xw, $config);
            $xw->endElement();
        }
        if (isset($this->subType)) {
            $xw->startElement(self::FIELD_SUB_TYPE);
            $this->subType->xmlSerialize($xw, $config);
            $xw->endElement();
        }
        if (isset($this->use)
            && (ValueXMLLocationEnum::PARENT_ATTRIBUTE !== $this->_valueXMLLocations[self::FIELD_USE]
                || $this->use->_nonValueFieldDefined())) {
            $xw->startElement(self::FIELD_USE);
            $this->use->xmlSerialize($xw, $config, $this->_valueXMLLocations[self::FIELD_USE]);
            $xw->endElement();
        }
        if (isset($this->patient)) {
            $xw->startElement(self::FIELD_PATIENT);
            $this->patient->xmlSerialize($xw, $config);
            $xw->endElement();
        }
        if (isset($this->billablePeriod)) {
            $xw->startElement(self::FIELD_BILLABLE_PERIOD);
            $this->billablePeriod->xmlSerialize($xw, $config);
            $xw->endElement();
        }
        if (isset($this->created)
            && (ValueXMLLocationEnum::PARENT_ATTRIBUTE !== $this->_valueXMLLocations[self::FIELD_CREATED]
                || $this->created->_nonValueFieldDefined())) {
            $xw->startElement(self::FIELD_CREATED);
            $this->created->xmlSerialize($xw, $config, $this->_valueXMLLocations[self::FIELD_CREATED]);
            $xw->endElement();
        }
        if (isset($this->enterer)) {
            $xw->startElement(self::FIELD_ENTERER);
            $this->enterer->xmlSerialize($xw, $config);
            $xw->endElement();
        }
        if (isset($this->insurer)) {
            $xw->startElement(self::FIELD_INSURER);
            $this->insurer->xmlSerialize($xw, $config);
            $xw->endElement();
        }
        if (isset($this->provider)) {
            $xw->startElement(self::FIELD_PROVIDER);
            $this->provider->xmlSerialize($xw, $config);
            $xw->endElement();
        }
        if (isset($this->priority)) {
            $xw->startElement(self::FIELD_PRIORITY);
            $this->priority->xmlSerialize($xw, $config);
            $xw->endElement();
        }
        if (isset($this->fundsReserveRequested)) {
            $xw->startElement(self::FIELD_FUNDS_RESERVE_REQUESTED);
            $this->fundsReserveRequested->xmlSerialize($xw, $config);
            $xw->endElement();
        }
        if (isset($this->fundsReserve)) {
            $xw->startElement(self::FIELD_FUNDS_RESERVE);
            $this->fundsReserve->xmlSerialize($xw, $config);
            $xw->endElement();
        }
        if (isset($this->related)) {
            foreach ($this->related as $v) {
                $xw->startElement(self::FIELD_RELATED);
                $v->xmlSerialize($xw, $config);
                $xw->endElement();
            }
        }
        if (isset($this->prescription)) {
            $xw->startElement(self::FIELD_PRESCRIPTION);
            $this->prescription->xmlSerialize($xw, $config);
            $xw->endElement();
        }
        if (isset($this->originalPrescription)) {
            $xw->startElement(self::FIELD_ORIGINAL_PRESCRIPTION);
            $this->originalPrescription->xmlSerialize($xw, $config);
            $xw->endElement();
        }
        if (isset($this->payee)) {
            $xw->startElement(self::FIELD_PAYEE);
            $this->payee->xmlSerialize($xw, $config);
            $xw->endElement();
        }
        if (isset($this->referral)) {
            $xw->startElement(self::FIELD_REFERRAL);
            $this->referral->xmlSerialize($xw, $config);
            $xw->endElement();
        }
        if (isset($this->facility)) {
            $xw->startElement(self::FIELD_FACILITY);
            $this->facility->xmlSerialize($xw, $config);
            $xw->endElement();
        }
        if (isset($this->claim)) {
            $xw->startElement(self::FIELD_CLAIM);
            $this->claim->xmlSerialize($xw, $config);
            $xw->endElement();
        }
        if (isset($this->claimResponse)) {
            $xw->startElement(self::FIELD_CLAIM_RESPONSE);
            $this->claimResponse->xmlSerialize($xw, $config);
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
        if (isset($this->preAuthRef) && [] !== $this->preAuthRef) {
            foreach($this->preAuthRef as $v) {
                $xw->startElement(self::FIELD_PRE_AUTH_REF);
                $v->xmlSerialize($xw, $config);
                $xw->endElement();
            }
        }
        if (isset($this->preAuthRefPeriod)) {
            foreach ($this->preAuthRefPeriod as $v) {
                $xw->startElement(self::FIELD_PRE_AUTH_REF_PERIOD);
                $v->xmlSerialize($xw, $config);
                $xw->endElement();
            }
        }
        if (isset($this->careTeam)) {
            foreach ($this->careTeam as $v) {
                $xw->startElement(self::FIELD_CARE_TEAM);
                $v->xmlSerialize($xw, $config);
                $xw->endElement();
            }
        }
        if (isset($this->supportingInfo)) {
            foreach ($this->supportingInfo as $v) {
                $xw->startElement(self::FIELD_SUPPORTING_INFO);
                $v->xmlSerialize($xw, $config);
                $xw->endElement();
            }
        }
        if (isset($this->diagnosis)) {
            foreach ($this->diagnosis as $v) {
                $xw->startElement(self::FIELD_DIAGNOSIS);
                $v->xmlSerialize($xw, $config);
                $xw->endElement();
            }
        }
        if (isset($this->procedure)) {
            foreach ($this->procedure as $v) {
                $xw->startElement(self::FIELD_PROCEDURE);
                $v->xmlSerialize($xw, $config);
                $xw->endElement();
            }
        }
        if (isset($this->precedence)
            && (ValueXMLLocationEnum::PARENT_ATTRIBUTE !== $this->_valueXMLLocations[self::FIELD_PRECEDENCE]
                || $this->precedence->_nonValueFieldDefined())) {
            $xw->startElement(self::FIELD_PRECEDENCE);
            $this->precedence->xmlSerialize($xw, $config, $this->_valueXMLLocations[self::FIELD_PRECEDENCE]);
            $xw->endElement();
        }
        if (isset($this->insurance)) {
            foreach ($this->insurance as $v) {
                $xw->startElement(self::FIELD_INSURANCE);
                $v->xmlSerialize($xw, $config);
                $xw->endElement();
            }
        }
        if (isset($this->accident)) {
            $xw->startElement(self::FIELD_ACCIDENT);
            $this->accident->xmlSerialize($xw, $config);
            $xw->endElement();
        }
        if (isset($this->item)) {
            foreach ($this->item as $v) {
                $xw->startElement(self::FIELD_ITEM);
                $v->xmlSerialize($xw, $config);
                $xw->endElement();
            }
        }
        if (isset($this->addItem)) {
            foreach ($this->addItem as $v) {
                $xw->startElement(self::FIELD_ADD_ITEM);
                $v->xmlSerialize($xw, $config);
                $xw->endElement();
            }
        }
        if (isset($this->adjudication)) {
            foreach ($this->adjudication as $v) {
                $xw->startElement(self::FIELD_ADJUDICATION);
                $v->xmlSerialize($xw, $config);
                $xw->endElement();
            }
        }
        if (isset($this->total)) {
            foreach ($this->total as $v) {
                $xw->startElement(self::FIELD_TOTAL);
                $v->xmlSerialize($xw, $config);
                $xw->endElement();
            }
        }
        if (isset($this->payment)) {
            $xw->startElement(self::FIELD_PAYMENT);
            $this->payment->xmlSerialize($xw, $config);
            $xw->endElement();
        }
        if (isset($this->formCode)) {
            $xw->startElement(self::FIELD_FORM_CODE);
            $this->formCode->xmlSerialize($xw, $config);
            $xw->endElement();
        }
        if (isset($this->form)) {
            $xw->startElement(self::FIELD_FORM);
            $this->form->xmlSerialize($xw, $config);
            $xw->endElement();
        }
        if (isset($this->processNote)) {
            foreach ($this->processNote as $v) {
                $xw->startElement(self::FIELD_PROCESS_NOTE);
                $v->xmlSerialize($xw, $config);
                $xw->endElement();
            }
        }
        if (isset($this->benefitPeriod)) {
            $xw->startElement(self::FIELD_BENEFIT_PERIOD);
            $this->benefitPeriod->xmlSerialize($xw, $config);
            $xw->endElement();
        }
        if (isset($this->benefitBalance)) {
            foreach ($this->benefitBalance as $v) {
                $xw->startElement(self::FIELD_BENEFIT_BALANCE);
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
     * @param null|\OpenEMR\FHIR\Versions\R4\Types\FHIRResource\FHIRDomainResource\FHIRExplanationOfBenefit $type
     * @return \OpenEMR\FHIR\Versions\R4\Types\FHIRResource\FHIRDomainResource\FHIRExplanationOfBenefit
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
        } else if (!($type instanceof FHIRExplanationOfBenefit)) {
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
            $type->setStatus(FHIRExplanationOfBenefitStatus::jsonUnserialize($v, $config));
        }
        if (isset($decoded->type) || property_exists($decoded, self::FIELD_TYPE)) {
            if (is_array($decoded->type)) {
                $type->setType(FHIRCodeableConcept::jsonUnserialize(reset($decoded->type), $config));
            } else {
                $type->setType(FHIRCodeableConcept::jsonUnserialize($decoded->type, $config));
            }
        }
        if (isset($decoded->subType) || property_exists($decoded, self::FIELD_SUB_TYPE)) {
            if (is_array($decoded->subType)) {
                $type->setSubType(FHIRCodeableConcept::jsonUnserialize(reset($decoded->subType), $config));
            } else {
                $type->setSubType(FHIRCodeableConcept::jsonUnserialize($decoded->subType, $config));
            }
        }
        if (isset($decoded->use)
            || isset($decoded->_use)
            || property_exists($decoded, self::FIELD_USE)
            || property_exists($decoded, self::FIELD_USE_EXT)) {
            $v = $decoded->_use ?? new \stdClass();
            $v->value = $decoded->use ?? null;
            $type->setUse(FHIRUse::jsonUnserialize($v, $config));
        }
        if (isset($decoded->patient) || property_exists($decoded, self::FIELD_PATIENT)) {
            if (is_array($decoded->patient)) {
                $type->setPatient(FHIRReference::jsonUnserialize(reset($decoded->patient), $config));
            } else {
                $type->setPatient(FHIRReference::jsonUnserialize($decoded->patient, $config));
            }
        }
        if (isset($decoded->billablePeriod) || property_exists($decoded, self::FIELD_BILLABLE_PERIOD)) {
            if (is_array($decoded->billablePeriod)) {
                $type->setBillablePeriod(FHIRPeriod::jsonUnserialize(reset($decoded->billablePeriod), $config));
            } else {
                $type->setBillablePeriod(FHIRPeriod::jsonUnserialize($decoded->billablePeriod, $config));
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
        if (isset($decoded->enterer) || property_exists($decoded, self::FIELD_ENTERER)) {
            if (is_array($decoded->enterer)) {
                $type->setEnterer(FHIRReference::jsonUnserialize(reset($decoded->enterer), $config));
            } else {
                $type->setEnterer(FHIRReference::jsonUnserialize($decoded->enterer, $config));
            }
        }
        if (isset($decoded->insurer) || property_exists($decoded, self::FIELD_INSURER)) {
            if (is_array($decoded->insurer)) {
                $type->setInsurer(FHIRReference::jsonUnserialize(reset($decoded->insurer), $config));
            } else {
                $type->setInsurer(FHIRReference::jsonUnserialize($decoded->insurer, $config));
            }
        }
        if (isset($decoded->provider) || property_exists($decoded, self::FIELD_PROVIDER)) {
            if (is_array($decoded->provider)) {
                $type->setProvider(FHIRReference::jsonUnserialize(reset($decoded->provider), $config));
            } else {
                $type->setProvider(FHIRReference::jsonUnserialize($decoded->provider, $config));
            }
        }
        if (isset($decoded->priority) || property_exists($decoded, self::FIELD_PRIORITY)) {
            if (is_array($decoded->priority)) {
                $type->setPriority(FHIRCodeableConcept::jsonUnserialize(reset($decoded->priority), $config));
            } else {
                $type->setPriority(FHIRCodeableConcept::jsonUnserialize($decoded->priority, $config));
            }
        }
        if (isset($decoded->fundsReserveRequested) || property_exists($decoded, self::FIELD_FUNDS_RESERVE_REQUESTED)) {
            if (is_array($decoded->fundsReserveRequested)) {
                $type->setFundsReserveRequested(FHIRCodeableConcept::jsonUnserialize(reset($decoded->fundsReserveRequested), $config));
            } else {
                $type->setFundsReserveRequested(FHIRCodeableConcept::jsonUnserialize($decoded->fundsReserveRequested, $config));
            }
        }
        if (isset($decoded->fundsReserve) || property_exists($decoded, self::FIELD_FUNDS_RESERVE)) {
            if (is_array($decoded->fundsReserve)) {
                $type->setFundsReserve(FHIRCodeableConcept::jsonUnserialize(reset($decoded->fundsReserve), $config));
            } else {
                $type->setFundsReserve(FHIRCodeableConcept::jsonUnserialize($decoded->fundsReserve, $config));
            }
        }
        if (isset($decoded->related) || property_exists($decoded, self::FIELD_RELATED)) {
            if (is_object($decoded->related)) {
                $vals = [$decoded->related];
                $type->_setJSONFieldElideSingletonArray(self::FIELD_RELATED, true);
            } else {
                $vals = $decoded->related;
            }
            foreach($vals as $v) {
                $type->addRelated(FHIRExplanationOfBenefitRelated::jsonUnserialize($v, $config));
            }
        }
        if (isset($decoded->prescription) || property_exists($decoded, self::FIELD_PRESCRIPTION)) {
            if (is_array($decoded->prescription)) {
                $type->setPrescription(FHIRReference::jsonUnserialize(reset($decoded->prescription), $config));
            } else {
                $type->setPrescription(FHIRReference::jsonUnserialize($decoded->prescription, $config));
            }
        }
        if (isset($decoded->originalPrescription) || property_exists($decoded, self::FIELD_ORIGINAL_PRESCRIPTION)) {
            if (is_array($decoded->originalPrescription)) {
                $type->setOriginalPrescription(FHIRReference::jsonUnserialize(reset($decoded->originalPrescription), $config));
            } else {
                $type->setOriginalPrescription(FHIRReference::jsonUnserialize($decoded->originalPrescription, $config));
            }
        }
        if (isset($decoded->payee) || property_exists($decoded, self::FIELD_PAYEE)) {
            if (is_array($decoded->payee)) {
                $type->setPayee(FHIRExplanationOfBenefitPayee::jsonUnserialize(reset($decoded->payee), $config));
            } else {
                $type->setPayee(FHIRExplanationOfBenefitPayee::jsonUnserialize($decoded->payee, $config));
            }
        }
        if (isset($decoded->referral) || property_exists($decoded, self::FIELD_REFERRAL)) {
            if (is_array($decoded->referral)) {
                $type->setReferral(FHIRReference::jsonUnserialize(reset($decoded->referral), $config));
            } else {
                $type->setReferral(FHIRReference::jsonUnserialize($decoded->referral, $config));
            }
        }
        if (isset($decoded->facility) || property_exists($decoded, self::FIELD_FACILITY)) {
            if (is_array($decoded->facility)) {
                $type->setFacility(FHIRReference::jsonUnserialize(reset($decoded->facility), $config));
            } else {
                $type->setFacility(FHIRReference::jsonUnserialize($decoded->facility, $config));
            }
        }
        if (isset($decoded->claim) || property_exists($decoded, self::FIELD_CLAIM)) {
            if (is_array($decoded->claim)) {
                $type->setClaim(FHIRReference::jsonUnserialize(reset($decoded->claim), $config));
            } else {
                $type->setClaim(FHIRReference::jsonUnserialize($decoded->claim, $config));
            }
        }
        if (isset($decoded->claimResponse) || property_exists($decoded, self::FIELD_CLAIM_RESPONSE)) {
            if (is_array($decoded->claimResponse)) {
                $type->setClaimResponse(FHIRReference::jsonUnserialize(reset($decoded->claimResponse), $config));
            } else {
                $type->setClaimResponse(FHIRReference::jsonUnserialize($decoded->claimResponse, $config));
            }
        }
        if (isset($decoded->outcome)
            || isset($decoded->_outcome)
            || property_exists($decoded, self::FIELD_OUTCOME)
            || property_exists($decoded, self::FIELD_OUTCOME_EXT)) {
            $v = $decoded->_outcome ?? new \stdClass();
            $v->value = $decoded->outcome ?? null;
            $type->setOutcome(FHIRClaimProcessingCodes::jsonUnserialize($v, $config));
        }
        if (isset($decoded->disposition)
            || isset($decoded->_disposition)
            || property_exists($decoded, self::FIELD_DISPOSITION)
            || property_exists($decoded, self::FIELD_DISPOSITION_EXT)) {
            $v = $decoded->_disposition ?? new \stdClass();
            $v->value = $decoded->disposition ?? null;
            $type->setDisposition(FHIRString::jsonUnserialize($v, $config));
        }
        if (isset($decoded->preAuthRef)
            || isset($decoded->_preAuthRef)
            || property_exists($decoded, self::FIELD_PRE_AUTH_REF)
            || property_exists($decoded, self::FIELD_PRE_AUTH_REF_EXT)) {
            $vals = (array)($decoded->preAuthRef ?? []);
            $exts = (array)($decoded->_preAuthRef ?? []);
            $valCnt = count($vals);
            $extCnt = count($exts);
            if ($extCnt > $valCnt) {
                $valCnt = $extCnt;
            }
            for ($i = 0; $i < $valCnt; $i++) {
                $v = $exts[$i] ?? new \stdClass();
                $v->value = $vals[$i] ?? null;
                $type->addPreAuthRef(FHIRString::jsonUnserialize($v, $config));
            }
        }
        if (isset($decoded->preAuthRefPeriod) || property_exists($decoded, self::FIELD_PRE_AUTH_REF_PERIOD)) {
            if (is_object($decoded->preAuthRefPeriod)) {
                $vals = [$decoded->preAuthRefPeriod];
                $type->_setJSONFieldElideSingletonArray(self::FIELD_PRE_AUTH_REF_PERIOD, true);
            } else {
                $vals = $decoded->preAuthRefPeriod;
            }
            foreach($vals as $v) {
                $type->addPreAuthRefPeriod(FHIRPeriod::jsonUnserialize($v, $config));
            }
        }
        if (isset($decoded->careTeam) || property_exists($decoded, self::FIELD_CARE_TEAM)) {
            if (is_object($decoded->careTeam)) {
                $vals = [$decoded->careTeam];
                $type->_setJSONFieldElideSingletonArray(self::FIELD_CARE_TEAM, true);
            } else {
                $vals = $decoded->careTeam;
            }
            foreach($vals as $v) {
                $type->addCareTeam(FHIRExplanationOfBenefitCareTeam::jsonUnserialize($v, $config));
            }
        }
        if (isset($decoded->supportingInfo) || property_exists($decoded, self::FIELD_SUPPORTING_INFO)) {
            if (is_object($decoded->supportingInfo)) {
                $vals = [$decoded->supportingInfo];
                $type->_setJSONFieldElideSingletonArray(self::FIELD_SUPPORTING_INFO, true);
            } else {
                $vals = $decoded->supportingInfo;
            }
            foreach($vals as $v) {
                $type->addSupportingInfo(FHIRExplanationOfBenefitSupportingInfo::jsonUnserialize($v, $config));
            }
        }
        if (isset($decoded->diagnosis) || property_exists($decoded, self::FIELD_DIAGNOSIS)) {
            if (is_object($decoded->diagnosis)) {
                $vals = [$decoded->diagnosis];
                $type->_setJSONFieldElideSingletonArray(self::FIELD_DIAGNOSIS, true);
            } else {
                $vals = $decoded->diagnosis;
            }
            foreach($vals as $v) {
                $type->addDiagnosis(FHIRExplanationOfBenefitDiagnosis::jsonUnserialize($v, $config));
            }
        }
        if (isset($decoded->procedure) || property_exists($decoded, self::FIELD_PROCEDURE)) {
            if (is_object($decoded->procedure)) {
                $vals = [$decoded->procedure];
                $type->_setJSONFieldElideSingletonArray(self::FIELD_PROCEDURE, true);
            } else {
                $vals = $decoded->procedure;
            }
            foreach($vals as $v) {
                $type->addProcedure(FHIRExplanationOfBenefitProcedure::jsonUnserialize($v, $config));
            }
        }
        if (isset($decoded->precedence)
            || isset($decoded->_precedence)
            || property_exists($decoded, self::FIELD_PRECEDENCE)
            || property_exists($decoded, self::FIELD_PRECEDENCE_EXT)) {
            $v = $decoded->_precedence ?? new \stdClass();
            $v->value = $decoded->precedence ?? null;
            $type->setPrecedence(FHIRPositiveInt::jsonUnserialize($v, $config));
        }
        if (isset($decoded->insurance) || property_exists($decoded, self::FIELD_INSURANCE)) {
            if (is_object($decoded->insurance)) {
                $vals = [$decoded->insurance];
                $type->_setJSONFieldElideSingletonArray(self::FIELD_INSURANCE, true);
            } else {
                $vals = $decoded->insurance;
            }
            foreach($vals as $v) {
                $type->addInsurance(FHIRExplanationOfBenefitInsurance::jsonUnserialize($v, $config));
            }
        }
        if (isset($decoded->accident) || property_exists($decoded, self::FIELD_ACCIDENT)) {
            if (is_array($decoded->accident)) {
                $type->setAccident(FHIRExplanationOfBenefitAccident::jsonUnserialize(reset($decoded->accident), $config));
            } else {
                $type->setAccident(FHIRExplanationOfBenefitAccident::jsonUnserialize($decoded->accident, $config));
            }
        }
        if (isset($decoded->item) || property_exists($decoded, self::FIELD_ITEM)) {
            if (is_object($decoded->item)) {
                $vals = [$decoded->item];
                $type->_setJSONFieldElideSingletonArray(self::FIELD_ITEM, true);
            } else {
                $vals = $decoded->item;
            }
            foreach($vals as $v) {
                $type->addItem(FHIRExplanationOfBenefitItem::jsonUnserialize($v, $config));
            }
        }
        if (isset($decoded->addItem) || property_exists($decoded, self::FIELD_ADD_ITEM)) {
            if (is_object($decoded->addItem)) {
                $vals = [$decoded->addItem];
                $type->_setJSONFieldElideSingletonArray(self::FIELD_ADD_ITEM, true);
            } else {
                $vals = $decoded->addItem;
            }
            foreach($vals as $v) {
                $type->addAddItem(FHIRExplanationOfBenefitAddItem::jsonUnserialize($v, $config));
            }
        }
        if (isset($decoded->adjudication) || property_exists($decoded, self::FIELD_ADJUDICATION)) {
            if (is_object($decoded->adjudication)) {
                $vals = [$decoded->adjudication];
                $type->_setJSONFieldElideSingletonArray(self::FIELD_ADJUDICATION, true);
            } else {
                $vals = $decoded->adjudication;
            }
            foreach($vals as $v) {
                $type->addAdjudication(FHIRExplanationOfBenefitAdjudication::jsonUnserialize($v, $config));
            }
        }
        if (isset($decoded->total) || property_exists($decoded, self::FIELD_TOTAL)) {
            if (is_object($decoded->total)) {
                $vals = [$decoded->total];
                $type->_setJSONFieldElideSingletonArray(self::FIELD_TOTAL, true);
            } else {
                $vals = $decoded->total;
            }
            foreach($vals as $v) {
                $type->addTotal(FHIRExplanationOfBenefitTotal::jsonUnserialize($v, $config));
            }
        }
        if (isset($decoded->payment) || property_exists($decoded, self::FIELD_PAYMENT)) {
            if (is_array($decoded->payment)) {
                $type->setPayment(FHIRExplanationOfBenefitPayment::jsonUnserialize(reset($decoded->payment), $config));
            } else {
                $type->setPayment(FHIRExplanationOfBenefitPayment::jsonUnserialize($decoded->payment, $config));
            }
        }
        if (isset($decoded->formCode) || property_exists($decoded, self::FIELD_FORM_CODE)) {
            if (is_array($decoded->formCode)) {
                $type->setFormCode(FHIRCodeableConcept::jsonUnserialize(reset($decoded->formCode), $config));
            } else {
                $type->setFormCode(FHIRCodeableConcept::jsonUnserialize($decoded->formCode, $config));
            }
        }
        if (isset($decoded->form) || property_exists($decoded, self::FIELD_FORM)) {
            if (is_array($decoded->form)) {
                $type->setForm(FHIRAttachment::jsonUnserialize(reset($decoded->form), $config));
            } else {
                $type->setForm(FHIRAttachment::jsonUnserialize($decoded->form, $config));
            }
        }
        if (isset($decoded->processNote) || property_exists($decoded, self::FIELD_PROCESS_NOTE)) {
            if (is_object($decoded->processNote)) {
                $vals = [$decoded->processNote];
                $type->_setJSONFieldElideSingletonArray(self::FIELD_PROCESS_NOTE, true);
            } else {
                $vals = $decoded->processNote;
            }
            foreach($vals as $v) {
                $type->addProcessNote(FHIRExplanationOfBenefitProcessNote::jsonUnserialize($v, $config));
            }
        }
        if (isset($decoded->benefitPeriod) || property_exists($decoded, self::FIELD_BENEFIT_PERIOD)) {
            if (is_array($decoded->benefitPeriod)) {
                $type->setBenefitPeriod(FHIRPeriod::jsonUnserialize(reset($decoded->benefitPeriod), $config));
            } else {
                $type->setBenefitPeriod(FHIRPeriod::jsonUnserialize($decoded->benefitPeriod, $config));
            }
        }
        if (isset($decoded->benefitBalance) || property_exists($decoded, self::FIELD_BENEFIT_BALANCE)) {
            if (is_object($decoded->benefitBalance)) {
                $vals = [$decoded->benefitBalance];
                $type->_setJSONFieldElideSingletonArray(self::FIELD_BENEFIT_BALANCE, true);
            } else {
                $vals = $decoded->benefitBalance;
            }
            foreach($vals as $v) {
                $type->addBenefitBalance(FHIRExplanationOfBenefitBenefitBalance::jsonUnserialize($v, $config));
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
        if (isset($this->type)) {
            $out->type = $this->type;
        }
        if (isset($this->subType)) {
            $out->subType = $this->subType;
        }
        if (isset($this->use)) {
            if (null !== ($val = $this->use->getValue())) {
                $out->use = $val;
            }
            if ($this->use->_nonValueFieldDefined()) {
                $ext = $this->use->jsonSerialize();
                unset($ext->value);
                $out->_use = $ext;
            }
        }
        if (isset($this->patient)) {
            $out->patient = $this->patient;
        }
        if (isset($this->billablePeriod)) {
            $out->billablePeriod = $this->billablePeriod;
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
        if (isset($this->enterer)) {
            $out->enterer = $this->enterer;
        }
        if (isset($this->insurer)) {
            $out->insurer = $this->insurer;
        }
        if (isset($this->provider)) {
            $out->provider = $this->provider;
        }
        if (isset($this->priority)) {
            $out->priority = $this->priority;
        }
        if (isset($this->fundsReserveRequested)) {
            $out->fundsReserveRequested = $this->fundsReserveRequested;
        }
        if (isset($this->fundsReserve)) {
            $out->fundsReserve = $this->fundsReserve;
        }
        if (isset($this->related) && [] !== $this->related) {
            if ($this->_getJSONFieldElideSingletonArray(self::FIELD_RELATED) && 1 === count($this->related)) {
                $out->related = $this->related[0];
            } else {
                $out->related = $this->related;
            }
        }
        if (isset($this->prescription)) {
            $out->prescription = $this->prescription;
        }
        if (isset($this->originalPrescription)) {
            $out->originalPrescription = $this->originalPrescription;
        }
        if (isset($this->payee)) {
            $out->payee = $this->payee;
        }
        if (isset($this->referral)) {
            $out->referral = $this->referral;
        }
        if (isset($this->facility)) {
            $out->facility = $this->facility;
        }
        if (isset($this->claim)) {
            $out->claim = $this->claim;
        }
        if (isset($this->claimResponse)) {
            $out->claimResponse = $this->claimResponse;
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
        if (isset($this->preAuthRef) && [] !== $this->preAuthRef) {
            $vals = [];
            $exts = [];
            $hasVals = false;
            $hasExts = false;
            foreach ($this->preAuthRef as $v) {
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
                $out->preAuthRef = $vals;
            }
            if ($hasExts) {
                $out->_preAuthRef = $exts;
            }
        }
        if (isset($this->preAuthRefPeriod) && [] !== $this->preAuthRefPeriod) {
            if ($this->_getJSONFieldElideSingletonArray(self::FIELD_PRE_AUTH_REF_PERIOD) && 1 === count($this->preAuthRefPeriod)) {
                $out->preAuthRefPeriod = $this->preAuthRefPeriod[0];
            } else {
                $out->preAuthRefPeriod = $this->preAuthRefPeriod;
            }
        }
        if (isset($this->careTeam) && [] !== $this->careTeam) {
            if ($this->_getJSONFieldElideSingletonArray(self::FIELD_CARE_TEAM) && 1 === count($this->careTeam)) {
                $out->careTeam = $this->careTeam[0];
            } else {
                $out->careTeam = $this->careTeam;
            }
        }
        if (isset($this->supportingInfo) && [] !== $this->supportingInfo) {
            if ($this->_getJSONFieldElideSingletonArray(self::FIELD_SUPPORTING_INFO) && 1 === count($this->supportingInfo)) {
                $out->supportingInfo = $this->supportingInfo[0];
            } else {
                $out->supportingInfo = $this->supportingInfo;
            }
        }
        if (isset($this->diagnosis) && [] !== $this->diagnosis) {
            if ($this->_getJSONFieldElideSingletonArray(self::FIELD_DIAGNOSIS) && 1 === count($this->diagnosis)) {
                $out->diagnosis = $this->diagnosis[0];
            } else {
                $out->diagnosis = $this->diagnosis;
            }
        }
        if (isset($this->procedure) && [] !== $this->procedure) {
            if ($this->_getJSONFieldElideSingletonArray(self::FIELD_PROCEDURE) && 1 === count($this->procedure)) {
                $out->procedure = $this->procedure[0];
            } else {
                $out->procedure = $this->procedure;
            }
        }
        if (isset($this->precedence)) {
            if (null !== ($val = $this->precedence->getValue())) {
                $out->precedence = $val;
            }
            if ($this->precedence->_nonValueFieldDefined()) {
                $ext = $this->precedence->jsonSerialize();
                unset($ext->value);
                $out->_precedence = $ext;
            }
        }
        if (isset($this->insurance) && [] !== $this->insurance) {
            if ($this->_getJSONFieldElideSingletonArray(self::FIELD_INSURANCE) && 1 === count($this->insurance)) {
                $out->insurance = $this->insurance[0];
            } else {
                $out->insurance = $this->insurance;
            }
        }
        if (isset($this->accident)) {
            $out->accident = $this->accident;
        }
        if (isset($this->item) && [] !== $this->item) {
            if ($this->_getJSONFieldElideSingletonArray(self::FIELD_ITEM) && 1 === count($this->item)) {
                $out->item = $this->item[0];
            } else {
                $out->item = $this->item;
            }
        }
        if (isset($this->addItem) && [] !== $this->addItem) {
            if ($this->_getJSONFieldElideSingletonArray(self::FIELD_ADD_ITEM) && 1 === count($this->addItem)) {
                $out->addItem = $this->addItem[0];
            } else {
                $out->addItem = $this->addItem;
            }
        }
        if (isset($this->adjudication) && [] !== $this->adjudication) {
            if ($this->_getJSONFieldElideSingletonArray(self::FIELD_ADJUDICATION) && 1 === count($this->adjudication)) {
                $out->adjudication = $this->adjudication[0];
            } else {
                $out->adjudication = $this->adjudication;
            }
        }
        if (isset($this->total) && [] !== $this->total) {
            if ($this->_getJSONFieldElideSingletonArray(self::FIELD_TOTAL) && 1 === count($this->total)) {
                $out->total = $this->total[0];
            } else {
                $out->total = $this->total;
            }
        }
        if (isset($this->payment)) {
            $out->payment = $this->payment;
        }
        if (isset($this->formCode)) {
            $out->formCode = $this->formCode;
        }
        if (isset($this->form)) {
            $out->form = $this->form;
        }
        if (isset($this->processNote) && [] !== $this->processNote) {
            if ($this->_getJSONFieldElideSingletonArray(self::FIELD_PROCESS_NOTE) && 1 === count($this->processNote)) {
                $out->processNote = $this->processNote[0];
            } else {
                $out->processNote = $this->processNote;
            }
        }
        if (isset($this->benefitPeriod)) {
            $out->benefitPeriod = $this->benefitPeriod;
        }
        if (isset($this->benefitBalance) && [] !== $this->benefitBalance) {
            if ($this->_getJSONFieldElideSingletonArray(self::FIELD_BENEFIT_BALANCE) && 1 === count($this->benefitBalance)) {
                $out->benefitBalance = $this->benefitBalance[0];
            } else {
                $out->benefitBalance = $this->benefitBalance;
            }
        }
        $out->resourceType = $this->_getResourceType();
        return $out;
    }
}
