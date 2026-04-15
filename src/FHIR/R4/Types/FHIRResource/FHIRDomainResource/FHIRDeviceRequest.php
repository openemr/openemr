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
use OpenEMR\FHIR\Versions\R4\Types\FHIRCodePrimitive\FHIRRequestIntentList;
use OpenEMR\FHIR\Versions\R4\Types\FHIRCodePrimitive\FHIRRequestPriorityList;
use OpenEMR\FHIR\Versions\R4\Types\FHIRCodePrimitive\FHIRRequestStatusList;
use OpenEMR\FHIR\Versions\R4\Types\FHIRDateTimePrimitive;
use OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRAnnotation;
use OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRDeviceRequest\FHIRDeviceRequestParameter;
use OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRTiming;
use OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRCanonical;
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
use OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRRequestIntent;
use OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRRequestPriority;
use OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRRequestStatus;
use OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRUri;
use OpenEMR\FHIR\Versions\R4\Types\FHIRIdPrimitive;
use OpenEMR\FHIR\Versions\R4\Types\FHIRResource\FHIRDomainResource;
use OpenEMR\FHIR\Versions\R4\Types\FHIRUriPrimitive;
use OpenEMR\FHIR\Versions\R4\Version;
use OpenEMR\FHIR\Versions\R4\VersionConstants;
use OpenEMR\FHIR\Versions\R4\VersionContainedTypeInterface;
use OpenEMR\FHIR\Versions\R4\VersionTypeMap;

/**
 * Represents a request for a patient to employ a medical device. The device may be
 * an implantable device, or an external assistive device, such as a walker.
 * If the element is present, it must have either a \@value, an \@id, or extensions
 */
#[\Attribute(\Attribute::TARGET_PROPERTY)]
class FHIRDeviceRequest extends FHIRDomainResource implements VersionContainedTypeInterface
{
    use TypeValidationsTrait,
        JSONSerializationOptionsTrait,
        XMLSerializationOptionsTrait;

    // name of FHIR type this class describes
    public const FHIR_TYPE_NAME = VersionConstants::TYPE_NAME_DEVICE_REQUEST;

    /* class_default.php:56 */
    public const FIELD_IDENTIFIER = 'identifier';
    public const FIELD_INSTANTIATES_CANONICAL = 'instantiatesCanonical';
    public const FIELD_INSTANTIATES_CANONICAL_EXT = '_instantiatesCanonical';
    public const FIELD_INSTANTIATES_URI = 'instantiatesUri';
    public const FIELD_INSTANTIATES_URI_EXT = '_instantiatesUri';
    public const FIELD_BASED_ON = 'basedOn';
    public const FIELD_PRIOR_REQUEST = 'priorRequest';
    public const FIELD_GROUP_IDENTIFIER = 'groupIdentifier';
    public const FIELD_STATUS = 'status';
    public const FIELD_STATUS_EXT = '_status';
    public const FIELD_INTENT = 'intent';
    public const FIELD_INTENT_EXT = '_intent';
    public const FIELD_PRIORITY = 'priority';
    public const FIELD_PRIORITY_EXT = '_priority';
    public const FIELD_CODE_REFERENCE = 'codeReference';
    public const FIELD_CODE_CODEABLE_CONCEPT = 'codeCodeableConcept';
    public const FIELD_PARAMETER = 'parameter';
    public const FIELD_SUBJECT = 'subject';
    public const FIELD_ENCOUNTER = 'encounter';
    public const FIELD_OCCURRENCE_DATE_TIME = 'occurrenceDateTime';
    public const FIELD_OCCURRENCE_DATE_TIME_EXT = '_occurrenceDateTime';
    public const FIELD_OCCURRENCE_PERIOD = 'occurrencePeriod';
    public const FIELD_OCCURRENCE_TIMING = 'occurrenceTiming';
    public const FIELD_AUTHORED_ON = 'authoredOn';
    public const FIELD_AUTHORED_ON_EXT = '_authoredOn';
    public const FIELD_REQUESTER = 'requester';
    public const FIELD_PERFORMER_TYPE = 'performerType';
    public const FIELD_PERFORMER = 'performer';
    public const FIELD_REASON_CODE = 'reasonCode';
    public const FIELD_REASON_REFERENCE = 'reasonReference';
    public const FIELD_INSURANCE = 'insurance';
    public const FIELD_SUPPORTING_INFO = 'supportingInfo';
    public const FIELD_NOTE = 'note';
    public const FIELD_RELEVANT_HISTORY = 'relevantHistory';

    /* class_default.php:75 */
    private const _FHIR_VALIDATION_RULES = [
        self::FIELD_INTENT => [
            MinOccursRule::NAME => 1,
        ],
        self::FIELD_SUBJECT => [
            MinOccursRule::NAME => 1,
        ],
    ];

    /* class_default.php:96 */
    private array $_valueXMLLocations = [
        self::FIELD_STATUS => ValueXMLLocationEnum::CONTAINER_ATTRIBUTE,
        self::FIELD_INTENT => ValueXMLLocationEnum::CONTAINER_ATTRIBUTE,
        self::FIELD_PRIORITY => ValueXMLLocationEnum::CONTAINER_ATTRIBUTE,
        self::FIELD_OCCURRENCE_DATE_TIME => ValueXMLLocationEnum::CONTAINER_ATTRIBUTE,
        self::FIELD_AUTHORED_ON => ValueXMLLocationEnum::CONTAINER_ATTRIBUTE,
    ];

