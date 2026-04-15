<?php

declare(strict_types=1);

namespace OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRCarePlan;

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
use OpenEMR\FHIR\Validation\Rules\MinOccursRule;
use OpenEMR\FHIR\Validation\TypeValidationsTrait;
use OpenEMR\FHIR\Versions\R4\Types\FHIRBooleanPrimitive;
use OpenEMR\FHIR\Versions\R4\Types\FHIRCanonicalPrimitive;
use OpenEMR\FHIR\Versions\R4\Types\FHIRCodePrimitive\FHIRCarePlanActivityKindList;
use OpenEMR\FHIR\Versions\R4\Types\FHIRCodePrimitive\FHIRCarePlanActivityStatusList;
use OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement;
use OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRTiming;
use OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBoolean;
use OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRCanonical;
use OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRCarePlanActivityKind;
use OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRCarePlanActivityStatus;
use OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRCodeableConcept;
use OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRExtension;
use OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRPeriod;
use OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRQuantity;
use OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRReference;
use OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRString;
use OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRUri;
use OpenEMR\FHIR\Versions\R4\Types\FHIRStringPrimitive;
use OpenEMR\FHIR\Versions\R4\Types\FHIRUriPrimitive;
use OpenEMR\FHIR\Versions\R4\VersionConstants;

/**
 * Describes the intention of how one or more practitioners intend to deliver care
 * for a particular patient, group or community for a period of time, possibly
 * limited to care for a specific condition or set of conditions.
 */
#[\Attribute(\Attribute::TARGET_PROPERTY)]
class FHIRCarePlanDetail extends FHIRBackboneElement
{
    use TypeValidationsTrait,
        JSONSerializationOptionsTrait,
        XMLSerializationOptionsTrait;

    // name of FHIR type this class describes
    public const FHIR_TYPE_NAME = VersionConstants::TYPE_NAME_CARE_PLAN_DOT_DETAIL;

    /* class_default.php:56 */
    public const FIELD_KIND = 'kind';
    public const FIELD_KIND_EXT = '_kind';
    public const FIELD_INSTANTIATES_CANONICAL = 'instantiatesCanonical';
    public const FIELD_INSTANTIATES_CANONICAL_EXT = '_instantiatesCanonical';
    public const FIELD_INSTANTIATES_URI = 'instantiatesUri';
    public const FIELD_INSTANTIATES_URI_EXT = '_instantiatesUri';
    public const FIELD_CODE = 'code';
    public const FIELD_REASON_CODE = 'reasonCode';
    public const FIELD_REASON_REFERENCE = 'reasonReference';
    public const FIELD_GOAL = 'goal';
    public const FIELD_STATUS = 'status';
    public const FIELD_STATUS_EXT = '_status';
    public const FIELD_STATUS_REASON = 'statusReason';
    public const FIELD_DO_NOT_PERFORM = 'doNotPerform';
    public const FIELD_DO_NOT_PERFORM_EXT = '_doNotPerform';
    public const FIELD_SCHEDULED_TIMING = 'scheduledTiming';
    public const FIELD_SCHEDULED_PERIOD = 'scheduledPeriod';
    public const FIELD_SCHEDULED_STRING = 'scheduledString';
    public const FIELD_SCHEDULED_STRING_EXT = '_scheduledString';
    public const FIELD_LOCATION = 'location';
    public const FIELD_PERFORMER = 'performer';
    public const FIELD_PRODUCT_CODEABLE_CONCEPT = 'productCodeableConcept';
    public const FIELD_PRODUCT_REFERENCE = 'productReference';
    public const FIELD_DAILY_AMOUNT = 'dailyAmount';
    public const FIELD_QUANTITY = 'quantity';
    public const FIELD_DESCRIPTION = 'description';
    public const FIELD_DESCRIPTION_EXT = '_description';

    /* class_default.php:75 */
    private const _FHIR_VALIDATION_RULES = [
        self::FIELD_STATUS => [
            MinOccursRule::NAME => 1,
        ],
    ];

    /* class_default.php:96 */
    private array $_valueXMLLocations = [
        self::FIELD_KIND => ValueXMLLocationEnum::CONTAINER_ATTRIBUTE,
        self::FIELD_STATUS => ValueXMLLocationEnum::CONTAINER_ATTRIBUTE,
        self::FIELD_DO_NOT_PERFORM => ValueXMLLocationEnum::CONTAINER_ATTRIBUTE,
        self::FIELD_SCHEDULED_STRING => ValueXMLLocationEnum::CONTAINER_ATTRIBUTE,
        self::FIELD_DESCRIPTION => ValueXMLLocationEnum::CONTAINER_ATTRIBUTE,
    ];

