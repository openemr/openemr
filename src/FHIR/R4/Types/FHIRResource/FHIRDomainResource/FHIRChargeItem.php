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
use OpenEMR\FHIR\Versions\R4\Types\FHIRCanonicalPrimitive;
use OpenEMR\FHIR\Versions\R4\Types\FHIRCodePrimitive;
use OpenEMR\FHIR\Versions\R4\Types\FHIRCodePrimitive\FHIRChargeItemStatusList;
use OpenEMR\FHIR\Versions\R4\Types\FHIRDateTimePrimitive;
use OpenEMR\FHIR\Versions\R4\Types\FHIRDecimalPrimitive;
use OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRAnnotation;
use OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRChargeItem\FHIRChargeItemPerformer;
use OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRTiming;
use OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRCanonical;
use OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRChargeItemStatus;
use OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRCode;
use OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRCodeableConcept;
use OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRDateTime;
use OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRDecimal;
use OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRExtension;
use OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRId;
use OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRIdentifier;
use OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRMeta;
use OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRMoney;
use OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRNarrative;
use OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRPeriod;
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
 * The resource ChargeItem describes the provision of healthcare provider products
 * for a certain patient, therefore referring not only to the product, but
 * containing in addition details of the provision, like date, time, amounts and
 * participating organizations and persons. Main Usage of the ChargeItem is to
 * enable the billing process and internal cost allocation.
 * If the element is present, it must have either a \@value, an \@id, or extensions
 */
#[\Attribute(\Attribute::TARGET_PROPERTY)]
class FHIRChargeItem extends FHIRDomainResource implements VersionContainedTypeInterface
{
    use TypeValidationsTrait,
        JSONSerializationOptionsTrait,
        XMLSerializationOptionsTrait;

    // name of FHIR type this class describes
    public const FHIR_TYPE_NAME = VersionConstants::TYPE_NAME_CHARGE_ITEM;

    /* class_default.php:56 */
    public const FIELD_IDENTIFIER = 'identifier';
    public const FIELD_DEFINITION_URI = 'definitionUri';
    public const FIELD_DEFINITION_URI_EXT = '_definitionUri';
    public const FIELD_DEFINITION_CANONICAL = 'definitionCanonical';
    public const FIELD_DEFINITION_CANONICAL_EXT = '_definitionCanonical';
    public const FIELD_STATUS = 'status';
    public const FIELD_STATUS_EXT = '_status';
    public const FIELD_PART_OF = 'partOf';
    public const FIELD_CODE = 'code';
    public const FIELD_SUBJECT = 'subject';
    public const FIELD_CONTEXT = 'context';
    public const FIELD_OCCURRENCE_DATE_TIME = 'occurrenceDateTime';
    public const FIELD_OCCURRENCE_DATE_TIME_EXT = '_occurrenceDateTime';
    public const FIELD_OCCURRENCE_PERIOD = 'occurrencePeriod';
    public const FIELD_OCCURRENCE_TIMING = 'occurrenceTiming';
    public const FIELD_PERFORMER = 'performer';
    public const FIELD_PERFORMING_ORGANIZATION = 'performingOrganization';
    public const FIELD_REQUESTING_ORGANIZATION = 'requestingOrganization';
    public const FIELD_COST_CENTER = 'costCenter';
    public const FIELD_QUANTITY = 'quantity';
    public const FIELD_BODYSITE = 'bodysite';
    public const FIELD_FACTOR_OVERRIDE = 'factorOverride';
    public const FIELD_FACTOR_OVERRIDE_EXT = '_factorOverride';
    public const FIELD_PRICE_OVERRIDE = 'priceOverride';
    public const FIELD_OVERRIDE_REASON = 'overrideReason';
    public const FIELD_OVERRIDE_REASON_EXT = '_overrideReason';
    public const FIELD_ENTERER = 'enterer';
    public const FIELD_ENTERED_DATE = 'enteredDate';
    public const FIELD_ENTERED_DATE_EXT = '_enteredDate';
    public const FIELD_REASON = 'reason';
    public const FIELD_SERVICE = 'service';
    public const FIELD_PRODUCT_REFERENCE = 'productReference';
    public const FIELD_PRODUCT_CODEABLE_CONCEPT = 'productCodeableConcept';
    public const FIELD_ACCOUNT = 'account';
    public const FIELD_NOTE = 'note';
    public const FIELD_SUPPORTING_INFORMATION = 'supportingInformation';

    /* class_default.php:75 */
    private const _FHIR_VALIDATION_RULES = [
        self::FIELD_STATUS => [
            MinOccursRule::NAME => 1,
        ],
        self::FIELD_CODE => [
            MinOccursRule::NAME => 1,
        ],
        self::FIELD_SUBJECT => [
            MinOccursRule::NAME => 1,
        ],
    ];

    /* class_default.php:96 */
    private array $_valueXMLLocations = [
        self::FIELD_STATUS => ValueXMLLocationEnum::CONTAINER_ATTRIBUTE,
        self::FIELD_OCCURRENCE_DATE_TIME => ValueXMLLocationEnum::CONTAINER_ATTRIBUTE,
        self::FIELD_FACTOR_OVERRIDE => ValueXMLLocationEnum::CONTAINER_ATTRIBUTE,
        self::FIELD_OVERRIDE_REASON => ValueXMLLocationEnum::CONTAINER_ATTRIBUTE,
        self::FIELD_ENTERED_DATE => ValueXMLLocationEnum::CONTAINER_ATTRIBUTE,
    ];

    /* class_default.php:112 */
    /**
     * An identifier - identifies some entity uniquely and unambiguously. Typically
     * this is used for business identifiers.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * Identifiers assigned to this event performer or other systems.
     *
     * @var iterable<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRIdentifier>
     */
    #[FHIRIdentifier]
    protected array $identifier;
    /**
     * String of characters used to identify a name or a resource
     * see http://en.wikipedia.org/wiki/Uniform_resource_identifier
     * If the element is present, it must have either a \@value, an \@id, or extensions
     *
     * References the (external) source of pricing information, rules of application
     * for the code this ChargeItem uses.
     *
     * @var iterable<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRUri>
     */
    #[FHIRUri]
    protected array $definitionUri;
    /**
     * A URI that is a reference to a canonical URL on a FHIR resource
     * see [Canonical References](references.html#canonical)
     * If the element is present, it must have either a \@value, an \@id referenced from
     * the Narrative, or extensions
     *
     * References the source of pricing information, rules of application for the code
     * this ChargeItem uses.
     *
     * @var iterable<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRCanonical>
     */
    #[FHIRCanonical]
    protected array $definitionCanonical;
    /**
     * Codes identifying the lifecycle stage of a ChargeItem.
     * If the element is present, it must have either a \@value, an \@id, or extensions
     *
     * The current state of the ChargeItem.
     *
     * @var \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRChargeItemStatus
     */
    #[FHIRChargeItemStatus]
    protected FHIRChargeItemStatus $status;
    /**
     * A reference from one resource to another.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * ChargeItems can be grouped to larger ChargeItems covering the whole set.
     *
     * @var iterable<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRReference>
     */
    #[FHIRReference]
    protected array $partOf;
    /**
     * A concept that may be defined by a formal reference to a terminology or ontology
     * or may be provided by text.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * A code that identifies the charge, like a billing code.
     *
     * @var \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRCodeableConcept
     */
    #[FHIRCodeableConcept]
    protected FHIRCodeableConcept $code;
    /**
     * A reference from one resource to another.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * The individual or set of individuals the action is being or was performed on.
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
     * The encounter or episode of care that establishes the context for this event.
     *
     * @var \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRReference
     */
    #[FHIRReference]
    protected FHIRReference $context;
    /**
     * A date, date-time or partial date (e.g. just year or year + month). If hours and
     * minutes are specified, a time zone SHALL be populated. The format is a union of
     * the schema types gYear, gYearMonth, date and dateTime. Seconds must be provided
     * due to schema type constraints but may be zero-filled and may be ignored. Dates
     * SHALL be valid dates.
     * If the element is present, it must have either a \@value, an \@id, or extensions
     *
     * Date/time(s) or duration when the charged service was applied. (choose any one
     * of occurrence*, but only one)
     *
     * @var \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRDateTime
     */
    #[FHIRDateTime]
    protected FHIRDateTime $occurrenceDateTime;
    /**
     * A time period defined by a start and end date and optionally time.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * Date/time(s) or duration when the charged service was applied. (choose any one
     * of occurrence*, but only one)
     *
     * @var \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRPeriod
     */
    #[FHIRPeriod]
    protected FHIRPeriod $occurrencePeriod;
    /**
     * Specifies an event that may occur multiple times. Timing schedules are used to
     * record when things are planned, expected or requested to occur. The most common
     * usage is in dosage instructions for medications. They are also used when
     * planning care of various kinds, and may be used for reporting the schedule to
     * which past regular activities were carried out.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * Date/time(s) or duration when the charged service was applied. (choose any one
     * of occurrence*, but only one)
     *
     * @var \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRTiming
     */
    #[FHIRTiming]
    protected FHIRTiming $occurrenceTiming;
    /**
     * The resource ChargeItem describes the provision of healthcare provider products
     * for a certain patient, therefore referring not only to the product, but
     * containing in addition details of the provision, like date, time, amounts and
     * participating organizations and persons. Main Usage of the ChargeItem is to
     * enable the billing process and internal cost allocation.
     *
     * Indicates who or what performed or participated in the charged service.
     *
     * @var iterable<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRChargeItem\FHIRChargeItemPerformer>
     */
    #[FHIRChargeItemPerformer]
    protected array $performer;
    /**
     * A reference from one resource to another.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * The organization requesting the service.
     *
     * @var \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRReference
     */
    #[FHIRReference]
    protected FHIRReference $performingOrganization;
    /**
     * A reference from one resource to another.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * The organization performing the service.
     *
     * @var \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRReference
     */
    #[FHIRReference]
    protected FHIRReference $requestingOrganization;
    /**
     * A reference from one resource to another.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * The financial cost center permits the tracking of charge attribution.
     *
     * @var \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRReference
     */
    #[FHIRReference]
    protected FHIRReference $costCenter;
    /**
     * A measured amount (or an amount that can potentially be measured). Note that
     * measured amounts include amounts that are not precisely quantified, including
     * amounts involving arbitrary units and floating currencies.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * Quantity of which the charge item has been serviced.
     *
     * @var \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRQuantity
     */
    #[FHIRQuantity]
    protected FHIRQuantity $quantity;
    /**
     * A concept that may be defined by a formal reference to a terminology or ontology
     * or may be provided by text.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * The anatomical location where the related service has been applied.
     *
     * @var iterable<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRCodeableConcept>
     */
    #[FHIRCodeableConcept]
    protected array $bodysite;
    /**
     * A rational number with implicit precision
     * Do not use an IEEE type floating point type, instead use something that works
     * like a true decimal, with inbuilt precision (e.g. Java BigInteger)
     * If the element is present, it must have either a \@value, an \@id, or extensions
     *
     * Factor overriding the factor determined by the rules associated with the code.
     *
     * @var \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRDecimal
     */
    #[FHIRDecimal]
    protected FHIRDecimal $factorOverride;
    /**
     * An amount of economic utility in some recognized currency.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * Total price of the charge overriding the list price associated with the code.
     *
     * @var \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRMoney
     */
    #[FHIRMoney]
    protected FHIRMoney $priceOverride;
    /**
     * A sequence of Unicode characters
     * Note that FHIR strings SHALL NOT exceed 1MB in size
     * If the element is present, it must have either a \@value, an \@id, or extensions
     *
     * If the list price or the rule-based factor associated with the code is
     * overridden, this attribute can capture a text to indicate the reason for this
     * action.
     *
     * @var \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRString
     */
    #[FHIRString]
    protected FHIRString $overrideReason;
    /**
     * A reference from one resource to another.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * The device, practitioner, etc. who entered the charge item.
     *
     * @var \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRReference
     */
    #[FHIRReference]
    protected FHIRReference $enterer;
    /**
     * A date, date-time or partial date (e.g. just year or year + month). If hours and
     * minutes are specified, a time zone SHALL be populated. The format is a union of
     * the schema types gYear, gYearMonth, date and dateTime. Seconds must be provided
     * due to schema type constraints but may be zero-filled and may be ignored. Dates
     * SHALL be valid dates.
     * If the element is present, it must have either a \@value, an \@id, or extensions
     *
     * Date the charge item was entered.
     *
     * @var \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRDateTime
     */
    #[FHIRDateTime]
    protected FHIRDateTime $enteredDate;
    /**
     * A concept that may be defined by a formal reference to a terminology or ontology
     * or may be provided by text.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * Describes why the event occurred in coded or textual form.
     *
     * @var iterable<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRCodeableConcept>
     */
    #[FHIRCodeableConcept]
    protected array $reason;
    /**
     * A reference from one resource to another.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * Indicated the rendered service that caused this charge.
     *
     * @var iterable<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRReference>
     */
    #[FHIRReference]
    protected array $service;
    /**
     * A reference from one resource to another.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * Identifies the device, food, drug or other product being charged either by type
     * code or reference to an instance. (choose any one of product*, but only one)
     *
     * @var \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRReference
     */
    #[FHIRReference]
    protected FHIRReference $productReference;
    /**
     * A concept that may be defined by a formal reference to a terminology or ontology
     * or may be provided by text.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * Identifies the device, food, drug or other product being charged either by type
     * code or reference to an instance. (choose any one of product*, but only one)
     *
     * @var \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRCodeableConcept
     */
    #[FHIRCodeableConcept]
    protected FHIRCodeableConcept $productCodeableConcept;
    /**
     * A reference from one resource to another.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * Account into which this ChargeItems belongs.
     *
     * @var iterable<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRReference>
     */
    #[FHIRReference]
    protected array $account;
    /**
     * A text note which also contains information about who made the statement and
     * when.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * Comments made about the event by the performer, subject or other participants.
     *
     * @var iterable<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRAnnotation>
     */
    #[FHIRAnnotation]
    protected array $note;
    /**
     * A reference from one resource to another.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * Further information supporting this charge.
     *
     * @var iterable<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRReference>
     */
    #[FHIRReference]
    protected array $supportingInformation;