    /* class_default.php:112 */
    /**
     * An identifier - identifies some entity uniquely and unambiguously. Typically
     * this is used for business identifiers.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * Identifiers assigned to this order by the orderer or by the receiver.
     *
     * @var iterable<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRIdentifier>
     */
    #[FHIRIdentifier]
    protected array $identifier;
    /**
     * A URI that is a reference to a canonical URL on a FHIR resource
     * see [Canonical References](references.html#canonical)
     * If the element is present, it must have either a \@value, an \@id referenced from
     * the Narrative, or extensions
     *
     * The URL pointing to a FHIR-defined protocol, guideline, orderset or other
     * definition that is adhered to in whole or in part by this DeviceRequest.
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
     * The URL pointing to an externally maintained protocol, guideline, orderset or
     * other definition that is adhered to in whole or in part by this DeviceRequest.
     *
     * @var iterable<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRUri>
     */
    #[FHIRUri]
    protected array $instantiatesUri;
    /**
     * A reference from one resource to another.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * Plan/proposal/order fulfilled by this request.
     *
     * @var iterable<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRReference>
     */
    #[FHIRReference]
    protected array $basedOn;
    /**
     * A reference from one resource to another.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * The request takes the place of the referenced completed or terminated
     * request(s).
     *
     * @var iterable<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRReference>
     */
    #[FHIRReference]
    protected array $priorRequest;
    /**
     * An identifier - identifies some entity uniquely and unambiguously. Typically
     * this is used for business identifiers.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * Composite request this is part of.
     *
     * @var \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRIdentifier
     */
    #[FHIRIdentifier]
    protected FHIRIdentifier $groupIdentifier;
    /**
     * Indicates whether the plan is currently being acted upon, represents future
     * intentions or is now a historical record.
     * If the element is present, it must have either a \@value, an \@id, or extensions
     *
     * The status of the request.
     *
     * @var \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRRequestStatus
     */
    #[FHIRRequestStatus]
    protected FHIRRequestStatus $status;
    /**
     * Codes indicating the degree of authority/intentionality associated with a
     * request.
     * If the element is present, it must have either a \@value, an \@id, or extensions
     *
     * Whether the request is a proposal, plan, an original order or a reflex order.
     *
     * @var \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRRequestIntent
     */
    #[FHIRRequestIntent]
    protected FHIRRequestIntent $intent;
    /**
     * Identifies the level of importance to be assigned to actioning the request.
     * If the element is present, it must have either a \@value, an \@id, or extensions
     *
     * Indicates how quickly the {{title}} should be addressed with respect to other
     * requests.
     *
     * @var \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRRequestPriority
     */
    #[FHIRRequestPriority]
    protected FHIRRequestPriority $priority;
    /**
     * A reference from one resource to another.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * The details of the device to be used. (choose any one of code*, but only one)
     *
     * @var \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRReference
     */
    #[FHIRReference]
    protected FHIRReference $codeReference;
    /**
     * A concept that may be defined by a formal reference to a terminology or ontology
     * or may be provided by text.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * The details of the device to be used. (choose any one of code*, but only one)
     *
     * @var \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRCodeableConcept
     */
    #[FHIRCodeableConcept]
    protected FHIRCodeableConcept $codeCodeableConcept;
    /**
     * Represents a request for a patient to employ a medical device. The device may be
     * an implantable device, or an external assistive device, such as a walker.
     *
     * Specific parameters for the ordered item. For example, the prism value for
     * lenses.
     *
     * @var iterable<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRDeviceRequest\FHIRDeviceRequestParameter>
     */
    #[FHIRDeviceRequestParameter]
    protected array $parameter;
    /**
     * A reference from one resource to another.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * The patient who will use the device.
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
     * An encounter that provides additional context in which this request is made.
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
     * The timing schedule for the use of the device. The Schedule data type allows
     * many different expressions, for example. "Every 8 hours"; "Three times a day";
     * "1/2 an hour before breakfast for 10 days from 23-Dec 2011:"; "15 Oct 2013, 17
     * Oct 2013 and 1 Nov 2013". (choose any one of occurrence*, but only one)
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
     * The timing schedule for the use of the device. The Schedule data type allows
     * many different expressions, for example. "Every 8 hours"; "Three times a day";
     * "1/2 an hour before breakfast for 10 days from 23-Dec 2011:"; "15 Oct 2013, 17
     * Oct 2013 and 1 Nov 2013". (choose any one of occurrence*, but only one)
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
     * The timing schedule for the use of the device. The Schedule data type allows
     * many different expressions, for example. "Every 8 hours"; "Three times a day";
     * "1/2 an hour before breakfast for 10 days from 23-Dec 2011:"; "15 Oct 2013, 17
     * Oct 2013 and 1 Nov 2013". (choose any one of occurrence*, but only one)
     *
     * @var \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRTiming
     */
    #[FHIRTiming]
    protected FHIRTiming $occurrenceTiming;
    /**
     * A date, date-time or partial date (e.g. just year or year + month). If hours and
     * minutes are specified, a time zone SHALL be populated. The format is a union of
     * the schema types gYear, gYearMonth, date and dateTime. Seconds must be provided
     * due to schema type constraints but may be zero-filled and may be ignored. Dates
     * SHALL be valid dates.
     * If the element is present, it must have either a \@value, an \@id, or extensions
     *
     * When the request transitioned to being actionable.
     *
     * @var \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRDateTime
     */
    #[FHIRDateTime]
    protected FHIRDateTime $authoredOn;
    /**
     * A reference from one resource to another.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * The individual who initiated the request and has responsibility for its
     * activation.
     *
     * @var \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRReference
     */
    #[FHIRReference]
    protected FHIRReference $requester;
    /**
     * A concept that may be defined by a formal reference to a terminology or ontology
     * or may be provided by text.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * Desired type of performer for doing the diagnostic testing.
     *
     * @var \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRCodeableConcept
     */
    #[FHIRCodeableConcept]
    protected FHIRCodeableConcept $performerType;
    /**
     * A reference from one resource to another.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * The desired performer for doing the diagnostic testing.
     *
     * @var \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRReference
     */
    #[FHIRReference]
    protected FHIRReference $performer;
    /**
     * A concept that may be defined by a formal reference to a terminology or ontology
     * or may be provided by text.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * Reason or justification for the use of this device.
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
     * Reason or justification for the use of this device.
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
     * Insurance plans, coverage extensions, pre-authorizations and/or
     * pre-determinations that may be required for delivering the requested service.
     *
     * @var iterable<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRReference>
     */
    #[FHIRReference]
    protected array $insurance;
    /**
     * A reference from one resource to another.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * Additional clinical information about the patient that may influence the request
     * fulfilment. For example, this may include where on the subject's body the device
     * will be used (i.e. the target site).
     *
     * @var iterable<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRReference>
     */
    #[FHIRReference]
    protected array $supportingInfo;
    /**
     * A text note which also contains information about who made the statement and
     * when.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * Details about this request that were not represented at all or sufficiently in
     * one of the attributes provided in a class. These may include for example a
     * comment, an instruction, or a note associated with the statement.
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
     * Key events in the history of the request.
     *
     * @var iterable<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRReference>
     */
    #[FHIRReference]
    protected array $relevantHistory;

