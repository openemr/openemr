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
use OpenEMR\FHIR\Versions\R4\Types\FHIRCodePrimitive\FHIRRequestPriorityList;
use OpenEMR\FHIR\Versions\R4\Types\FHIRCodePrimitive\FHIRTaskIntentList;
use OpenEMR\FHIR\Versions\R4\Types\FHIRCodePrimitive\FHIRTaskStatusList;
use OpenEMR\FHIR\Versions\R4\Types\FHIRDateTimePrimitive;
use OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRAnnotation;
use OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRTask\FHIRTaskInput;
use OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRTask\FHIRTaskOutput;
use OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRTask\FHIRTaskRestriction;
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
use OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRRequestPriority;
use OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRString;
use OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRTaskIntent;
use OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRTaskStatus;
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
 * A task to be performed.
 * If the element is present, it must have either a \@value, an \@id, or extensions
 */
#[\Attribute(\Attribute::TARGET_PROPERTY)]
class FHIRTask extends FHIRDomainResource implements VersionContainedTypeInterface
{
    use TypeValidationsTrait,
        JSONSerializationOptionsTrait,
        XMLSerializationOptionsTrait;

    // name of FHIR type this class describes
    public const FHIR_TYPE_NAME = VersionConstants::TYPE_NAME_TASK;

    /* class_default.php:56 */
    public const FIELD_IDENTIFIER = 'identifier';
    public const FIELD_INSTANTIATES_CANONICAL = 'instantiatesCanonical';
    public const FIELD_INSTANTIATES_CANONICAL_EXT = '_instantiatesCanonical';
    public const FIELD_INSTANTIATES_URI = 'instantiatesUri';
    public const FIELD_INSTANTIATES_URI_EXT = '_instantiatesUri';
    public const FIELD_BASED_ON = 'basedOn';
    public const FIELD_GROUP_IDENTIFIER = 'groupIdentifier';
    public const FIELD_PART_OF = 'partOf';
    public const FIELD_STATUS = 'status';
    public const FIELD_STATUS_EXT = '_status';
    public const FIELD_STATUS_REASON = 'statusReason';
    public const FIELD_BUSINESS_STATUS = 'businessStatus';
    public const FIELD_INTENT = 'intent';
    public const FIELD_INTENT_EXT = '_intent';
    public const FIELD_PRIORITY = 'priority';
    public const FIELD_PRIORITY_EXT = '_priority';
    public const FIELD_CODE = 'code';
    public const FIELD_DESCRIPTION = 'description';
    public const FIELD_DESCRIPTION_EXT = '_description';
    public const FIELD_FOCUS = 'focus';
    public const FIELD_FOR = 'for';
    public const FIELD_ENCOUNTER = 'encounter';
    public const FIELD_EXECUTION_PERIOD = 'executionPeriod';
    public const FIELD_AUTHORED_ON = 'authoredOn';
    public const FIELD_AUTHORED_ON_EXT = '_authoredOn';
    public const FIELD_LAST_MODIFIED = 'lastModified';
    public const FIELD_LAST_MODIFIED_EXT = '_lastModified';
    public const FIELD_REQUESTER = 'requester';
    public const FIELD_PERFORMER_TYPE = 'performerType';
    public const FIELD_OWNER = 'owner';
    public const FIELD_LOCATION = 'location';
    public const FIELD_REASON_CODE = 'reasonCode';
    public const FIELD_REASON_REFERENCE = 'reasonReference';
    public const FIELD_INSURANCE = 'insurance';
    public const FIELD_NOTE = 'note';
    public const FIELD_RELEVANT_HISTORY = 'relevantHistory';
    public const FIELD_RESTRICTION = 'restriction';
    public const FIELD_INPUT = 'input';
    public const FIELD_OUTPUT = 'output';

    /* class_default.php:75 */
    private const _FHIR_VALIDATION_RULES = [
        self::FIELD_STATUS => [
            MinOccursRule::NAME => 1,
        ],
        self::FIELD_INTENT => [
            MinOccursRule::NAME => 1,
        ],
    ];

    /* class_default.php:96 */
    private array $_valueXMLLocations = [
        self::FIELD_INSTANTIATES_CANONICAL => ValueXMLLocationEnum::CONTAINER_ATTRIBUTE,
        self::FIELD_INSTANTIATES_URI => ValueXMLLocationEnum::CONTAINER_ATTRIBUTE,
        self::FIELD_STATUS => ValueXMLLocationEnum::CONTAINER_ATTRIBUTE,
        self::FIELD_INTENT => ValueXMLLocationEnum::CONTAINER_ATTRIBUTE,
        self::FIELD_PRIORITY => ValueXMLLocationEnum::CONTAINER_ATTRIBUTE,
        self::FIELD_DESCRIPTION => ValueXMLLocationEnum::CONTAINER_ATTRIBUTE,
        self::FIELD_AUTHORED_ON => ValueXMLLocationEnum::CONTAINER_ATTRIBUTE,
        self::FIELD_LAST_MODIFIED => ValueXMLLocationEnum::CONTAINER_ATTRIBUTE,
    ];