    /* constructor.php:61 */
    /**
     * FHIRChargeItem Constructor
     * @param null|string|\OpenEMR\FHIR\Versions\R4\Types\FHIRIdPrimitive|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRId $id
     * @param null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRMeta $meta
     * @param null|string|\OpenEMR\FHIR\Versions\R4\Types\FHIRUriPrimitive|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRUri $implicitRules
     * @param null|string|\OpenEMR\FHIR\Versions\R4\Types\FHIRCodePrimitive|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRCode $language
     * @param null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRNarrative $text
     * @param null|iterable<\OpenEMR\FHIR\Versions\R4\Types\FHIRResourceContainer>|iterable<\OpenEMR\FHIR\Versions\R4\VersionContainedTypeInterface> $contained
     * @param null|iterable<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRExtension> $extension
     * @param null|iterable<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRExtension> $modifierExtension
     * @param null|iterable<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRIdentifier> $identifier
     * @param null|iterable<string>|iterable<\OpenEMR\FHIR\Versions\R4\Types\FHIRUriPrimitive>|iterable<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRUri> $definitionUri
     * @param null|iterable<string>|iterable<\OpenEMR\FHIR\Versions\R4\Types\FHIRCanonicalPrimitive>|iterable<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRCanonical> $definitionCanonical
     * @param null|string|\OpenEMR\FHIR\Versions\R4\Types\FHIRCodePrimitive\FHIRChargeItemStatusList|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRChargeItemStatus $status
     * @param null|iterable<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRReference> $partOf
     * @param null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRCodeableConcept $code
     * @param null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRReference $subject
     * @param null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRReference $context
     * @param null|string|\DateTimeInterface|\OpenEMR\FHIR\Versions\R4\Types\FHIRDateTimePrimitive|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRDateTime $occurrenceDateTime
     * @param null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRPeriod $occurrencePeriod
     * @param null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRTiming $occurrenceTiming
     * @param null|iterable<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRChargeItem\FHIRChargeItemPerformer> $performer
     * @param null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRReference $performingOrganization
     * @param null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRReference $requestingOrganization
     * @param null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRReference $costCenter
     * @param null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRQuantity $quantity
     * @param null|iterable<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRCodeableConcept> $bodysite
     * @param null|string|int|float|\OpenEMR\FHIR\Versions\R4\Types\FHIRDecimalPrimitive|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRDecimal $factorOverride
     * @param null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRMoney $priceOverride
     * @param null|string|\OpenEMR\FHIR\Versions\R4\Types\FHIRStringPrimitive|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRString $overrideReason
     * @param null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRReference $enterer
     * @param null|string|\DateTimeInterface|\OpenEMR\FHIR\Versions\R4\Types\FHIRDateTimePrimitive|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRDateTime $enteredDate
     * @param null|iterable<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRCodeableConcept> $reason
     * @param null|iterable<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRReference> $service
     * @param null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRReference $productReference
     * @param null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRCodeableConcept $productCodeableConcept
     * @param null|iterable<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRReference> $account
     * @param null|iterable<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRAnnotation> $note
     * @param null|iterable<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRReference> $supportingInformation
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
                                null|iterable $definitionUri = null,
                                null|iterable $definitionCanonical = null,
                                null|string|FHIRChargeItemStatusList|FHIRChargeItemStatus $status = null,
                                null|iterable $partOf = null,
                                null|FHIRCodeableConcept $code = null,
                                null|FHIRReference $subject = null,
                                null|FHIRReference $context = null,
                                null|string|\DateTimeInterface|FHIRDateTimePrimitive|FHIRDateTime $occurrenceDateTime = null,
                                null|FHIRPeriod $occurrencePeriod = null,
                                null|FHIRTiming $occurrenceTiming = null,
                                null|iterable $performer = null,
                                null|FHIRReference $performingOrganization = null,
                                null|FHIRReference $requestingOrganization = null,
                                null|FHIRReference $costCenter = null,
                                null|FHIRQuantity $quantity = null,
                                null|iterable $bodysite = null,
                                null|string|int|float|FHIRDecimalPrimitive|FHIRDecimal $factorOverride = null,
                                null|FHIRMoney $priceOverride = null,
                                null|string|FHIRStringPrimitive|FHIRString $overrideReason = null,
                                null|FHIRReference $enterer = null,
                                null|string|\DateTimeInterface|FHIRDateTimePrimitive|FHIRDateTime $enteredDate = null,
                                null|iterable $reason = null,
                                null|iterable $service = null,
                                null|FHIRReference $productReference = null,
                                null|FHIRCodeableConcept $productCodeableConcept = null,
                                null|iterable $account = null,
                                null|iterable $note = null,
                                null|iterable $supportingInformation = null,
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
        if (null !== $definitionUri) {
            $this->setDefinitionUri(...$definitionUri);
        }
        if (null !== $definitionCanonical) {
            $this->setDefinitionCanonical(...$definitionCanonical);
        }
        if (null !== $status) {
            $this->setStatus($status);
        }
        if (null !== $partOf) {
            $this->setPartOf(...$partOf);
        }
        if (null !== $code) {
            $this->setCode($code);
        }
        if (null !== $subject) {
            $this->setSubject($subject);
        }
        if (null !== $context) {
            $this->setContext($context);
        }
        if (null !== $occurrenceDateTime) {
            $this->setOccurrenceDateTime($occurrenceDateTime);
        }
        if (null !== $occurrencePeriod) {
            $this->setOccurrencePeriod($occurrencePeriod);
        }
        if (null !== $occurrenceTiming) {
            $this->setOccurrenceTiming($occurrenceTiming);
        }
        if (null !== $performer) {
            $this->setPerformer(...$performer);
        }
        if (null !== $performingOrganization) {
            $this->setPerformingOrganization($performingOrganization);
        }
        if (null !== $requestingOrganization) {
            $this->setRequestingOrganization($requestingOrganization);
        }
        if (null !== $costCenter) {
            $this->setCostCenter($costCenter);
        }
        if (null !== $quantity) {
            $this->setQuantity($quantity);
        }
        if (null !== $bodysite) {
            $this->setBodysite(...$bodysite);
        }
        if (null !== $factorOverride) {
            $this->setFactorOverride($factorOverride);
        }
        if (null !== $priceOverride) {
            $this->setPriceOverride($priceOverride);
        }
        if (null !== $overrideReason) {
            $this->setOverrideReason($overrideReason);
        }
        if (null !== $enterer) {
            $this->setEnterer($enterer);
        }
        if (null !== $enteredDate) {
            $this->setEnteredDate($enteredDate);
        }
        if (null !== $reason) {
            $this->setReason(...$reason);
        }
        if (null !== $service) {
            $this->setService(...$service);
        }
        if (null !== $productReference) {
            $this->setProductReference($productReference);
        }
        if (null !== $productCodeableConcept) {
            $this->setProductCodeableConcept($productCodeableConcept);
        }
        if (null !== $account) {
            $this->setAccount(...$account);
        }
        if (null !== $note) {
            $this->setNote(...$note);
        }
        if (null !== $supportingInformation) {
            $this->setSupportingInformation(...$supportingInformation);
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
     * Identifiers assigned to this event performer or other systems.
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
     * Identifiers assigned to this event performer or other systems.
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
     * Identifiers assigned to this event performer or other systems.
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
     * String of characters used to identify a name or a resource
     * see http://en.wikipedia.org/wiki/Uniform_resource_identifier
     * If the element is present, it must have either a \@value, an \@id, or extensions
     *
     * References the (external) source of pricing information, rules of application
     * for the code this ChargeItem uses.
     *
     * @return iterable<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRUri>
     */
    public function getDefinitionUri(): array
    {
        return $this->definitionUri ?? [];
    }