    /* constructor.php:61 */
    /**
     * FHIRDeviceRequest Constructor
     * @param null|string|\OpenEMR\FHIR\Versions\R4\Types\FHIRIdPrimitive|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRId $id
     * @param null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRMeta $meta
     * @param null|string|\OpenEMR\FHIR\Versions\R4\Types\FHIRUriPrimitive|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRUri $implicitRules
     * @param null|string|\OpenEMR\FHIR\Versions\R4\Types\FHIRCodePrimitive|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRCode $language
     * @param null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRNarrative $text
     * @param null|iterable<\OpenEMR\FHIR\Versions\R4\Types\FHIRResourceContainer>|iterable<\OpenEMR\FHIR\Versions\R4\VersionContainedTypeInterface> $contained
     * @param null|iterable<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRExtension> $extension
     * @param null|iterable<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRExtension> $modifierExtension
     * @param null|iterable<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRIdentifier> $identifier
     * @param null|iterable<string>|iterable<\OpenEMR\FHIR\Versions\R4\Types\FHIRCanonicalPrimitive>|iterable<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRCanonical> $instantiatesCanonical
     * @param null|iterable<string>|iterable<\OpenEMR\FHIR\Versions\R4\Types\FHIRUriPrimitive>|iterable<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRUri> $instantiatesUri
     * @param null|iterable<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRReference> $basedOn
     * @param null|iterable<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRReference> $priorRequest
     * @param null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRIdentifier $groupIdentifier
     * @param null|string|\OpenEMR\FHIR\Versions\R4\Types\FHIRCodePrimitive\FHIRRequestStatusList|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRRequestStatus $status
     * @param null|string|\OpenEMR\FHIR\Versions\R4\Types\FHIRCodePrimitive\FHIRRequestIntentList|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRRequestIntent $intent
     * @param null|string|\OpenEMR\FHIR\Versions\R4\Types\FHIRCodePrimitive\FHIRRequestPriorityList|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRRequestPriority $priority
     * @param null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRReference $codeReference
     * @param null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRCodeableConcept $codeCodeableConcept
     * @param null|iterable<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRDeviceRequest\FHIRDeviceRequestParameter> $parameter
     * @param null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRReference $subject
     * @param null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRReference $encounter
     * @param null|string|\DateTimeInterface|\OpenEMR\FHIR\Versions\R4\Types\FHIRDateTimePrimitive|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRDateTime $occurrenceDateTime
     * @param null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRPeriod $occurrencePeriod
     * @param null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRTiming $occurrenceTiming
     * @param null|string|\DateTimeInterface|\OpenEMR\FHIR\Versions\R4\Types\FHIRDateTimePrimitive|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRDateTime $authoredOn
     * @param null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRReference $requester
     * @param null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRCodeableConcept $performerType
     * @param null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRReference $performer
     * @param null|iterable<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRCodeableConcept> $reasonCode
     * @param null|iterable<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRReference> $reasonReference
     * @param null|iterable<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRReference> $insurance
     * @param null|iterable<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRReference> $supportingInfo
     * @param null|iterable<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRAnnotation> $note
     * @param null|iterable<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRReference> $relevantHistory
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
                                null|iterable $instantiatesCanonical = null,
                                null|iterable $instantiatesUri = null,
                                null|iterable $basedOn = null,
                                null|iterable $priorRequest = null,
                                null|FHIRIdentifier $groupIdentifier = null,
                                null|string|FHIRRequestStatusList|FHIRRequestStatus $status = null,
                                null|string|FHIRRequestIntentList|FHIRRequestIntent $intent = null,
                                null|string|FHIRRequestPriorityList|FHIRRequestPriority $priority = null,
                                null|FHIRReference $codeReference = null,
                                null|FHIRCodeableConcept $codeCodeableConcept = null,
                                null|iterable $parameter = null,
                                null|FHIRReference $subject = null,
                                null|FHIRReference $encounter = null,
                                null|string|\DateTimeInterface|FHIRDateTimePrimitive|FHIRDateTime $occurrenceDateTime = null,
                                null|FHIRPeriod $occurrencePeriod = null,
                                null|FHIRTiming $occurrenceTiming = null,
                                null|string|\DateTimeInterface|FHIRDateTimePrimitive|FHIRDateTime $authoredOn = null,
                                null|FHIRReference $requester = null,
                                null|FHIRCodeableConcept $performerType = null,
                                null|FHIRReference $performer = null,
                                null|iterable $reasonCode = null,
                                null|iterable $reasonReference = null,
                                null|iterable $insurance = null,
                                null|iterable $supportingInfo = null,
                                null|iterable $note = null,
                                null|iterable $relevantHistory = null,
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
        if (null !== $instantiatesCanonical) {
            $this->setInstantiatesCanonical(...$instantiatesCanonical);
        }
        if (null !== $instantiatesUri) {
            $this->setInstantiatesUri(...$instantiatesUri);
        }
        if (null !== $basedOn) {
            $this->setBasedOn(...$basedOn);
        }
        if (null !== $priorRequest) {
            $this->setPriorRequest(...$priorRequest);
        }
        if (null !== $groupIdentifier) {
            $this->setGroupIdentifier($groupIdentifier);
        }
        if (null !== $status) {
            $this->setStatus($status);
        }
        if (null !== $intent) {
            $this->setIntent($intent);
        }
        if (null !== $priority) {
            $this->setPriority($priority);
        }
        if (null !== $codeReference) {
            $this->setCodeReference($codeReference);
        }
        if (null !== $codeCodeableConcept) {
            $this->setCodeCodeableConcept($codeCodeableConcept);
        }
        if (null !== $parameter) {
            $this->setParameter(...$parameter);
        }
        if (null !== $subject) {
            $this->setSubject($subject);
        }
        if (null !== $encounter) {
            $this->setEncounter($encounter);
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
        if (null !== $authoredOn) {
            $this->setAuthoredOn($authoredOn);
        }
        if (null !== $requester) {
            $this->setRequester($requester);
        }
        if (null !== $performerType) {
            $this->setPerformerType($performerType);
        }
        if (null !== $performer) {
            $this->setPerformer($performer);
        }
        if (null !== $reasonCode) {
            $this->setReasonCode(...$reasonCode);
        }
        if (null !== $reasonReference) {
            $this->setReasonReference(...$reasonReference);
        }
        if (null !== $insurance) {
            $this->setInsurance(...$insurance);
        }
        if (null !== $supportingInfo) {
            $this->setSupportingInfo(...$supportingInfo);
        }
        if (null !== $note) {
            $this->setNote(...$note);
        }
        if (null !== $relevantHistory) {
            $this->setRelevantHistory(...$relevantHistory);
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
     * Identifiers assigned to this order by the orderer or by the receiver.
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
     * Identifiers assigned to this order by the orderer or by the receiver.
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
     * Identifiers assigned to this order by the orderer or by the receiver.
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
     * A URI that is a reference to a canonical URL on a FHIR resource
     * see [Canonical References](references.html#canonical)
     * If the element is present, it must have either a \@value, an \@id referenced from
     * the Narrative, or extensions
     *
     * The URL pointing to a FHIR-defined protocol, guideline, orderset or other
     * definition that is adhered to in whole or in part by this DeviceRequest.
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
     * The URL pointing to a FHIR-defined protocol, guideline, orderset or other
     * definition that is adhered to in whole or in part by this DeviceRequest.
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
     * The URL pointing to a FHIR-defined protocol, guideline, orderset or other
     * definition that is adhered to in whole or in part by this DeviceRequest.
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
     * The URL pointing to an externally maintained protocol, guideline, orderset or
     * other definition that is adhered to in whole or in part by this DeviceRequest.
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
     * The URL pointing to an externally maintained protocol, guideline, orderset or
     * other definition that is adhered to in whole or in part by this DeviceRequest.
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
     * The URL pointing to an externally maintained protocol, guideline, orderset or
     * other definition that is adhered to in whole or in part by this DeviceRequest.
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
     * A reference from one resource to another.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * Plan/proposal/order fulfilled by this request.
     *
     * @return iterable<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRReference>
     */
    public function getBasedOn(): array
    {
        return $this->basedOn ?? [];
    }

    /**
     * @return \ArrayIterator<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRReference>
     */
    public function getBasedOnIterator(): iterable
    {
        if (!isset($this->basedOn)) {
            return new \EmptyIterator();
        }
        return new \ArrayIterator($this->basedOn);
    }

    /**
     * A reference from one resource to another.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * Plan/proposal/order fulfilled by this request.
     *
     * @param \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRReference $basedOn
     * @return static
     */
    public function addBasedOn(FHIRReference $basedOn): self
    {
        if (!isset($this->basedOn)) {
            $this->basedOn = [];
        }
        $this->basedOn[] = $basedOn;
        return $this;
    }

    /**
     * A reference from one resource to another.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * Plan/proposal/order fulfilled by this request.
     *
     * @param \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRReference ...$basedOn
     * @return static
     */
    public function setBasedOn(FHIRReference ...$basedOn): self
    {
        if ([] === $basedOn) {
            unset($this->basedOn);
            return $this;
        }
        $this->basedOn = $basedOn;
        return $this;
    }

    /**
     * A reference from one resource to another.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * The request takes the place of the referenced completed or terminated
     * request(s).
     *
     * @return iterable<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRReference>
     */
    public function getPriorRequest(): array
    {
        return $this->priorRequest ?? [];
    }

    /**
     * @return \ArrayIterator<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRReference>
     */
    public function getPriorRequestIterator(): iterable
    {
        if (!isset($this->priorRequest)) {
            return new \EmptyIterator();
        }
        return new \ArrayIterator($this->priorRequest);
    }

    /**
     * A reference from one resource to another.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * The request takes the place of the referenced completed or terminated
     * request(s).
     *
     * @param \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRReference $priorRequest
     * @return static
     */
    public function addPriorRequest(FHIRReference $priorRequest): self
    {
        if (!isset($this->priorRequest)) {
            $this->priorRequest = [];
        }
        $this->priorRequest[] = $priorRequest;
        return $this;
    }

    /**
     * A reference from one resource to another.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * The request takes the place of the referenced completed or terminated
     * request(s).
     *
     * @param \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRReference ...$priorRequest
     * @return static
     */
    public function setPriorRequest(FHIRReference ...$priorRequest): self
    {
        if ([] === $priorRequest) {
            unset($this->priorRequest);
            return $this;
        }
        $this->priorRequest = $priorRequest;
        return $this;
    }

    /**
     * An identifier - identifies some entity uniquely and unambiguously. Typically
     * this is used for business identifiers.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * Composite request this is part of.
     *
     * @return null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRIdentifier
     */
    public function getGroupIdentifier(): null|FHIRIdentifier
    {
        return $this->groupIdentifier ?? null;
    }

    /**
     * An identifier - identifies some entity uniquely and unambiguously. Typically
     * this is used for business identifiers.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * Composite request this is part of.
     *
     * @param null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRIdentifier $groupIdentifier
     * @return static
     */
    public function setGroupIdentifier(null|FHIRIdentifier $groupIdentifier): self
    {
        if (null === $groupIdentifier) {
            unset($this->groupIdentifier);
            return $this;
        }
        $this->groupIdentifier = $groupIdentifier;
        return $this;
    }

    /**
     * Indicates whether the plan is currently being acted upon, represents future
     * intentions or is now a historical record.
     * If the element is present, it must have either a \@value, an \@id, or extensions
     *
     * The status of the request.
     *
     * @return null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRRequestStatus
     */
    public function getStatus(): null|FHIRRequestStatus
    {
        return $this->status ?? null;
    }

    /**
     * Indicates whether the plan is currently being acted upon, represents future
     * intentions or is now a historical record.
     * If the element is present, it must have either a \@value, an \@id, or extensions
     *
     * The status of the request.
     *
     * @param null|string|\OpenEMR\FHIR\Versions\R4\Types\FHIRCodePrimitive\FHIRRequestStatusList|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRRequestStatus $status
     * @return static
     */
    public function setStatus(null|string|FHIRRequestStatusList|FHIRRequestStatus $status): self
    {
        if (null === $status) {
            unset($this->status);
            return $this;
        }
        if (!($status instanceof FHIRRequestStatus)) {
            $status = new FHIRRequestStatus(value: $status);
        }
        $this->status = $status;
        return $this;
    }