    /* class_default.php:112 */
    /**
     * An identifier - identifies some entity uniquely and unambiguously. Typically
     * this is used for business identifiers.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * The business identifier for this task.
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
     * The URL pointing to a *FHIR*-defined protocol, guideline, orderset or other
     * definition that is adhered to in whole or in part by this Task.
     *
     * @var \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRCanonical
     */
    #[FHIRCanonical]
    protected FHIRCanonical $instantiatesCanonical;
    /**
     * String of characters used to identify a name or a resource
     * see http://en.wikipedia.org/wiki/Uniform_resource_identifier
     * If the element is present, it must have either a \@value, an \@id, or extensions
     *
     * The URL pointing to an *externally* maintained protocol, guideline, orderset or
     * other definition that is adhered to in whole or in part by this Task.
     *
     * @var \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRUri
     */
    #[FHIRUri]
    protected FHIRUri $instantiatesUri;
    /**
     * A reference from one resource to another.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * BasedOn refers to a higher-level authorization that triggered the creation of
     * the task. It references a "request" resource such as a ServiceRequest,
     * MedicationRequest, ServiceRequest, CarePlan, etc. which is distinct from the
     * "request" resource the task is seeking to fulfill. This latter resource is
     * referenced by FocusOn. For example, based on a ServiceRequest (= BasedOn), a
     * task is created to fulfill a procedureRequest ( = FocusOn ) to collect a
     * specimen from a patient.
     *
     * @var iterable<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRReference>
     */
    #[FHIRReference]
    protected array $basedOn;
    /**
     * An identifier - identifies some entity uniquely and unambiguously. Typically
     * this is used for business identifiers.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * An identifier that links together multiple tasks and other requests that were
     * created in the same context.
     *
     * @var \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRIdentifier
     */
    #[FHIRIdentifier]
    protected FHIRIdentifier $groupIdentifier;
    /**
     * A reference from one resource to another.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * Task that this particular task is part of.
     *
     * @var iterable<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRReference>
     */
    #[FHIRReference]
    protected array $partOf;
    /**
     * The current status of the task.
     * If the element is present, it must have either a \@value, an \@id, or extensions
     *
     * The current status of the task.
     *
     * @var \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRTaskStatus
     */
    #[FHIRTaskStatus]
    protected FHIRTaskStatus $status;
    /**
     * A concept that may be defined by a formal reference to a terminology or ontology
     * or may be provided by text.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * An explanation as to why this task is held, failed, was refused, etc.
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
     * Contains business-specific nuances of the business state.
     *
     * @var \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRCodeableConcept
     */
    #[FHIRCodeableConcept]
    protected FHIRCodeableConcept $businessStatus;
    /**
     * Distinguishes whether the task is a proposal, plan or full order.
     * If the element is present, it must have either a \@value, an \@id, or extensions
     *
     * Indicates the "level" of actionability associated with the Task, i.e. i+R[9]Cs
     * this a proposed task, a planned task, an actionable task, etc.
     *
     * @var \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRTaskIntent
     */
    #[FHIRTaskIntent]
    protected FHIRTaskIntent $intent;
    /**
     * Identifies the level of importance to be assigned to actioning the request.
     * If the element is present, it must have either a \@value, an \@id, or extensions
     *
     * Indicates how quickly the Task should be addressed with respect to other
     * requests.
     *
     * @var \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRRequestPriority
     */
    #[FHIRRequestPriority]
    protected FHIRRequestPriority $priority;
    /**
     * A concept that may be defined by a formal reference to a terminology or ontology
     * or may be provided by text.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * A name or code (or both) briefly describing what the task involves.
     *
     * @var \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRCodeableConcept
     */
    #[FHIRCodeableConcept]
    protected FHIRCodeableConcept $code;
    /**
     * A sequence of Unicode characters
     * Note that FHIR strings SHALL NOT exceed 1MB in size
     * If the element is present, it must have either a \@value, an \@id, or extensions
     *
     * A free-text description of what is to be performed.
     *
     * @var \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRString
     */
    #[FHIRString]
    protected FHIRString $description;
    /**
     * A reference from one resource to another.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * The request being actioned or the resource being manipulated by this task.
     *
     * @var \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRReference
     */
    #[FHIRReference]
    protected FHIRReference $focus;
    /**
     * A reference from one resource to another.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * The entity who benefits from the performance of the service specified in the
     * task (e.g., the patient).
     *
     * @var \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRReference
     */
    #[FHIRReference]
    protected FHIRReference $for;
    /**
     * A reference from one resource to another.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * The healthcare event (e.g. a patient and healthcare provider interaction) during
     * which this task was created.
     *
     * @var \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRReference
     */
    #[FHIRReference]
    protected FHIRReference $encounter;
    /**
     * A time period defined by a start and end date and optionally time.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * Identifies the time action was first taken against the task (start) and/or the
     * time final action was taken against the task prior to marking it as completed
     * (end).
     *
     * @var \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRPeriod
     */
    #[FHIRPeriod]
    protected FHIRPeriod $executionPeriod;
    /**
     * A date, date-time or partial date (e.g. just year or year + month). If hours and
     * minutes are specified, a time zone SHALL be populated. The format is a union of
     * the schema types gYear, gYearMonth, date and dateTime. Seconds must be provided
     * due to schema type constraints but may be zero-filled and may be ignored. Dates
     * SHALL be valid dates.
     * If the element is present, it must have either a \@value, an \@id, or extensions
     *
     * The date and time this task was created.
     *
     * @var \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRDateTime
     */
    #[FHIRDateTime]
    protected FHIRDateTime $authoredOn;
    /**
     * A date, date-time or partial date (e.g. just year or year + month). If hours and
     * minutes are specified, a time zone SHALL be populated. The format is a union of
     * the schema types gYear, gYearMonth, date and dateTime. Seconds must be provided
     * due to schema type constraints but may be zero-filled and may be ignored. Dates
     * SHALL be valid dates.
     * If the element is present, it must have either a \@value, an \@id, or extensions
     *
     * The date and time of last modification to this task.
     *
     * @var \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRDateTime
     */
    #[FHIRDateTime]
    protected FHIRDateTime $lastModified;
    /**
     * A reference from one resource to another.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * The creator of the task.
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
     * The kind of participant that should perform the task.
     *
     * @var iterable<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRCodeableConcept>
     */
    #[FHIRCodeableConcept]
    protected array $performerType;
    /**
     * A reference from one resource to another.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * Individual organization or Device currently responsible for task execution.
     *
     * @var \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRReference
     */
    #[FHIRReference]
    protected FHIRReference $owner;
    /**
     * A reference from one resource to another.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * Principal physical location where the this task is performed.
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
     * A description or code indicating why this task needs to be performed.
     *
     * @var \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRCodeableConcept
     */
    #[FHIRCodeableConcept]
    protected FHIRCodeableConcept $reasonCode;
    /**
     * A reference from one resource to another.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * A resource reference indicating why this task needs to be performed.
     *
     * @var \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRReference
     */
    #[FHIRReference]
    protected FHIRReference $reasonReference;
    /**
     * A reference from one resource to another.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * Insurance plans, coverage extensions, pre-authorizations and/or
     * pre-determinations that may be relevant to the Task.
     *
     * @var iterable<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRReference>
     */
    #[FHIRReference]
    protected array $insurance;
    /**
     * A text note which also contains information about who made the statement and
     * when.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * Free-text information captured about the task as it progresses.
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
     * Links to Provenance records for past versions of this Task that identify key
     * state transitions or updates that are likely to be relevant to a user looking at
     * the current version of the task.
     *
     * @var iterable<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRReference>
     */
    #[FHIRReference]
    protected array $relevantHistory;
    /**
     * A task to be performed.
     *
     * If the Task.focus is a request resource and the task is seeking fulfillment
     * (i.e. is asking for the request to be actioned), this element identifies any
     * limitations on what parts of the referenced request should be actioned.
     *
     * @var \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRTask\FHIRTaskRestriction
     */
    #[FHIRTaskRestriction]
    protected FHIRTaskRestriction $restriction;
    /**
     * A task to be performed.
     *
     * Additional information that may be needed in the execution of the task.
     *
     * @var iterable<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRTask\FHIRTaskInput>
     */
    #[FHIRTaskInput]
    protected array $input;
    /**
     * A task to be performed.
     *
     * Outputs produced by the Task.
     *
     * @var iterable<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRTask\FHIRTaskOutput>
     */
    #[FHIRTaskOutput]
    protected array $output;

    /* constructor.php:61 */
    /**
     * FHIRTask Constructor
     * @param null|string|\OpenEMR\FHIR\Versions\R4\Types\FHIRIdPrimitive|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRId $id
     * @param null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRMeta $meta
     * @param null|string|\OpenEMR\FHIR\Versions\R4\Types\FHIRUriPrimitive|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRUri $implicitRules
     * @param null|string|\OpenEMR\FHIR\Versions\R4\Types\FHIRCodePrimitive|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRCode $language
     * @param null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRNarrative $text
     * @param null|iterable<\OpenEMR\FHIR\Versions\R4\Types\FHIRResourceContainer>|iterable<\OpenEMR\FHIR\Versions\R4\VersionContainedTypeInterface> $contained
     * @param null|iterable<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRExtension> $extension
     * @param null|iterable<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRExtension> $modifierExtension
     * @param null|iterable<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRIdentifier> $identifier
     * @param null|string|\OpenEMR\FHIR\Versions\R4\Types\FHIRCanonicalPrimitive|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRCanonical $instantiatesCanonical
     * @param null|string|\OpenEMR\FHIR\Versions\R4\Types\FHIRUriPrimitive|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRUri $instantiatesUri
     * @param null|iterable<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRReference> $basedOn
     * @param null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRIdentifier $groupIdentifier
     * @param null|iterable<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRReference> $partOf
     * @param null|string|\OpenEMR\FHIR\Versions\R4\Types\FHIRCodePrimitive\FHIRTaskStatusList|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRTaskStatus $status
     * @param null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRCodeableConcept $statusReason
     * @param null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRCodeableConcept $businessStatus
     * @param null|string|\OpenEMR\FHIR\Versions\R4\Types\FHIRCodePrimitive\FHIRTaskIntentList|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRTaskIntent $intent
     * @param null|string|\OpenEMR\FHIR\Versions\R4\Types\FHIRCodePrimitive\FHIRRequestPriorityList|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRRequestPriority $priority
     * @param null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRCodeableConcept $code
     * @param null|string|\OpenEMR\FHIR\Versions\R4\Types\FHIRStringPrimitive|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRString $description
     * @param null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRReference $focus
     * @param null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRReference $for
     * @param null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRReference $encounter
     * @param null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRPeriod $executionPeriod
     * @param null|string|\DateTimeInterface|\OpenEMR\FHIR\Versions\R4\Types\FHIRDateTimePrimitive|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRDateTime $authoredOn
     * @param null|string|\DateTimeInterface|\OpenEMR\FHIR\Versions\R4\Types\FHIRDateTimePrimitive|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRDateTime $lastModified
     * @param null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRReference $requester
     * @param null|iterable<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRCodeableConcept> $performerType
     * @param null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRReference $owner
     * @param null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRReference $location
     * @param null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRCodeableConcept $reasonCode
     * @param null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRReference $reasonReference
     * @param null|iterable<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRReference> $insurance
     * @param null|iterable<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRAnnotation> $note
     * @param null|iterable<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRReference> $relevantHistory
     * @param null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRTask\FHIRTaskRestriction $restriction
     * @param null|iterable<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRTask\FHIRTaskInput> $input
     * @param null|iterable<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRTask\FHIRTaskOutput> $output
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
                                null|string|FHIRCanonicalPrimitive|FHIRCanonical $instantiatesCanonical = null,
                                null|string|FHIRUriPrimitive|FHIRUri $instantiatesUri = null,
                                null|iterable $basedOn = null,
                                null|FHIRIdentifier $groupIdentifier = null,
                                null|iterable $partOf = null,
                                null|string|FHIRTaskStatusList|FHIRTaskStatus $status = null,
                                null|FHIRCodeableConcept $statusReason = null,
                                null|FHIRCodeableConcept $businessStatus = null,
                                null|string|FHIRTaskIntentList|FHIRTaskIntent $intent = null,
                                null|string|FHIRRequestPriorityList|FHIRRequestPriority $priority = null,
                                null|FHIRCodeableConcept $code = null,
                                null|string|FHIRStringPrimitive|FHIRString $description = null,
                                null|FHIRReference $focus = null,
                                null|FHIRReference $for = null,
                                null|FHIRReference $encounter = null,
                                null|FHIRPeriod $executionPeriod = null,
                                null|string|\DateTimeInterface|FHIRDateTimePrimitive|FHIRDateTime $authoredOn = null,
                                null|string|\DateTimeInterface|FHIRDateTimePrimitive|FHIRDateTime $lastModified = null,
                                null|FHIRReference $requester = null,
                                null|iterable $performerType = null,
                                null|FHIRReference $owner = null,
                                null|FHIRReference $location = null,
                                null|FHIRCodeableConcept $reasonCode = null,
                                null|FHIRReference $reasonReference = null,
                                null|iterable $insurance = null,
                                null|iterable $note = null,
                                null|iterable $relevantHistory = null,
                                null|FHIRTaskRestriction $restriction = null,
                                null|iterable $input = null,
                                null|iterable $output = null,
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
            $this->setInstantiatesCanonical($instantiatesCanonical);
        }
        if (null !== $instantiatesUri) {
            $this->setInstantiatesUri($instantiatesUri);
        }
        if (null !== $basedOn) {
            $this->setBasedOn(...$basedOn);
        }
        if (null !== $groupIdentifier) {
            $this->setGroupIdentifier($groupIdentifier);
        }
        if (null !== $partOf) {
            $this->setPartOf(...$partOf);
        }
        if (null !== $status) {
            $this->setStatus($status);
        }
        if (null !== $statusReason) {
            $this->setStatusReason($statusReason);
        }
        if (null !== $businessStatus) {
            $this->setBusinessStatus($businessStatus);
        }
        if (null !== $intent) {
            $this->setIntent($intent);
        }
        if (null !== $priority) {
            $this->setPriority($priority);
        }
        if (null !== $code) {
            $this->setCode($code);
        }
        if (null !== $description) {
            $this->setDescription($description);
        }
        if (null !== $focus) {
            $this->setFocus($focus);
        }
        if (null !== $for) {
            $this->setFor($for);
        }
        if (null !== $encounter) {
            $this->setEncounter($encounter);
        }
        if (null !== $executionPeriod) {
            $this->setExecutionPeriod($executionPeriod);
        }
        if (null !== $authoredOn) {
            $this->setAuthoredOn($authoredOn);
        }
        if (null !== $lastModified) {
            $this->setLastModified($lastModified);
        }
        if (null !== $requester) {
            $this->setRequester($requester);
        }
        if (null !== $performerType) {
            $this->setPerformerType(...$performerType);
        }
        if (null !== $owner) {
            $this->setOwner($owner);
        }
        if (null !== $location) {
            $this->setLocation($location);
        }
        if (null !== $reasonCode) {
            $this->setReasonCode($reasonCode);
        }
        if (null !== $reasonReference) {
            $this->setReasonReference($reasonReference);
        }
        if (null !== $insurance) {
            $this->setInsurance(...$insurance);
        }
        if (null !== $note) {
            $this->setNote(...$note);
        }
        if (null !== $relevantHistory) {
            $this->setRelevantHistory(...$relevantHistory);
        }
        if (null !== $restriction) {
            $this->setRestriction($restriction);
        }
        if (null !== $input) {
            $this->setInput(...$input);
        }
        if (null !== $output) {
            $this->setOutput(...$output);
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
     * The business identifier for this task.
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
     * The business identifier for this task.
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
     * The business identifier for this task.
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
     * The URL pointing to a *FHIR*-defined protocol, guideline, orderset or other
     * definition that is adhered to in whole or in part by this Task.
     *
     * @return null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRCanonical
     */
    public function getInstantiatesCanonical(): null|FHIRCanonical
    {
        return $this->instantiatesCanonical ?? null;
    }