    /**
     * @return \ArrayIterator<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRUri>
     */
    public function getDefinitionUriIterator(): iterable
    {
        if (!isset($this->definitionUri)) {
            return new \EmptyIterator();
        }
        return new \ArrayIterator($this->definitionUri);
    }

    /**
     * String of characters used to identify a name or a resource
     * see http://en.wikipedia.org/wiki/Uniform_resource_identifier
     * If the element is present, it must have either a \@value, an \@id, or extensions
     *
     * References the (external) source of pricing information, rules of application
     * for the code this ChargeItem uses.
     *
     * @param string|\OpenEMR\FHIR\Versions\R4\Types\FHIRUriPrimitive|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRUri $definitionUri
     * @return static
     */
    public function addDefinitionUri(string|FHIRUriPrimitive|FHIRUri $definitionUri): self
    {
        if (!($definitionUri instanceof FHIRUri)) {
            $definitionUri = new FHIRUri(value: $definitionUri);
        }
        if (!isset($this->definitionUri)) {
            $this->definitionUri = [];
        }
        $this->definitionUri[] = $definitionUri;
        return $this;
    }

    /**
     * String of characters used to identify a name or a resource
     * see http://en.wikipedia.org/wiki/Uniform_resource_identifier
     * If the element is present, it must have either a \@value, an \@id, or extensions
     *
     * References the (external) source of pricing information, rules of application
     * for the code this ChargeItem uses.
     *
     * @param string|\OpenEMR\FHIR\Versions\R4\Types\FHIRUriPrimitive|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRUri ...$definitionUri
     * @return static
     */
    public function setDefinitionUri(string|FHIRUriPrimitive|FHIRUri ...$definitionUri): self
    {
        if ([] === $definitionUri) {
            unset($this->definitionUri);
            return $this;
        }
        $this->definitionUri = [];
        foreach($definitionUri as $v) {
            if ($v instanceof FHIRUri) {
                $this->definitionUri[] = $v;
            } else {
                $this->definitionUri[] = new FHIRUri(value: $v);
            }
        }
        return $this;
    }

    /**
     * A URI that is a reference to a canonical URL on a FHIR resource
     * see [Canonical References](references.html#canonical)
     * If the element is present, it must have either a \@value, an \@id referenced from
     * the Narrative, or extensions
     *
     * References the source of pricing information, rules of application for the code
     * this ChargeItem uses.
     *
     * @return iterable<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRCanonical>
     */
    public function getDefinitionCanonical(): array
    {
        return $this->definitionCanonical ?? [];
    }

    /**
     * @return \ArrayIterator<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRCanonical>
     */
    public function getDefinitionCanonicalIterator(): iterable
    {
        if (!isset($this->definitionCanonical)) {
            return new \EmptyIterator();
        }
        return new \ArrayIterator($this->definitionCanonical);
    }

    /**
     * A URI that is a reference to a canonical URL on a FHIR resource
     * see [Canonical References](references.html#canonical)
     * If the element is present, it must have either a \@value, an \@id referenced from
     * the Narrative, or extensions
     *
     * References the source of pricing information, rules of application for the code
     * this ChargeItem uses.
     *
     * @param string|\OpenEMR\FHIR\Versions\R4\Types\FHIRCanonicalPrimitive|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRCanonical $definitionCanonical
     * @return static
     */
    public function addDefinitionCanonical(string|FHIRCanonicalPrimitive|FHIRCanonical $definitionCanonical): self
    {
        if (!($definitionCanonical instanceof FHIRCanonical)) {
            $definitionCanonical = new FHIRCanonical(value: $definitionCanonical);
        }
        if (!isset($this->definitionCanonical)) {
            $this->definitionCanonical = [];
        }
        $this->definitionCanonical[] = $definitionCanonical;
        return $this;
    }

    /**
     * A URI that is a reference to a canonical URL on a FHIR resource
     * see [Canonical References](references.html#canonical)
     * If the element is present, it must have either a \@value, an \@id referenced from
     * the Narrative, or extensions
     *
     * References the source of pricing information, rules of application for the code
     * this ChargeItem uses.
     *
     * @param string|\OpenEMR\FHIR\Versions\R4\Types\FHIRCanonicalPrimitive|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRCanonical ...$definitionCanonical
     * @return static
     */
    public function setDefinitionCanonical(string|FHIRCanonicalPrimitive|FHIRCanonical ...$definitionCanonical): self
    {
        if ([] === $definitionCanonical) {
            unset($this->definitionCanonical);
            return $this;
        }
        $this->definitionCanonical = [];
        foreach($definitionCanonical as $v) {
            if ($v instanceof FHIRCanonical) {
                $this->definitionCanonical[] = $v;
            } else {
                $this->definitionCanonical[] = new FHIRCanonical(value: $v);
            }
        }
        return $this;
    }

    /**
     * Codes identifying the lifecycle stage of a ChargeItem.
     * If the element is present, it must have either a \@value, an \@id, or extensions
     *
     * The current state of the ChargeItem.
     *
     * @return null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRChargeItemStatus
     */
    public function getStatus(): null|FHIRChargeItemStatus
    {
        return $this->status ?? null;
    }

    /**
     * Codes identifying the lifecycle stage of a ChargeItem.
     * If the element is present, it must have either a \@value, an \@id, or extensions
     *
     * The current state of the ChargeItem.
     *
     * @param null|string|\OpenEMR\FHIR\Versions\R4\Types\FHIRCodePrimitive\FHIRChargeItemStatusList|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRChargeItemStatus $status
     * @return static
     */
    public function setStatus(null|string|FHIRChargeItemStatusList|FHIRChargeItemStatus $status): self
    {
        if (null === $status) {
            unset($this->status);
            return $this;
        }
        if (!($status instanceof FHIRChargeItemStatus)) {
            $status = new FHIRChargeItemStatus(value: $status);
        }
        $this->status = $status;
        return $this;
    }

    /**
     * A reference from one resource to another.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * ChargeItems can be grouped to larger ChargeItems covering the whole set.
     *
     * @return iterable<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRReference>
     */
    public function getPartOf(): array
    {
        return $this->partOf ?? [];
    }

    /**
     * @return \ArrayIterator<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRReference>
     */
    public function getPartOfIterator(): iterable
    {
        if (!isset($this->partOf)) {
            return new \EmptyIterator();
        }
        return new \ArrayIterator($this->partOf);
    }

    /**
     * A reference from one resource to another.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * ChargeItems can be grouped to larger ChargeItems covering the whole set.
     *
     * @param \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRReference $partOf
     * @return static
     */
    public function addPartOf(FHIRReference $partOf): self
    {
        if (!isset($this->partOf)) {
            $this->partOf = [];
        }
        $this->partOf[] = $partOf;
        return $this;
    }

    /**
     * A reference from one resource to another.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * ChargeItems can be grouped to larger ChargeItems covering the whole set.
     *
     * @param \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRReference ...$partOf
     * @return static
     */
    public function setPartOf(FHIRReference ...$partOf): self
    {
        if ([] === $partOf) {
            unset($this->partOf);
            return $this;
        }
        $this->partOf = $partOf;
        return $this;
    }

    /**
     * A concept that may be defined by a formal reference to a terminology or ontology
     * or may be provided by text.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * A code that identifies the charge, like a billing code.
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
     * A code that identifies the charge, like a billing code.
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
     * A reference from one resource to another.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * The individual or set of individuals the action is being or was performed on.
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
     * The individual or set of individuals the action is being or was performed on.
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
     * The encounter or episode of care that establishes the context for this event.
     *
     * @return null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRReference
     */
    public function getContext(): null|FHIRReference
    {
        return $this->context ?? null;
    }