    /**
     * Codes indicating the degree of authority/intentionality associated with a
     * request.
     * If the element is present, it must have either a \@value, an \@id, or extensions
     *
     * Whether the request is a proposal, plan, an original order or a reflex order.
     *
     * @return null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRRequestIntent
     */
    public function getIntent(): null|FHIRRequestIntent
    {
        return $this->intent ?? null;
    }

    /**
     * Codes indicating the degree of authority/intentionality associated with a
     * request.
     * If the element is present, it must have either a \@value, an \@id, or extensions
     *
     * Whether the request is a proposal, plan, an original order or a reflex order.
     *
     * @param null|string|\OpenEMR\FHIR\Versions\R4\Types\FHIRCodePrimitive\FHIRRequestIntentList|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRRequestIntent $intent
     * @return static
     */
    public function setIntent(null|string|FHIRRequestIntentList|FHIRRequestIntent $intent): self
    {
        if (null === $intent) {
            unset($this->intent);
            return $this;
        }
        if (!($intent instanceof FHIRRequestIntent)) {
            $intent = new FHIRRequestIntent(value: $intent);
        }
        $this->intent = $intent;
        return $this;
    }

    /**
     * Identifies the level of importance to be assigned to actioning the request.
     * If the element is present, it must have either a \@value, an \@id, or extensions
     *
     * Indicates how quickly the {{title}} should be addressed with respect to other
     * requests.
     *
     * @return null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRRequestPriority
     */
    public function getPriority(): null|FHIRRequestPriority
    {
        return $this->priority ?? null;
    }

    /**
     * Identifies the level of importance to be assigned to actioning the request.
     * If the element is present, it must have either a \@value, an \@id, or extensions
     *
     * Indicates how quickly the {{title}} should be addressed with respect to other
     * requests.
     *
     * @param null|string|\OpenEMR\FHIR\Versions\R4\Types\FHIRCodePrimitive\FHIRRequestPriorityList|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRRequestPriority $priority
     * @return static
     */
    public function setPriority(null|string|FHIRRequestPriorityList|FHIRRequestPriority $priority): self
    {
        if (null === $priority) {
            unset($this->priority);
            return $this;
        }
        if (!($priority instanceof FHIRRequestPriority)) {
            $priority = new FHIRRequestPriority(value: $priority);
        }
        $this->priority = $priority;
        return $this;
    }

    /**
     * A reference from one resource to another.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * The details of the device to be used. (choose any one of code*, but only one)
     *
     * @return null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRReference
     */
    public function getCodeReference(): null|FHIRReference
    {
        return $this->codeReference ?? null;
    }

    /**
     * A reference from one resource to another.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * The details of the device to be used. (choose any one of code*, but only one)
     *
     * @param null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRReference $codeReference
     * @return static
     */
    public function setCodeReference(null|FHIRReference $codeReference): self
    {
        if (null === $codeReference) {
            unset($this->codeReference);
            return $this;
        }
        $this->codeReference = $codeReference;
        return $this;
    }

    /**
     * A concept that may be defined by a formal reference to a terminology or ontology
     * or may be provided by text.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * The details of the device to be used. (choose any one of code*, but only one)
     *
     * @return null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRCodeableConcept
     */
    public function getCodeCodeableConcept(): null|FHIRCodeableConcept
    {
        return $this->codeCodeableConcept ?? null;
    }

    /**
     * A concept that may be defined by a formal reference to a terminology or ontology
     * or may be provided by text.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * The details of the device to be used. (choose any one of code*, but only one)
     *
     * @param null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRCodeableConcept $codeCodeableConcept
     * @return static
     */
    public function setCodeCodeableConcept(null|FHIRCodeableConcept $codeCodeableConcept): self
    {
        if (null === $codeCodeableConcept) {
            unset($this->codeCodeableConcept);
            return $this;
        }
        $this->codeCodeableConcept = $codeCodeableConcept;
        return $this;
    }

    /**
     * Represents a request for a patient to employ a medical device. The device may be
     * an implantable device, or an external assistive device, such as a walker.
     *
     * Specific parameters for the ordered item. For example, the prism value for
     * lenses.
     *
     * @return iterable<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRDeviceRequest\FHIRDeviceRequestParameter>
     */
    public function getParameter(): array
    {
        return $this->parameter ?? [];
    }

    /**
     * @return \ArrayIterator<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRDeviceRequest\FHIRDeviceRequestParameter>
     */
    public function getParameterIterator(): iterable
    {
        if (!isset($this->parameter)) {
            return new \EmptyIterator();
        }
        return new \ArrayIterator($this->parameter);
    }

    /**
     * Represents a request for a patient to employ a medical device. The device may be
     * an implantable device, or an external assistive device, such as a walker.
     *
     * Specific parameters for the ordered item. For example, the prism value for
     * lenses.
     *
     * @param \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRDeviceRequest\FHIRDeviceRequestParameter $parameter
     * @return static
     */
    public function addParameter(FHIRDeviceRequestParameter $parameter): self
    {
        if (!isset($this->parameter)) {
            $this->parameter = [];
        }
        $this->parameter[] = $parameter;
        return $this;
    }

    /**
     * Represents a request for a patient to employ a medical device. The device may be
     * an implantable device, or an external assistive device, such as a walker.
     *
     * Specific parameters for the ordered item. For example, the prism value for
     * lenses.
     *
     * @param \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRDeviceRequest\FHIRDeviceRequestParameter ...$parameter
     * @return static
     */
    public function setParameter(FHIRDeviceRequestParameter ...$parameter): self
    {
        if ([] === $parameter) {
            unset($this->parameter);
            return $this;
        }
        $this->parameter = $parameter;
        return $this;
    }

    /**
     * A reference from one resource to another.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * The patient who will use the device.
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
     * The patient who will use the device.
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
     * An encounter that provides additional context in which this request is made.
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
     * An encounter that provides additional context in which this request is made.
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
     * The timing schedule for the use of the device. The Schedule data type allows
     * many different expressions, for example. "Every 8 hours"; "Three times a day";
     * "1/2 an hour before breakfast for 10 days from 23-Dec 2011:"; "15 Oct 2013, 17
     * Oct 2013 and 1 Nov 2013". (choose any one of occurrence*, but only one)
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
     * The timing schedule for the use of the device. The Schedule data type allows
     * many different expressions, for example. "Every 8 hours"; "Three times a day";
     * "1/2 an hour before breakfast for 10 days from 23-Dec 2011:"; "15 Oct 2013, 17
     * Oct 2013 and 1 Nov 2013". (choose any one of occurrence*, but only one)
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
     * The timing schedule for the use of the device. The Schedule data type allows
     * many different expressions, for example. "Every 8 hours"; "Three times a day";
     * "1/2 an hour before breakfast for 10 days from 23-Dec 2011:"; "15 Oct 2013, 17
     * Oct 2013 and 1 Nov 2013". (choose any one of occurrence*, but only one)
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
     * The timing schedule for the use of the device. The Schedule data type allows
     * many different expressions, for example. "Every 8 hours"; "Three times a day";
     * "1/2 an hour before breakfast for 10 days from 23-Dec 2011:"; "15 Oct 2013, 17
     * Oct 2013 and 1 Nov 2013". (choose any one of occurrence*, but only one)
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
     * The timing schedule for the use of the device. The Schedule data type allows
     * many different expressions, for example. "Every 8 hours"; "Three times a day";
     * "1/2 an hour before breakfast for 10 days from 23-Dec 2011:"; "15 Oct 2013, 17
     * Oct 2013 and 1 Nov 2013". (choose any one of occurrence*, but only one)
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
     * The timing schedule for the use of the device. The Schedule data type allows
     * many different expressions, for example. "Every 8 hours"; "Three times a day";
     * "1/2 an hour before breakfast for 10 days from 23-Dec 2011:"; "15 Oct 2013, 17
     * Oct 2013 and 1 Nov 2013". (choose any one of occurrence*, but only one)
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
     * A date, date-time or partial date (e.g. just year or year + month). If hours and
     * minutes are specified, a time zone SHALL be populated. The format is a union of
     * the schema types gYear, gYearMonth, date and dateTime. Seconds must be provided
     * due to schema type constraints but may be zero-filled and may be ignored. Dates
     * SHALL be valid dates.
     * If the element is present, it must have either a \@value, an \@id, or extensions
     *
     * When the request transitioned to being actionable.
     *
     * @return null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRDateTime
     */
    public function getAuthoredOn(): null|FHIRDateTime
    {
        return $this->authoredOn ?? null;
    }