    /* class_default.php:112 */
    /**
     * Resource types defined as part of FHIR that can be represented as in-line
     * definitions of a care plan activity.
     * If the element is present, it must have either a \@value, an \@id, or extensions
     *
     * A description of the kind of resource the in-line definition of a care plan
     * activity is representing. The CarePlan.activity.detail is an in-line definition
     * when a resource is not referenced using CarePlan.activity.reference. For
     * example, a MedicationRequest, a ServiceRequest, or a CommunicationRequest.
     *
     * @var \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRCarePlanActivityKind
     */
    #[FHIRCarePlanActivityKind]
    protected FHIRCarePlanActivityKind $kind;
    /**
     * A URI that is a reference to a canonical URL on a FHIR resource
     * see [Canonical References](references.html#canonical)
     * If the element is present, it must have either a \@value, an \@id referenced from
     * the Narrative, or extensions
     *
     * The URL pointing to a FHIR-defined protocol, guideline, questionnaire or other
     * definition that is adhered to in whole or in part by this CarePlan activity.
     *
     * @var iterable<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRCanonical>
     */
    #[FHIRCanonical]
    protected array $instantiatesCanonical;
    /**
     * String of characters used to identify a name or a resource
     * see http://en.wikipedia.org/wiki/Uniform_resource_identifier
     * If the element is present, it must have either a \@value, an \@id, or extensions
     *
     * The URL pointing to an externally maintained protocol, guideline, questionnaire
     * or other definition that is adhered to in whole or in part by this CarePlan
     * activity.
     *
     * @var iterable<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRUri>
     */
    #[FHIRUri]
    protected array $instantiatesUri;
    /**
     * A concept that may be defined by a formal reference to a terminology or ontology
     * or may be provided by text.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * Detailed description of the type of planned activity; e.g. what lab test, what
     * procedure, what kind of encounter.
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
     * Provides the rationale that drove the inclusion of this particular activity as
     * part of the plan or the reason why the activity was prohibited.
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
     * Indicates another resource, such as the health condition(s), whose existence
     * justifies this request and drove the inclusion of this particular activity as
     * part of the plan.
     *
     * @var iterable<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRReference>
     */
    #[FHIRReference]
    protected array $reasonReference;
    /**
     * A reference from one resource to another.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * Internal reference that identifies the goals that this activity is intended to
     * contribute towards meeting.
     *
     * @var iterable<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRReference>
     */
    #[FHIRReference]
    protected array $goal;
    /**
     * Codes that reflect the current state of a care plan activity within its overall
     * life cycle.
     * If the element is present, it must have either a \@value, an \@id, or extensions
     *
     * Identifies what progress is being made for the specific activity.
     *
     * @var \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRCarePlanActivityStatus
     */
    #[FHIRCarePlanActivityStatus]
    protected FHIRCarePlanActivityStatus $status;
    /**
     * A concept that may be defined by a formal reference to a terminology or ontology
     * or may be provided by text.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * Provides reason why the activity isn't yet started, is on hold, was cancelled,
     * etc.
     *
     * @var \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRCodeableConcept
     */
    #[FHIRCodeableConcept]
    protected FHIRCodeableConcept $statusReason;
    /**
     * Value of "true" or "false"
     * If the element is present, it must have either a \@value, an \@id, or extensions
     *
     * If true, indicates that the described activity is one that must NOT be engaged
     * in when following the plan. If false, or missing, indicates that the described
     * activity is one that should be engaged in when following the plan.
     *
     * @var \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBoolean
     */
    #[FHIRBoolean]
    protected FHIRBoolean $doNotPerform;
    /**
     * Specifies an event that may occur multiple times. Timing schedules are used to
     * record when things are planned, expected or requested to occur. The most common
     * usage is in dosage instructions for medications. They are also used when
     * planning care of various kinds, and may be used for reporting the schedule to
     * which past regular activities were carried out.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * The period, timing or frequency upon which the described activity is to occur.
     * (choose any one of scheduled*, but only one)
     *
     * @var \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRTiming
     */
    #[FHIRTiming]
    protected FHIRTiming $scheduledTiming;
    /**
     * A time period defined by a start and end date and optionally time.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * The period, timing or frequency upon which the described activity is to occur.
     * (choose any one of scheduled*, but only one)
     *
     * @var \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRPeriod
     */
    #[FHIRPeriod]
    protected FHIRPeriod $scheduledPeriod;
    /**
     * A sequence of Unicode characters
     * Note that FHIR strings SHALL NOT exceed 1MB in size
     * If the element is present, it must have either a \@value, an \@id, or extensions
     *
     * The period, timing or frequency upon which the described activity is to occur.
     * (choose any one of scheduled*, but only one)
     *
     * @var \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRString
     */
    #[FHIRString]
    protected FHIRString $scheduledString;
    /**
     * A reference from one resource to another.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * Identifies the facility where the activity will occur; e.g. home, hospital,
     * specific clinic, etc.
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
     * Identifies who's expected to be involved in the activity.
     *
     * @var iterable<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRReference>
     */
    #[FHIRReference]
    protected array $performer;
    /**
     * A concept that may be defined by a formal reference to a terminology or ontology
     * or may be provided by text.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * Identifies the food, drug or other product to be consumed or supplied in the
     * activity. (choose any one of product*, but only one)
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
     * Identifies the food, drug or other product to be consumed or supplied in the
     * activity. (choose any one of product*, but only one)
     *
     * @var \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRReference
     */
    #[FHIRReference]
    protected FHIRReference $productReference;
    /**
     * A measured amount (or an amount that can potentially be measured). Note that
     * measured amounts include amounts that are not precisely quantified, including
     * amounts involving arbitrary units and floating currencies.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * Identifies the quantity expected to be consumed in a given day.
     *
     * @var \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRQuantity
     */
    #[FHIRQuantity]
    protected FHIRQuantity $dailyAmount;
    /**
     * A measured amount (or an amount that can potentially be measured). Note that
     * measured amounts include amounts that are not precisely quantified, including
     * amounts involving arbitrary units and floating currencies.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * Identifies the quantity expected to be supplied, administered or consumed by the
     * subject.
     *
     * @var \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRQuantity
     */
    #[FHIRQuantity]
    protected FHIRQuantity $quantity;
    /**
     * A sequence of Unicode characters
     * Note that FHIR strings SHALL NOT exceed 1MB in size
     * If the element is present, it must have either a \@value, an \@id, or extensions
     *
     * This provides a textual description of constraints on the intended activity
     * occurrence, including relation to other activities. It may also include
     * objectives, pre-conditions and end-conditions. Finally, it may convey specifics
     * about the activity such as body site, method, route, etc.
     *
     * @var \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRString
     */
    #[FHIRString]
    protected FHIRString $description;

    /* constructor.php:61 */
    /**
     * FHIRCarePlanDetail Constructor
     * @param null|iterable<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRExtension> $extension
     * @param null|string|\OpenEMR\FHIR\Versions\R4\Types\FHIRStringPrimitive $id
     * @param null|iterable<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRExtension> $modifierExtension
     * @param null|string|\OpenEMR\FHIR\Versions\R4\Types\FHIRCodePrimitive\FHIRCarePlanActivityKindList|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRCarePlanActivityKind $kind
     * @param null|iterable<string>|iterable<\OpenEMR\FHIR\Versions\R4\Types\FHIRCanonicalPrimitive>|iterable<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRCanonical> $instantiatesCanonical
     * @param null|iterable<string>|iterable<\OpenEMR\FHIR\Versions\R4\Types\FHIRUriPrimitive>|iterable<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRUri> $instantiatesUri
     * @param null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRCodeableConcept $code
     * @param null|iterable<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRCodeableConcept> $reasonCode
     * @param null|iterable<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRReference> $reasonReference
     * @param null|iterable<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRReference> $goal
     * @param null|string|\OpenEMR\FHIR\Versions\R4\Types\FHIRCodePrimitive\FHIRCarePlanActivityStatusList|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRCarePlanActivityStatus $status
     * @param null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRCodeableConcept $statusReason
     * @param null|string|bool|\OpenEMR\FHIR\Versions\R4\Types\FHIRBooleanPrimitive|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBoolean $doNotPerform
     * @param null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRTiming $scheduledTiming
     * @param null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRPeriod $scheduledPeriod
     * @param null|string|\OpenEMR\FHIR\Versions\R4\Types\FHIRStringPrimitive|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRString $scheduledString
     * @param null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRReference $location
     * @param null|iterable<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRReference> $performer
     * @param null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRCodeableConcept $productCodeableConcept
     * @param null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRReference $productReference
     * @param null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRQuantity $dailyAmount
     * @param null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRQuantity $quantity
     * @param null|string|\OpenEMR\FHIR\Versions\R4\Types\FHIRStringPrimitive|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRString $description
     * @param null|string[] $fhirComments
     */
    public function __construct(null|iterable $extension = null,
                                null|string|FHIRStringPrimitive $id = null,
                                null|iterable $modifierExtension = null,
                                null|string|FHIRCarePlanActivityKindList|FHIRCarePlanActivityKind $kind = null,
                                null|iterable $instantiatesCanonical = null,
                                null|iterable $instantiatesUri = null,
                                null|FHIRCodeableConcept $code = null,
                                null|iterable $reasonCode = null,
                                null|iterable $reasonReference = null,
                                null|iterable $goal = null,
                                null|string|FHIRCarePlanActivityStatusList|FHIRCarePlanActivityStatus $status = null,
                                null|FHIRCodeableConcept $statusReason = null,
                                null|string|bool|FHIRBooleanPrimitive|FHIRBoolean $doNotPerform = null,
                                null|FHIRTiming $scheduledTiming = null,
                                null|FHIRPeriod $scheduledPeriod = null,
                                null|string|FHIRStringPrimitive|FHIRString $scheduledString = null,
                                null|FHIRReference $location = null,
                                null|iterable $performer = null,
                                null|FHIRCodeableConcept $productCodeableConcept = null,
                                null|FHIRReference $productReference = null,
                                null|FHIRQuantity $dailyAmount = null,
                                null|FHIRQuantity $quantity = null,
                                null|string|FHIRStringPrimitive|FHIRString $description = null,
                                null|iterable $fhirComments = null)
    {
        parent::__construct(extension: $extension,
                            id: $id,
                            modifierExtension: $modifierExtension,
                            fhirComments: $fhirComments);
        if (null !== $kind) {
            $this->setKind($kind);
        }
        if (null !== $instantiatesCanonical) {
            $this->setInstantiatesCanonical(...$instantiatesCanonical);
        }
        if (null !== $instantiatesUri) {
            $this->setInstantiatesUri(...$instantiatesUri);
        }
        if (null !== $code) {
            $this->setCode($code);
        }
        if (null !== $reasonCode) {
            $this->setReasonCode(...$reasonCode);
        }
        if (null !== $reasonReference) {
            $this->setReasonReference(...$reasonReference);
        }
        if (null !== $goal) {
            $this->setGoal(...$goal);
        }
        if (null !== $status) {
            $this->setStatus($status);
        }
        if (null !== $statusReason) {
            $this->setStatusReason($statusReason);
        }
        if (null !== $doNotPerform) {
            $this->setDoNotPerform($doNotPerform);
        }
        if (null !== $scheduledTiming) {
            $this->setScheduledTiming($scheduledTiming);
        }
        if (null !== $scheduledPeriod) {
            $this->setScheduledPeriod($scheduledPeriod);
        }
        if (null !== $scheduledString) {
            $this->setScheduledString($scheduledString);
        }
        if (null !== $location) {
            $this->setLocation($location);
        }
        if (null !== $performer) {
            $this->setPerformer(...$performer);
        }
        if (null !== $productCodeableConcept) {
            $this->setProductCodeableConcept($productCodeableConcept);
        }
        if (null !== $productReference) {
            $this->setProductReference($productReference);
        }
        if (null !== $dailyAmount) {
            $this->setDailyAmount($dailyAmount);
        }
        if (null !== $quantity) {
            $this->setQuantity($quantity);
        }
        if (null !== $description) {
            $this->setDescription($description);
        }
    }