    /**
     * A reference from one resource to another.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * The encounter or episode of care that establishes the context for this event.
     *
     * @param null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRReference $context
     * @return static
     */
    public function setContext(null|FHIRReference $context): self
    {
        if (null === $context) {
            unset($this->context);
            return $this;
        }
        $this->context = $context;
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
     * Date/time(s) or duration when the charged service was applied. (choose any one
     * of occurrence*, but only one)
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
     * Date/time(s) or duration when the charged service was applied. (choose any one
     * of occurrence*, but only one)
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
     * A time period defined by a start and end date and optionally time.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * Date/time(s) or duration when the charged service was applied. (choose any one
     * of occurrence*, but only one)
     *
     * @return null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRPeriod
     */
    public function getOccurrencePeriod(): null|FHIRPeriod
    {
        return $this->occurrencePeriod ?? null;
    }

    /**
     * A time period defined by a start and end date and optionally time.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * Date/time(s) or duration when the charged service was applied. (choose any one
     * of occurrence*, but only one)
     *
     * @param null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRPeriod $occurrencePeriod
     * @return static
     */
    public function setOccurrencePeriod(null|FHIRPeriod $occurrencePeriod): self
    {
        if (null === $occurrencePeriod) {
            unset($this->occurrencePeriod);
            return $this;
        }
        $this->occurrencePeriod = $occurrencePeriod;
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
     * Date/time(s) or duration when the charged service was applied. (choose any one
     * of occurrence*, but only one)
     *
     * @return null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRTiming
     */
    public function getOccurrenceTiming(): null|FHIRTiming
    {
        return $this->occurrenceTiming ?? null;
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
     * Date/time(s) or duration when the charged service was applied. (choose any one
     * of occurrence*, but only one)
     *
     * @param null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRTiming $occurrenceTiming
     * @return static
     */
    public function setOccurrenceTiming(null|FHIRTiming $occurrenceTiming): self
    {
        if (null === $occurrenceTiming) {
            unset($this->occurrenceTiming);
            return $this;
        }
        $this->occurrenceTiming = $occurrenceTiming;
        return $this;
    }

    /**
     * The resource ChargeItem describes the provision of healthcare provider products
     * for a certain patient, therefore referring not only to the product, but
     * containing in addition details of the provision, like date, time, amounts and
     * participating organizations and persons. Main Usage of the ChargeItem is to
     * enable the billing process and internal cost allocation.
     *
     * Indicates who or what performed or participated in the charged service.
     *
     * @return iterable<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRChargeItem\FHIRChargeItemPerformer>
     */
    public function getPerformer(): array
    {
        return $this->performer ?? [];
    }

    /**
     * @return \ArrayIterator<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRChargeItem\FHIRChargeItemPerformer>
     */
    public function getPerformerIterator(): iterable
    {
        if (!isset($this->performer)) {
            return new \EmptyIterator();
        }
        return new \ArrayIterator($this->performer);
    }

    /**
     * The resource ChargeItem describes the provision of healthcare provider products
     * for a certain patient, therefore referring not only to the product, but
     * containing in addition details of the provision, like date, time, amounts and
     * participating organizations and persons. Main Usage of the ChargeItem is to
     * enable the billing process and internal cost allocation.
     *
     * Indicates who or what performed or participated in the charged service.
     *
     * @param \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRChargeItem\FHIRChargeItemPerformer $performer
     * @return static
     */
    public function addPerformer(FHIRChargeItemPerformer $performer): self
    {
        if (!isset($this->performer)) {
            $this->performer = [];
        }
        $this->performer[] = $performer;
        return $this;
    }

    /**
     * The resource ChargeItem describes the provision of healthcare provider products
     * for a certain patient, therefore referring not only to the product, but
     * containing in addition details of the provision, like date, time, amounts and
     * participating organizations and persons. Main Usage of the ChargeItem is to
     * enable the billing process and internal cost allocation.
     *
     * Indicates who or what performed or participated in the charged service.
     *
     * @param \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRChargeItem\FHIRChargeItemPerformer ...$performer
     * @return static
     */
    public function setPerformer(FHIRChargeItemPerformer ...$performer): self
    {
        if ([] === $performer) {
            unset($this->performer);
            return $this;
        }
        $this->performer = $performer;
        return $this;
    }

    /**
     * A reference from one resource to another.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * The organization requesting the service.
     *
     * @return null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRReference
     */
    public function getPerformingOrganization(): null|FHIRReference
    {
        return $this->performingOrganization ?? null;
    }

    /**
     * A reference from one resource to another.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * The organization requesting the service.
     *
     * @param null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRReference $performingOrganization
     * @return static
     */
    public function setPerformingOrganization(null|FHIRReference $performingOrganization): self
    {
        if (null === $performingOrganization) {
            unset($this->performingOrganization);
            return $this;
        }
        $this->performingOrganization = $performingOrganization;
        return $this;
    }

    /**
     * A reference from one resource to another.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * The organization performing the service.
     *
     * @return null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRReference
     */
    public function getRequestingOrganization(): null|FHIRReference
    {
        return $this->requestingOrganization ?? null;
    }

    /**
     * A reference from one resource to another.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * The organization performing the service.
     *
     * @param null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRReference $requestingOrganization
     * @return static
     */
    public function setRequestingOrganization(null|FHIRReference $requestingOrganization): self
    {
        if (null === $requestingOrganization) {
            unset($this->requestingOrganization);
            return $this;
        }
        $this->requestingOrganization = $requestingOrganization;
        return $this;
    }

    /**
     * A reference from one resource to another.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * The financial cost center permits the tracking of charge attribution.
     *
     * @return null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRReference
     */
    public function getCostCenter(): null|FHIRReference
    {
        return $this->costCenter ?? null;
    }

    /**
     * A reference from one resource to another.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * The financial cost center permits the tracking of charge attribution.
     *
     * @param null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRReference $costCenter
     * @return static
     */
    public function setCostCenter(null|FHIRReference $costCenter): self
    {
        if (null === $costCenter) {
            unset($this->costCenter);
            return $this;
        }
        $this->costCenter = $costCenter;
        return $this;
    }

    /**
     * A measured amount (or an amount that can potentially be measured). Note that
     * measured amounts include amounts that are not precisely quantified, including
     * amounts involving arbitrary units and floating currencies.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * Quantity of which the charge item has been serviced.
     *
     * @return null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRQuantity
     */
    public function getQuantity(): null|FHIRQuantity
    {
        return $this->quantity ?? null;
    }

    /**
     * A measured amount (or an amount that can potentially be measured). Note that
     * measured amounts include amounts that are not precisely quantified, including
     * amounts involving arbitrary units and floating currencies.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * Quantity of which the charge item has been serviced.
     *
     * @param null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRQuantity $quantity
     * @return static
     */
    public function setQuantity(null|FHIRQuantity $quantity): self
    {
        if (null === $quantity) {
            unset($this->quantity);
            return $this;
        }
        $this->quantity = $quantity;
        return $this;
    }

    /**
     * A concept that may be defined by a formal reference to a terminology or ontology
     * or may be provided by text.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * The anatomical location where the related service has been applied.
     *
     * @return iterable<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRCodeableConcept>
     */
    public function getBodysite(): array
    {
        return $this->bodysite ?? [];
    }

    /**
     * @return \ArrayIterator<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRCodeableConcept>
     */
    public function getBodysiteIterator(): iterable
    {
        if (!isset($this->bodysite)) {
            return new \EmptyIterator();
        }
        return new \ArrayIterator($this->bodysite);
    }

    /**
     * A concept that may be defined by a formal reference to a terminology or ontology
     * or may be provided by text.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * The anatomical location where the related service has been applied.
     *
     * @param \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRCodeableConcept $bodysite
     * @return static
     */
    public function addBodysite(FHIRCodeableConcept $bodysite): self
    {
        if (!isset($this->bodysite)) {
            $this->bodysite = [];
        }
        $this->bodysite[] = $bodysite;
        return $this;
    }

    /**
     * A concept that may be defined by a formal reference to a terminology or ontology
     * or may be provided by text.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * The anatomical location where the related service has been applied.
     *
     * @param \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRCodeableConcept ...$bodysite
     * @return static
     */
    public function setBodysite(FHIRCodeableConcept ...$bodysite): self
    {
        if ([] === $bodysite) {
            unset($this->bodysite);
            return $this;
        }
        $this->bodysite = $bodysite;
        return $this;
    }

    /**
     * A rational number with implicit precision
     * Do not use an IEEE type floating point type, instead use something that works
     * like a true decimal, with inbuilt precision (e.g. Java BigInteger)
     * If the element is present, it must have either a \@value, an \@id, or extensions
     *
     * Factor overriding the factor determined by the rules associated with the code.
     *
     * @return null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRDecimal
     */
    public function getFactorOverride(): null|FHIRDecimal
    {
        return $this->factorOverride ?? null;
    }

    /**
     * A rational number with implicit precision
     * Do not use an IEEE type floating point type, instead use something that works
     * like a true decimal, with inbuilt precision (e.g. Java BigInteger)
     * If the element is present, it must have either a \@value, an \@id, or extensions
     *
     * Factor overriding the factor determined by the rules associated with the code.
     *
     * @param null|string|int|float|\OpenEMR\FHIR\Versions\R4\Types\FHIRDecimalPrimitive|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRDecimal $factorOverride
     * @return static
     */
    public function setFactorOverride(null|string|int|float|FHIRDecimalPrimitive|FHIRDecimal $factorOverride): self
    {
        if (null === $factorOverride) {
            unset($this->factorOverride);
            return $this;
        }
        if (!($factorOverride instanceof FHIRDecimal)) {
            $factorOverride = new FHIRDecimal(value: $factorOverride);
        }
        $this->factorOverride = $factorOverride;
        return $this;
    }

    /**
     * An amount of economic utility in some recognized currency.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * Total price of the charge overriding the list price associated with the code.
     *
     * @return null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRMoney
     */
    public function getPriceOverride(): null|FHIRMoney
    {
        return $this->priceOverride ?? null;
    }

    /**
     * An amount of economic utility in some recognized currency.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * Total price of the charge overriding the list price associated with the code.
     *
     * @param null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRMoney $priceOverride
     * @return static
     */
    public function setPriceOverride(null|FHIRMoney $priceOverride): self
    {
        if (null === $priceOverride) {
            unset($this->priceOverride);
            return $this;
        }
        $this->priceOverride = $priceOverride;
        return $this;
    }

    /**
     * A sequence of Unicode characters
     * Note that FHIR strings SHALL NOT exceed 1MB in size
     * If the element is present, it must have either a \@value, an \@id, or extensions
     *
     * If the list price or the rule-based factor associated with the code is
     * overridden, this attribute can capture a text to indicate the reason for this
     * action.
     *
     * @return null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRString
     */
    public function getOverrideReason(): null|FHIRString
    {
        return $this->overrideReason ?? null;
    }

    /**
     * A sequence of Unicode characters
     * Note that FHIR strings SHALL NOT exceed 1MB in size
     * If the element is present, it must have either a \@value, an \@id, or extensions
     *
     * If the list price or the rule-based factor associated with the code is
     * overridden, this attribute can capture a text to indicate the reason for this
     * action.
     *
     * @param null|string|\OpenEMR\FHIR\Versions\R4\Types\FHIRStringPrimitive|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRString $overrideReason
     * @return static
     */
    public function setOverrideReason(null|string|FHIRStringPrimitive|FHIRString $overrideReason): self
    {
        if (null === $overrideReason) {
            unset($this->overrideReason);
            return $this;
        }
        if (!($overrideReason instanceof FHIRString)) {
            $overrideReason = new FHIRString(value: $overrideReason);
        }
        $this->overrideReason = $overrideReason;
        return $this;
    }

    /**
     * A reference from one resource to another.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * The device, practitioner, etc. who entered the charge item.
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
     * The device, practitioner, etc. who entered the charge item.
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
     * A date, date-time or partial date (e.g. just year or year + month). If hours and
     * minutes are specified, a time zone SHALL be populated. The format is a union of
     * the schema types gYear, gYearMonth, date and dateTime. Seconds must be provided
     * due to schema type constraints but may be zero-filled and may be ignored. Dates
     * SHALL be valid dates.
     * If the element is present, it must have either a \@value, an \@id, or extensions
     *
     * Date the charge item was entered.
     *
     * @return null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRDateTime
     */
    public function getEnteredDate(): null|FHIRDateTime
    {
        return $this->enteredDate ?? null;
    }

    /**
     * A date, date-time or partial date (e.g. just year or year + month). If hours and
     * minutes are specified, a time zone SHALL be populated. The format is a union of
     * the schema types gYear, gYearMonth, date and dateTime. Seconds must be provided
     * due to schema type constraints but may be zero-filled and may be ignored. Dates
     * SHALL be valid dates.
     * If the element is present, it must have either a \@value, an \@id, or extensions
     *
     * Date the charge item was entered.
     *
     * @param null|string|\DateTimeInterface|\OpenEMR\FHIR\Versions\R4\Types\FHIRDateTimePrimitive|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRDateTime $enteredDate
     * @return static
     */
    public function setEnteredDate(null|string|\DateTimeInterface|FHIRDateTimePrimitive|FHIRDateTime $enteredDate): self
    {
        if (null === $enteredDate) {
            unset($this->enteredDate);
            return $this;
        }
        if (!($enteredDate instanceof FHIRDateTime)) {
            $enteredDate = new FHIRDateTime(value: $enteredDate);
        }
        $this->enteredDate = $enteredDate;
        return $this;
    }

    /**
     * A concept that may be defined by a formal reference to a terminology or ontology
     * or may be provided by text.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * Describes why the event occurred in coded or textual form.
     *
     * @return iterable<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRCodeableConcept>
     */
    public function getReason(): array
    {
        return $this->reason ?? [];
    }

    /**
     * @return \ArrayIterator<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRCodeableConcept>
     */
    public function getReasonIterator(): iterable
    {
        if (!isset($this->reason)) {
            return new \EmptyIterator();
        }
        return new \ArrayIterator($this->reason);
    }

    /**
     * A concept that may be defined by a formal reference to a terminology or ontology
     * or may be provided by text.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * Describes why the event occurred in coded or textual form.
     *
     * @param \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRCodeableConcept $reason
     * @return static
     */
    public function addReason(FHIRCodeableConcept $reason): self
    {
        if (!isset($this->reason)) {
            $this->reason = [];
        }
        $this->reason[] = $reason;
        return $this;
    }

    /**
     * A concept that may be defined by a formal reference to a terminology or ontology
     * or may be provided by text.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * Describes why the event occurred in coded or textual form.
     *
     * @param \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRCodeableConcept ...$reason
     * @return static
     */
    public function setReason(FHIRCodeableConcept ...$reason): self
    {
        if ([] === $reason) {
            unset($this->reason);
            return $this;
        }
        $this->reason = $reason;
        return $this;
    }

    /**
     * A reference from one resource to another.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * Indicated the rendered service that caused this charge.
     *
     * @return iterable<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRReference>
     */
    public function getService(): array
    {
        return $this->service ?? [];
    }

    /**
     * @return \ArrayIterator<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRReference>
     */
    public function getServiceIterator(): iterable
    {
        if (!isset($this->service)) {
            return new \EmptyIterator();
        }
        return new \ArrayIterator($this->service);
    }

    /**
     * A reference from one resource to another.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * Indicated the rendered service that caused this charge.
     *
     * @param \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRReference $service
     * @return static
     */
    public function addService(FHIRReference $service): self
    {
        if (!isset($this->service)) {
            $this->service = [];
        }
        $this->service[] = $service;
        return $this;
    }

    /**
     * A reference from one resource to another.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * Indicated the rendered service that caused this charge.
     *
     * @param \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRReference ...$service
     * @return static
     */
    public function setService(FHIRReference ...$service): self
    {
        if ([] === $service) {
            unset($this->service);
            return $this;
        }
        $this->service = $service;
        return $this;
    }

    /**
     * A reference from one resource to another.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * Identifies the device, food, drug or other product being charged either by type
     * code or reference to an instance. (choose any one of product*, but only one)
     *
     * @return null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRReference
     */
    public function getProductReference(): null|FHIRReference
    {
        return $this->productReference ?? null;
    }

    /**
     * A reference from one resource to another.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * Identifies the device, food, drug or other product being charged either by type
     * code or reference to an instance. (choose any one of product*, but only one)
     *
     * @param null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRReference $productReference
     * @return static
     */
    public function setProductReference(null|FHIRReference $productReference): self
    {
        if (null === $productReference) {
            unset($this->productReference);
            return $this;
        }
        $this->productReference = $productReference;
        return $this;
    }

    /**
     * A concept that may be defined by a formal reference to a terminology or ontology
     * or may be provided by text.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * Identifies the device, food, drug or other product being charged either by type
     * code or reference to an instance. (choose any one of product*, but only one)
     *
     * @return null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRCodeableConcept
     */
    public function getProductCodeableConcept(): null|FHIRCodeableConcept
    {
        return $this->productCodeableConcept ?? null;
    }

    /**
     * A concept that may be defined by a formal reference to a terminology or ontology
     * or may be provided by text.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * Identifies the device, food, drug or other product being charged either by type
     * code or reference to an instance. (choose any one of product*, but only one)
     *
     * @param null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRCodeableConcept $productCodeableConcept
     * @return static
     */
    public function setProductCodeableConcept(null|FHIRCodeableConcept $productCodeableConcept): self
    {
        if (null === $productCodeableConcept) {
            unset($this->productCodeableConcept);
            return $this;
        }
        $this->productCodeableConcept = $productCodeableConcept;
        return $this;
    }

    /**
     * A reference from one resource to another.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * Account into which this ChargeItems belongs.
     *
     * @return iterable<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRReference>
     */
    public function getAccount(): array
    {
        return $this->account ?? [];
    }

    /**
     * @return \ArrayIterator<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRReference>
     */
    public function getAccountIterator(): iterable
    {
        if (!isset($this->account)) {
            return new \EmptyIterator();
        }
        return new \ArrayIterator($this->account);
    }

    /**
     * A reference from one resource to another.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * Account into which this ChargeItems belongs.
     *
     * @param \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRReference $account
     * @return static
     */
    public function addAccount(FHIRReference $account): self
    {
        if (!isset($this->account)) {
            $this->account = [];
        }
        $this->account[] = $account;
        return $this;
    }

    /**
     * A reference from one resource to another.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * Account into which this ChargeItems belongs.
     *
     * @param \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRReference ...$account
     * @return static
     */
    public function setAccount(FHIRReference ...$account): self
    {
        if ([] === $account) {
            unset($this->account);
            return $this;
        }
        $this->account = $account;
        return $this;
    }

    /**
     * A text note which also contains information about who made the statement and
     * when.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * Comments made about the event by the performer, subject or other participants.
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
     * Comments made about the event by the performer, subject or other participants.
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
     * Comments made about the event by the performer, subject or other participants.
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
     * A reference from one resource to another.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * Further information supporting this charge.
     *
     * @return iterable<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRReference>
     */
    public function getSupportingInformation(): array
    {
        return $this->supportingInformation ?? [];
    }

    /**
     * @return \ArrayIterator<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRReference>
     */
    public function getSupportingInformationIterator(): iterable
    {
        if (!isset($this->supportingInformation)) {
            return new \EmptyIterator();
        }
        return new \ArrayIterator($this->supportingInformation);
    }

    /**
     * A reference from one resource to another.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * Further information supporting this charge.
     *
     * @param \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRReference $supportingInformation
     * @return static
     */
    public function addSupportingInformation(FHIRReference $supportingInformation): self
    {
        if (!isset($this->supportingInformation)) {
            $this->supportingInformation = [];
        }
        $this->supportingInformation[] = $supportingInformation;
        return $this;
    }

    /**
     * A reference from one resource to another.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * Further information supporting this charge.
     *
     * @param \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRReference ...$supportingInformation
     * @return static
     */
    public function setSupportingInformation(FHIRReference ...$supportingInformation): self
    {
        if ([] === $supportingInformation) {
            unset($this->supportingInformation);
            return $this;
        }
        $this->supportingInformation = $supportingInformation;
        return $this;
    }

    /* class_default.php:201 */
    /**
     * @param string|\SimpleXMLElement $element
     * @param null|\OpenEMR\FHIR\Encoding\UnserializeConfig $config
     * @param null|\OpenEMR\FHIR\Versions\R4\Types\FHIRResource\FHIRDomainResource\FHIRChargeItem $type
     * @return \OpenEMR\FHIR\Versions\R4\Types\FHIRResource\FHIRDomainResource\FHIRChargeItem
     * @throws \Exception
     */
    public static function xmlUnserialize(string|\SimpleXMLElement $element,
                                          null|UnserializeConfig $config = null,
                                          null|ResourceTypeInterface $type = null): self
    {
        if (null === $type) {
            $type = new static();
        } else if (!($type instanceof FHIRChargeItem)) {
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
            } else if (self::FIELD_DEFINITION_URI === $cen) {
                $type->addDefinitionUri(FHIRUri::xmlUnserialize($ce, $config));
            } else if (self::FIELD_DEFINITION_CANONICAL === $cen) {
                $type->addDefinitionCanonical(FHIRCanonical::xmlUnserialize($ce, $config));
            } else if (self::FIELD_STATUS === $cen) {
                $type->setStatus(FHIRChargeItemStatus::xmlUnserialize($ce, $config));
            } else if (self::FIELD_PART_OF === $cen) {
                $type->addPartOf(FHIRReference::xmlUnserialize($ce, $config));
            } else if (self::FIELD_CODE === $cen) {
                $type->setCode(FHIRCodeableConcept::xmlUnserialize($ce, $config));
            } else if (self::FIELD_SUBJECT === $cen) {
                $type->setSubject(FHIRReference::xmlUnserialize($ce, $config));
            } else if (self::FIELD_CONTEXT === $cen) {
                $type->setContext(FHIRReference::xmlUnserialize($ce, $config));
            } else if (self::FIELD_OCCURRENCE_DATE_TIME === $cen) {
                $type->setOccurrenceDateTime(FHIRDateTime::xmlUnserialize($ce, $config));
            } else if (self::FIELD_OCCURRENCE_PERIOD === $cen) {
                $type->setOccurrencePeriod(FHIRPeriod::xmlUnserialize($ce, $config));
            } else if (self::FIELD_OCCURRENCE_TIMING === $cen) {
                $type->setOccurrenceTiming(FHIRTiming::xmlUnserialize($ce, $config));
            } else if (self::FIELD_PERFORMER === $cen) {
                $type->addPerformer(FHIRChargeItemPerformer::xmlUnserialize($ce, $config));
            } else if (self::FIELD_PERFORMING_ORGANIZATION === $cen) {
                $type->setPerformingOrganization(FHIRReference::xmlUnserialize($ce, $config));
            } else if (self::FIELD_REQUESTING_ORGANIZATION === $cen) {
                $type->setRequestingOrganization(FHIRReference::xmlUnserialize($ce, $config));
            } else if (self::FIELD_COST_CENTER === $cen) {
                $type->setCostCenter(FHIRReference::xmlUnserialize($ce, $config));
            } else if (self::FIELD_QUANTITY === $cen) {
                $type->setQuantity(FHIRQuantity::xmlUnserialize($ce, $config));
            } else if (self::FIELD_BODYSITE === $cen) {
                $type->addBodysite(FHIRCodeableConcept::xmlUnserialize($ce, $config));
            } else if (self::FIELD_FACTOR_OVERRIDE === $cen) {
                $type->setFactorOverride(FHIRDecimal::xmlUnserialize($ce, $config));
            } else if (self::FIELD_PRICE_OVERRIDE === $cen) {
                $type->setPriceOverride(FHIRMoney::xmlUnserialize($ce, $config));
            } else if (self::FIELD_OVERRIDE_REASON === $cen) {
                $type->setOverrideReason(FHIRString::xmlUnserialize($ce, $config));
            } else if (self::FIELD_ENTERER === $cen) {
                $type->setEnterer(FHIRReference::xmlUnserialize($ce, $config));
            } else if (self::FIELD_ENTERED_DATE === $cen) {
                $type->setEnteredDate(FHIRDateTime::xmlUnserialize($ce, $config));
            } else if (self::FIELD_REASON === $cen) {
                $type->addReason(FHIRCodeableConcept::xmlUnserialize($ce, $config));
            } else if (self::FIELD_SERVICE === $cen) {
                $type->addService(FHIRReference::xmlUnserialize($ce, $config));
            } else if (self::FIELD_PRODUCT_REFERENCE === $cen) {
                $type->setProductReference(FHIRReference::xmlUnserialize($ce, $config));
            } else if (self::FIELD_PRODUCT_CODEABLE_CONCEPT === $cen) {
                $type->setProductCodeableConcept(FHIRCodeableConcept::xmlUnserialize($ce, $config));
            } else if (self::FIELD_ACCOUNT === $cen) {
                $type->addAccount(FHIRReference::xmlUnserialize($ce, $config));
            } else if (self::FIELD_NOTE === $cen) {
                $type->addNote(FHIRAnnotation::xmlUnserialize($ce, $config));
            } else if (self::FIELD_SUPPORTING_INFORMATION === $cen) {
                $type->addSupportingInformation(FHIRReference::xmlUnserialize($ce, $config));
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
        if (isset($attributes[self::FIELD_FACTOR_OVERRIDE])) {
            if (isset($type->factorOverride)) {
                $type->factorOverride->setValue((string)$attributes[self::FIELD_FACTOR_OVERRIDE]);
            } else {
                $type->setFactorOverride((string)$attributes[self::FIELD_FACTOR_OVERRIDE]);
            }
            $type->_setXMLFieldValueLocation(self::FIELD_FACTOR_OVERRIDE, ValueXMLLocationEnum::PARENT_ATTRIBUTE);
        }
        if (isset($attributes[self::FIELD_OVERRIDE_REASON])) {
            if (isset($type->overrideReason)) {
                $type->overrideReason->setValue((string)$attributes[self::FIELD_OVERRIDE_REASON]);
            } else {
                $type->setOverrideReason((string)$attributes[self::FIELD_OVERRIDE_REASON]);
            }
            $type->_setXMLFieldValueLocation(self::FIELD_OVERRIDE_REASON, ValueXMLLocationEnum::PARENT_ATTRIBUTE);
        }
        if (isset($attributes[self::FIELD_ENTERED_DATE])) {
            if (isset($type->enteredDate)) {
                $type->enteredDate->setValue((string)$attributes[self::FIELD_ENTERED_DATE]);
            } else {
                $type->setEnteredDate((string)$attributes[self::FIELD_ENTERED_DATE]);
            }
            $type->_setXMLFieldValueLocation(self::FIELD_ENTERED_DATE, ValueXMLLocationEnum::PARENT_ATTRIBUTE);
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
            $xw->openRootNode('ChargeItem', $this->_getSourceXMLNS());
        }
        if (isset($this->status) && ValueXMLLocationEnum::PARENT_ATTRIBUTE === $this->_valueXMLLocations[self::FIELD_STATUS]) {
            $xw->writeAttribute(self::FIELD_STATUS, $this->status->_getValueAsString());
        }
        if (isset($this->occurrenceDateTime) && ValueXMLLocationEnum::PARENT_ATTRIBUTE === $this->_valueXMLLocations[self::FIELD_OCCURRENCE_DATE_TIME]) {
            $xw->writeAttribute(self::FIELD_OCCURRENCE_DATE_TIME, $this->occurrenceDateTime->_getValueAsString());
        }
        if (isset($this->factorOverride) && ValueXMLLocationEnum::PARENT_ATTRIBUTE === $this->_valueXMLLocations[self::FIELD_FACTOR_OVERRIDE]) {
            $xw->writeAttribute(self::FIELD_FACTOR_OVERRIDE, $this->factorOverride->_getValueAsString());
        }
        if (isset($this->overrideReason) && ValueXMLLocationEnum::PARENT_ATTRIBUTE === $this->_valueXMLLocations[self::FIELD_OVERRIDE_REASON]) {
            $xw->writeAttribute(self::FIELD_OVERRIDE_REASON, $this->overrideReason->_getValueAsString());
        }
        if (isset($this->enteredDate) && ValueXMLLocationEnum::PARENT_ATTRIBUTE === $this->_valueXMLLocations[self::FIELD_ENTERED_DATE]) {
            $xw->writeAttribute(self::FIELD_ENTERED_DATE, $this->enteredDate->_getValueAsString());
        }
        parent::xmlSerialize($xw, $config);
        if (isset($this->identifier)) {
            foreach ($this->identifier as $v) {
                $xw->startElement(self::FIELD_IDENTIFIER);
                $v->xmlSerialize($xw, $config);
                $xw->endElement();
            }
        }
        if (isset($this->definitionUri) && [] !== $this->definitionUri) {
            foreach($this->definitionUri as $v) {
                $xw->startElement(self::FIELD_DEFINITION_URI);
                $v->xmlSerialize($xw, $config);
                $xw->endElement();
            }
        }
        if (isset($this->definitionCanonical) && [] !== $this->definitionCanonical) {
            foreach($this->definitionCanonical as $v) {
                $xw->startElement(self::FIELD_DEFINITION_CANONICAL);
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
        if (isset($this->partOf)) {
            foreach ($this->partOf as $v) {
                $xw->startElement(self::FIELD_PART_OF);
                $v->xmlSerialize($xw, $config);
                $xw->endElement();
            }
        }
        if (isset($this->code)) {
            $xw->startElement(self::FIELD_CODE);
            $this->code->xmlSerialize($xw, $config);
            $xw->endElement();
        }
        if (isset($this->subject)) {
            $xw->startElement(self::FIELD_SUBJECT);
            $this->subject->xmlSerialize($xw, $config);
            $xw->endElement();
        }
        if (isset($this->context)) {
            $xw->startElement(self::FIELD_CONTEXT);
            $this->context->xmlSerialize($xw, $config);
            $xw->endElement();
        }
        if (isset($this->occurrenceDateTime)
            && (ValueXMLLocationEnum::PARENT_ATTRIBUTE !== $this->_valueXMLLocations[self::FIELD_OCCURRENCE_DATE_TIME]
                || $this->occurrenceDateTime->_nonValueFieldDefined())) {
            $xw->startElement(self::FIELD_OCCURRENCE_DATE_TIME);
            $this->occurrenceDateTime->xmlSerialize($xw, $config, $this->_valueXMLLocations[self::FIELD_OCCURRENCE_DATE_TIME]);
            $xw->endElement();
        }
        if (isset($this->occurrencePeriod)) {
            $xw->startElement(self::FIELD_OCCURRENCE_PERIOD);
            $this->occurrencePeriod->xmlSerialize($xw, $config);
            $xw->endElement();
        }
        if (isset($this->occurrenceTiming)) {
            $xw->startElement(self::FIELD_OCCURRENCE_TIMING);
            $this->occurrenceTiming->xmlSerialize($xw, $config);
            $xw->endElement();
        }
        if (isset($this->performer)) {
            foreach ($this->performer as $v) {
                $xw->startElement(self::FIELD_PERFORMER);
                $v->xmlSerialize($xw, $config);
                $xw->endElement();
            }
        }
        if (isset($this->performingOrganization)) {
            $xw->startElement(self::FIELD_PERFORMING_ORGANIZATION);
            $this->performingOrganization->xmlSerialize($xw, $config);
            $xw->endElement();
        }
        if (isset($this->requestingOrganization)) {
            $xw->startElement(self::FIELD_REQUESTING_ORGANIZATION);
            $this->requestingOrganization->xmlSerialize($xw, $config);
            $xw->endElement();
        }
        if (isset($this->costCenter)) {
            $xw->startElement(self::FIELD_COST_CENTER);
            $this->costCenter->xmlSerialize($xw, $config);
            $xw->endElement();
        }
        if (isset($this->quantity)) {
            $xw->startElement(self::FIELD_QUANTITY);
            $this->quantity->xmlSerialize($xw, $config);
            $xw->endElement();
        }
        if (isset($this->bodysite)) {
            foreach ($this->bodysite as $v) {
                $xw->startElement(self::FIELD_BODYSITE);
                $v->xmlSerialize($xw, $config);
                $xw->endElement();
            }
        }
        if (isset($this->factorOverride)
            && (ValueXMLLocationEnum::PARENT_ATTRIBUTE !== $this->_valueXMLLocations[self::FIELD_FACTOR_OVERRIDE]
                || $this->factorOverride->_nonValueFieldDefined())) {
            $xw->startElement(self::FIELD_FACTOR_OVERRIDE);
            $this->factorOverride->xmlSerialize($xw, $config, $this->_valueXMLLocations[self::FIELD_FACTOR_OVERRIDE]);
            $xw->endElement();
        }
        if (isset($this->priceOverride)) {
            $xw->startElement(self::FIELD_PRICE_OVERRIDE);
            $this->priceOverride->xmlSerialize($xw, $config);
            $xw->endElement();
        }
        if (isset($this->overrideReason)
            && (ValueXMLLocationEnum::PARENT_ATTRIBUTE !== $this->_valueXMLLocations[self::FIELD_OVERRIDE_REASON]
                || $this->overrideReason->_nonValueFieldDefined())) {
            $xw->startElement(self::FIELD_OVERRIDE_REASON);
            $this->overrideReason->xmlSerialize($xw, $config, $this->_valueXMLLocations[self::FIELD_OVERRIDE_REASON]);
            $xw->endElement();
        }
        if (isset($this->enterer)) {
            $xw->startElement(self::FIELD_ENTERER);
            $this->enterer->xmlSerialize($xw, $config);
            $xw->endElement();
        }
        if (isset($this->enteredDate)
            && (ValueXMLLocationEnum::PARENT_ATTRIBUTE !== $this->_valueXMLLocations[self::FIELD_ENTERED_DATE]
                || $this->enteredDate->_nonValueFieldDefined())) {
            $xw->startElement(self::FIELD_ENTERED_DATE);
            $this->enteredDate->xmlSerialize($xw, $config, $this->_valueXMLLocations[self::FIELD_ENTERED_DATE]);
            $xw->endElement();
        }
        if (isset($this->reason)) {
            foreach ($this->reason as $v) {
                $xw->startElement(self::FIELD_REASON);
                $v->xmlSerialize($xw, $config);
                $xw->endElement();
            }
        }
        if (isset($this->service)) {
            foreach ($this->service as $v) {
                $xw->startElement(self::FIELD_SERVICE);
                $v->xmlSerialize($xw, $config);
                $xw->endElement();
            }
        }
        if (isset($this->productReference)) {
            $xw->startElement(self::FIELD_PRODUCT_REFERENCE);
            $this->productReference->xmlSerialize($xw, $config);
            $xw->endElement();
        }
        if (isset($this->productCodeableConcept)) {
            $xw->startElement(self::FIELD_PRODUCT_CODEABLE_CONCEPT);
            $this->productCodeableConcept->xmlSerialize($xw, $config);
            $xw->endElement();
        }
        if (isset($this->account)) {
            foreach ($this->account as $v) {
                $xw->startElement(self::FIELD_ACCOUNT);
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
        if (isset($this->supportingInformation)) {
            foreach ($this->supportingInformation as $v) {
                $xw->startElement(self::FIELD_SUPPORTING_INFORMATION);
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
     * @param null|\OpenEMR\FHIR\Versions\R4\Types\FHIRResource\FHIRDomainResource\FHIRChargeItem $type
     * @return \OpenEMR\FHIR\Versions\R4\Types\FHIRResource\FHIRDomainResource\FHIRChargeItem
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
        } else if (!($type instanceof FHIRChargeItem)) {
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
        if (isset($decoded->definitionUri)
            || isset($decoded->_definitionUri)
            || property_exists($decoded, self::FIELD_DEFINITION_URI)
            || property_exists($decoded, self::FIELD_DEFINITION_URI_EXT)) {
            $vals = (array)($decoded->definitionUri ?? []);
            $exts = (array)($decoded->_definitionUri ?? []);
            $valCnt = count($vals);
            $extCnt = count($exts);
            if ($extCnt > $valCnt) {
                $valCnt = $extCnt;
            }
            for ($i = 0; $i < $valCnt; $i++) {
                $v = $exts[$i] ?? new \stdClass();
                $v->value = $vals[$i] ?? null;
                $type->addDefinitionUri(FHIRUri::jsonUnserialize($v, $config));
            }
        }
        if (isset($decoded->definitionCanonical)
            || isset($decoded->_definitionCanonical)
            || property_exists($decoded, self::FIELD_DEFINITION_CANONICAL)
            || property_exists($decoded, self::FIELD_DEFINITION_CANONICAL_EXT)) {
            $vals = (array)($decoded->definitionCanonical ?? []);
            $exts = (array)($decoded->_definitionCanonical ?? []);
            $valCnt = count($vals);
            $extCnt = count($exts);
            if ($extCnt > $valCnt) {
                $valCnt = $extCnt;
            }
            for ($i = 0; $i < $valCnt; $i++) {
                $v = $exts[$i] ?? new \stdClass();
                $v->value = $vals[$i] ?? null;
                $type->addDefinitionCanonical(FHIRCanonical::jsonUnserialize($v, $config));
            }
        }
        if (isset($decoded->status)
            || isset($decoded->_status)
            || property_exists($decoded, self::FIELD_STATUS)
            || property_exists($decoded, self::FIELD_STATUS_EXT)) {
            $v = $decoded->_status ?? new \stdClass();
            $v->value = $decoded->status ?? null;
            $type->setStatus(FHIRChargeItemStatus::jsonUnserialize($v, $config));
        }
        if (isset($decoded->partOf) || property_exists($decoded, self::FIELD_PART_OF)) {
            if (is_object($decoded->partOf)) {
                $vals = [$decoded->partOf];
                $type->_setJSONFieldElideSingletonArray(self::FIELD_PART_OF, true);
            } else {
                $vals = $decoded->partOf;
            }
            foreach($vals as $v) {
                $type->addPartOf(FHIRReference::jsonUnserialize($v, $config));
            }
        }
        if (isset($decoded->code) || property_exists($decoded, self::FIELD_CODE)) {
            if (is_array($decoded->code)) {
                $type->setCode(FHIRCodeableConcept::jsonUnserialize(reset($decoded->code), $config));
            } else {
                $type->setCode(FHIRCodeableConcept::jsonUnserialize($decoded->code, $config));
            }
        }
        if (isset($decoded->subject) || property_exists($decoded, self::FIELD_SUBJECT)) {
            if (is_array($decoded->subject)) {
                $type->setSubject(FHIRReference::jsonUnserialize(reset($decoded->subject), $config));
            } else {
                $type->setSubject(FHIRReference::jsonUnserialize($decoded->subject, $config));
            }
        }
        if (isset($decoded->context) || property_exists($decoded, self::FIELD_CONTEXT)) {
            if (is_array($decoded->context)) {
                $type->setContext(FHIRReference::jsonUnserialize(reset($decoded->context), $config));
            } else {
                $type->setContext(FHIRReference::jsonUnserialize($decoded->context, $config));
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
        if (isset($decoded->occurrencePeriod) || property_exists($decoded, self::FIELD_OCCURRENCE_PERIOD)) {
            if (is_array($decoded->occurrencePeriod)) {
                $type->setOccurrencePeriod(FHIRPeriod::jsonUnserialize(reset($decoded->occurrencePeriod), $config));
            } else {
                $type->setOccurrencePeriod(FHIRPeriod::jsonUnserialize($decoded->occurrencePeriod, $config));
            }
        }
        if (isset($decoded->occurrenceTiming) || property_exists($decoded, self::FIELD_OCCURRENCE_TIMING)) {
            if (is_array($decoded->occurrenceTiming)) {
                $type->setOccurrenceTiming(FHIRTiming::jsonUnserialize(reset($decoded->occurrenceTiming), $config));
            } else {
                $type->setOccurrenceTiming(FHIRTiming::jsonUnserialize($decoded->occurrenceTiming, $config));
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
                $type->addPerformer(FHIRChargeItemPerformer::jsonUnserialize($v, $config));
            }
        }
        if (isset($decoded->performingOrganization) || property_exists($decoded, self::FIELD_PERFORMING_ORGANIZATION)) {
            if (is_array($decoded->performingOrganization)) {
                $type->setPerformingOrganization(FHIRReference::jsonUnserialize(reset($decoded->performingOrganization), $config));
            } else {
                $type->setPerformingOrganization(FHIRReference::jsonUnserialize($decoded->performingOrganization, $config));
            }
        }
        if (isset($decoded->requestingOrganization) || property_exists($decoded, self::FIELD_REQUESTING_ORGANIZATION)) {
            if (is_array($decoded->requestingOrganization)) {
                $type->setRequestingOrganization(FHIRReference::jsonUnserialize(reset($decoded->requestingOrganization), $config));
            } else {
                $type->setRequestingOrganization(FHIRReference::jsonUnserialize($decoded->requestingOrganization, $config));
            }
        }
        if (isset($decoded->costCenter) || property_exists($decoded, self::FIELD_COST_CENTER)) {
            if (is_array($decoded->costCenter)) {
                $type->setCostCenter(FHIRReference::jsonUnserialize(reset($decoded->costCenter), $config));
            } else {
                $type->setCostCenter(FHIRReference::jsonUnserialize($decoded->costCenter, $config));
            }
        }
        if (isset($decoded->quantity) || property_exists($decoded, self::FIELD_QUANTITY)) {
            if (is_array($decoded->quantity)) {
                $type->setQuantity(FHIRQuantity::jsonUnserialize(reset($decoded->quantity), $config));
            } else {
                $type->setQuantity(FHIRQuantity::jsonUnserialize($decoded->quantity, $config));
            }
        }
        if (isset($decoded->bodysite) || property_exists($decoded, self::FIELD_BODYSITE)) {
            if (is_object($decoded->bodysite)) {
                $vals = [$decoded->bodysite];
                $type->_setJSONFieldElideSingletonArray(self::FIELD_BODYSITE, true);
            } else {
                $vals = $decoded->bodysite;
            }
            foreach($vals as $v) {
                $type->addBodysite(FHIRCodeableConcept::jsonUnserialize($v, $config));
            }
        }
        if (isset($decoded->factorOverride)
            || isset($decoded->_factorOverride)
            || property_exists($decoded, self::FIELD_FACTOR_OVERRIDE)
            || property_exists($decoded, self::FIELD_FACTOR_OVERRIDE_EXT)) {
            $v = $decoded->_factorOverride ?? new \stdClass();
            $v->value = $decoded->factorOverride ?? null;
            $type->setFactorOverride(FHIRDecimal::jsonUnserialize($v, $config));
        }
        if (isset($decoded->priceOverride) || property_exists($decoded, self::FIELD_PRICE_OVERRIDE)) {
            if (is_array($decoded->priceOverride)) {
                $type->setPriceOverride(FHIRMoney::jsonUnserialize(reset($decoded->priceOverride), $config));
            } else {
                $type->setPriceOverride(FHIRMoney::jsonUnserialize($decoded->priceOverride, $config));
            }
        }
        if (isset($decoded->overrideReason)
            || isset($decoded->_overrideReason)
            || property_exists($decoded, self::FIELD_OVERRIDE_REASON)
            || property_exists($decoded, self::FIELD_OVERRIDE_REASON_EXT)) {
            $v = $decoded->_overrideReason ?? new \stdClass();
            $v->value = $decoded->overrideReason ?? null;
            $type->setOverrideReason(FHIRString::jsonUnserialize($v, $config));
        }
        if (isset($decoded->enterer) || property_exists($decoded, self::FIELD_ENTERER)) {
            if (is_array($decoded->enterer)) {
                $type->setEnterer(FHIRReference::jsonUnserialize(reset($decoded->enterer), $config));
            } else {
                $type->setEnterer(FHIRReference::jsonUnserialize($decoded->enterer, $config));
            }
        }
        if (isset($decoded->enteredDate)
            || isset($decoded->_enteredDate)
            || property_exists($decoded, self::FIELD_ENTERED_DATE)
            || property_exists($decoded, self::FIELD_ENTERED_DATE_EXT)) {
            $v = $decoded->_enteredDate ?? new \stdClass();
            $v->value = $decoded->enteredDate ?? null;
            $type->setEnteredDate(FHIRDateTime::jsonUnserialize($v, $config));
        }
        if (isset($decoded->reason) || property_exists($decoded, self::FIELD_REASON)) {
            if (is_object($decoded->reason)) {
                $vals = [$decoded->reason];
                $type->_setJSONFieldElideSingletonArray(self::FIELD_REASON, true);
            } else {
                $vals = $decoded->reason;
            }
            foreach($vals as $v) {
                $type->addReason(FHIRCodeableConcept::jsonUnserialize($v, $config));
            }
        }
        if (isset($decoded->service) || property_exists($decoded, self::FIELD_SERVICE)) {
            if (is_object($decoded->service)) {
                $vals = [$decoded->service];
                $type->_setJSONFieldElideSingletonArray(self::FIELD_SERVICE, true);
            } else {
                $vals = $decoded->service;
            }
            foreach($vals as $v) {
                $type->addService(FHIRReference::jsonUnserialize($v, $config));
            }
        }
        if (isset($decoded->productReference) || property_exists($decoded, self::FIELD_PRODUCT_REFERENCE)) {
            if (is_array($decoded->productReference)) {
                $type->setProductReference(FHIRReference::jsonUnserialize(reset($decoded->productReference), $config));
            } else {
                $type->setProductReference(FHIRReference::jsonUnserialize($decoded->productReference, $config));
            }
        }
        if (isset($decoded->productCodeableConcept) || property_exists($decoded, self::FIELD_PRODUCT_CODEABLE_CONCEPT)) {
            if (is_array($decoded->productCodeableConcept)) {
                $type->setProductCodeableConcept(FHIRCodeableConcept::jsonUnserialize(reset($decoded->productCodeableConcept), $config));
            } else {
                $type->setProductCodeableConcept(FHIRCodeableConcept::jsonUnserialize($decoded->productCodeableConcept, $config));
            }
        }
        if (isset($decoded->account) || property_exists($decoded, self::FIELD_ACCOUNT)) {
            if (is_object($decoded->account)) {
                $vals = [$decoded->account];
                $type->_setJSONFieldElideSingletonArray(self::FIELD_ACCOUNT, true);
            } else {
                $vals = $decoded->account;
            }
            foreach($vals as $v) {
                $type->addAccount(FHIRReference::jsonUnserialize($v, $config));
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
        if (isset($decoded->supportingInformation) || property_exists($decoded, self::FIELD_SUPPORTING_INFORMATION)) {
            if (is_object($decoded->supportingInformation)) {
                $vals = [$decoded->supportingInformation];
                $type->_setJSONFieldElideSingletonArray(self::FIELD_SUPPORTING_INFORMATION, true);
            } else {
                $vals = $decoded->supportingInformation;
            }
            foreach($vals as $v) {
                $type->addSupportingInformation(FHIRReference::jsonUnserialize($v, $config));
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
        if (isset($this->definitionUri) && [] !== $this->definitionUri) {
            $vals = [];
            $exts = [];
            $hasVals = false;
            $hasExts = false;
            foreach ($this->definitionUri as $v) {
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
                $out->definitionUri = $vals;
            }
            if ($hasExts) {
                $out->_definitionUri = $exts;
            }
        }
        if (isset($this->definitionCanonical) && [] !== $this->definitionCanonical) {
            $vals = [];
            $exts = [];
            $hasVals = false;
            $hasExts = false;
            foreach ($this->definitionCanonical as $v) {
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
                $out->definitionCanonical = $vals;
            }
            if ($hasExts) {
                $out->_definitionCanonical = $exts;
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
        if (isset($this->partOf) && [] !== $this->partOf) {
            if ($this->_getJSONFieldElideSingletonArray(self::FIELD_PART_OF) && 1 === count($this->partOf)) {
                $out->partOf = $this->partOf[0];
            } else {
                $out->partOf = $this->partOf;
            }
        }
        if (isset($this->code)) {
            $out->code = $this->code;
        }
        if (isset($this->subject)) {
            $out->subject = $this->subject;
        }
        if (isset($this->context)) {
            $out->context = $this->context;
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
        if (isset($this->occurrencePeriod)) {
            $out->occurrencePeriod = $this->occurrencePeriod;
        }
        if (isset($this->occurrenceTiming)) {
            $out->occurrenceTiming = $this->occurrenceTiming;
        }
        if (isset($this->performer) && [] !== $this->performer) {
            if ($this->_getJSONFieldElideSingletonArray(self::FIELD_PERFORMER) && 1 === count($this->performer)) {
                $out->performer = $this->performer[0];
            } else {
                $out->performer = $this->performer;
            }
        }
        if (isset($this->performingOrganization)) {
            $out->performingOrganization = $this->performingOrganization;
        }
        if (isset($this->requestingOrganization)) {
            $out->requestingOrganization = $this->requestingOrganization;
        }
        if (isset($this->costCenter)) {
            $out->costCenter = $this->costCenter;
        }
        if (isset($this->quantity)) {
            $out->quantity = $this->quantity;
        }
        if (isset($this->bodysite) && [] !== $this->bodysite) {
            if ($this->_getJSONFieldElideSingletonArray(self::FIELD_BODYSITE) && 1 === count($this->bodysite)) {
                $out->bodysite = $this->bodysite[0];
            } else {
                $out->bodysite = $this->bodysite;
            }
        }
        if (isset($this->factorOverride)) {
            if (null !== ($val = $this->factorOverride->getValue())) {
                $out->factorOverride = $val;
            }
            if ($this->factorOverride->_nonValueFieldDefined()) {
                $ext = $this->factorOverride->jsonSerialize();
                unset($ext->value);
                $out->_factorOverride = $ext;
            }
        }
        if (isset($this->priceOverride)) {
            $out->priceOverride = $this->priceOverride;
        }
        if (isset($this->overrideReason)) {
            if (null !== ($val = $this->overrideReason->getValue())) {
                $out->overrideReason = $val;
            }
            if ($this->overrideReason->_nonValueFieldDefined()) {
                $ext = $this->overrideReason->jsonSerialize();
                unset($ext->value);
                $out->_overrideReason = $ext;
            }
        }
        if (isset($this->enterer)) {
            $out->enterer = $this->enterer;
        }
        if (isset($this->enteredDate)) {
            if (null !== ($val = $this->enteredDate->getValue())) {
                $out->enteredDate = $val;
            }
            if ($this->enteredDate->_nonValueFieldDefined()) {
                $ext = $this->enteredDate->jsonSerialize();
                unset($ext->value);
                $out->_enteredDate = $ext;
            }
        }
        if (isset($this->reason) && [] !== $this->reason) {
            if ($this->_getJSONFieldElideSingletonArray(self::FIELD_REASON) && 1 === count($this->reason)) {
                $out->reason = $this->reason[0];
            } else {
                $out->reason = $this->reason;
            }
        }
        if (isset($this->service) && [] !== $this->service) {
            if ($this->_getJSONFieldElideSingletonArray(self::FIELD_SERVICE) && 1 === count($this->service)) {
                $out->service = $this->service[0];
            } else {
                $out->service = $this->service;
            }
        }
        if (isset($this->productReference)) {
            $out->productReference = $this->productReference;
        }
        if (isset($this->productCodeableConcept)) {
            $out->productCodeableConcept = $this->productCodeableConcept;
        }
        if (isset($this->account) && [] !== $this->account) {
            if ($this->_getJSONFieldElideSingletonArray(self::FIELD_ACCOUNT) && 1 === count($this->account)) {
                $out->account = $this->account[0];
            } else {
                $out->account = $this->account;
            }
        }
        if (isset($this->note) && [] !== $this->note) {
            if ($this->_getJSONFieldElideSingletonArray(self::FIELD_NOTE) && 1 === count($this->note)) {
                $out->note = $this->note[0];
            } else {
                $out->note = $this->note;
            }
        }
        if (isset($this->supportingInformation) && [] !== $this->supportingInformation) {
            if ($this->_getJSONFieldElideSingletonArray(self::FIELD_SUPPORTING_INFORMATION) && 1 === count($this->supportingInformation)) {
                $out->supportingInformation = $this->supportingInformation[0];
            } else {
                $out->supportingInformation = $this->supportingInformation;
            }
        }
        $out->resourceType = $this->_getResourceType();
        return $out;
    }
}