    /**
     * A URI that is a reference to a canonical URL on a FHIR resource
     * see [Canonical References](references.html#canonical)
     * If the element is present, it must have either a \@value, an \@id referenced from
     * the Narrative, or extensions
     *
     * The URL pointing to a *FHIR*-defined protocol, guideline, orderset or other
     * definition that is adhered to in whole or in part by this Task.
     *
     * @param null|string|\OpenEMR\FHIR\Versions\R4\Types\FHIRCanonicalPrimitive|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRCanonical $instantiatesCanonical
     * @return static
     */
    public function setInstantiatesCanonical(null|string|FHIRCanonicalPrimitive|FHIRCanonical $instantiatesCanonical): self
    {
        if (null === $instantiatesCanonical) {
            unset($this->instantiatesCanonical);
            return $this;
        }
        if (!($instantiatesCanonical instanceof FHIRCanonical)) {
            $instantiatesCanonical = new FHIRCanonical(value: $instantiatesCanonical);
        }
        $this->instantiatesCanonical = $instantiatesCanonical;
        return $this;
    }

    /**
     * String of characters used to identify a name or a resource
     * see http://en.wikipedia.org/wiki/Uniform_resource_identifier
     * If the element is present, it must have either a \@value, an \@id, or extensions
     *
     * The URL pointing to an *externally* maintained protocol, guideline, orderset or
     * other definition that is adhered to in whole or in part by this Task.
     *
     * @return null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRUri
     */
    public function getInstantiatesUri(): null|FHIRUri
    {
        return $this->instantiatesUri ?? null;
    }

    /**
     * String of characters used to identify a name or a resource
     * see http://en.wikipedia.org/wiki/Uniform_resource_identifier
     * If the element is present, it must have either a \@value, an \@id, or extensions
     *
     * The URL pointing to an *externally* maintained protocol, guideline, orderset or
     * other definition that is adhered to in whole or in part by this Task.
     *
     * @param null|string|\OpenEMR\FHIR\Versions\R4\Types\FHIRUriPrimitive|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRUri $instantiatesUri
     * @return static
     */
    public function setInstantiatesUri(null|string|FHIRUriPrimitive|FHIRUri $instantiatesUri): self
    {
        if (null === $instantiatesUri) {
            unset($this->instantiatesUri);
            return $this;
        }
        if (!($instantiatesUri instanceof FHIRUri)) {
            $instantiatesUri = new FHIRUri(value: $instantiatesUri);
        }
        $this->instantiatesUri = $instantiatesUri;
        return $this;
    }

    /**
     * A reference from one resource to another.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * BasedOn refers to a higher-level authorization that triggered the creation of
     * the task. It references a "request" resource such as a ServiceRequest,
     * MedicationRequest, ServiceRequest, CarePlan, etc. which is distinct from the
     * "request" resource the task is seeking to fulfill. This latter resource is
     * referenced by FocusOn. For example, based on a ServiceRequest (= BasedOn), a
     * task is created to fulfill a procedureRequest ( = FocusOn ) to collect a
     * specimen from a patient.
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
     * BasedOn refers to a higher-level authorization that triggered the creation of
     * the task. It references a "request" resource such as a ServiceRequest,
     * MedicationRequest, ServiceRequest, CarePlan, etc. which is distinct from the
     * "request" resource the task is seeking to fulfill. This latter resource is
     * referenced by FocusOn. For example, based on a ServiceRequest (= BasedOn), a
     * task is created to fulfill a procedureRequest ( = FocusOn ) to collect a
     * specimen from a patient.
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
     * BasedOn refers to a higher-level authorization that triggered the creation of
     * the task. It references a "request" resource such as a ServiceRequest,
     * MedicationRequest, ServiceRequest, CarePlan, etc. which is distinct from the
     * "request" resource the task is seeking to fulfill. This latter resource is
     * referenced by FocusOn. For example, based on a ServiceRequest (= BasedOn), a
     * task is created to fulfill a procedureRequest ( = FocusOn ) to collect a
     * specimen from a patient.
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
     * An identifier - identifies some entity uniquely and unambiguously. Typically
     * this is used for business identifiers.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * An identifier that links together multiple tasks and other requests that were
     * created in the same context.
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
     * An identifier that links together multiple tasks and other requests that were
     * created in the same context.
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
     * A reference from one resource to another.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * Task that this particular task is part of.
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
     * Task that this particular task is part of.
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
     * Task that this particular task is part of.
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
     * The current status of the task.
     * If the element is present, it must have either a \@value, an \@id, or extensions
     *
     * The current status of the task.
     *
     * @return null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRTaskStatus
     */
    public function getStatus(): null|FHIRTaskStatus
    {
        return $this->status ?? null;
    }