    /**
     * A date, date-time or partial date (e.g. just year or year + month). If hours and
     * minutes are specified, a time zone SHALL be populated. The format is a union of
     * the schema types gYear, gYearMonth, date and dateTime. Seconds must be provided
     * due to schema type constraints but may be zero-filled and may be ignored. Dates
     * SHALL be valid dates.
     * If the element is present, it must have either a \@value, an \@id, or extensions
     *
     * When the request transitioned to being actionable.
     *
     * @param null|string|\DateTimeInterface|\OpenEMR\FHIR\Versions\R4\Types\FHIRDateTimePrimitive|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRDateTime $authoredOn
     * @return static
     */
    public function setAuthoredOn(null|string|\DateTimeInterface|FHIRDateTimePrimitive|FHIRDateTime $authoredOn): self
    {
        if (null === $authoredOn) {
            unset($this->authoredOn);
            return $this;
        }
        if (!($authoredOn instanceof FHIRDateTime)) {
            $authoredOn = new FHIRDateTime(value: $authoredOn);
        }
        $this->authoredOn = $authoredOn;
        return $this;
    }

    /**
     * A reference from one resource to another.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * The individual who initiated the request and has responsibility for its
     * activation.
     *
     * @return null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRReference
     */
    public function getRequester(): null|FHIRReference
    {
        return $this->requester ?? null;
    }

    /**
     * A reference from one resource to another.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * The individual who initiated the request and has responsibility for its
     * activation.
     *
     * @param null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRReference $requester
     * @return static
     */
    public function setRequester(null|FHIRReference $requester): self
    {
        if (null === $requester) {
            unset($this->requester);
            return $this;
        }
        $this->requester = $requester;
        return $this;
    }

    /**
     * A concept that may be defined by a formal reference to a terminology or ontology
     * or may be provided by text.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * Desired type of performer for doing the diagnostic testing.
     *
     * @return null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRCodeableConcept
     */
    public function getPerformerType(): null|FHIRCodeableConcept
    {
        return $this->performerType ?? null;
    }

    /**
     * A concept that may be defined by a formal reference to a terminology or ontology
     * or may be provided by text.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * Desired type of performer for doing the diagnostic testing.
     *
     * @param null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRCodeableConcept $performerType
     * @return static
     */
    public function setPerformerType(null|FHIRCodeableConcept $performerType): self
    {
        if (null === $performerType) {
            unset($this->performerType);
            return $this;
        }
        $this->performerType = $performerType;
        return $this;
    }

    /**
     * A reference from one resource to another.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * The desired performer for doing the diagnostic testing.
     *
     * @return null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRReference
     */
    public function getPerformer(): null|FHIRReference
    {
        return $this->performer ?? null;
    }