    /* class_default.php:145 */
    public function _getFHIRTypeName(): string
    {
        return self::FHIR_TYPE_NAME;
    }

    /* class_default.php:174 */
    /**
     * Resource types defined as part of FHIR that can be represented as in-line
     * definitions of a care plan activity.
     * If the element is present, it must have either a \@value, an \@id, or extensions
     *
     * A description of the kind of resource the in-line definition of a care plan
     * activity is representing. The CarePlan.activity.detail is an in-line definition
     * when a resource is not referenced using CarePlan.activity.reference. For
     * example, a MedicationRequest, a ServiceRequest, or a CommunicationRequest.
     *
     * @return null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRCarePlanActivityKind
     */
    public function getKind(): null|FHIRCarePlanActivityKind
    {
        return $this->kind ?? null;
    }

    /**
     * Resource types defined as part of FHIR that can be represented as in-line
     * definitions of a care plan activity.
     * If the element is present, it must have either a \@value, an \@id, or extensions
     *
     * A description of the kind of resource the in-line definition of a care plan
     * activity is representing. The CarePlan.activity.detail is an in-line definition
     * when a resource is not referenced using CarePlan.activity.reference. For
     * example, a MedicationRequest, a ServiceRequest, or a CommunicationRequest.
     *
     * @param null|string|\OpenEMR\FHIR\Versions\R4\Types\FHIRCodePrimitive\FHIRCarePlanActivityKindList|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRCarePlanActivityKind $kind
     * @return static
     */
    public function setKind(null|string|FHIRCarePlanActivityKindList|FHIRCarePlanActivityKind $kind): self
    {
        if (null === $kind) {
            unset($this->kind);
            return $this;
        }
        if (!($kind instanceof FHIRCarePlanActivityKind)) {
            $kind = new FHIRCarePlanActivityKind(value: $kind);
        }
        $this->kind = $kind;
        return $this;
    }

    /**
     * A URI that is a reference to a canonical URL on a FHIR resource
     * see [Canonical References](references.html#canonical)
     * If the element is present, it must have either a \@value, an \@id referenced from
     * the Narrative, or extensions
     *
     * The URL pointing to a FHIR-defined protocol, guideline, questionnaire or other
     * definition that is adhered to in whole or in part by this CarePlan activity.
     *
     * @return iterable<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRCanonical>
     */
    public function getInstantiatesCanonical(): array
    {
        return $this->instantiatesCanonical ?? [];
    }

    /**
     * @return \ArrayIterator<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRCanonical>
     */
    public function getInstantiatesCanonicalIterator(): iterable
    {
        if (!isset($this->instantiatesCanonical)) {
            return new \EmptyIterator();
        }
        return new \ArrayIterator($this->instantiatesCanonical);
    }

    /**
     * A URI that is a reference to a canonical URL on a FHIR resource
     * see [Canonical References](references.html#canonical)
     * If the element is present, it must have either a \@value, an \@id referenced from
     * the Narrative, or extensions
     *
     * The URL pointing to a FHIR-defined protocol, guideline, questionnaire or other
     * definition that is adhered to in whole or in part by this CarePlan activity.
     *
     * @param string|\OpenEMR\FHIR\Versions\R4\Types\FHIRCanonicalPrimitive|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRCanonical $instantiatesCanonical
     * @return static
     */
    public function addInstantiatesCanonical(string|FHIRCanonicalPrimitive|FHIRCanonical $instantiatesCanonical): self
    {
        if (!($instantiatesCanonical instanceof FHIRCanonical)) {
            $instantiatesCanonical = new FHIRCanonical(value: $instantiatesCanonical);
        }
        if (!isset($this->instantiatesCanonical)) {
            $this->instantiatesCanonical = [];
        }
        $this->instantiatesCanonical[] = $instantiatesCanonical;
        return $this;
    }

    /**
     * A URI that is a reference to a canonical URL on a FHIR resource
     * see [Canonical References](references.html#canonical)
     * If the element is present, it must have either a \@value, an \@id referenced from
     * the Narrative, or extensions
     *
     * The URL pointing to a FHIR-defined protocol, guideline, questionnaire or other
     * definition that is adhered to in whole or in part by this CarePlan activity.
     *
     * @param string|\OpenEMR\FHIR\Versions\R4\Types\FHIRCanonicalPrimitive|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRCanonical ...$instantiatesCanonical
     * @return static
     */
    public function setInstantiatesCanonical(string|FHIRCanonicalPrimitive|FHIRCanonical ...$instantiatesCanonical): self
    {
        if ([] === $instantiatesCanonical) {
            unset($this->instantiatesCanonical);
            return $this;
        }
        $this->instantiatesCanonical = [];
        foreach($instantiatesCanonical as $v) {
            if ($v instanceof FHIRCanonical) {
                $this->instantiatesCanonical[] = $v;
            } else {
                $this->instantiatesCanonical[] = new FHIRCanonical(value: $v);
            }
        }
        return $this;
    }

    /**
     * String of characters used to identify a name or a resource
     * see http://en.wikipedia.org/wiki/Uniform_resource_identifier
     * If the element is present, it must have either a \@value, an \@id, or extensions
     *
     * The URL pointing to an externally maintained protocol, guideline, questionnaire
     * or other definition that is adhered to in whole or in part by this CarePlan
     * activity.
     *
     * @return iterable<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRUri>
     */
    public function getInstantiatesUri(): array
    {
        return $this->instantiatesUri ?? [];
    }

    /**
     * @return \ArrayIterator<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRUri>
     */
    public function getInstantiatesUriIterator(): iterable
    {
        if (!isset($this->instantiatesUri)) {
            return new \EmptyIterator();
        }
        return new \ArrayIterator($this->instantiatesUri);
    }