    /**
     * The current status of the task.
     * If the element is present, it must have either a \@value, an \@id, or extensions
     *
     * The current status of the task.
     *
     * @param null|string|\OpenEMR\FHIR\Versions\R4\Types\FHIRCodePrimitive\FHIRTaskStatusList|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRTaskStatus $status
     * @return static
     */
    public function setStatus(null|string|FHIRTaskStatusList|FHIRTaskStatus $status): self
    {
        if (null === $status) {
            unset($this->status);
            return $this;
        }
        if (!($status instanceof FHIRTaskStatus)) {
            $status = new FHIRTaskStatus(value: $status);
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
     * An explanation as to why this task is held, failed, was refused, etc.
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
     * An explanation as to why this task is held, failed, was refused, etc.
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
     * Contains business-specific nuances of the business state.
     *
     * @return null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRCodeableConcept
     */
    public function getBusinessStatus(): null|FHIRCodeableConcept
    {
        return $this->businessStatus ?? null;
    }

    /**
     * A concept that may be defined by a formal reference to a terminology or ontology
     * or may be provided by text.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * Contains business-specific nuances of the business state.
     *
     * @param null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRCodeableConcept $businessStatus
     * @return static
     */
    public function setBusinessStatus(null|FHIRCodeableConcept $businessStatus): self
    {
        if (null === $businessStatus) {
            unset($this->businessStatus);
            return $this;
        }
        $this->businessStatus = $businessStatus;
        return $this;
    }

    /**
     * Distinguishes whether the task is a proposal, plan or full order.
     * If the element is present, it must have either a \@value, an \@id, or extensions
     *
     * Indicates the "level" of actionability associated with the Task, i.e. i+R[9]Cs
     * this a proposed task, a planned task, an actionable task, etc.
     *
     * @return null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRTaskIntent
     */
    public function getIntent(): null|FHIRTaskIntent
    {
        return $this->intent ?? null;
    }

    /**
     * Distinguishes whether the task is a proposal, plan or full order.
     * If the element is present, it must have either a \@value, an \@id, or extensions
     *
     * Indicates the "level" of actionability associated with the Task, i.e. i+R[9]Cs
     * this a proposed task, a planned task, an actionable task, etc.
     *
     * @param null|string|\OpenEMR\FHIR\Versions\R4\Types\FHIRCodePrimitive\FHIRTaskIntentList|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRTaskIntent $intent
     * @return static
     */
    public function setIntent(null|string|FHIRTaskIntentList|FHIRTaskIntent $intent): self
    {
        if (null === $intent) {
            unset($this->intent);
            return $this;
        }
        if (!($intent instanceof FHIRTaskIntent)) {
            $intent = new FHIRTaskIntent(value: $intent);
        }
        $this->intent = $intent;
        return $this;
    }

    /**
     * Identifies the level of importance to be assigned to actioning the request.
     * If the element is present, it must have either a \@value, an \@id, or extensions
     *
     * Indicates how quickly the Task should be addressed with respect to other
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
     * Indicates how quickly the Task should be addressed with respect to other
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
     * A concept that may be defined by a formal reference to a terminology or ontology
     * or may be provided by text.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * A name or code (or both) briefly describing what the task involves.
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
     * A name or code (or both) briefly describing what the task involves.
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
     * A sequence of Unicode characters
     * Note that FHIR strings SHALL NOT exceed 1MB in size
     * If the element is present, it must have either a \@value, an \@id, or extensions
     *
     * A free-text description of what is to be performed.
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
     * A free-text description of what is to be performed.
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
     * A reference from one resource to another.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * The request being actioned or the resource being manipulated by this task.
     *
     * @return null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRReference
     */
    public function getFocus(): null|FHIRReference
    {
        return $this->focus ?? null;
    }

    /**
     * A reference from one resource to another.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * The request being actioned or the resource being manipulated by this task.
     *
     * @param null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRReference $focus
     * @return static
     */
    public function setFocus(null|FHIRReference $focus): self
    {
        if (null === $focus) {
            unset($this->focus);
            return $this;
        }
        $this->focus = $focus;
        return $this;
    }

    /**
     * A reference from one resource to another.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * The entity who benefits from the performance of the service specified in the
     * task (e.g., the patient).
     *
     * @return null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRReference
     */
    public function getFor(): null|FHIRReference
    {
        return $this->for ?? null;
    }

    /**
     * A reference from one resource to another.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * The entity who benefits from the performance of the service specified in the
     * task (e.g., the patient).
     *
     * @param null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRReference $for
     * @return static
     */
    public function setFor(null|FHIRReference $for): self
    {
        if (null === $for) {
            unset($this->for);
            return $this;
        }
        $this->for = $for;
        return $this;
    }

    /**
     * A reference from one resource to another.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * The healthcare event (e.g. a patient and healthcare provider interaction) during
     * which this task was created.
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
     * The healthcare event (e.g. a patient and healthcare provider interaction) during
     * which this task was created.
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
     * A time period defined by a start and end date and optionally time.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * Identifies the time action was first taken against the task (start) and/or the
     * time final action was taken against the task prior to marking it as completed
     * (end).
     *
     * @return null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRPeriod
     */
    public function getExecutionPeriod(): null|FHIRPeriod
    {
        return $this->executionPeriod ?? null;
    }

    /**
     * A time period defined by a start and end date and optionally time.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * Identifies the time action was first taken against the task (start) and/or the
     * time final action was taken against the task prior to marking it as completed
     * (end).
     *
     * @param null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRPeriod $executionPeriod
     * @return static
     */
    public function setExecutionPeriod(null|FHIRPeriod $executionPeriod): self
    {
        if (null === $executionPeriod) {
            unset($this->executionPeriod);
            return $this;
        }
        $this->executionPeriod = $executionPeriod;
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
     * The date and time this task was created.
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
     * The date and time this task was created.
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
     * A date, date-time or partial date (e.g. just year or year + month). If hours and
     * minutes are specified, a time zone SHALL be populated. The format is a union of
     * the schema types gYear, gYearMonth, date and dateTime. Seconds must be provided
     * due to schema type constraints but may be zero-filled and may be ignored. Dates
     * SHALL be valid dates.
     * If the element is present, it must have either a \@value, an \@id, or extensions
     *
     * The date and time of last modification to this task.
     *
     * @return null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRDateTime
     */
    public function getLastModified(): null|FHIRDateTime
    {
        return $this->lastModified ?? null;
    }

    /**
     * A date, date-time or partial date (e.g. just year or year + month). If hours and
     * minutes are specified, a time zone SHALL be populated. The format is a union of
     * the schema types gYear, gYearMonth, date and dateTime. Seconds must be provided
     * due to schema type constraints but may be zero-filled and may be ignored. Dates
     * SHALL be valid dates.
     * If the element is present, it must have either a \@value, an \@id, or extensions
     *
     * The date and time of last modification to this task.
     *
     * @param null|string|\DateTimeInterface|\OpenEMR\FHIR\Versions\R4\Types\FHIRDateTimePrimitive|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRDateTime $lastModified
     * @return static
     */
    public function setLastModified(null|string|\DateTimeInterface|FHIRDateTimePrimitive|FHIRDateTime $lastModified): self
    {
        if (null === $lastModified) {
            unset($this->lastModified);
            return $this;
        }
        if (!($lastModified instanceof FHIRDateTime)) {
            $lastModified = new FHIRDateTime(value: $lastModified);
        }
        $this->lastModified = $lastModified;
        return $this;
    }

    /**
     * A reference from one resource to another.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * The creator of the task.
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
     * The creator of the task.
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
     * The kind of participant that should perform the task.
     *
     * @return iterable<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRCodeableConcept>
     */
    public function getPerformerType(): array
    {
        return $this->performerType ?? [];
    }

    /**
     * @return \ArrayIterator<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRCodeableConcept>
     */
    public function getPerformerTypeIterator(): iterable
    {
        if (!isset($this->performerType)) {
            return new \EmptyIterator();
        }
        return new \ArrayIterator($this->performerType);
    }

    /**
     * A concept that may be defined by a formal reference to a terminology or ontology
     * or may be provided by text.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * The kind of participant that should perform the task.
     *
     * @param \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRCodeableConcept $performerType
     * @return static
     */
    public function addPerformerType(FHIRCodeableConcept $performerType): self
    {
        if (!isset($this->performerType)) {
            $this->performerType = [];
        }
        $this->performerType[] = $performerType;
        return $this;
    }

    /**
     * A concept that may be defined by a formal reference to a terminology or ontology
     * or may be provided by text.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * The kind of participant that should perform the task.
     *
     * @param \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRCodeableConcept ...$performerType
     * @return static
     */
    public function setPerformerType(FHIRCodeableConcept ...$performerType): self
    {
        if ([] === $performerType) {
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
     * Individual organization or Device currently responsible for task execution.
     *
     * @return null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRReference
     */
    public function getOwner(): null|FHIRReference
    {
        return $this->owner ?? null;
    }

    /**
     * A reference from one resource to another.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * Individual organization or Device currently responsible for task execution.
     *
     * @param null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRReference $owner
     * @return static
     */
    public function setOwner(null|FHIRReference $owner): self
    {
        if (null === $owner) {
            unset($this->owner);
            return $this;
        }
        $this->owner = $owner;
        return $this;
    }

    /**
     * A reference from one resource to another.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * Principal physical location where the this task is performed.
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
     * Principal physical location where the this task is performed.
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
     * A description or code indicating why this task needs to be performed.
     *
     * @return null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRCodeableConcept
     */
    public function getReasonCode(): null|FHIRCodeableConcept
    {
        return $this->reasonCode ?? null;
    }

    /**
     * A concept that may be defined by a formal reference to a terminology or ontology
     * or may be provided by text.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * A description or code indicating why this task needs to be performed.
     *
     * @param null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRCodeableConcept $reasonCode
     * @return static
     */
    public function setReasonCode(null|FHIRCodeableConcept $reasonCode): self
    {
        if (null === $reasonCode) {
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
     * A resource reference indicating why this task needs to be performed.
     *
     * @return null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRReference
     */
    public function getReasonReference(): null|FHIRReference
    {
        return $this->reasonReference ?? null;
    }

    /**
     * A reference from one resource to another.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * A resource reference indicating why this task needs to be performed.
     *
     * @param null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRReference $reasonReference
     * @return static
     */
    public function setReasonReference(null|FHIRReference $reasonReference): self
    {
        if (null === $reasonReference) {
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
     * pre-determinations that may be relevant to the Task.
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
     * pre-determinations that may be relevant to the Task.
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
     * pre-determinations that may be relevant to the Task.
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
     * A text note which also contains information about who made the statement and
     * when.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * Free-text information captured about the task as it progresses.
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
     * Free-text information captured about the task as it progresses.
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
     * Free-text information captured about the task as it progresses.
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
     * Links to Provenance records for past versions of this Task that identify key
     * state transitions or updates that are likely to be relevant to a user looking at
     * the current version of the task.
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
     * Links to Provenance records for past versions of this Task that identify key
     * state transitions or updates that are likely to be relevant to a user looking at
     * the current version of the task.
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
     * Links to Provenance records for past versions of this Task that identify key
     * state transitions or updates that are likely to be relevant to a user looking at
     * the current version of the task.
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

    /**
     * A task to be performed.
     *
     * If the Task.focus is a request resource and the task is seeking fulfillment
     * (i.e. is asking for the request to be actioned), this element identifies any
     * limitations on what parts of the referenced request should be actioned.
     *
     * @return null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRTask\FHIRTaskRestriction
     */
    public function getRestriction(): null|FHIRTaskRestriction
    {
        return $this->restriction ?? null;
    }

    /**
     * A task to be performed.
     *
     * If the Task.focus is a request resource and the task is seeking fulfillment
     * (i.e. is asking for the request to be actioned), this element identifies any
     * limitations on what parts of the referenced request should be actioned.
     *
     * @param null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRTask\FHIRTaskRestriction $restriction
     * @return static
     */
    public function setRestriction(null|FHIRTaskRestriction $restriction): self
    {
        if (null === $restriction) {
            unset($this->restriction);
            return $this;
        }
        $this->restriction = $restriction;
        return $this;
    }

    /**
     * A task to be performed.
     *
     * Additional information that may be needed in the execution of the task.
     *
     * @return iterable<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRTask\FHIRTaskInput>
     */
    public function getInput(): array
    {
        return $this->input ?? [];
    }

    /**
     * @return \ArrayIterator<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRTask\FHIRTaskInput>
     */
    public function getInputIterator(): iterable
    {
        if (!isset($this->input)) {
            return new \EmptyIterator();
        }
        return new \ArrayIterator($this->input);
    }

    /**
     * A task to be performed.
     *
     * Additional information that may be needed in the execution of the task.
     *
     * @param \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRTask\FHIRTaskInput $input
     * @return static
     */
    public function addInput(FHIRTaskInput $input): self
    {
        if (!isset($this->input)) {
            $this->input = [];
        }
        $this->input[] = $input;
        return $this;
    }

    /**
     * A task to be performed.
     *
     * Additional information that may be needed in the execution of the task.
     *
     * @param \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRTask\FHIRTaskInput ...$input
     * @return static
     */
    public function setInput(FHIRTaskInput ...$input): self
    {
        if ([] === $input) {
            unset($this->input);
            return $this;
        }
        $this->input = $input;
        return $this;
    }

    /**
     * A task to be performed.
     *
     * Outputs produced by the Task.
     *
     * @return iterable<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRTask\FHIRTaskOutput>
     */
    public function getOutput(): array
    {
        return $this->output ?? [];
    }

    /**
     * @return \ArrayIterator<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRTask\FHIRTaskOutput>
     */
    public function getOutputIterator(): iterable
    {
        if (!isset($this->output)) {
            return new \EmptyIterator();
        }
        return new \ArrayIterator($this->output);
    }

    /**
     * A task to be performed.
     *
     * Outputs produced by the Task.
     *
     * @param \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRTask\FHIRTaskOutput $output
     * @return static
     */
    public function addOutput(FHIRTaskOutput $output): self
    {
        if (!isset($this->output)) {
            $this->output = [];
        }
        $this->output[] = $output;
        return $this;
    }

    /**
     * A task to be performed.
     *
     * Outputs produced by the Task.
     *
     * @param \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRTask\FHIRTaskOutput ...$output
     * @return static
     */
    public function setOutput(FHIRTaskOutput ...$output): self
    {
        if ([] === $output) {
            unset($this->output);
            return $this;
        }
        $this->output = $output;
        return $this;
    }

    /* class_default.php:201 */
    /**
     * @param string|\SimpleXMLElement $element
     * @param null|\OpenEMR\FHIR\Encoding\UnserializeConfig $config
     * @param null|\OpenEMR\FHIR\Versions\R4\Types\FHIRResource\FHIRDomainResource\FHIRTask $type
     * @return \OpenEMR\FHIR\Versions\R4\Types\FHIRResource\FHIRDomainResource\FHIRTask
     * @throws \Exception
     */
    public static function xmlUnserialize(string|\SimpleXMLElement $element,
                                          null|UnserializeConfig $config = null,
                                          null|ResourceTypeInterface $type = null): self
    {
        if (null === $type) {
            $type = new static();
        } else if (!($type instanceof FHIRTask)) {
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
                $type->setInstantiatesCanonical(FHIRCanonical::xmlUnserialize($ce, $config));
            } else if (self::FIELD_INSTANTIATES_URI === $cen) {
                $type->setInstantiatesUri(FHIRUri::xmlUnserialize($ce, $config));
            } else if (self::FIELD_BASED_ON === $cen) {
                $type->addBasedOn(FHIRReference::xmlUnserialize($ce, $config));
            } else if (self::FIELD_GROUP_IDENTIFIER === $cen) {
                $type->setGroupIdentifier(FHIRIdentifier::xmlUnserialize($ce, $config));
            } else if (self::FIELD_PART_OF === $cen) {
                $type->addPartOf(FHIRReference::xmlUnserialize($ce, $config));
            } else if (self::FIELD_STATUS === $cen) {
                $type->setStatus(FHIRTaskStatus::xmlUnserialize($ce, $config));
            } else if (self::FIELD_STATUS_REASON === $cen) {
                $type->setStatusReason(FHIRCodeableConcept::xmlUnserialize($ce, $config));
            } else if (self::FIELD_BUSINESS_STATUS === $cen) {
                $type->setBusinessStatus(FHIRCodeableConcept::xmlUnserialize($ce, $config));
            } else if (self::FIELD_INTENT === $cen) {
                $type->setIntent(FHIRTaskIntent::xmlUnserialize($ce, $config));
            } else if (self::FIELD_PRIORITY === $cen) {
                $type->setPriority(FHIRRequestPriority::xmlUnserialize($ce, $config));
            } else if (self::FIELD_CODE === $cen) {
                $type->setCode(FHIRCodeableConcept::xmlUnserialize($ce, $config));
            } else if (self::FIELD_DESCRIPTION === $cen) {
                $type->setDescription(FHIRString::xmlUnserialize($ce, $config));
            } else if (self::FIELD_FOCUS === $cen) {
                $type->setFocus(FHIRReference::xmlUnserialize($ce, $config));
            } else if (self::FIELD_FOR === $cen) {
                $type->setFor(FHIRReference::xmlUnserialize($ce, $config));
            } else if (self::FIELD_ENCOUNTER === $cen) {
                $type->setEncounter(FHIRReference::xmlUnserialize($ce, $config));
            } else if (self::FIELD_EXECUTION_PERIOD === $cen) {
                $type->setExecutionPeriod(FHIRPeriod::xmlUnserialize($ce, $config));
            } else if (self::FIELD_AUTHORED_ON === $cen) {
                $type->setAuthoredOn(FHIRDateTime::xmlUnserialize($ce, $config));
            } else if (self::FIELD_LAST_MODIFIED === $cen) {
                $type->setLastModified(FHIRDateTime::xmlUnserialize($ce, $config));
            } else if (self::FIELD_REQUESTER === $cen) {
                $type->setRequester(FHIRReference::xmlUnserialize($ce, $config));
            } else if (self::FIELD_PERFORMER_TYPE === $cen) {
                $type->addPerformerType(FHIRCodeableConcept::xmlUnserialize($ce, $config));
            } else if (self::FIELD_OWNER === $cen) {
                $type->setOwner(FHIRReference::xmlUnserialize($ce, $config));
            } else if (self::FIELD_LOCATION === $cen) {
                $type->setLocation(FHIRReference::xmlUnserialize($ce, $config));
            } else if (self::FIELD_REASON_CODE === $cen) {
                $type->setReasonCode(FHIRCodeableConcept::xmlUnserialize($ce, $config));
            } else if (self::FIELD_REASON_REFERENCE === $cen) {
                $type->setReasonReference(FHIRReference::xmlUnserialize($ce, $config));
            } else if (self::FIELD_INSURANCE === $cen) {
                $type->addInsurance(FHIRReference::xmlUnserialize($ce, $config));
            } else if (self::FIELD_NOTE === $cen) {
                $type->addNote(FHIRAnnotation::xmlUnserialize($ce, $config));
            } else if (self::FIELD_RELEVANT_HISTORY === $cen) {
                $type->addRelevantHistory(FHIRReference::xmlUnserialize($ce, $config));
            } else if (self::FIELD_RESTRICTION === $cen) {
                $type->setRestriction(FHIRTaskRestriction::xmlUnserialize($ce, $config));
            } else if (self::FIELD_INPUT === $cen) {
                $type->addInput(FHIRTaskInput::xmlUnserialize($ce, $config));
            } else if (self::FIELD_OUTPUT === $cen) {
                $type->addOutput(FHIRTaskOutput::xmlUnserialize($ce, $config));
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
        if (isset($attributes[self::FIELD_INSTANTIATES_CANONICAL])) {
            if (isset($type->instantiatesCanonical)) {
                $type->instantiatesCanonical->setValue((string)$attributes[self::FIELD_INSTANTIATES_CANONICAL]);
            } else {
                $type->setInstantiatesCanonical((string)$attributes[self::FIELD_INSTANTIATES_CANONICAL]);
            }
            $type->_setXMLFieldValueLocation(self::FIELD_INSTANTIATES_CANONICAL, ValueXMLLocationEnum::PARENT_ATTRIBUTE);
        }
        if (isset($attributes[self::FIELD_INSTANTIATES_URI])) {
            if (isset($type->instantiatesUri)) {
                $type->instantiatesUri->setValue((string)$attributes[self::FIELD_INSTANTIATES_URI]);
            } else {
                $type->setInstantiatesUri((string)$attributes[self::FIELD_INSTANTIATES_URI]);
            }
            $type->_setXMLFieldValueLocation(self::FIELD_INSTANTIATES_URI, ValueXMLLocationEnum::PARENT_ATTRIBUTE);
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
        if (isset($attributes[self::FIELD_DESCRIPTION])) {
            if (isset($type->description)) {
                $type->description->setValue((string)$attributes[self::FIELD_DESCRIPTION]);
            } else {
                $type->setDescription((string)$attributes[self::FIELD_DESCRIPTION]);
            }
            $type->_setXMLFieldValueLocation(self::FIELD_DESCRIPTION, ValueXMLLocationEnum::PARENT_ATTRIBUTE);
        }
        if (isset($attributes[self::FIELD_AUTHORED_ON])) {
            if (isset($type->authoredOn)) {
                $type->authoredOn->setValue((string)$attributes[self::FIELD_AUTHORED_ON]);
            } else {
                $type->setAuthoredOn((string)$attributes[self::FIELD_AUTHORED_ON]);
            }
            $type->_setXMLFieldValueLocation(self::FIELD_AUTHORED_ON, ValueXMLLocationEnum::PARENT_ATTRIBUTE);
        }
        if (isset($attributes[self::FIELD_LAST_MODIFIED])) {
            if (isset($type->lastModified)) {
                $type->lastModified->setValue((string)$attributes[self::FIELD_LAST_MODIFIED]);
            } else {
                $type->setLastModified((string)$attributes[self::FIELD_LAST_MODIFIED]);
            }
            $type->_setXMLFieldValueLocation(self::FIELD_LAST_MODIFIED, ValueXMLLocationEnum::PARENT_ATTRIBUTE);
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
            $xw->openRootNode('Task', $this->_getSourceXMLNS());
        }
        if (isset($this->instantiatesCanonical) && ValueXMLLocationEnum::PARENT_ATTRIBUTE === $this->_valueXMLLocations[self::FIELD_INSTANTIATES_CANONICAL]) {
            $xw->writeAttribute(self::FIELD_INSTANTIATES_CANONICAL, $this->instantiatesCanonical->_getValueAsString());
        }
        if (isset($this->instantiatesUri) && ValueXMLLocationEnum::PARENT_ATTRIBUTE === $this->_valueXMLLocations[self::FIELD_INSTANTIATES_URI]) {
            $xw->writeAttribute(self::FIELD_INSTANTIATES_URI, $this->instantiatesUri->_getValueAsString());
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
        if (isset($this->description) && ValueXMLLocationEnum::PARENT_ATTRIBUTE === $this->_valueXMLLocations[self::FIELD_DESCRIPTION]) {
            $xw->writeAttribute(self::FIELD_DESCRIPTION, $this->description->_getValueAsString());
        }
        if (isset($this->authoredOn) && ValueXMLLocationEnum::PARENT_ATTRIBUTE === $this->_valueXMLLocations[self::FIELD_AUTHORED_ON]) {
            $xw->writeAttribute(self::FIELD_AUTHORED_ON, $this->authoredOn->_getValueAsString());
        }
        if (isset($this->lastModified) && ValueXMLLocationEnum::PARENT_ATTRIBUTE === $this->_valueXMLLocations[self::FIELD_LAST_MODIFIED]) {
            $xw->writeAttribute(self::FIELD_LAST_MODIFIED, $this->lastModified->_getValueAsString());
        }
        parent::xmlSerialize($xw, $config);
        if (isset($this->identifier)) {
            foreach ($this->identifier as $v) {
                $xw->startElement(self::FIELD_IDENTIFIER);
                $v->xmlSerialize($xw, $config);
                $xw->endElement();
            }
        }
        if (isset($this->instantiatesCanonical)
            && (ValueXMLLocationEnum::PARENT_ATTRIBUTE !== $this->_valueXMLLocations[self::FIELD_INSTANTIATES_CANONICAL]
                || $this->instantiatesCanonical->_nonValueFieldDefined())) {
            $xw->startElement(self::FIELD_INSTANTIATES_CANONICAL);
            $this->instantiatesCanonical->xmlSerialize($xw, $config, $this->_valueXMLLocations[self::FIELD_INSTANTIATES_CANONICAL]);
            $xw->endElement();
        }
        if (isset($this->instantiatesUri)
            && (ValueXMLLocationEnum::PARENT_ATTRIBUTE !== $this->_valueXMLLocations[self::FIELD_INSTANTIATES_URI]
                || $this->instantiatesUri->_nonValueFieldDefined())) {
            $xw->startElement(self::FIELD_INSTANTIATES_URI);
            $this->instantiatesUri->xmlSerialize($xw, $config, $this->_valueXMLLocations[self::FIELD_INSTANTIATES_URI]);
            $xw->endElement();
        }
        if (isset($this->basedOn)) {
            foreach ($this->basedOn as $v) {
                $xw->startElement(self::FIELD_BASED_ON);
                $v->xmlSerialize($xw, $config);
                $xw->endElement();
            }
        }
        if (isset($this->groupIdentifier)) {
            $xw->startElement(self::FIELD_GROUP_IDENTIFIER);
            $this->groupIdentifier->xmlSerialize($xw, $config);
            $xw->endElement();
        }
        if (isset($this->partOf)) {
            foreach ($this->partOf as $v) {
                $xw->startElement(self::FIELD_PART_OF);
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
        if (isset($this->businessStatus)) {
            $xw->startElement(self::FIELD_BUSINESS_STATUS);
            $this->businessStatus->xmlSerialize($xw, $config);
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
        if (isset($this->code)) {
            $xw->startElement(self::FIELD_CODE);
            $this->code->xmlSerialize($xw, $config);
            $xw->endElement();
        }
        if (isset($this->description)
            && (ValueXMLLocationEnum::PARENT_ATTRIBUTE !== $this->_valueXMLLocations[self::FIELD_DESCRIPTION]
                || $this->description->_nonValueFieldDefined())) {
            $xw->startElement(self::FIELD_DESCRIPTION);
            $this->description->xmlSerialize($xw, $config, $this->_valueXMLLocations[self::FIELD_DESCRIPTION]);
            $xw->endElement();
        }
        if (isset($this->focus)) {
            $xw->startElement(self::FIELD_FOCUS);
            $this->focus->xmlSerialize($xw, $config);
            $xw->endElement();
        }
        if (isset($this->for)) {
            $xw->startElement(self::FIELD_FOR);
            $this->for->xmlSerialize($xw, $config);
            $xw->endElement();
        }
        if (isset($this->encounter)) {
            $xw->startElement(self::FIELD_ENCOUNTER);
            $this->encounter->xmlSerialize($xw, $config);
            $xw->endElement();
        }
        if (isset($this->executionPeriod)) {
            $xw->startElement(self::FIELD_EXECUTION_PERIOD);
            $this->executionPeriod->xmlSerialize($xw, $config);
            $xw->endElement();
        }
        if (isset($this->authoredOn)
            && (ValueXMLLocationEnum::PARENT_ATTRIBUTE !== $this->_valueXMLLocations[self::FIELD_AUTHORED_ON]
                || $this->authoredOn->_nonValueFieldDefined())) {
            $xw->startElement(self::FIELD_AUTHORED_ON);
            $this->authoredOn->xmlSerialize($xw, $config, $this->_valueXMLLocations[self::FIELD_AUTHORED_ON]);
            $xw->endElement();
        }
        if (isset($this->lastModified)
            && (ValueXMLLocationEnum::PARENT_ATTRIBUTE !== $this->_valueXMLLocations[self::FIELD_LAST_MODIFIED]
                || $this->lastModified->_nonValueFieldDefined())) {
            $xw->startElement(self::FIELD_LAST_MODIFIED);
            $this->lastModified->xmlSerialize($xw, $config, $this->_valueXMLLocations[self::FIELD_LAST_MODIFIED]);
            $xw->endElement();
        }
        if (isset($this->requester)) {
            $xw->startElement(self::FIELD_REQUESTER);
            $this->requester->xmlSerialize($xw, $config);
            $xw->endElement();
        }
        if (isset($this->performerType)) {
            foreach ($this->performerType as $v) {
                $xw->startElement(self::FIELD_PERFORMER_TYPE);
                $v->xmlSerialize($xw, $config);
                $xw->endElement();
            }
        }
        if (isset($this->owner)) {
            $xw->startElement(self::FIELD_OWNER);
            $this->owner->xmlSerialize($xw, $config);
            $xw->endElement();
        }
        if (isset($this->location)) {
            $xw->startElement(self::FIELD_LOCATION);
            $this->location->xmlSerialize($xw, $config);
            $xw->endElement();
        }
        if (isset($this->reasonCode)) {
            $xw->startElement(self::FIELD_REASON_CODE);
            $this->reasonCode->xmlSerialize($xw, $config);
            $xw->endElement();
        }
        if (isset($this->reasonReference)) {
            $xw->startElement(self::FIELD_REASON_REFERENCE);
            $this->reasonReference->xmlSerialize($xw, $config);
            $xw->endElement();
        }
        if (isset($this->insurance)) {
            foreach ($this->insurance as $v) {
                $xw->startElement(self::FIELD_INSURANCE);
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
        if (isset($this->restriction)) {
            $xw->startElement(self::FIELD_RESTRICTION);
            $this->restriction->xmlSerialize($xw, $config);
            $xw->endElement();
        }
        if (isset($this->input)) {
            foreach ($this->input as $v) {
                $xw->startElement(self::FIELD_INPUT);
                $v->xmlSerialize($xw, $config);
                $xw->endElement();
            }
        }
        if (isset($this->output)) {
            foreach ($this->output as $v) {
                $xw->startElement(self::FIELD_OUTPUT);
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
     * @param null|\OpenEMR\FHIR\Versions\R4\Types\FHIRResource\FHIRDomainResource\FHIRTask $type
     * @return \OpenEMR\FHIR\Versions\R4\Types\FHIRResource\FHIRDomainResource\FHIRTask
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
        } else if (!($type instanceof FHIRTask)) {
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
            $v = $decoded->_instantiatesCanonical ?? new \stdClass();
            $v->value = $decoded->instantiatesCanonical ?? null;
            $type->setInstantiatesCanonical(FHIRCanonical::jsonUnserialize($v, $config));
        }
        if (isset($decoded->instantiatesUri)
            || isset($decoded->_instantiatesUri)
            || property_exists($decoded, self::FIELD_INSTANTIATES_URI)
            || property_exists($decoded, self::FIELD_INSTANTIATES_URI_EXT)) {
            $v = $decoded->_instantiatesUri ?? new \stdClass();
            $v->value = $decoded->instantiatesUri ?? null;
            $type->setInstantiatesUri(FHIRUri::jsonUnserialize($v, $config));
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
        if (isset($decoded->groupIdentifier) || property_exists($decoded, self::FIELD_GROUP_IDENTIFIER)) {
            if (is_array($decoded->groupIdentifier)) {
                $type->setGroupIdentifier(FHIRIdentifier::jsonUnserialize(reset($decoded->groupIdentifier), $config));
            } else {
                $type->setGroupIdentifier(FHIRIdentifier::jsonUnserialize($decoded->groupIdentifier, $config));
            }
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
        if (isset($decoded->status)
            || isset($decoded->_status)
            || property_exists($decoded, self::FIELD_STATUS)
            || property_exists($decoded, self::FIELD_STATUS_EXT)) {
            $v = $decoded->_status ?? new \stdClass();
            $v->value = $decoded->status ?? null;
            $type->setStatus(FHIRTaskStatus::jsonUnserialize($v, $config));
        }
        if (isset($decoded->statusReason) || property_exists($decoded, self::FIELD_STATUS_REASON)) {
            if (is_array($decoded->statusReason)) {
                $type->setStatusReason(FHIRCodeableConcept::jsonUnserialize(reset($decoded->statusReason), $config));
            } else {
                $type->setStatusReason(FHIRCodeableConcept::jsonUnserialize($decoded->statusReason, $config));
            }
        }
        if (isset($decoded->businessStatus) || property_exists($decoded, self::FIELD_BUSINESS_STATUS)) {
            if (is_array($decoded->businessStatus)) {
                $type->setBusinessStatus(FHIRCodeableConcept::jsonUnserialize(reset($decoded->businessStatus), $config));
            } else {
                $type->setBusinessStatus(FHIRCodeableConcept::jsonUnserialize($decoded->businessStatus, $config));
            }
        }
        if (isset($decoded->intent)
            || isset($decoded->_intent)
            || property_exists($decoded, self::FIELD_INTENT)
            || property_exists($decoded, self::FIELD_INTENT_EXT)) {
            $v = $decoded->_intent ?? new \stdClass();
            $v->value = $decoded->intent ?? null;
            $type->setIntent(FHIRTaskIntent::jsonUnserialize($v, $config));
        }
        if (isset($decoded->priority)
            || isset($decoded->_priority)
            || property_exists($decoded, self::FIELD_PRIORITY)
            || property_exists($decoded, self::FIELD_PRIORITY_EXT)) {
            $v = $decoded->_priority ?? new \stdClass();
            $v->value = $decoded->priority ?? null;
            $type->setPriority(FHIRRequestPriority::jsonUnserialize($v, $config));
        }
        if (isset($decoded->code) || property_exists($decoded, self::FIELD_CODE)) {
            if (is_array($decoded->code)) {
                $type->setCode(FHIRCodeableConcept::jsonUnserialize(reset($decoded->code), $config));
            } else {
                $type->setCode(FHIRCodeableConcept::jsonUnserialize($decoded->code, $config));
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
        if (isset($decoded->focus) || property_exists($decoded, self::FIELD_FOCUS)) {
            if (is_array($decoded->focus)) {
                $type->setFocus(FHIRReference::jsonUnserialize(reset($decoded->focus), $config));
            } else {
                $type->setFocus(FHIRReference::jsonUnserialize($decoded->focus, $config));
            }
        }
        if (isset($decoded->for) || property_exists($decoded, self::FIELD_FOR)) {
            if (is_array($decoded->for)) {
                $type->setFor(FHIRReference::jsonUnserialize(reset($decoded->for), $config));
            } else {
                $type->setFor(FHIRReference::jsonUnserialize($decoded->for, $config));
            }
        }
        if (isset($decoded->encounter) || property_exists($decoded, self::FIELD_ENCOUNTER)) {
            if (is_array($decoded->encounter)) {
                $type->setEncounter(FHIRReference::jsonUnserialize(reset($decoded->encounter), $config));
            } else {
                $type->setEncounter(FHIRReference::jsonUnserialize($decoded->encounter, $config));
            }
        }
        if (isset($decoded->executionPeriod) || property_exists($decoded, self::FIELD_EXECUTION_PERIOD)) {
            if (is_array($decoded->executionPeriod)) {
                $type->setExecutionPeriod(FHIRPeriod::jsonUnserialize(reset($decoded->executionPeriod), $config));
            } else {
                $type->setExecutionPeriod(FHIRPeriod::jsonUnserialize($decoded->executionPeriod, $config));
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
        if (isset($decoded->lastModified)
            || isset($decoded->_lastModified)
            || property_exists($decoded, self::FIELD_LAST_MODIFIED)
            || property_exists($decoded, self::FIELD_LAST_MODIFIED_EXT)) {
            $v = $decoded->_lastModified ?? new \stdClass();
            $v->value = $decoded->lastModified ?? null;
            $type->setLastModified(FHIRDateTime::jsonUnserialize($v, $config));
        }
        if (isset($decoded->requester) || property_exists($decoded, self::FIELD_REQUESTER)) {
            if (is_array($decoded->requester)) {
                $type->setRequester(FHIRReference::jsonUnserialize(reset($decoded->requester), $config));
            } else {
                $type->setRequester(FHIRReference::jsonUnserialize($decoded->requester, $config));
            }
        }
        if (isset($decoded->performerType) || property_exists($decoded, self::FIELD_PERFORMER_TYPE)) {
            if (is_object($decoded->performerType)) {
                $vals = [$decoded->performerType];
                $type->_setJSONFieldElideSingletonArray(self::FIELD_PERFORMER_TYPE, true);
            } else {
                $vals = $decoded->performerType;
            }
            foreach($vals as $v) {
                $type->addPerformerType(FHIRCodeableConcept::jsonUnserialize($v, $config));
            }
        }
        if (isset($decoded->owner) || property_exists($decoded, self::FIELD_OWNER)) {
            if (is_array($decoded->owner)) {
                $type->setOwner(FHIRReference::jsonUnserialize(reset($decoded->owner), $config));
            } else {
                $type->setOwner(FHIRReference::jsonUnserialize($decoded->owner, $config));
            }
        }
        if (isset($decoded->location) || property_exists($decoded, self::FIELD_LOCATION)) {
            if (is_array($decoded->location)) {
                $type->setLocation(FHIRReference::jsonUnserialize(reset($decoded->location), $config));
            } else {
                $type->setLocation(FHIRReference::jsonUnserialize($decoded->location, $config));
            }
        }
        if (isset($decoded->reasonCode) || property_exists($decoded, self::FIELD_REASON_CODE)) {
            if (is_array($decoded->reasonCode)) {
                $type->setReasonCode(FHIRCodeableConcept::jsonUnserialize(reset($decoded->reasonCode), $config));
            } else {
                $type->setReasonCode(FHIRCodeableConcept::jsonUnserialize($decoded->reasonCode, $config));
            }
        }
        if (isset($decoded->reasonReference) || property_exists($decoded, self::FIELD_REASON_REFERENCE)) {
            if (is_array($decoded->reasonReference)) {
                $type->setReasonReference(FHIRReference::jsonUnserialize(reset($decoded->reasonReference), $config));
            } else {
                $type->setReasonReference(FHIRReference::jsonUnserialize($decoded->reasonReference, $config));
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
        if (isset($decoded->restriction) || property_exists($decoded, self::FIELD_RESTRICTION)) {
            if (is_array($decoded->restriction)) {
                $type->setRestriction(FHIRTaskRestriction::jsonUnserialize(reset($decoded->restriction), $config));
            } else {
                $type->setRestriction(FHIRTaskRestriction::jsonUnserialize($decoded->restriction, $config));
            }
        }
        if (isset($decoded->input) || property_exists($decoded, self::FIELD_INPUT)) {
            if (is_object($decoded->input)) {
                $vals = [$decoded->input];
                $type->_setJSONFieldElideSingletonArray(self::FIELD_INPUT, true);
            } else {
                $vals = $decoded->input;
            }
            foreach($vals as $v) {
                $type->addInput(FHIRTaskInput::jsonUnserialize($v, $config));
            }
        }
        if (isset($decoded->output) || property_exists($decoded, self::FIELD_OUTPUT)) {
            if (is_object($decoded->output)) {
                $vals = [$decoded->output];
                $type->_setJSONFieldElideSingletonArray(self::FIELD_OUTPUT, true);
            } else {
                $vals = $decoded->output;
            }
            foreach($vals as $v) {
                $type->addOutput(FHIRTaskOutput::jsonUnserialize($v, $config));
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
        if (isset($this->instantiatesCanonical)) {
            if (null !== ($val = $this->instantiatesCanonical->getValue())) {
                $out->instantiatesCanonical = $val;
            }
            if ($this->instantiatesCanonical->_nonValueFieldDefined()) {
                $ext = $this->instantiatesCanonical->jsonSerialize();
                unset($ext->value);
                $out->_instantiatesCanonical = $ext;
            }
        }
        if (isset($this->instantiatesUri)) {
            if (null !== ($val = $this->instantiatesUri->getValue())) {
                $out->instantiatesUri = $val;
            }
            if ($this->instantiatesUri->_nonValueFieldDefined()) {
                $ext = $this->instantiatesUri->jsonSerialize();
                unset($ext->value);
                $out->_instantiatesUri = $ext;
            }
        }
        if (isset($this->basedOn) && [] !== $this->basedOn) {
            if ($this->_getJSONFieldElideSingletonArray(self::FIELD_BASED_ON) && 1 === count($this->basedOn)) {
                $out->basedOn = $this->basedOn[0];
            } else {
                $out->basedOn = $this->basedOn;
            }
        }
        if (isset($this->groupIdentifier)) {
            $out->groupIdentifier = $this->groupIdentifier;
        }
        if (isset($this->partOf) && [] !== $this->partOf) {
            if ($this->_getJSONFieldElideSingletonArray(self::FIELD_PART_OF) && 1 === count($this->partOf)) {
                $out->partOf = $this->partOf[0];
            } else {
                $out->partOf = $this->partOf;
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
        if (isset($this->businessStatus)) {
            $out->businessStatus = $this->businessStatus;
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
        if (isset($this->code)) {
            $out->code = $this->code;
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
        if (isset($this->focus)) {
            $out->focus = $this->focus;
        }
        if (isset($this->for)) {
            $out->for = $this->for;
        }
        if (isset($this->encounter)) {
            $out->encounter = $this->encounter;
        }
        if (isset($this->executionPeriod)) {
            $out->executionPeriod = $this->executionPeriod;
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
        if (isset($this->lastModified)) {
            if (null !== ($val = $this->lastModified->getValue())) {
                $out->lastModified = $val;
            }
            if ($this->lastModified->_nonValueFieldDefined()) {
                $ext = $this->lastModified->jsonSerialize();
                unset($ext->value);
                $out->_lastModified = $ext;
            }
        }
        if (isset($this->requester)) {
            $out->requester = $this->requester;
        }
        if (isset($this->performerType) && [] !== $this->performerType) {
            if ($this->_getJSONFieldElideSingletonArray(self::FIELD_PERFORMER_TYPE) && 1 === count($this->performerType)) {
                $out->performerType = $this->performerType[0];
            } else {
                $out->performerType = $this->performerType;
            }
        }
        if (isset($this->owner)) {
            $out->owner = $this->owner;
        }
        if (isset($this->location)) {
            $out->location = $this->location;
        }
        if (isset($this->reasonCode)) {
            $out->reasonCode = $this->reasonCode;
        }
        if (isset($this->reasonReference)) {
            $out->reasonReference = $this->reasonReference;
        }
        if (isset($this->insurance) && [] !== $this->insurance) {
            if ($this->_getJSONFieldElideSingletonArray(self::FIELD_INSURANCE) && 1 === count($this->insurance)) {
                $out->insurance = $this->insurance[0];
            } else {
                $out->insurance = $this->insurance;
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
        if (isset($this->restriction)) {
            $out->restriction = $this->restriction;
        }
        if (isset($this->input) && [] !== $this->input) {
            if ($this->_getJSONFieldElideSingletonArray(self::FIELD_INPUT) && 1 === count($this->input)) {
                $out->input = $this->input[0];
            } else {
                $out->input = $this->input;
            }
        }
        if (isset($this->output) && [] !== $this->output) {
            if ($this->_getJSONFieldElideSingletonArray(self::FIELD_OUTPUT) && 1 === count($this->output)) {
                $out->output = $this->output[0];
            } else {
                $out->output = $this->output;
            }
        }
        $out->resourceType = $this->_getResourceType();
        return $out;
    }
}