    /**
     * A reference from one resource to another.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * The desired performer for doing the diagnostic testing.
     *
     * @param null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRReference $performer
     * @return static
     */
    public function setPerformer(null|FHIRReference $performer): self
    {
        if (null === $performer) {
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
     * Reason or justification for the use of this device.
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
     * Reason or justification for the use of this device.
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
     * Reason or justification for the use of this device.
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
     * Reason or justification for the use of this device.
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
     * Reason or justification for the use of this device.
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
     * Reason or justification for the use of this device.
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
     * Insurance plans, coverage extensions, pre-authorizations and/or
     * pre-determinations that may be required for delivering the requested service.
     *
     * @return iterable<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRReference>
     */
    public function getInsurance(): array
    {
        return $this->insurance ?? [];
    }

    /**
     * @return \ArrayIterator<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRReference>
     */
    public function getInsuranceIterator(): iterable
    {
        if (!isset($this->insurance)) {
            return new \EmptyIterator();
        }
        return new \ArrayIterator($this->insurance);
    }

    /**
     * A reference from one resource to another.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * Insurance plans, coverage extensions, pre-authorizations and/or
     * pre-determinations that may be required for delivering the requested service.
     *
     * @param \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRReference $insurance
     * @return static
     */
    public function addInsurance(FHIRReference $insurance): self
    {
        if (!isset($this->insurance)) {
            $this->insurance = [];
        }
        $this->insurance[] = $insurance;
        return $this;
    }

    /**
     * A reference from one resource to another.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * Insurance plans, coverage extensions, pre-authorizations and/or
     * pre-determinations that may be required for delivering the requested service.
     *
     * @param \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRReference ...$insurance
     * @return static
     */
    public function setInsurance(FHIRReference ...$insurance): self
    {
        if ([] === $insurance) {
            unset($this->insurance);
            return $this;
        }
        $this->insurance = $insurance;
        return $this;
    }

    /**
     * A reference from one resource to another.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * Additional clinical information about the patient that may influence the request
     * fulfilment. For example, this may include where on the subject's body the device
     * will be used (i.e. the target site).
     *
     * @return iterable<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRReference>
     */
    public function getSupportingInfo(): array
    {
        return $this->supportingInfo ?? [];
    }

    /**
     * @return \ArrayIterator<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRReference>
     */
    public function getSupportingInfoIterator(): iterable
    {
        if (!isset($this->supportingInfo)) {
            return new \EmptyIterator();
        }
        return new \ArrayIterator($this->supportingInfo);
    }

    /**
     * A reference from one resource to another.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * Additional clinical information about the patient that may influence the request
     * fulfilment. For example, this may include where on the subject's body the device
     * will be used (i.e. the target site).
     *
     * @param \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRReference $supportingInfo
     * @return static
     */
    public function addSupportingInfo(FHIRReference $supportingInfo): self
    {
        if (!isset($this->supportingInfo)) {
            $this->supportingInfo = [];
        }
        $this->supportingInfo[] = $supportingInfo;
        return $this;
    }

    /**
     * A reference from one resource to another.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * Additional clinical information about the patient that may influence the request
     * fulfilment. For example, this may include where on the subject's body the device
     * will be used (i.e. the target site).
     *
     * @param \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRReference ...$supportingInfo
     * @return static
     */
    public function setSupportingInfo(FHIRReference ...$supportingInfo): self
    {
        if ([] === $supportingInfo) {
            unset($this->supportingInfo);
            return $this;
        }
        $this->supportingInfo = $supportingInfo;
        return $this;
    }

    /**
     * A text note which also contains information about who made the statement and
     * when.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * Details about this request that were not represented at all or sufficiently in
     * one of the attributes provided in a class. These may include for example a
     * comment, an instruction, or a note associated with the statement.
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
     * Details about this request that were not represented at all or sufficiently in
     * one of the attributes provided in a class. These may include for example a
     * comment, an instruction, or a note associated with the statement.
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
     * Details about this request that were not represented at all or sufficiently in
     * one of the attributes provided in a class. These may include for example a
     * comment, an instruction, or a note associated with the statement.
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
     * Key events in the history of the request.
     *
     * @return iterable<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRReference>
     */
    public function getRelevantHistory(): array
    {
        return $this->relevantHistory ?? [];
    }

    /**
     * @return \ArrayIterator<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRReference>
     */
    public function getRelevantHistoryIterator(): iterable
    {
        if (!isset($this->relevantHistory)) {
            return new \EmptyIterator();
        }
        return new \ArrayIterator($this->relevantHistory);
    }

    /**
     * A reference from one resource to another.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * Key events in the history of the request.
     *
     * @param \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRReference $relevantHistory
     * @return static
     */
    public function addRelevantHistory(FHIRReference $relevantHistory): self
    {
        if (!isset($this->relevantHistory)) {
            $this->relevantHistory = [];
        }
        $this->relevantHistory[] = $relevantHistory;
        return $this;
    }

    /**
     * A reference from one resource to another.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * Key events in the history of the request.
     *
     * @param \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRReference ...$relevantHistory
     * @return static
     */
    public function setRelevantHistory(FHIRReference ...$relevantHistory): self
    {
        if ([] === $relevantHistory) {
            unset($this->relevantHistory);
            return $this;
        }
        $this->relevantHistory = $relevantHistory;
        return $this;
    }

    /* class_default.php:201 */
    /**
     * @param string|\SimpleXMLElement $element
     * @param null|\OpenEMR\FHIR\Encoding\UnserializeConfig $config
     * @param null|\OpenEMR\FHIR\Versions\R4\Types\FHIRResource\FHIRDomainResource\FHIRDeviceRequest $type
     * @return \OpenEMR\FHIR\Versions\R4\Types\FHIRResource\FHIRDomainResource\FHIRDeviceRequest
     * @throws \Exception
     */
    public static function xmlUnserialize(string|\SimpleXMLElement $element,
                                          null|UnserializeConfig $config = null,
                                          null|ResourceTypeInterface $type = null): self
    {
        if (null === $type) {
            $type = new static();
        } else if (!($type instanceof FHIRDeviceRequest)) {
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
            } else if (self::FIELD_INSTANTIATES_CANONICAL === $cen) {
                $type->addInstantiatesCanonical(FHIRCanonical::xmlUnserialize($ce, $config));
            } else if (self::FIELD_INSTANTIATES_URI === $cen) {
                $type->addInstantiatesUri(FHIRUri::xmlUnserialize($ce, $config));
            } else if (self::FIELD_BASED_ON === $cen) {
                $type->addBasedOn(FHIRReference::xmlUnserialize($ce, $config));
            } else if (self::FIELD_PRIOR_REQUEST === $cen) {
                $type->addPriorRequest(FHIRReference::xmlUnserialize($ce, $config));
            } else if (self::FIELD_GROUP_IDENTIFIER === $cen) {
                $type->setGroupIdentifier(FHIRIdentifier::xmlUnserialize($ce, $config));
            } else if (self::FIELD_STATUS === $cen) {
                $type->setStatus(FHIRRequestStatus::xmlUnserialize($ce, $config));
            } else if (self::FIELD_INTENT === $cen) {
                $type->setIntent(FHIRRequestIntent::xmlUnserialize($ce, $config));
            } else if (self::FIELD_PRIORITY === $cen) {
                $type->setPriority(FHIRRequestPriority::xmlUnserialize($ce, $config));
            } else if (self::FIELD_CODE_REFERENCE === $cen) {
                $type->setCodeReference(FHIRReference::xmlUnserialize($ce, $config));
            } else if (self::FIELD_CODE_CODEABLE_CONCEPT === $cen) {
                $type->setCodeCodeableConcept(FHIRCodeableConcept::xmlUnserialize($ce, $config));
            } else if (self::FIELD_PARAMETER === $cen) {
                $type->addParameter(FHIRDeviceRequestParameter::xmlUnserialize($ce, $config));
            } else if (self::FIELD_SUBJECT === $cen) {
                $type->setSubject(FHIRReference::xmlUnserialize($ce, $config));
            } else if (self::FIELD_ENCOUNTER === $cen) {
                $type->setEncounter(FHIRReference::xmlUnserialize($ce, $config));
            } else if (self::FIELD_OCCURRENCE_DATE_TIME === $cen) {
                $type->setOccurrenceDateTime(FHIRDateTime::xmlUnserialize($ce, $config));
            } else if (self::FIELD_OCCURRENCE_PERIOD === $cen) {
                $type->setOccurrencePeriod(FHIRPeriod::xmlUnserialize($ce, $config));
            } else if (self::FIELD_OCCURRENCE_TIMING === $cen) {
                $type->setOccurrenceTiming(FHIRTiming::xmlUnserialize($ce, $config));
            } else if (self::FIELD_AUTHORED_ON === $cen) {
                $type->setAuthoredOn(FHIRDateTime::xmlUnserialize($ce, $config));
            } else if (self::FIELD_REQUESTER === $cen) {
                $type->setRequester(FHIRReference::xmlUnserialize($ce, $config));
            } else if (self::FIELD_PERFORMER_TYPE === $cen) {
                $type->setPerformerType(FHIRCodeableConcept::xmlUnserialize($ce, $config));
            } else if (self::FIELD_PERFORMER === $cen) {
                $type->setPerformer(FHIRReference::xmlUnserialize($ce, $config));
            } else if (self::FIELD_REASON_CODE === $cen) {
                $type->addReasonCode(FHIRCodeableConcept::xmlUnserialize($ce, $config));
            } else if (self::FIELD_REASON_REFERENCE === $cen) {
                $type->addReasonReference(FHIRReference::xmlUnserialize($ce, $config));
            } else if (self::FIELD_INSURANCE === $cen) {
                $type->addInsurance(FHIRReference::xmlUnserialize($ce, $config));
            } else if (self::FIELD_SUPPORTING_INFO === $cen) {
                $type->addSupportingInfo(FHIRReference::xmlUnserialize($ce, $config));
            } else if (self::FIELD_NOTE === $cen) {
                $type->addNote(FHIRAnnotation::xmlUnserialize($ce, $config));
            } else if (self::FIELD_RELEVANT_HISTORY === $cen) {
                $type->addRelevantHistory(FHIRReference::xmlUnserialize($ce, $config));
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
        if (isset($attributes[self::FIELD_INTENT])) {
            if (isset($type->intent)) {
                $type->intent->setValue((string)$attributes[self::FIELD_INTENT]);
            } else {
                $type->setIntent((string)$attributes[self::FIELD_INTENT]);
            }
            $type->_setXMLFieldValueLocation(self::FIELD_INTENT, ValueXMLLocationEnum::PARENT_ATTRIBUTE);
        }
        if (isset($attributes[self::FIELD_PRIORITY])) {
            if (isset($type->priority)) {
                $type->priority->setValue((string)$attributes[self::FIELD_PRIORITY]);
            } else {
                $type->setPriority((string)$attributes[self::FIELD_PRIORITY]);
            }
            $type->_setXMLFieldValueLocation(self::FIELD_PRIORITY, ValueXMLLocationEnum::PARENT_ATTRIBUTE);
        }
        if (isset($attributes[self::FIELD_OCCURRENCE_DATE_TIME])) {
            if (isset($type->occurrenceDateTime)) {
                $type->occurrenceDateTime->setValue((string)$attributes[self::FIELD_OCCURRENCE_DATE_TIME]);
            } else {
                $type->setOccurrenceDateTime((string)$attributes[self::FIELD_OCCURRENCE_DATE_TIME]);
            }
            $type->_setXMLFieldValueLocation(self::FIELD_OCCURRENCE_DATE_TIME, ValueXMLLocationEnum::PARENT_ATTRIBUTE);
        }
        if (isset($attributes[self::FIELD_AUTHORED_ON])) {
            if (isset($type->authoredOn)) {
                $type->authoredOn->setValue((string)$attributes[self::FIELD_AUTHORED_ON]);
            } else {
                $type->setAuthoredOn((string)$attributes[self::FIELD_AUTHORED_ON]);
            }
            $type->_setXMLFieldValueLocation(self::FIELD_AUTHORED_ON, ValueXMLLocationEnum::PARENT_ATTRIBUTE);
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
            $xw->openRootNode('DeviceRequest', $this->_getSourceXMLNS());
        }
        if (isset($this->status) && ValueXMLLocationEnum::PARENT_ATTRIBUTE === $this->_valueXMLLocations[self::FIELD_STATUS]) {
            $xw->writeAttribute(self::FIELD_STATUS, $this->status->_getValueAsString());
        }
        if (isset($this->intent) && ValueXMLLocationEnum::PARENT_ATTRIBUTE === $this->_valueXMLLocations[self::FIELD_INTENT]) {
            $xw->writeAttribute(self::FIELD_INTENT, $this->intent->_getValueAsString());
        }
        if (isset($this->priority) && ValueXMLLocationEnum::PARENT_ATTRIBUTE === $this->_valueXMLLocations[self::FIELD_PRIORITY]) {
            $xw->writeAttribute(self::FIELD_PRIORITY, $this->priority->_getValueAsString());
        }
        if (isset($this->occurrenceDateTime) && ValueXMLLocationEnum::PARENT_ATTRIBUTE === $this->_valueXMLLocations[self::FIELD_OCCURRENCE_DATE_TIME]) {
            $xw->writeAttribute(self::FIELD_OCCURRENCE_DATE_TIME, $this->occurrenceDateTime->_getValueAsString());
        }
        if (isset($this->authoredOn) && ValueXMLLocationEnum::PARENT_ATTRIBUTE === $this->_valueXMLLocations[self::FIELD_AUTHORED_ON]) {
            $xw->writeAttribute(self::FIELD_AUTHORED_ON, $this->authoredOn->_getValueAsString());
        }
        parent::xmlSerialize($xw, $config);
        if (isset($this->identifier)) {
            foreach ($this->identifier as $v) {
                $xw->startElement(self::FIELD_IDENTIFIER);
                $v->xmlSerialize($xw, $config);
                $xw->endElement();
            }
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
        if (isset($this->basedOn)) {
            foreach ($this->basedOn as $v) {
                $xw->startElement(self::FIELD_BASED_ON);
                $v->xmlSerialize($xw, $config);
                $xw->endElement();
            }
        }
        if (isset($this->priorRequest)) {
            foreach ($this->priorRequest as $v) {
                $xw->startElement(self::FIELD_PRIOR_REQUEST);
                $v->xmlSerialize($xw, $config);
                $xw->endElement();
            }
        }
        if (isset($this->groupIdentifier)) {
            $xw->startElement(self::FIELD_GROUP_IDENTIFIER);
            $this->groupIdentifier->xmlSerialize($xw, $config);
            $xw->endElement();
        }
        if (isset($this->status)
            && (ValueXMLLocationEnum::PARENT_ATTRIBUTE !== $this->_valueXMLLocations[self::FIELD_STATUS]
                || $this->status->_nonValueFieldDefined())) {
            $xw->startElement(self::FIELD_STATUS);
            $this->status->xmlSerialize($xw, $config, $this->_valueXMLLocations[self::FIELD_STATUS]);
            $xw->endElement();
        }
        if (isset($this->intent)
            && (ValueXMLLocationEnum::PARENT_ATTRIBUTE !== $this->_valueXMLLocations[self::FIELD_INTENT]
                || $this->intent->_nonValueFieldDefined())) {
            $xw->startElement(self::FIELD_INTENT);
            $this->intent->xmlSerialize($xw, $config, $this->_valueXMLLocations[self::FIELD_INTENT]);
            $xw->endElement();
        }
        if (isset($this->priority)
            && (ValueXMLLocationEnum::PARENT_ATTRIBUTE !== $this->_valueXMLLocations[self::FIELD_PRIORITY]
                || $this->priority->_nonValueFieldDefined())) {
            $xw->startElement(self::FIELD_PRIORITY);
            $this->priority->xmlSerialize($xw, $config, $this->_valueXMLLocations[self::FIELD_PRIORITY]);
            $xw->endElement();
        }
        if (isset($this->codeReference)) {
            $xw->startElement(self::FIELD_CODE_REFERENCE);
            $this->codeReference->xmlSerialize($xw, $config);
            $xw->endElement();
        }
        if (isset($this->codeCodeableConcept)) {
            $xw->startElement(self::FIELD_CODE_CODEABLE_CONCEPT);
            $this->codeCodeableConcept->xmlSerialize($xw, $config);
            $xw->endElement();
        }
        if (isset($this->parameter)) {
            foreach ($this->parameter as $v) {
                $xw->startElement(self::FIELD_PARAMETER);
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
        if (isset($this->authoredOn)
            && (ValueXMLLocationEnum::PARENT_ATTRIBUTE !== $this->_valueXMLLocations[self::FIELD_AUTHORED_ON]
                || $this->authoredOn->_nonValueFieldDefined())) {
            $xw->startElement(self::FIELD_AUTHORED_ON);
            $this->authoredOn->xmlSerialize($xw, $config, $this->_valueXMLLocations[self::FIELD_AUTHORED_ON]);
            $xw->endElement();
        }
        if (isset($this->requester)) {
            $xw->startElement(self::FIELD_REQUESTER);
            $this->requester->xmlSerialize($xw, $config);
            $xw->endElement();
        }
        if (isset($this->performerType)) {
            $xw->startElement(self::FIELD_PERFORMER_TYPE);
            $this->performerType->xmlSerialize($xw, $config);
            $xw->endElement();
        }
        if (isset($this->performer)) {
            $xw->startElement(self::FIELD_PERFORMER);
            $this->performer->xmlSerialize($xw, $config);
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
        if (isset($this->insurance)) {
            foreach ($this->insurance as $v) {
                $xw->startElement(self::FIELD_INSURANCE);
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
        if (isset($this->note)) {
            foreach ($this->note as $v) {
                $xw->startElement(self::FIELD_NOTE);
                $v->xmlSerialize($xw, $config);
                $xw->endElement();
            }
        }
        if (isset($this->relevantHistory)) {
            foreach ($this->relevantHistory as $v) {
                $xw->startElement(self::FIELD_RELEVANT_HISTORY);
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
     * @param null|\OpenEMR\FHIR\Versions\R4\Types\FHIRResource\FHIRDomainResource\FHIRDeviceRequest $type
     * @return \OpenEMR\FHIR\Versions\R4\Types\FHIRResource\FHIRDomainResource\FHIRDeviceRequest
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
        } else if (!($type instanceof FHIRDeviceRequest)) {
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
        if (isset($decoded->basedOn) || property_exists($decoded, self::FIELD_BASED_ON)) {
            if (is_object($decoded->basedOn)) {
                $vals = [$decoded->basedOn];
                $type->_setJSONFieldElideSingletonArray(self::FIELD_BASED_ON, true);
            } else {
                $vals = $decoded->basedOn;
            }
            foreach($vals as $v) {
                $type->addBasedOn(FHIRReference::jsonUnserialize($v, $config));
            }
        }
        if (isset($decoded->priorRequest) || property_exists($decoded, self::FIELD_PRIOR_REQUEST)) {
            if (is_object($decoded->priorRequest)) {
                $vals = [$decoded->priorRequest];
                $type->_setJSONFieldElideSingletonArray(self::FIELD_PRIOR_REQUEST, true);
            } else {
                $vals = $decoded->priorRequest;
            }
            foreach($vals as $v) {
                $type->addPriorRequest(FHIRReference::jsonUnserialize($v, $config));
            }
        }
        if (isset($decoded->groupIdentifier) || property_exists($decoded, self::FIELD_GROUP_IDENTIFIER)) {
            if (is_array($decoded->groupIdentifier)) {
                $type->setGroupIdentifier(FHIRIdentifier::jsonUnserialize(reset($decoded->groupIdentifier), $config));
            } else {
                $type->setGroupIdentifier(FHIRIdentifier::jsonUnserialize($decoded->groupIdentifier, $config));
            }
        }
        if (isset($decoded->status)
            || isset($decoded->_status)
            || property_exists($decoded, self::FIELD_STATUS)
            || property_exists($decoded, self::FIELD_STATUS_EXT)) {
            $v = $decoded->_status ?? new \stdClass();
            $v->value = $decoded->status ?? null;
            $type->setStatus(FHIRRequestStatus::jsonUnserialize($v, $config));
        }
        if (isset($decoded->intent)
            || isset($decoded->_intent)
            || property_exists($decoded, self::FIELD_INTENT)
            || property_exists($decoded, self::FIELD_INTENT_EXT)) {
            $v = $decoded->_intent ?? new \stdClass();
            $v->value = $decoded->intent ?? null;
            $type->setIntent(FHIRRequestIntent::jsonUnserialize($v, $config));
        }
        if (isset($decoded->priority)
            || isset($decoded->_priority)
            || property_exists($decoded, self::FIELD_PRIORITY)
            || property_exists($decoded, self::FIELD_PRIORITY_EXT)) {
            $v = $decoded->_priority ?? new \stdClass();
            $v->value = $decoded->priority ?? null;
            $type->setPriority(FHIRRequestPriority::jsonUnserialize($v, $config));
        }
        if (isset($decoded->codeReference) || property_exists($decoded, self::FIELD_CODE_REFERENCE)) {
            if (is_array($decoded->codeReference)) {
                $type->setCodeReference(FHIRReference::jsonUnserialize(reset($decoded->codeReference), $config));
            } else {
                $type->setCodeReference(FHIRReference::jsonUnserialize($decoded->codeReference, $config));
            }
        }
        if (isset($decoded->codeCodeableConcept) || property_exists($decoded, self::FIELD_CODE_CODEABLE_CONCEPT)) {
            if (is_array($decoded->codeCodeableConcept)) {
                $type->setCodeCodeableConcept(FHIRCodeableConcept::jsonUnserialize(reset($decoded->codeCodeableConcept), $config));
            } else {
                $type->setCodeCodeableConcept(FHIRCodeableConcept::jsonUnserialize($decoded->codeCodeableConcept, $config));
            }
        }
        if (isset($decoded->parameter) || property_exists($decoded, self::FIELD_PARAMETER)) {
            if (is_object($decoded->parameter)) {
                $vals = [$decoded->parameter];
                $type->_setJSONFieldElideSingletonArray(self::FIELD_PARAMETER, true);
            } else {
                $vals = $decoded->parameter;
            }
            foreach($vals as $v) {
                $type->addParameter(FHIRDeviceRequestParameter::jsonUnserialize($v, $config));
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
        if (isset($decoded->authoredOn)
            || isset($decoded->_authoredOn)
            || property_exists($decoded, self::FIELD_AUTHORED_ON)
            || property_exists($decoded, self::FIELD_AUTHORED_ON_EXT)) {
            $v = $decoded->_authoredOn ?? new \stdClass();
            $v->value = $decoded->authoredOn ?? null;
            $type->setAuthoredOn(FHIRDateTime::jsonUnserialize($v, $config));
        }
        if (isset($decoded->requester) || property_exists($decoded, self::FIELD_REQUESTER)) {
            if (is_array($decoded->requester)) {
                $type->setRequester(FHIRReference::jsonUnserialize(reset($decoded->requester), $config));
            } else {
                $type->setRequester(FHIRReference::jsonUnserialize($decoded->requester, $config));
            }
        }
        if (isset($decoded->performerType) || property_exists($decoded, self::FIELD_PERFORMER_TYPE)) {
            if (is_array($decoded->performerType)) {
                $type->setPerformerType(FHIRCodeableConcept::jsonUnserialize(reset($decoded->performerType), $config));
            } else {
                $type->setPerformerType(FHIRCodeableConcept::jsonUnserialize($decoded->performerType, $config));
            }
        }
        if (isset($decoded->performer) || property_exists($decoded, self::FIELD_PERFORMER)) {
            if (is_array($decoded->performer)) {
                $type->setPerformer(FHIRReference::jsonUnserialize(reset($decoded->performer), $config));
            } else {
                $type->setPerformer(FHIRReference::jsonUnserialize($decoded->performer, $config));
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
        if (isset($decoded->insurance) || property_exists($decoded, self::FIELD_INSURANCE)) {
            if (is_object($decoded->insurance)) {
                $vals = [$decoded->insurance];
                $type->_setJSONFieldElideSingletonArray(self::FIELD_INSURANCE, true);
            } else {
                $vals = $decoded->insurance;
            }
            foreach($vals as $v) {
                $type->addInsurance(FHIRReference::jsonUnserialize($v, $config));
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
                $type->addSupportingInfo(FHIRReference::jsonUnserialize($v, $config));
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
        if (isset($decoded->relevantHistory) || property_exists($decoded, self::FIELD_RELEVANT_HISTORY)) {
            if (is_object($decoded->relevantHistory)) {
                $vals = [$decoded->relevantHistory];
                $type->_setJSONFieldElideSingletonArray(self::FIELD_RELEVANT_HISTORY, true);
            } else {
                $vals = $decoded->relevantHistory;
            }
            foreach($vals as $v) {
                $type->addRelevantHistory(FHIRReference::jsonUnserialize($v, $config));
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
        if (isset($this->basedOn) && [] !== $this->basedOn) {
            if ($this->_getJSONFieldElideSingletonArray(self::FIELD_BASED_ON) && 1 === count($this->basedOn)) {
                $out->basedOn = $this->basedOn[0];
            } else {
                $out->basedOn = $this->basedOn;
            }
        }
        if (isset($this->priorRequest) && [] !== $this->priorRequest) {
            if ($this->_getJSONFieldElideSingletonArray(self::FIELD_PRIOR_REQUEST) && 1 === count($this->priorRequest)) {
                $out->priorRequest = $this->priorRequest[0];
            } else {
                $out->priorRequest = $this->priorRequest;
            }
        }
        if (isset($this->groupIdentifier)) {
            $out->groupIdentifier = $this->groupIdentifier;
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
        if (isset($this->intent)) {
            if (null !== ($val = $this->intent->getValue())) {
                $out->intent = $val;
            }
            if ($this->intent->_nonValueFieldDefined()) {
                $ext = $this->intent->jsonSerialize();
                unset($ext->value);
                $out->_intent = $ext;
            }
        }
        if (isset($this->priority)) {
            if (null !== ($val = $this->priority->getValue())) {
                $out->priority = $val;
            }
            if ($this->priority->_nonValueFieldDefined()) {
                $ext = $this->priority->jsonSerialize();
                unset($ext->value);
                $out->_priority = $ext;
            }
        }
        if (isset($this->codeReference)) {
            $out->codeReference = $this->codeReference;
        }
        if (isset($this->codeCodeableConcept)) {
            $out->codeCodeableConcept = $this->codeCodeableConcept;
        }
        if (isset($this->parameter) && [] !== $this->parameter) {
            if ($this->_getJSONFieldElideSingletonArray(self::FIELD_PARAMETER) && 1 === count($this->parameter)) {
                $out->parameter = $this->parameter[0];
            } else {
                $out->parameter = $this->parameter;
            }
        }
        if (isset($this->subject)) {
            $out->subject = $this->subject;
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
        if (isset($this->occurrencePeriod)) {
            $out->occurrencePeriod = $this->occurrencePeriod;
        }
        if (isset($this->occurrenceTiming)) {
            $out->occurrenceTiming = $this->occurrenceTiming;
        }
        if (isset($this->authoredOn)) {
            if (null !== ($val = $this->authoredOn->getValue())) {
                $out->authoredOn = $val;
            }
            if ($this->authoredOn->_nonValueFieldDefined()) {
                $ext = $this->authoredOn->jsonSerialize();
                unset($ext->value);
                $out->_authoredOn = $ext;
            }
        }
        if (isset($this->requester)) {
            $out->requester = $this->requester;
        }
        if (isset($this->performerType)) {
            $out->performerType = $this->performerType;
        }
        if (isset($this->performer)) {
            $out->performer = $this->performer;
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
        if (isset($this->insurance) && [] !== $this->insurance) {
            if ($this->_getJSONFieldElideSingletonArray(self::FIELD_INSURANCE) && 1 === count($this->insurance)) {
                $out->insurance = $this->insurance[0];
            } else {
                $out->insurance = $this->insurance;
            }
        }
        if (isset($this->supportingInfo) && [] !== $this->supportingInfo) {
            if ($this->_getJSONFieldElideSingletonArray(self::FIELD_SUPPORTING_INFO) && 1 === count($this->supportingInfo)) {
                $out->supportingInfo = $this->supportingInfo[0];
            } else {
                $out->supportingInfo = $this->supportingInfo;
            }
        }
        if (isset($this->note) && [] !== $this->note) {
            if ($this->_getJSONFieldElideSingletonArray(self::FIELD_NOTE) && 1 === count($this->note)) {
                $out->note = $this->note[0];
            } else {
                $out->note = $this->note;
            }
        }
        if (isset($this->relevantHistory) && [] !== $this->relevantHistory) {
            if ($this->_getJSONFieldElideSingletonArray(self::FIELD_RELEVANT_HISTORY) && 1 === count($this->relevantHistory)) {
                $out->relevantHistory = $this->relevantHistory[0];
            } else {
                $out->relevantHistory = $this->relevantHistory;
            }
        }
        $out->resourceType = $this->_getResourceType();
        return $out;
    }
}