    /**
     * String of characters used to identify a name or a resource
     * see http://en.wikipedia.org/wiki/Uniform_resource_identifier
     * If the element is present, it must have either a \@value, an \@id, or extensions
     *
     * The URL pointing to an externally maintained protocol, guideline, questionnaire
     * or other definition that is adhered to in whole or in part by this CarePlan
     * activity.
     *
     * @param string|\OpenEMR\FHIR\Versions\R4\Types\FHIRUriPrimitive|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRUri $instantiatesUri
     * @return static
     */
    public function addInstantiatesUri(string|FHIRUriPrimitive|FHIRUri $instantiatesUri): self
    {
        if (!($instantiatesUri instanceof FHIRUri)) {
            $instantiatesUri = new FHIRUri(value: $instantiatesUri);
        }
        if (!isset($this->instantiatesUri)) {
            $this->instantiatesUri = [];
        }
        $this->instantiatesUri[] = $instantiatesUri;
        return $this;
    }

    /**
     * String of characters used to identify a name or a resource
     * see http://en.wikipedia.org/wiki/Uniform_resource_identifier
     * If the element is present, it must have either a \@value, an \@id, or extensions
     *
     * The URL pointing to an externally maintained protocol, guideline, questionnaire
     * or other definition that is adhered to in whole or in part by this CarePlan
     * activity.
     *
     * @param string|\OpenEMR\FHIR\Versions\R4\Types\FHIRUriPrimitive|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRUri ...$instantiatesUri
     * @return static
     */
    public function setInstantiatesUri(string|FHIRUriPrimitive|FHIRUri ...$instantiatesUri): self
    {
        if ([] === $instantiatesUri) {
            unset($this->instantiatesUri);
            return $this;
        }
        $this->instantiatesUri = [];
        foreach($instantiatesUri as $v) {
            if ($v instanceof FHIRUri) {
                $this->instantiatesUri[] = $v;
            } else {
                $this->instantiatesUri[] = new FHIRUri(value: $v);
            }
        }
        return $this;
    }

    /**
     * A concept that may be defined by a formal reference to a terminology or ontology
     * or may be provided by text.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * Detailed description of the type of planned activity; e.g. what lab test, what
     * procedure, what kind of encounter.
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
     * Detailed description of the type of planned activity; e.g. what lab test, what
     * procedure, what kind of encounter.
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
     * Provides the rationale that drove the inclusion of this particular activity as
     * part of the plan or the reason why the activity was prohibited.
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
     * Provides the rationale that drove the inclusion of this particular activity as
     * part of the plan or the reason why the activity was prohibited.
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
     * Provides the rationale that drove the inclusion of this particular activity as
     * part of the plan or the reason why the activity was prohibited.
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
     * Indicates another resource, such as the health condition(s), whose existence
     * justifies this request and drove the inclusion of this particular activity as
     * part of the plan.
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
     * Indicates another resource, such as the health condition(s), whose existence
     * justifies this request and drove the inclusion of this particular activity as
     * part of the plan.
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
     * Indicates another resource, such as the health condition(s), whose existence
     * justifies this request and drove the inclusion of this particular activity as
     * part of the plan.
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
     * A reference from one resource to another.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * Internal reference that identifies the goals that this activity is intended to
     * contribute towards meeting.
     *
     * @return iterable<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRReference>
     */
    public function getGoal(): array
    {
        return $this->goal ?? [];
    }

    /**
     * @return \ArrayIterator<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRReference>
     */
    public function getGoalIterator(): iterable
    {
        if (!isset($this->goal)) {
            return new \EmptyIterator();
        }
        return new \ArrayIterator($this->goal);
    }

    /**
     * A reference from one resource to another.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * Internal reference that identifies the goals that this activity is intended to
     * contribute towards meeting.
     *
     * @param \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRReference $goal
     * @return static
     */
    public function addGoal(FHIRReference $goal): self
    {
        if (!isset($this->goal)) {
            $this->goal = [];
        }
        $this->goal[] = $goal;
        return $this;
    }

    /**
     * A reference from one resource to another.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * Internal reference that identifies the goals that this activity is intended to
     * contribute towards meeting.
     *
     * @param \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRReference ...$goal
     * @return static
     */
    public function setGoal(FHIRReference ...$goal): self
    {
        if ([] === $goal) {
            unset($this->goal);
            return $this;
        }
        $this->goal = $goal;
        return $this;
    }

    /**
     * Codes that reflect the current state of a care plan activity within its overall
     * life cycle.
     * If the element is present, it must have either a \@value, an \@id, or extensions
     *
     * Identifies what progress is being made for the specific activity.
     *
     * @return null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRCarePlanActivityStatus
     */
    public function getStatus(): null|FHIRCarePlanActivityStatus
    {
        return $this->status ?? null;
    }

    /**
     * Codes that reflect the current state of a care plan activity within its overall
     * life cycle.
     * If the element is present, it must have either a \@value, an \@id, or extensions
     *
     * Identifies what progress is being made for the specific activity.
     *
     * @param null|string|\OpenEMR\FHIR\Versions\R4\Types\FHIRCodePrimitive\FHIRCarePlanActivityStatusList|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRCarePlanActivityStatus $status
     * @return static
     */
    public function setStatus(null|string|FHIRCarePlanActivityStatusList|FHIRCarePlanActivityStatus $status): self
    {
        if (null === $status) {
            unset($this->status);
            return $this;
        }
        if (!($status instanceof FHIRCarePlanActivityStatus)) {
            $status = new FHIRCarePlanActivityStatus(value: $status);
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
     * Provides reason why the activity isn't yet started, is on hold, was cancelled,
     * etc.
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
     * Provides reason why the activity isn't yet started, is on hold, was cancelled,
     * etc.
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
     * Value of "true" or "false"
     * If the element is present, it must have either a \@value, an \@id, or extensions
     *
     * If true, indicates that the described activity is one that must NOT be engaged
     * in when following the plan. If false, or missing, indicates that the described
     * activity is one that should be engaged in when following the plan.
     *
     * @return null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBoolean
     */
    public function getDoNotPerform(): null|FHIRBoolean
    {
        return $this->doNotPerform ?? null;
    }

    /**
     * Value of "true" or "false"
     * If the element is present, it must have either a \@value, an \@id, or extensions
     *
     * If true, indicates that the described activity is one that must NOT be engaged
     * in when following the plan. If false, or missing, indicates that the described
     * activity is one that should be engaged in when following the plan.
     *
     * @param null|string|bool|\OpenEMR\FHIR\Versions\R4\Types\FHIRBooleanPrimitive|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBoolean $doNotPerform
     * @return static
     */
    public function setDoNotPerform(null|string|bool|FHIRBooleanPrimitive|FHIRBoolean $doNotPerform): self
    {
        if (null === $doNotPerform) {
            unset($this->doNotPerform);
            return $this;
        }
        if (!($doNotPerform instanceof FHIRBoolean)) {
            $doNotPerform = new FHIRBoolean(value: $doNotPerform);
        }
        $this->doNotPerform = $doNotPerform;
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
     * The period, timing or frequency upon which the described activity is to occur.
     * (choose any one of scheduled*, but only one)
     *
     * @return null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRTiming
     */
    public function getScheduledTiming(): null|FHIRTiming
    {
        return $this->scheduledTiming ?? null;
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
     * The period, timing or frequency upon which the described activity is to occur.
     * (choose any one of scheduled*, but only one)
     *
     * @param null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRTiming $scheduledTiming
     * @return static
     */
    public function setScheduledTiming(null|FHIRTiming $scheduledTiming): self
    {
        if (null === $scheduledTiming) {
            unset($this->scheduledTiming);
            return $this;
        }
        $this->scheduledTiming = $scheduledTiming;
        return $this;
    }

    /**
     * A time period defined by a start and end date and optionally time.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * The period, timing or frequency upon which the described activity is to occur.
     * (choose any one of scheduled*, but only one)
     *
     * @return null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRPeriod
     */
    public function getScheduledPeriod(): null|FHIRPeriod
    {
        return $this->scheduledPeriod ?? null;
    }

    /**
     * A time period defined by a start and end date and optionally time.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * The period, timing or frequency upon which the described activity is to occur.
     * (choose any one of scheduled*, but only one)
     *
     * @param null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRPeriod $scheduledPeriod
     * @return static
     */
    public function setScheduledPeriod(null|FHIRPeriod $scheduledPeriod): self
    {
        if (null === $scheduledPeriod) {
            unset($this->scheduledPeriod);
            return $this;
        }
        $this->scheduledPeriod = $scheduledPeriod;
        return $this;
    }

    /**
     * A sequence of Unicode characters
     * Note that FHIR strings SHALL NOT exceed 1MB in size
     * If the element is present, it must have either a \@value, an \@id, or extensions
     *
     * The period, timing or frequency upon which the described activity is to occur.
     * (choose any one of scheduled*, but only one)
     *
     * @return null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRString
     */
    public function getScheduledString(): null|FHIRString
    {
        return $this->scheduledString ?? null;
    }

    /**
     * A sequence of Unicode characters
     * Note that FHIR strings SHALL NOT exceed 1MB in size
     * If the element is present, it must have either a \@value, an \@id, or extensions
     *
     * The period, timing or frequency upon which the described activity is to occur.
     * (choose any one of scheduled*, but only one)
     *
     * @param null|string|\OpenEMR\FHIR\Versions\R4\Types\FHIRStringPrimitive|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRString $scheduledString
     * @return static
     */
    public function setScheduledString(null|string|FHIRStringPrimitive|FHIRString $scheduledString): self
    {
        if (null === $scheduledString) {
            unset($this->scheduledString);
            return $this;
        }
        if (!($scheduledString instanceof FHIRString)) {
            $scheduledString = new FHIRString(value: $scheduledString);
        }
        $this->scheduledString = $scheduledString;
        return $this;
    }

    /**
     * A reference from one resource to another.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * Identifies the facility where the activity will occur; e.g. home, hospital,
     * specific clinic, etc.
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
     * Identifies the facility where the activity will occur; e.g. home, hospital,
     * specific clinic, etc.
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
     * Identifies who's expected to be involved in the activity.
     *
     * @return iterable<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRReference>
     */
    public function getPerformer(): array
    {
        return $this->performer ?? [];
    }

    /**
     * @return \ArrayIterator<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRReference>
     */
    public function getPerformerIterator(): iterable
    {
        if (!isset($this->performer)) {
            return new \EmptyIterator();
        }
        return new \ArrayIterator($this->performer);
    }

    /**
     * A reference from one resource to another.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * Identifies who's expected to be involved in the activity.
     *
     * @param \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRReference $performer
     * @return static
     */
    public function addPerformer(FHIRReference $performer): self
    {
        if (!isset($this->performer)) {
            $this->performer = [];
        }
        $this->performer[] = $performer;
        return $this;
    }

    /**
     * A reference from one resource to another.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * Identifies who's expected to be involved in the activity.
     *
     * @param \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRReference ...$performer
     * @return static
     */
    public function setPerformer(FHIRReference ...$performer): self
    {
        if ([] === $performer) {
            unset($this->performer);
            return $this;
        }
        $this->performer = $performer;
        return $this;
    }

    /**
     * A concept that may be defined by a formal reference to a terminology or ontology
     * or may be provided by text.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * Identifies the food, drug or other product to be consumed or supplied in the
     * activity. (choose any one of product*, but only one)
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
     * Identifies the food, drug or other product to be consumed or supplied in the
     * activity. (choose any one of product*, but only one)
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
     * Identifies the food, drug or other product to be consumed or supplied in the
     * activity. (choose any one of product*, but only one)
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
     * Identifies the food, drug or other product to be consumed or supplied in the
     * activity. (choose any one of product*, but only one)
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
     * A measured amount (or an amount that can potentially be measured). Note that
     * measured amounts include amounts that are not precisely quantified, including
     * amounts involving arbitrary units and floating currencies.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * Identifies the quantity expected to be consumed in a given day.
     *
     * @return null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRQuantity
     */
    public function getDailyAmount(): null|FHIRQuantity
    {
        return $this->dailyAmount ?? null;
    }

    /**
     * A measured amount (or an amount that can potentially be measured). Note that
     * measured amounts include amounts that are not precisely quantified, including
     * amounts involving arbitrary units and floating currencies.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * Identifies the quantity expected to be consumed in a given day.
     *
     * @param null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRQuantity $dailyAmount
     * @return static
     */
    public function setDailyAmount(null|FHIRQuantity $dailyAmount): self
    {
        if (null === $dailyAmount) {
            unset($this->dailyAmount);
            return $this;
        }
        $this->dailyAmount = $dailyAmount;
        return $this;
    }

    /**
     * A measured amount (or an amount that can potentially be measured). Note that
     * measured amounts include amounts that are not precisely quantified, including
     * amounts involving arbitrary units and floating currencies.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * Identifies the quantity expected to be supplied, administered or consumed by the
     * subject.
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
     * Identifies the quantity expected to be supplied, administered or consumed by the
     * subject.
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
     * A sequence of Unicode characters
     * Note that FHIR strings SHALL NOT exceed 1MB in size
     * If the element is present, it must have either a \@value, an \@id, or extensions
     *
     * This provides a textual description of constraints on the intended activity
     * occurrence, including relation to other activities. It may also include
     * objectives, pre-conditions and end-conditions. Finally, it may convey specifics
     * about the activity such as body site, method, route, etc.
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
     * This provides a textual description of constraints on the intended activity
     * occurrence, including relation to other activities. It may also include
     * objectives, pre-conditions and end-conditions. Finally, it may convey specifics
     * about the activity such as body site, method, route, etc.
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

    /* class_default.php:201 */
    /**
     * @param \SimpleXMLElement $element
     * @param \OpenEMR\FHIR\Encoding\UnserializeConfig $config
     * @param null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRCarePlan\FHIRCarePlanDetail $type
     * @return \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRCarePlan\FHIRCarePlanDetail
     * @throws \Exception
     */
    public static function xmlUnserialize(\SimpleXMLElement $element,
                                          UnserializeConfig $config,
                                          null|ElementTypeInterface $type = null): self
    {
        if (null === $type) {
            $type = new static();
        } else if (!($type instanceof FHIRCarePlanDetail)) {
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
            } else if (self::FIELD_KIND === $cen) {
                $type->setKind(FHIRCarePlanActivityKind::xmlUnserialize($ce, $config));
            } else if (self::FIELD_INSTANTIATES_CANONICAL === $cen) {
                $type->addInstantiatesCanonical(FHIRCanonical::xmlUnserialize($ce, $config));
            } else if (self::FIELD_INSTANTIATES_URI === $cen) {
                $type->addInstantiatesUri(FHIRUri::xmlUnserialize($ce, $config));
            } else if (self::FIELD_CODE === $cen) {
                $type->setCode(FHIRCodeableConcept::xmlUnserialize($ce, $config));
            } else if (self::FIELD_REASON_CODE === $cen) {
                $type->addReasonCode(FHIRCodeableConcept::xmlUnserialize($ce, $config));
            } else if (self::FIELD_REASON_REFERENCE === $cen) {
                $type->addReasonReference(FHIRReference::xmlUnserialize($ce, $config));
            } else if (self::FIELD_GOAL === $cen) {
                $type->addGoal(FHIRReference::xmlUnserialize($ce, $config));
            } else if (self::FIELD_STATUS === $cen) {
                $type->setStatus(FHIRCarePlanActivityStatus::xmlUnserialize($ce, $config));
            } else if (self::FIELD_STATUS_REASON === $cen) {
                $type->setStatusReason(FHIRCodeableConcept::xmlUnserialize($ce, $config));
            } else if (self::FIELD_DO_NOT_PERFORM === $cen) {
                $type->setDoNotPerform(FHIRBoolean::xmlUnserialize($ce, $config));
            } else if (self::FIELD_SCHEDULED_TIMING === $cen) {
                $type->setScheduledTiming(FHIRTiming::xmlUnserialize($ce, $config));
            } else if (self::FIELD_SCHEDULED_PERIOD === $cen) {
                $type->setScheduledPeriod(FHIRPeriod::xmlUnserialize($ce, $config));
            } else if (self::FIELD_SCHEDULED_STRING === $cen) {
                $type->setScheduledString(FHIRString::xmlUnserialize($ce, $config));
            } else if (self::FIELD_LOCATION === $cen) {
                $type->setLocation(FHIRReference::xmlUnserialize($ce, $config));
            } else if (self::FIELD_PERFORMER === $cen) {
                $type->addPerformer(FHIRReference::xmlUnserialize($ce, $config));
            } else if (self::FIELD_PRODUCT_CODEABLE_CONCEPT === $cen) {
                $type->setProductCodeableConcept(FHIRCodeableConcept::xmlUnserialize($ce, $config));
            } else if (self::FIELD_PRODUCT_REFERENCE === $cen) {
                $type->setProductReference(FHIRReference::xmlUnserialize($ce, $config));
            } else if (self::FIELD_DAILY_AMOUNT === $cen) {
                $type->setDailyAmount(FHIRQuantity::xmlUnserialize($ce, $config));
            } else if (self::FIELD_QUANTITY === $cen) {
                $type->setQuantity(FHIRQuantity::xmlUnserialize($ce, $config));
            } else if (self::FIELD_DESCRIPTION === $cen) {
                $type->setDescription(FHIRString::xmlUnserialize($ce, $config));
            }
        }
        $attributes = $element->attributes();
        if (isset($attributes[self::FIELD_ID])) {
            $type->setId((string)$attributes[self::FIELD_ID]);
            $type->_setXMLFieldValueLocation(self::FIELD_ID, ValueXMLLocationEnum::PARENT_ATTRIBUTE);
        }
        if (isset($attributes[self::FIELD_KIND])) {
            if (isset($type->kind)) {
                $type->kind->setValue((string)$attributes[self::FIELD_KIND]);
            } else {
                $type->setKind((string)$attributes[self::FIELD_KIND]);
            }
            $type->_setXMLFieldValueLocation(self::FIELD_KIND, ValueXMLLocationEnum::PARENT_ATTRIBUTE);
        }
        if (isset($attributes[self::FIELD_STATUS])) {
            if (isset($type->status)) {
                $type->status->setValue((string)$attributes[self::FIELD_STATUS]);
            } else {
                $type->setStatus((string)$attributes[self::FIELD_STATUS]);
            }
            $type->_setXMLFieldValueLocation(self::FIELD_STATUS, ValueXMLLocationEnum::PARENT_ATTRIBUTE);
        }
        if (isset($attributes[self::FIELD_DO_NOT_PERFORM])) {
            if (isset($type->doNotPerform)) {
                $type->doNotPerform->setValue((string)$attributes[self::FIELD_DO_NOT_PERFORM]);
            } else {
                $type->setDoNotPerform((string)$attributes[self::FIELD_DO_NOT_PERFORM]);
            }
            $type->_setXMLFieldValueLocation(self::FIELD_DO_NOT_PERFORM, ValueXMLLocationEnum::PARENT_ATTRIBUTE);
        }
        if (isset($attributes[self::FIELD_SCHEDULED_STRING])) {
            if (isset($type->scheduledString)) {
                $type->scheduledString->setValue((string)$attributes[self::FIELD_SCHEDULED_STRING]);
            } else {
                $type->setScheduledString((string)$attributes[self::FIELD_SCHEDULED_STRING]);
            }
            $type->_setXMLFieldValueLocation(self::FIELD_SCHEDULED_STRING, ValueXMLLocationEnum::PARENT_ATTRIBUTE);
        }
        if (isset($attributes[self::FIELD_DESCRIPTION])) {
            if (isset($type->description)) {
                $type->description->setValue((string)$attributes[self::FIELD_DESCRIPTION]);
            } else {
                $type->setDescription((string)$attributes[self::FIELD_DESCRIPTION]);
            }
            $type->_setXMLFieldValueLocation(self::FIELD_DESCRIPTION, ValueXMLLocationEnum::PARENT_ATTRIBUTE);
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
        if (isset($this->kind) && ValueXMLLocationEnum::PARENT_ATTRIBUTE === $this->_valueXMLLocations[self::FIELD_KIND]) {
            $xw->writeAttribute(self::FIELD_KIND, $this->kind->_getValueAsString());
        }
        if (isset($this->status) && ValueXMLLocationEnum::PARENT_ATTRIBUTE === $this->_valueXMLLocations[self::FIELD_STATUS]) {
            $xw->writeAttribute(self::FIELD_STATUS, $this->status->_getValueAsString());
        }
        if (isset($this->doNotPerform) && ValueXMLLocationEnum::PARENT_ATTRIBUTE === $this->_valueXMLLocations[self::FIELD_DO_NOT_PERFORM]) {
            $xw->writeAttribute(self::FIELD_DO_NOT_PERFORM, $this->doNotPerform->_getValueAsString());
        }
        if (isset($this->scheduledString) && ValueXMLLocationEnum::PARENT_ATTRIBUTE === $this->_valueXMLLocations[self::FIELD_SCHEDULED_STRING]) {
            $xw->writeAttribute(self::FIELD_SCHEDULED_STRING, $this->scheduledString->_getValueAsString());
        }
        if (isset($this->description) && ValueXMLLocationEnum::PARENT_ATTRIBUTE === $this->_valueXMLLocations[self::FIELD_DESCRIPTION]) {
            $xw->writeAttribute(self::FIELD_DESCRIPTION, $this->description->_getValueAsString());
        }
        parent::xmlSerialize($xw, $config);
        if (isset($this->kind)
            && (ValueXMLLocationEnum::PARENT_ATTRIBUTE !== $this->_valueXMLLocations[self::FIELD_KIND]
                || $this->kind->_nonValueFieldDefined())) {
            $xw->startElement(self::FIELD_KIND);
            $this->kind->xmlSerialize($xw, $config, $this->_valueXMLLocations[self::FIELD_KIND]);
            $xw->endElement();
        }
        if (isset($this->instantiatesCanonical) && [] !== $this->instantiatesCanonical) {
            foreach($this->instantiatesCanonical as $v) {
                $xw->startElement(self::FIELD_INSTANTIATES_CANONICAL);
                $v->xmlSerialize($xw, $config);
                $xw->endElement();
            }
        }
        if (isset($this->instantiatesUri) && [] !== $this->instantiatesUri) {
            foreach($this->instantiatesUri as $v) {
                $xw->startElement(self::FIELD_INSTANTIATES_URI);
                $v->xmlSerialize($xw, $config);
                $xw->endElement();
            }
        }
        if (isset($this->code)) {
            $xw->startElement(self::FIELD_CODE);
            $this->code->xmlSerialize($xw, $config);
            $xw->endElement();
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
        if (isset($this->goal)) {
            foreach ($this->goal as $v) {
                $xw->startElement(self::FIELD_GOAL);
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
        if (isset($this->doNotPerform)
            && (ValueXMLLocationEnum::PARENT_ATTRIBUTE !== $this->_valueXMLLocations[self::FIELD_DO_NOT_PERFORM]
                || $this->doNotPerform->_nonValueFieldDefined())) {
            $xw->startElement(self::FIELD_DO_NOT_PERFORM);
            $this->doNotPerform->xmlSerialize($xw, $config, $this->_valueXMLLocations[self::FIELD_DO_NOT_PERFORM]);
            $xw->endElement();
        }
        if (isset($this->scheduledTiming)) {
            $xw->startElement(self::FIELD_SCHEDULED_TIMING);
            $this->scheduledTiming->xmlSerialize($xw, $config);
            $xw->endElement();
        }
        if (isset($this->scheduledPeriod)) {
            $xw->startElement(self::FIELD_SCHEDULED_PERIOD);
            $this->scheduledPeriod->xmlSerialize($xw, $config);
            $xw->endElement();
        }
        if (isset($this->scheduledString)
            && (ValueXMLLocationEnum::PARENT_ATTRIBUTE !== $this->_valueXMLLocations[self::FIELD_SCHEDULED_STRING]
                || $this->scheduledString->_nonValueFieldDefined())) {
            $xw->startElement(self::FIELD_SCHEDULED_STRING);
            $this->scheduledString->xmlSerialize($xw, $config, $this->_valueXMLLocations[self::FIELD_SCHEDULED_STRING]);
            $xw->endElement();
        }
        if (isset($this->location)) {
            $xw->startElement(self::FIELD_LOCATION);
            $this->location->xmlSerialize($xw, $config);
            $xw->endElement();
        }
        if (isset($this->performer)) {
            foreach ($this->performer as $v) {
                $xw->startElement(self::FIELD_PERFORMER);
                $v->xmlSerialize($xw, $config);
                $xw->endElement();
            }
        }
        if (isset($this->productCodeableConcept)) {
            $xw->startElement(self::FIELD_PRODUCT_CODEABLE_CONCEPT);
            $this->productCodeableConcept->xmlSerialize($xw, $config);
            $xw->endElement();
        }
        if (isset($this->productReference)) {
            $xw->startElement(self::FIELD_PRODUCT_REFERENCE);
            $this->productReference->xmlSerialize($xw, $config);
            $xw->endElement();
        }
        if (isset($this->dailyAmount)) {
            $xw->startElement(self::FIELD_DAILY_AMOUNT);
            $this->dailyAmount->xmlSerialize($xw, $config);
            $xw->endElement();
        }
        if (isset($this->quantity)) {
            $xw->startElement(self::FIELD_QUANTITY);
            $this->quantity->xmlSerialize($xw, $config);
            $xw->endElement();
        }
        if (isset($this->description)
            && (ValueXMLLocationEnum::PARENT_ATTRIBUTE !== $this->_valueXMLLocations[self::FIELD_DESCRIPTION]
                || $this->description->_nonValueFieldDefined())) {
            $xw->startElement(self::FIELD_DESCRIPTION);
            $this->description->xmlSerialize($xw, $config, $this->_valueXMLLocations[self::FIELD_DESCRIPTION]);
            $xw->endElement();
        }
    }

    /**
     * @param \stdClass $decoded
     * @param \OpenEMR\FHIR\Encoding\UnserializeConfig $config
     * @param null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRCarePlan\FHIRCarePlanDetail $type
     * @return \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRCarePlan\FHIRCarePlanDetail
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
        } else if (!($type instanceof FHIRCarePlanDetail)) {
            throw new \RuntimeException(sprintf(
                '%s::jsonUnserialize - $type must be instance of \\%s or null, %s seen.',
                ltrim(substr(self::class, (int)strrpos(self::class, '\\')), '\\'),
                static::class,
                $type::class
            ));
        }
        parent::jsonUnserialize($decoded, $config, $type);
        if (isset($decoded->kind)
            || isset($decoded->_kind)
            || property_exists($decoded, self::FIELD_KIND)
            || property_exists($decoded, self::FIELD_KIND_EXT)) {
            $v = $decoded->_kind ?? new \stdClass();
            $v->value = $decoded->kind ?? null;
            $type->setKind(FHIRCarePlanActivityKind::jsonUnserialize($v, $config));
        }
        if (isset($decoded->instantiatesCanonical)
            || isset($decoded->_instantiatesCanonical)
            || property_exists($decoded, self::FIELD_INSTANTIATES_CANONICAL)
            || property_exists($decoded, self::FIELD_INSTANTIATES_CANONICAL_EXT)) {
            $vals = (array)($decoded->instantiatesCanonical ?? []);
            $exts = (array)($decoded->_instantiatesCanonical ?? []);
            $valCnt = count($vals);
            $extCnt = count($exts);
            if ($extCnt > $valCnt) {
                $valCnt = $extCnt;
            }
            for ($i = 0; $i < $valCnt; $i++) {
                $v = $exts[$i] ?? new \stdClass();
                $v->value = $vals[$i] ?? null;
                $type->addInstantiatesCanonical(FHIRCanonical::jsonUnserialize($v, $config));
            }
        }
        if (isset($decoded->instantiatesUri)
            || isset($decoded->_instantiatesUri)
            || property_exists($decoded, self::FIELD_INSTANTIATES_URI)
            || property_exists($decoded, self::FIELD_INSTANTIATES_URI_EXT)) {
            $vals = (array)($decoded->instantiatesUri ?? []);
            $exts = (array)($decoded->_instantiatesUri ?? []);
            $valCnt = count($vals);
            $extCnt = count($exts);
            if ($extCnt > $valCnt) {
                $valCnt = $extCnt;
            }
            for ($i = 0; $i < $valCnt; $i++) {
                $v = $exts[$i] ?? new \stdClass();
                $v->value = $vals[$i] ?? null;
                $type->addInstantiatesUri(FHIRUri::jsonUnserialize($v, $config));
            }
        }
        if (isset($decoded->code) || property_exists($decoded, self::FIELD_CODE)) {
            if (is_array($decoded->code)) {
                $type->setCode(FHIRCodeableConcept::jsonUnserialize(reset($decoded->code), $config));
            } else {
                $type->setCode(FHIRCodeableConcept::jsonUnserialize($decoded->code, $config));
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
        if (isset($decoded->goal) || property_exists($decoded, self::FIELD_GOAL)) {
            if (is_object($decoded->goal)) {
                $vals = [$decoded->goal];
                $type->_setJSONFieldElideSingletonArray(self::FIELD_GOAL, true);
            } else {
                $vals = $decoded->goal;
            }
            foreach($vals as $v) {
                $type->addGoal(FHIRReference::jsonUnserialize($v, $config));
            }
        }
        if (isset($decoded->status)
            || isset($decoded->_status)
            || property_exists($decoded, self::FIELD_STATUS)
            || property_exists($decoded, self::FIELD_STATUS_EXT)) {
            $v = $decoded->_status ?? new \stdClass();
            $v->value = $decoded->status ?? null;
            $type->setStatus(FHIRCarePlanActivityStatus::jsonUnserialize($v, $config));
        }
        if (isset($decoded->statusReason) || property_exists($decoded, self::FIELD_STATUS_REASON)) {
            if (is_array($decoded->statusReason)) {
                $type->setStatusReason(FHIRCodeableConcept::jsonUnserialize(reset($decoded->statusReason), $config));
            } else {
                $type->setStatusReason(FHIRCodeableConcept::jsonUnserialize($decoded->statusReason, $config));
            }
        }
        if (isset($decoded->doNotPerform)
            || isset($decoded->_doNotPerform)
            || property_exists($decoded, self::FIELD_DO_NOT_PERFORM)
            || property_exists($decoded, self::FIELD_DO_NOT_PERFORM_EXT)) {
            $v = $decoded->_doNotPerform ?? new \stdClass();
            $v->value = $decoded->doNotPerform ?? null;
            $type->setDoNotPerform(FHIRBoolean::jsonUnserialize($v, $config));
        }
        if (isset($decoded->scheduledTiming) || property_exists($decoded, self::FIELD_SCHEDULED_TIMING)) {
            if (is_array($decoded->scheduledTiming)) {
                $type->setScheduledTiming(FHIRTiming::jsonUnserialize(reset($decoded->scheduledTiming), $config));
            } else {
                $type->setScheduledTiming(FHIRTiming::jsonUnserialize($decoded->scheduledTiming, $config));
            }
        }
        if (isset($decoded->scheduledPeriod) || property_exists($decoded, self::FIELD_SCHEDULED_PERIOD)) {
            if (is_array($decoded->scheduledPeriod)) {
                $type->setScheduledPeriod(FHIRPeriod::jsonUnserialize(reset($decoded->scheduledPeriod), $config));
            } else {
                $type->setScheduledPeriod(FHIRPeriod::jsonUnserialize($decoded->scheduledPeriod, $config));
            }
        }
        if (isset($decoded->scheduledString)
            || isset($decoded->_scheduledString)
            || property_exists($decoded, self::FIELD_SCHEDULED_STRING)
            || property_exists($decoded, self::FIELD_SCHEDULED_STRING_EXT)) {
            $v = $decoded->_scheduledString ?? new \stdClass();
            $v->value = $decoded->scheduledString ?? null;
            $type->setScheduledString(FHIRString::jsonUnserialize($v, $config));
        }
        if (isset($decoded->location) || property_exists($decoded, self::FIELD_LOCATION)) {
            if (is_array($decoded->location)) {
                $type->setLocation(FHIRReference::jsonUnserialize(reset($decoded->location), $config));
            } else {
                $type->setLocation(FHIRReference::jsonUnserialize($decoded->location, $config));
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
                $type->addPerformer(FHIRReference::jsonUnserialize($v, $config));
            }
        }
        if (isset($decoded->productCodeableConcept) || property_exists($decoded, self::FIELD_PRODUCT_CODEABLE_CONCEPT)) {
            if (is_array($decoded->productCodeableConcept)) {
                $type->setProductCodeableConcept(FHIRCodeableConcept::jsonUnserialize(reset($decoded->productCodeableConcept), $config));
            } else {
                $type->setProductCodeableConcept(FHIRCodeableConcept::jsonUnserialize($decoded->productCodeableConcept, $config));
            }
        }
        if (isset($decoded->productReference) || property_exists($decoded, self::FIELD_PRODUCT_REFERENCE)) {
            if (is_array($decoded->productReference)) {
                $type->setProductReference(FHIRReference::jsonUnserialize(reset($decoded->productReference), $config));
            } else {
                $type->setProductReference(FHIRReference::jsonUnserialize($decoded->productReference, $config));
            }
        }
        if (isset($decoded->dailyAmount) || property_exists($decoded, self::FIELD_DAILY_AMOUNT)) {
            if (is_array($decoded->dailyAmount)) {
                $type->setDailyAmount(FHIRQuantity::jsonUnserialize(reset($decoded->dailyAmount), $config));
            } else {
                $type->setDailyAmount(FHIRQuantity::jsonUnserialize($decoded->dailyAmount, $config));
            }
        }
        if (isset($decoded->quantity) || property_exists($decoded, self::FIELD_QUANTITY)) {
            if (is_array($decoded->quantity)) {
                $type->setQuantity(FHIRQuantity::jsonUnserialize(reset($decoded->quantity), $config));
            } else {
                $type->setQuantity(FHIRQuantity::jsonUnserialize($decoded->quantity, $config));
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
        return $type;
    }

    /**
     * @return \stdClass
     */
    public function jsonSerialize(): mixed
    {
        $out = parent::jsonSerialize();
        if (isset($this->kind)) {
            if (null !== ($val = $this->kind->getValue())) {
                $out->kind = $val;
            }
            if ($this->kind->_nonValueFieldDefined()) {
                $ext = $this->kind->jsonSerialize();
                unset($ext->value);
                $out->_kind = $ext;
            }
        }
        if (isset($this->instantiatesCanonical) && [] !== $this->instantiatesCanonical) {
            $vals = [];
            $exts = [];
            $hasVals = false;
            $hasExts = false;
            foreach ($this->instantiatesCanonical as $v) {
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
                $out->instantiatesCanonical = $vals;
            }
            if ($hasExts) {
                $out->_instantiatesCanonical = $exts;
            }
        }
        if (isset($this->instantiatesUri) && [] !== $this->instantiatesUri) {
            $vals = [];
            $exts = [];
            $hasVals = false;
            $hasExts = false;
            foreach ($this->instantiatesUri as $v) {
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
                $out->instantiatesUri = $vals;
            }
            if ($hasExts) {
                $out->_instantiatesUri = $exts;
            }
        }
        if (isset($this->code)) {
            $out->code = $this->code;
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
        if (isset($this->goal) && [] !== $this->goal) {
            if ($this->_getJSONFieldElideSingletonArray(self::FIELD_GOAL) && 1 === count($this->goal)) {
                $out->goal = $this->goal[0];
            } else {
                $out->goal = $this->goal;
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
        if (isset($this->doNotPerform)) {
            if (null !== ($val = $this->doNotPerform->getValue())) {
                $out->doNotPerform = $val;
            }
            if ($this->doNotPerform->_nonValueFieldDefined()) {
                $ext = $this->doNotPerform->jsonSerialize();
                unset($ext->value);
                $out->_doNotPerform = $ext;
            }
        }
        if (isset($this->scheduledTiming)) {
            $out->scheduledTiming = $this->scheduledTiming;
        }
        if (isset($this->scheduledPeriod)) {
            $out->scheduledPeriod = $this->scheduledPeriod;
        }
        if (isset($this->scheduledString)) {
            if (null !== ($val = $this->scheduledString->getValue())) {
                $out->scheduledString = $val;
            }
            if ($this->scheduledString->_nonValueFieldDefined()) {
                $ext = $this->scheduledString->jsonSerialize();
                unset($ext->value);
                $out->_scheduledString = $ext;
            }
        }
        if (isset($this->location)) {
            $out->location = $this->location;
        }
        if (isset($this->performer) && [] !== $this->performer) {
            if ($this->_getJSONFieldElideSingletonArray(self::FIELD_PERFORMER) && 1 === count($this->performer)) {
                $out->performer = $this->performer[0];
            } else {
                $out->performer = $this->performer;
            }
        }
        if (isset($this->productCodeableConcept)) {
            $out->productCodeableConcept = $this->productCodeableConcept;
        }
        if (isset($this->productReference)) {
            $out->productReference = $this->productReference;
        }
        if (isset($this->dailyAmount)) {
            $out->dailyAmount = $this->dailyAmount;
        }
        if (isset($this->quantity)) {
            $out->quantity = $this->quantity;
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
        return $out;
    }
}
