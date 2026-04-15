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
use OpenEMR\FHIR\Versions\R4\Types\FHIRCodePrimitive\FHIRAppointmentStatusList;
use OpenEMR\FHIR\Versions\R4\Types\FHIRDateTimePrimitive;
use OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRAppointmentStatus;
use OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRAppointment\FHIRAppointmentParticipant;
use OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRCode;
use OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRCodeableConcept;
use OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRDateTime;
use OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRExtension;
use OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRId;
use OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRIdentifier;
use OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRInstant;
use OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRMeta;
use OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRNarrative;
use OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRPeriod;
use OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRPositiveInt;
use OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRReference;
use OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRString;
use OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRUnsignedInt;
use OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRUri;
use OpenEMR\FHIR\Versions\R4\Types\FHIRIdPrimitive;
use OpenEMR\FHIR\Versions\R4\Types\FHIRInstantPrimitive;
use OpenEMR\FHIR\Versions\R4\Types\FHIRPositiveIntPrimitive;
use OpenEMR\FHIR\Versions\R4\Types\FHIRResource\FHIRDomainResource;
use OpenEMR\FHIR\Versions\R4\Types\FHIRStringPrimitive;
use OpenEMR\FHIR\Versions\R4\Types\FHIRUnsignedIntPrimitive;
use OpenEMR\FHIR\Versions\R4\Types\FHIRUriPrimitive;
use OpenEMR\FHIR\Versions\R4\Version;
use OpenEMR\FHIR\Versions\R4\VersionConstants;
use OpenEMR\FHIR\Versions\R4\VersionContainedTypeInterface;
use OpenEMR\FHIR\Versions\R4\VersionTypeMap;

/**
 * A booking of a healthcare event among patient(s), practitioner(s), related
 * person(s) and/or device(s) for a specific date/time. This may result in one or
 * more Encounter(s).
 * If the element is present, it must have either a \@value, an \@id, or extensions
 */
#[\Attribute(\Attribute::TARGET_PROPERTY)]
class FHIRAppointment extends FHIRDomainResource implements VersionContainedTypeInterface
{
    use TypeValidationsTrait,
        JSONSerializationOptionsTrait,
        XMLSerializationOptionsTrait;

    // name of FHIR type this class describes
    public const FHIR_TYPE_NAME = VersionConstants::TYPE_NAME_APPOINTMENT;

    /* class_default.php:56 */
    public const FIELD_IDENTIFIER = 'identifier';
    public const FIELD_STATUS = 'status';
    public const FIELD_STATUS_EXT = '_status';
    public const FIELD_CANCELATION_REASON = 'cancelationReason';
    public const FIELD_SERVICE_CATEGORY = 'serviceCategory';
    public const FIELD_SERVICE_TYPE = 'serviceType';
    public const FIELD_SPECIALTY = 'specialty';
    public const FIELD_APPOINTMENT_TYPE = 'appointmentType';
    public const FIELD_REASON_CODE = 'reasonCode';
    public const FIELD_REASON_REFERENCE = 'reasonReference';
    public const FIELD_PRIORITY = 'priority';
    public const FIELD_PRIORITY_EXT = '_priority';
    public const FIELD_DESCRIPTION = 'description';
    public const FIELD_DESCRIPTION_EXT = '_description';
    public const FIELD_SUPPORTING_INFORMATION = 'supportingInformation';
    public const FIELD_START = 'start';
    public const FIELD_START_EXT = '_start';
    public const FIELD_END = 'end';
    public const FIELD_END_EXT = '_end';
    public const FIELD_MINUTES_DURATION = 'minutesDuration';
    public const FIELD_MINUTES_DURATION_EXT = '_minutesDuration';
    public const FIELD_SLOT = 'slot';
    public const FIELD_CREATED = 'created';
    public const FIELD_CREATED_EXT = '_created';
    public const FIELD_COMMENT = 'comment';
    public const FIELD_COMMENT_EXT = '_comment';
    public const FIELD_PATIENT_INSTRUCTION = 'patientInstruction';
    public const FIELD_PATIENT_INSTRUCTION_EXT = '_patientInstruction';
    public const FIELD_BASED_ON = 'basedOn';
    public const FIELD_PARTICIPANT = 'participant';
    public const FIELD_REQUESTED_PERIOD = 'requestedPeriod';

    /* class_default.php:75 */
    private const _FHIR_VALIDATION_RULES = [
        self::FIELD_STATUS => [
            MinOccursRule::NAME => 1,
        ],
        self::FIELD_PARTICIPANT => [
            MinOccursRule::NAME => 1,
        ],
    ];

    /* class_default.php:96 */
    private array $_valueXMLLocations = [
        self::FIELD_STATUS => ValueXMLLocationEnum::CONTAINER_ATTRIBUTE,
        self::FIELD_PRIORITY => ValueXMLLocationEnum::CONTAINER_ATTRIBUTE,
        self::FIELD_DESCRIPTION => ValueXMLLocationEnum::CONTAINER_ATTRIBUTE,
        self::FIELD_START => ValueXMLLocationEnum::CONTAINER_ATTRIBUTE,
        self::FIELD_END => ValueXMLLocationEnum::CONTAINER_ATTRIBUTE,
        self::FIELD_MINUTES_DURATION => ValueXMLLocationEnum::CONTAINER_ATTRIBUTE,
        self::FIELD_CREATED => ValueXMLLocationEnum::CONTAINER_ATTRIBUTE,
        self::FIELD_COMMENT => ValueXMLLocationEnum::CONTAINER_ATTRIBUTE,
        self::FIELD_PATIENT_INSTRUCTION => ValueXMLLocationEnum::CONTAINER_ATTRIBUTE,
    ];

    /* class_default.php:112 */
    /**
     * An identifier - identifies some entity uniquely and unambiguously. Typically
     * this is used for business identifiers.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * This records identifiers associated with this appointment concern that are
     * defined by business processes and/or used to refer to it when a direct URL
     * reference to the resource itself is not appropriate (e.g. in CDA documents, or
     * in written / printed documentation).
     *
     * @var iterable<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRIdentifier>
     */
    #[FHIRIdentifier]
    protected array $identifier;
    /**
     * The free/busy status of an appointment.
     * If the element is present, it must have either a \@value, an \@id, or extensions
     *
     * The overall status of the Appointment. Each of the participants has their own
     * participation status which indicates their involvement in the process, however
     * this status indicates the shared status.
     *
     * @var \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRAppointmentStatus
     */
    #[FHIRAppointmentStatus]
    protected FHIRAppointmentStatus $status;
    /**
     * A concept that may be defined by a formal reference to a terminology or ontology
     * or may be provided by text.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * The coded reason for the appointment being cancelled. This is often used in
     * reporting/billing/futher processing to determine if further actions are
     * required, or specific fees apply.
     *
     * @var \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRCodeableConcept
     */
    #[FHIRCodeableConcept]
    protected FHIRCodeableConcept $cancelationReason;
    /**
     * A concept that may be defined by a formal reference to a terminology or ontology
     * or may be provided by text.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * A broad categorization of the service that is to be performed during this
     * appointment.
     *
     * @var iterable<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRCodeableConcept>
     */
    #[FHIRCodeableConcept]
    protected array $serviceCategory;
    /**
     * A concept that may be defined by a formal reference to a terminology or ontology
     * or may be provided by text.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * The specific service that is to be performed during this appointment.
     *
     * @var iterable<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRCodeableConcept>
     */
    #[FHIRCodeableConcept]
    protected array $serviceType;
    /**
     * A concept that may be defined by a formal reference to a terminology or ontology
     * or may be provided by text.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * The specialty of a practitioner that would be required to perform the service
     * requested in this appointment.
     *
     * @var iterable<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRCodeableConcept>
     */
    #[FHIRCodeableConcept]
    protected array $specialty;
    /**
     * A concept that may be defined by a formal reference to a terminology or ontology
     * or may be provided by text.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * The style of appointment or patient that has been booked in the slot (not
     * service type).
     *
     * @var \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRCodeableConcept
     */
    #[FHIRCodeableConcept]
    protected FHIRCodeableConcept $appointmentType;
    /**
     * A concept that may be defined by a formal reference to a terminology or ontology
     * or may be provided by text.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * The coded reason that this appointment is being scheduled. This is more clinical
     * than administrative.
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
     * Reason the appointment has been scheduled to take place, as specified using
     * information from another resource. When the patient arrives and the encounter
     * begins it may be used as the admission diagnosis. The indication will typically
     * be a Condition (with other resources referenced in the evidence.detail), or a
     * Procedure.
     *
     * @var iterable<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRReference>
     */
    #[FHIRReference]
    protected array $reasonReference;
    /**
     * An integer with a value that is not negative (e.g. >= 0)
     * If the element is present, it must have either a \@value, an \@id referenced from
     * the Narrative, or extensions
     *
     * The priority of the appointment. Can be used to make informed decisions if
     * needing to re-prioritize appointments. (The iCal Standard specifies as
     * undefined, 1 as highest, 9 as lowest priority).
     *
     * @var \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRUnsignedInt
     */
    #[FHIRUnsignedInt]
    protected FHIRUnsignedInt $priority;
    /**
     * A sequence of Unicode characters
     * Note that FHIR strings SHALL NOT exceed 1MB in size
     * If the element is present, it must have either a \@value, an \@id, or extensions
     *
     * The brief description of the appointment as would be shown on a subject line in
     * a meeting request, or appointment list. Detailed or expanded information should
     * be put in the comment field.
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
     * Additional information to support the appointment provided when making the
     * appointment.
     *
     * @var iterable<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRReference>
     */
    #[FHIRReference]
    protected array $supportingInformation;
    /**
     * An instant in time - known at least to the second
     * Note: This is intended for where precisely observed times are required,
     * typically system logs etc., and not human-reported times - for them, see date
     * and dateTime (which can be as precise as instant, but is not required to be)
     * below. Time zone is always required
     * If the element is present, it must have either a \@value, an \@id, or extensions
     *
     * Date/Time that the appointment is to take place.
     *
     * @var \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRInstant
     */
    #[FHIRInstant]
    protected FHIRInstant $start;
    /**
     * An instant in time - known at least to the second
     * Note: This is intended for where precisely observed times are required,
     * typically system logs etc., and not human-reported times - for them, see date
     * and dateTime (which can be as precise as instant, but is not required to be)
     * below. Time zone is always required
     * If the element is present, it must have either a \@value, an \@id, or extensions
     *
     * Date/Time that the appointment is to conclude.
     *
     * @var \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRInstant
     */
    #[FHIRInstant]
    protected FHIRInstant $end;
    /**
     * An integer with a value that is positive (e.g. >0)
     * If the element is present, it must have either a \@value, an \@id referenced from
     * the Narrative, or extensions
     *
     * Number of minutes that the appointment is to take. This can be less than the
     * duration between the start and end times. For example, where the actual time of
     * appointment is only an estimate or if a 30 minute appointment is being
     * requested, but any time would work. Also, if there is, for example, a planned 15
     * minute break in the middle of a long appointment, the duration may be 15 minutes
     * less than the difference between the start and end.
     *
     * @var \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRPositiveInt
     */
    #[FHIRPositiveInt]
    protected FHIRPositiveInt $minutesDuration;
    /**
     * A reference from one resource to another.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * The slots from the participants' schedules that will be filled by the
     * appointment.
     *
     * @var iterable<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRReference>
     */
    #[FHIRReference]
    protected array $slot;
    /**
     * A date, date-time or partial date (e.g. just year or year + month). If hours and
     * minutes are specified, a time zone SHALL be populated. The format is a union of
     * the schema types gYear, gYearMonth, date and dateTime. Seconds must be provided
     * due to schema type constraints but may be zero-filled and may be ignored. Dates
     * SHALL be valid dates.
     * If the element is present, it must have either a \@value, an \@id, or extensions
     *
     * The date that this appointment was initially created. This could be different to
     * the meta.lastModified value on the initial entry, as this could have been before
     * the resource was created on the FHIR server, and should remain unchanged over
     * the lifespan of the appointment.
     *
     * @var \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRDateTime
     */
    #[FHIRDateTime]
    protected FHIRDateTime $created;
    /**
     * A sequence of Unicode characters
     * Note that FHIR strings SHALL NOT exceed 1MB in size
     * If the element is present, it must have either a \@value, an \@id, or extensions
     *
     * Additional comments about the appointment.
     *
     * @var \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRString
     */
    #[FHIRString]
    protected FHIRString $comment;
    /**
     * A sequence of Unicode characters
     * Note that FHIR strings SHALL NOT exceed 1MB in size
     * If the element is present, it must have either a \@value, an \@id, or extensions
     *
     * While Appointment.comment contains information for internal use,
     * Appointment.patientInstructions is used to capture patient facing information
     * about the Appointment (e.g. please bring your referral or fast from 8pm night
     * before).
     *
     * @var \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRString
     */
    #[FHIRString]
    protected FHIRString $patientInstruction;
    /**
     * A reference from one resource to another.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * The service request this appointment is allocated to assess (e.g. incoming
     * referral or procedure request).
     *
     * @var iterable<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRReference>
     */
    #[FHIRReference]
    protected array $basedOn;
    /**
     * A booking of a healthcare event among patient(s), practitioner(s), related
     * person(s) and/or device(s) for a specific date/time. This may result in one or
     * more Encounter(s).
     *
     * List of participants involved in the appointment.
     *
     * @var iterable<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRAppointment\FHIRAppointmentParticipant>
     */
    #[FHIRAppointmentParticipant]
    protected array $participant;
    /**
     * A time period defined by a start and end date and optionally time.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * A set of date ranges (potentially including times) that the appointment is
     * preferred to be scheduled within. The duration (usually in minutes) could also
     * be provided to indicate the length of the appointment to fill and populate the
     * start/end times for the actual allocated time. However, in other situations the
     * duration may be calculated by the scheduling system.
     *
     * @var iterable<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRPeriod>
     */
    #[FHIRPeriod]
    protected array $requestedPeriod;

    /* constructor.php:61 */
    /**
     * FHIRAppointment Constructor
     * @param null|string|\OpenEMR\FHIR\Versions\R4\Types\FHIRIdPrimitive|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRId $id
     * @param null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRMeta $meta
     * @param null|string|\OpenEMR\FHIR\Versions\R4\Types\FHIRUriPrimitive|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRUri $implicitRules
     * @param null|string|\OpenEMR\FHIR\Versions\R4\Types\FHIRCodePrimitive|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRCode $language
     * @param null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRNarrative $text
     * @param null|iterable<\OpenEMR\FHIR\Versions\R4\Types\FHIRResourceContainer>|iterable<\OpenEMR\FHIR\Versions\R4\VersionContainedTypeInterface> $contained
     * @param null|iterable<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRExtension> $extension
     * @param null|iterable<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRExtension> $modifierExtension
     * @param null|iterable<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRIdentifier> $identifier
     * @param null|string|\OpenEMR\FHIR\Versions\R4\Types\FHIRCodePrimitive\FHIRAppointmentStatusList|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRAppointmentStatus $status
     * @param null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRCodeableConcept $cancelationReason
     * @param null|iterable<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRCodeableConcept> $serviceCategory
     * @param null|iterable<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRCodeableConcept> $serviceType
     * @param null|iterable<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRCodeableConcept> $specialty
     * @param null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRCodeableConcept $appointmentType
     * @param null|iterable<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRCodeableConcept> $reasonCode
     * @param null|iterable<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRReference> $reasonReference
     * @param null|string|int|float|\OpenEMR\FHIR\Versions\R4\Types\FHIRUnsignedIntPrimitive|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRUnsignedInt $priority
     * @param null|string|\OpenEMR\FHIR\Versions\R4\Types\FHIRStringPrimitive|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRString $description
     * @param null|iterable<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRReference> $supportingInformation
     * @param null|string|\DateTimeInterface|\OpenEMR\FHIR\Versions\R4\Types\FHIRInstantPrimitive|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRInstant $start
     * @param null|string|\DateTimeInterface|\OpenEMR\FHIR\Versions\R4\Types\FHIRInstantPrimitive|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRInstant $end
     * @param null|string|float|\OpenEMR\FHIR\Versions\R4\Types\FHIRPositiveIntPrimitive|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRPositiveInt $minutesDuration
     * @param null|iterable<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRReference> $slot
     * @param null|string|\DateTimeInterface|\OpenEMR\FHIR\Versions\R4\Types\FHIRDateTimePrimitive|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRDateTime $created
     * @param null|string|\OpenEMR\FHIR\Versions\R4\Types\FHIRStringPrimitive|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRString $comment
     * @param null|string|\OpenEMR\FHIR\Versions\R4\Types\FHIRStringPrimitive|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRString $patientInstruction
     * @param null|iterable<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRReference> $basedOn
     * @param null|iterable<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRAppointment\FHIRAppointmentParticipant> $participant
     * @param null|iterable<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRPeriod> $requestedPeriod
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
                                null|string|FHIRAppointmentStatusList|FHIRAppointmentStatus $status = null,
                                null|FHIRCodeableConcept $cancelationReason = null,
                                null|iterable $serviceCategory = null,
                                null|iterable $serviceType = null,
                                null|iterable $specialty = null,
                                null|FHIRCodeableConcept $appointmentType = null,
                                null|iterable $reasonCode = null,
                                null|iterable $reasonReference = null,
                                null|string|int|float|FHIRUnsignedIntPrimitive|FHIRUnsignedInt $priority = null,
                                null|string|FHIRStringPrimitive|FHIRString $description = null,
                                null|iterable $supportingInformation = null,
                                null|string|\DateTimeInterface|FHIRInstantPrimitive|FHIRInstant $start = null,
                                null|string|\DateTimeInterface|FHIRInstantPrimitive|FHIRInstant $end = null,
                                null|string|float|FHIRPositiveIntPrimitive|FHIRPositiveInt $minutesDuration = null,
                                null|iterable $slot = null,
                                null|string|\DateTimeInterface|FHIRDateTimePrimitive|FHIRDateTime $created = null,
                                null|string|FHIRStringPrimitive|FHIRString $comment = null,
                                null|string|FHIRStringPrimitive|FHIRString $patientInstruction = null,
                                null|iterable $basedOn = null,
                                null|iterable $participant = null,
                                null|iterable $requestedPeriod = null,
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
        if (null !== $cancelationReason) {
            $this->setCancelationReason($cancelationReason);
        }
        if (null !== $serviceCategory) {
            $this->setServiceCategory(...$serviceCategory);
        }
        if (null !== $serviceType) {
            $this->setServiceType(...$serviceType);
        }
        if (null !== $specialty) {
            $this->setSpecialty(...$specialty);
        }
        if (null !== $appointmentType) {
            $this->setAppointmentType($appointmentType);
        }
        if (null !== $reasonCode) {
            $this->setReasonCode(...$reasonCode);
        }
        if (null !== $reasonReference) {
            $this->setReasonReference(...$reasonReference);
        }
        if (null !== $priority) {
            $this->setPriority($priority);
        }
        if (null !== $description) {
            $this->setDescription($description);
        }
        if (null !== $supportingInformation) {
            $this->setSupportingInformation(...$supportingInformation);
        }
        if (null !== $start) {
            $this->setStart($start);
        }
        if (null !== $end) {
            $this->setEnd($end);
        }
        if (null !== $minutesDuration) {
            $this->setMinutesDuration($minutesDuration);
        }
        if (null !== $slot) {
            $this->setSlot(...$slot);
        }
        if (null !== $created) {
            $this->setCreated($created);
        }
        if (null !== $comment) {
            $this->setComment($comment);
        }
        if (null !== $patientInstruction) {
            $this->setPatientInstruction($patientInstruction);
        }
        if (null !== $basedOn) {
            $this->setBasedOn(...$basedOn);
        }
        if (null !== $participant) {
            $this->setParticipant(...$participant);
        }
        if (null !== $requestedPeriod) {
            $this->setRequestedPeriod(...$requestedPeriod);
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
     * This records identifiers associated with this appointment concern that are
     * defined by business processes and/or used to refer to it when a direct URL
     * reference to the resource itself is not appropriate (e.g. in CDA documents, or
     * in written / printed documentation).
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
     * This records identifiers associated with this appointment concern that are
     * defined by business processes and/or used to refer to it when a direct URL
     * reference to the resource itself is not appropriate (e.g. in CDA documents, or
     * in written / printed documentation).
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
     * This records identifiers associated with this appointment concern that are
     * defined by business processes and/or used to refer to it when a direct URL
     * reference to the resource itself is not appropriate (e.g. in CDA documents, or
     * in written / printed documentation).
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
     * The free/busy status of an appointment.
     * If the element is present, it must have either a \@value, an \@id, or extensions
     *
     * The overall status of the Appointment. Each of the participants has their own
     * participation status which indicates their involvement in the process, however
     * this status indicates the shared status.
     *
     * @return null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRAppointmentStatus
     */
    public function getStatus(): null|FHIRAppointmentStatus
    {
        return $this->status ?? null;
    }

    /**
     * The free/busy status of an appointment.
     * If the element is present, it must have either a \@value, an \@id, or extensions
     *
     * The overall status of the Appointment. Each of the participants has their own
     * participation status which indicates their involvement in the process, however
     * this status indicates the shared status.
     *
     * @param null|string|\OpenEMR\FHIR\Versions\R4\Types\FHIRCodePrimitive\FHIRAppointmentStatusList|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRAppointmentStatus $status
     * @return static
     */
    public function setStatus(null|string|FHIRAppointmentStatusList|FHIRAppointmentStatus $status): self
    {
        if (null === $status) {
            unset($this->status);
            return $this;
        }
        if (!($status instanceof FHIRAppointmentStatus)) {
            $status = new FHIRAppointmentStatus(value: $status);
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
     * The coded reason for the appointment being cancelled. This is often used in
     * reporting/billing/futher processing to determine if further actions are
     * required, or specific fees apply.
     *
     * @return null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRCodeableConcept
     */
    public function getCancelationReason(): null|FHIRCodeableConcept
    {
        return $this->cancelationReason ?? null;
    }

    /**
     * A concept that may be defined by a formal reference to a terminology or ontology
     * or may be provided by text.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * The coded reason for the appointment being cancelled. This is often used in
     * reporting/billing/futher processing to determine if further actions are
     * required, or specific fees apply.
     *
     * @param null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRCodeableConcept $cancelationReason
     * @return static
     */
    public function setCancelationReason(null|FHIRCodeableConcept $cancelationReason): self
    {
        if (null === $cancelationReason) {
            unset($this->cancelationReason);
            return $this;
        }
        $this->cancelationReason = $cancelationReason;
        return $this;
    }

    /**
     * A concept that may be defined by a formal reference to a terminology or ontology
     * or may be provided by text.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * A broad categorization of the service that is to be performed during this
     * appointment.
     *
     * @return iterable<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRCodeableConcept>
     */
    public function getServiceCategory(): array
    {
        return $this->serviceCategory ?? [];
    }

    /**
     * @return \ArrayIterator<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRCodeableConcept>
     */
    public function getServiceCategoryIterator(): iterable
    {
        if (!isset($this->serviceCategory)) {
            return new \EmptyIterator();
        }
        return new \ArrayIterator($this->serviceCategory);
    }

    /**
     * A concept that may be defined by a formal reference to a terminology or ontology
     * or may be provided by text.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * A broad categorization of the service that is to be performed during this
     * appointment.
     *
     * @param \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRCodeableConcept $serviceCategory
     * @return static
     */
    public function addServiceCategory(FHIRCodeableConcept $serviceCategory): self
    {
        if (!isset($this->serviceCategory)) {
            $this->serviceCategory = [];
        }
        $this->serviceCategory[] = $serviceCategory;
        return $this;
    }

    /**
     * A concept that may be defined by a formal reference to a terminology or ontology
     * or may be provided by text.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * A broad categorization of the service that is to be performed during this
     * appointment.
     *
     * @param \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRCodeableConcept ...$serviceCategory
     * @return static
     */
    public function setServiceCategory(FHIRCodeableConcept ...$serviceCategory): self
    {
        if ([] === $serviceCategory) {
            unset($this->serviceCategory);
            return $this;
        }
        $this->serviceCategory = $serviceCategory;
        return $this;
    }

    /**
     * A concept that may be defined by a formal reference to a terminology or ontology
     * or may be provided by text.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * The specific service that is to be performed during this appointment.
     *
     * @return iterable<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRCodeableConcept>
     */
    public function getServiceType(): array
    {
        return $this->serviceType ?? [];
    }

    /**
     * @return \ArrayIterator<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRCodeableConcept>
     */
    public function getServiceTypeIterator(): iterable
    {
        if (!isset($this->serviceType)) {
            return new \EmptyIterator();
        }
        return new \ArrayIterator($this->serviceType);
    }

    /**
     * A concept that may be defined by a formal reference to a terminology or ontology
     * or may be provided by text.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * The specific service that is to be performed during this appointment.
     *
     * @param \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRCodeableConcept $serviceType
     * @return static
     */
    public function addServiceType(FHIRCodeableConcept $serviceType): self
    {
        if (!isset($this->serviceType)) {
            $this->serviceType = [];
        }
        $this->serviceType[] = $serviceType;
        return $this;
    }

    /**
     * A concept that may be defined by a formal reference to a terminology or ontology
     * or may be provided by text.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * The specific service that is to be performed during this appointment.
     *
     * @param \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRCodeableConcept ...$serviceType
     * @return static
     */
    public function setServiceType(FHIRCodeableConcept ...$serviceType): self
    {
        if ([] === $serviceType) {
            unset($this->serviceType);
            return $this;
        }
        $this->serviceType = $serviceType;
        return $this;
    }

    /**
     * A concept that may be defined by a formal reference to a terminology or ontology
     * or may be provided by text.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * The specialty of a practitioner that would be required to perform the service
     * requested in this appointment.
     *
     * @return iterable<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRCodeableConcept>
     */
    public function getSpecialty(): array
    {
        return $this->specialty ?? [];
    }

    /**
     * @return \ArrayIterator<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRCodeableConcept>
     */
    public function getSpecialtyIterator(): iterable
    {
        if (!isset($this->specialty)) {
            return new \EmptyIterator();
        }
        return new \ArrayIterator($this->specialty);
    }

    /**
     * A concept that may be defined by a formal reference to a terminology or ontology
     * or may be provided by text.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * The specialty of a practitioner that would be required to perform the service
     * requested in this appointment.
     *
     * @param \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRCodeableConcept $specialty
     * @return static
     */
    public function addSpecialty(FHIRCodeableConcept $specialty): self
    {
        if (!isset($this->specialty)) {
            $this->specialty = [];
        }
        $this->specialty[] = $specialty;
        return $this;
    }

    /**
     * A concept that may be defined by a formal reference to a terminology or ontology
     * or may be provided by text.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * The specialty of a practitioner that would be required to perform the service
     * requested in this appointment.
     *
     * @param \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRCodeableConcept ...$specialty
     * @return static
     */
    public function setSpecialty(FHIRCodeableConcept ...$specialty): self
    {
        if ([] === $specialty) {
            unset($this->specialty);
            return $this;
        }
        $this->specialty = $specialty;
        return $this;
    }

    /**
     * A concept that may be defined by a formal reference to a terminology or ontology
     * or may be provided by text.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * The style of appointment or patient that has been booked in the slot (not
     * service type).
     *
     * @return null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRCodeableConcept
     */
    public function getAppointmentType(): null|FHIRCodeableConcept
    {
        return $this->appointmentType ?? null;
    }

    /**
     * A concept that may be defined by a formal reference to a terminology or ontology
     * or may be provided by text.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * The style of appointment or patient that has been booked in the slot (not
     * service type).
     *
     * @param null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRCodeableConcept $appointmentType
     * @return static
     */
    public function setAppointmentType(null|FHIRCodeableConcept $appointmentType): self
    {
        if (null === $appointmentType) {
            unset($this->appointmentType);
            return $this;
        }
        $this->appointmentType = $appointmentType;
        return $this;
    }

    /**
     * A concept that may be defined by a formal reference to a terminology or ontology
     * or may be provided by text.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * The coded reason that this appointment is being scheduled. This is more clinical
     * than administrative.
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
     * The coded reason that this appointment is being scheduled. This is more clinical
     * than administrative.
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
     * The coded reason that this appointment is being scheduled. This is more clinical
     * than administrative.
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
     * Reason the appointment has been scheduled to take place, as specified using
     * information from another resource. When the patient arrives and the encounter
     * begins it may be used as the admission diagnosis. The indication will typically
     * be a Condition (with other resources referenced in the evidence.detail), or a
     * Procedure.
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
     * Reason the appointment has been scheduled to take place, as specified using
     * information from another resource. When the patient arrives and the encounter
     * begins it may be used as the admission diagnosis. The indication will typically
     * be a Condition (with other resources referenced in the evidence.detail), or a
     * Procedure.
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
     * Reason the appointment has been scheduled to take place, as specified using
     * information from another resource. When the patient arrives and the encounter
     * begins it may be used as the admission diagnosis. The indication will typically
     * be a Condition (with other resources referenced in the evidence.detail), or a
     * Procedure.
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
     * An integer with a value that is not negative (e.g. >= 0)
     * If the element is present, it must have either a \@value, an \@id referenced from
     * the Narrative, or extensions
     *
     * The priority of the appointment. Can be used to make informed decisions if
     * needing to re-prioritize appointments. (The iCal Standard specifies as
     * undefined, 1 as highest, 9 as lowest priority).
     *
     * @return null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRUnsignedInt
     */
    public function getPriority(): null|FHIRUnsignedInt
    {
        return $this->priority ?? null;
    }

    /**
     * An integer with a value that is not negative (e.g. >= 0)
     * If the element is present, it must have either a \@value, an \@id referenced from
     * the Narrative, or extensions
     *
     * The priority of the appointment. Can be used to make informed decisions if
     * needing to re-prioritize appointments. (The iCal Standard specifies as
     * undefined, 1 as highest, 9 as lowest priority).
     *
     * @param null|string|int|float|\OpenEMR\FHIR\Versions\R4\Types\FHIRUnsignedIntPrimitive|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRUnsignedInt $priority
     * @return static
     */
    public function setPriority(null|string|int|float|FHIRUnsignedIntPrimitive|FHIRUnsignedInt $priority): self
    {
        if (null === $priority) {
            unset($this->priority);
            return $this;
        }
        if (!($priority instanceof FHIRUnsignedInt)) {
            $priority = new FHIRUnsignedInt(value: $priority);
        }
        $this->priority = $priority;
        return $this;
    }

    /**
     * A sequence of Unicode characters
     * Note that FHIR strings SHALL NOT exceed 1MB in size
     * If the element is present, it must have either a \@value, an \@id, or extensions
     *
     * The brief description of the appointment as would be shown on a subject line in
     * a meeting request, or appointment list. Detailed or expanded information should
     * be put in the comment field.
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
     * The brief description of the appointment as would be shown on a subject line in
     * a meeting request, or appointment list. Detailed or expanded information should
     * be put in the comment field.
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
     * Additional information to support the appointment provided when making the
     * appointment.
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
     * Additional information to support the appointment provided when making the
     * appointment.
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
     * Additional information to support the appointment provided when making the
     * appointment.
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

    /**
     * An instant in time - known at least to the second
     * Note: This is intended for where precisely observed times are required,
     * typically system logs etc., and not human-reported times - for them, see date
     * and dateTime (which can be as precise as instant, but is not required to be)
     * below. Time zone is always required
     * If the element is present, it must have either a \@value, an \@id, or extensions
     *
     * Date/Time that the appointment is to take place.
     *
     * @return null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRInstant
     */
    public function getStart(): null|FHIRInstant
    {
        return $this->start ?? null;
    }

    /**
     * An instant in time - known at least to the second
     * Note: This is intended for where precisely observed times are required,
     * typically system logs etc., and not human-reported times - for them, see date
     * and dateTime (which can be as precise as instant, but is not required to be)
     * below. Time zone is always required
     * If the element is present, it must have either a \@value, an \@id, or extensions
     *
     * Date/Time that the appointment is to take place.
     *
     * @param null|string|\DateTimeInterface|\OpenEMR\FHIR\Versions\R4\Types\FHIRInstantPrimitive|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRInstant $start
     * @return static
     */
    public function setStart(null|string|\DateTimeInterface|FHIRInstantPrimitive|FHIRInstant $start): self
    {
        if (null === $start) {
            unset($this->start);
            return $this;
        }
        if (!($start instanceof FHIRInstant)) {
            $start = new FHIRInstant(value: $start);
        }
        $this->start = $start;
        return $this;
    }

    /**
     * An instant in time - known at least to the second
     * Note: This is intended for where precisely observed times are required,
     * typically system logs etc., and not human-reported times - for them, see date
     * and dateTime (which can be as precise as instant, but is not required to be)
     * below. Time zone is always required
     * If the element is present, it must have either a \@value, an \@id, or extensions
     *
     * Date/Time that the appointment is to conclude.
     *
     * @return null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRInstant
     */
    public function getEnd(): null|FHIRInstant
    {
        return $this->end ?? null;
    }

    /**
     * An instant in time - known at least to the second
     * Note: This is intended for where precisely observed times are required,
     * typically system logs etc., and not human-reported times - for them, see date
     * and dateTime (which can be as precise as instant, but is not required to be)
     * below. Time zone is always required
     * If the element is present, it must have either a \@value, an \@id, or extensions
     *
     * Date/Time that the appointment is to conclude.
     *
     * @param null|string|\DateTimeInterface|\OpenEMR\FHIR\Versions\R4\Types\FHIRInstantPrimitive|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRInstant $end
     * @return static
     */
    public function setEnd(null|string|\DateTimeInterface|FHIRInstantPrimitive|FHIRInstant $end): self
    {
        if (null === $end) {
            unset($this->end);
            return $this;
        }
        if (!($end instanceof FHIRInstant)) {
            $end = new FHIRInstant(value: $end);
        }
        $this->end = $end;
        return $this;
    }

    /**
     * An integer with a value that is positive (e.g. >0)
     * If the element is present, it must have either a \@value, an \@id referenced from
     * the Narrative, or extensions
     *
     * Number of minutes that the appointment is to take. This can be less than the
     * duration between the start and end times. For example, where the actual time of
     * appointment is only an estimate or if a 30 minute appointment is being
     * requested, but any time would work. Also, if there is, for example, a planned 15
     * minute break in the middle of a long appointment, the duration may be 15 minutes
     * less than the difference between the start and end.
     *
     * @return null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRPositiveInt
     */
    public function getMinutesDuration(): null|FHIRPositiveInt
    {
        return $this->minutesDuration ?? null;
    }

    /**
     * An integer with a value that is positive (e.g. >0)
     * If the element is present, it must have either a \@value, an \@id referenced from
     * the Narrative, or extensions
     *
     * Number of minutes that the appointment is to take. This can be less than the
     * duration between the start and end times. For example, where the actual time of
     * appointment is only an estimate or if a 30 minute appointment is being
     * requested, but any time would work. Also, if there is, for example, a planned 15
     * minute break in the middle of a long appointment, the duration may be 15 minutes
     * less than the difference between the start and end.
     *
     * @param null|string|float|\OpenEMR\FHIR\Versions\R4\Types\FHIRPositiveIntPrimitive|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRPositiveInt $minutesDuration
     * @return static
     */
    public function setMinutesDuration(null|string|float|FHIRPositiveIntPrimitive|FHIRPositiveInt $minutesDuration): self
    {
        if (null === $minutesDuration) {
            unset($this->minutesDuration);
            return $this;
        }
        if (!($minutesDuration instanceof FHIRPositiveInt)) {
            $minutesDuration = new FHIRPositiveInt(value: $minutesDuration);
        }
        $this->minutesDuration = $minutesDuration;
        return $this;
    }

    /**
     * A reference from one resource to another.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * The slots from the participants' schedules that will be filled by the
     * appointment.
     *
     * @return iterable<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRReference>
     */
    public function getSlot(): array
    {
        return $this->slot ?? [];
    }

    /**
     * @return \ArrayIterator<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRReference>
     */
    public function getSlotIterator(): iterable
    {
        if (!isset($this->slot)) {
            return new \EmptyIterator();
        }
        return new \ArrayIterator($this->slot);
    }

    /**
     * A reference from one resource to another.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * The slots from the participants' schedules that will be filled by the
     * appointment.
     *
     * @param \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRReference $slot
     * @return static
     */
    public function addSlot(FHIRReference $slot): self
    {
        if (!isset($this->slot)) {
            $this->slot = [];
        }
        $this->slot[] = $slot;
        return $this;
    }

    /**
     * A reference from one resource to another.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * The slots from the participants' schedules that will be filled by the
     * appointment.
     *
     * @param \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRReference ...$slot
     * @return static
     */
    public function setSlot(FHIRReference ...$slot): self
    {
        if ([] === $slot) {
            unset($this->slot);
            return $this;
        }
        $this->slot = $slot;
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
     * The date that this appointment was initially created. This could be different to
     * the meta.lastModified value on the initial entry, as this could have been before
     * the resource was created on the FHIR server, and should remain unchanged over
     * the lifespan of the appointment.
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
     * The date that this appointment was initially created. This could be different to
     * the meta.lastModified value on the initial entry, as this could have been before
     * the resource was created on the FHIR server, and should remain unchanged over
     * the lifespan of the appointment.
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
     * A sequence of Unicode characters
     * Note that FHIR strings SHALL NOT exceed 1MB in size
     * If the element is present, it must have either a \@value, an \@id, or extensions
     *
     * Additional comments about the appointment.
     *
     * @return null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRString
     */
    public function getComment(): null|FHIRString
    {
        return $this->comment ?? null;
    }

    /**
     * A sequence of Unicode characters
     * Note that FHIR strings SHALL NOT exceed 1MB in size
     * If the element is present, it must have either a \@value, an \@id, or extensions
     *
     * Additional comments about the appointment.
     *
     * @param null|string|\OpenEMR\FHIR\Versions\R4\Types\FHIRStringPrimitive|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRString $comment
     * @return static
     */
    public function setComment(null|string|FHIRStringPrimitive|FHIRString $comment): self
    {
        if (null === $comment) {
            unset($this->comment);
            return $this;
        }
        if (!($comment instanceof FHIRString)) {
            $comment = new FHIRString(value: $comment);
        }
        $this->comment = $comment;
        return $this;
    }

    /**
     * A sequence of Unicode characters
     * Note that FHIR strings SHALL NOT exceed 1MB in size
     * If the element is present, it must have either a \@value, an \@id, or extensions
     *
     * While Appointment.comment contains information for internal use,
     * Appointment.patientInstructions is used to capture patient facing information
     * about the Appointment (e.g. please bring your referral or fast from 8pm night
     * before).
     *
     * @return null|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRString
     */
    public function getPatientInstruction(): null|FHIRString
    {
        return $this->patientInstruction ?? null;
    }

    /**
     * A sequence of Unicode characters
     * Note that FHIR strings SHALL NOT exceed 1MB in size
     * If the element is present, it must have either a \@value, an \@id, or extensions
     *
     * While Appointment.comment contains information for internal use,
     * Appointment.patientInstructions is used to capture patient facing information
     * about the Appointment (e.g. please bring your referral or fast from 8pm night
     * before).
     *
     * @param null|string|\OpenEMR\FHIR\Versions\R4\Types\FHIRStringPrimitive|\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRString $patientInstruction
     * @return static
     */
    public function setPatientInstruction(null|string|FHIRStringPrimitive|FHIRString $patientInstruction): self
    {
        if (null === $patientInstruction) {
            unset($this->patientInstruction);
            return $this;
        }
        if (!($patientInstruction instanceof FHIRString)) {
            $patientInstruction = new FHIRString(value: $patientInstruction);
        }
        $this->patientInstruction = $patientInstruction;
        return $this;
    }

    /**
     * A reference from one resource to another.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * The service request this appointment is allocated to assess (e.g. incoming
     * referral or procedure request).
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
     * The service request this appointment is allocated to assess (e.g. incoming
     * referral or procedure request).
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
     * The service request this appointment is allocated to assess (e.g. incoming
     * referral or procedure request).
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
     * A booking of a healthcare event among patient(s), practitioner(s), related
     * person(s) and/or device(s) for a specific date/time. This may result in one or
     * more Encounter(s).
     *
     * List of participants involved in the appointment.
     *
     * @return iterable<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRAppointment\FHIRAppointmentParticipant>
     */
    public function getParticipant(): array
    {
        return $this->participant ?? [];
    }

    /**
     * @return \ArrayIterator<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRAppointment\FHIRAppointmentParticipant>
     */
    public function getParticipantIterator(): iterable
    {
        if (!isset($this->participant)) {
            return new \EmptyIterator();
        }
        return new \ArrayIterator($this->participant);
    }

    /**
     * A booking of a healthcare event among patient(s), practitioner(s), related
     * person(s) and/or device(s) for a specific date/time. This may result in one or
     * more Encounter(s).
     *
     * List of participants involved in the appointment.
     *
     * @param \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRAppointment\FHIRAppointmentParticipant $participant
     * @return static
     */
    public function addParticipant(FHIRAppointmentParticipant $participant): self
    {
        if (!isset($this->participant)) {
            $this->participant = [];
        }
        $this->participant[] = $participant;
        return $this;
    }

    /**
     * A booking of a healthcare event among patient(s), practitioner(s), related
     * person(s) and/or device(s) for a specific date/time. This may result in one or
     * more Encounter(s).
     *
     * List of participants involved in the appointment.
     *
     * @param \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRBackboneElement\FHIRAppointment\FHIRAppointmentParticipant ...$participant
     * @return static
     */
    public function setParticipant(FHIRAppointmentParticipant ...$participant): self
    {
        if ([] === $participant) {
            unset($this->participant);
            return $this;
        }
        $this->participant = $participant;
        return $this;
    }

    /**
     * A time period defined by a start and end date and optionally time.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * A set of date ranges (potentially including times) that the appointment is
     * preferred to be scheduled within. The duration (usually in minutes) could also
     * be provided to indicate the length of the appointment to fill and populate the
     * start/end times for the actual allocated time. However, in other situations the
     * duration may be calculated by the scheduling system.
     *
     * @return iterable<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRPeriod>
     */
    public function getRequestedPeriod(): array
    {
        return $this->requestedPeriod ?? [];
    }

    /**
     * @return \ArrayIterator<\OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRPeriod>
     */
    public function getRequestedPeriodIterator(): iterable
    {
        if (!isset($this->requestedPeriod)) {
            return new \EmptyIterator();
        }
        return new \ArrayIterator($this->requestedPeriod);
    }

    /**
     * A time period defined by a start and end date and optionally time.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * A set of date ranges (potentially including times) that the appointment is
     * preferred to be scheduled within. The duration (usually in minutes) could also
     * be provided to indicate the length of the appointment to fill and populate the
     * start/end times for the actual allocated time. However, in other situations the
     * duration may be calculated by the scheduling system.
     *
     * @param \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRPeriod $requestedPeriod
     * @return static
     */
    public function addRequestedPeriod(FHIRPeriod $requestedPeriod): self
    {
        if (!isset($this->requestedPeriod)) {
            $this->requestedPeriod = [];
        }
        $this->requestedPeriod[] = $requestedPeriod;
        return $this;
    }

    /**
     * A time period defined by a start and end date and optionally time.
     * If the element is present, it must have a value for at least one of the defined
     * elements, an \@id referenced from the Narrative, or extensions
     *
     * A set of date ranges (potentially including times) that the appointment is
     * preferred to be scheduled within. The duration (usually in minutes) could also
     * be provided to indicate the length of the appointment to fill and populate the
     * start/end times for the actual allocated time. However, in other situations the
     * duration may be calculated by the scheduling system.
     *
     * @param \OpenEMR\FHIR\Versions\R4\Types\FHIRElement\FHIRPeriod ...$requestedPeriod
     * @return static
     */
    public function setRequestedPeriod(FHIRPeriod ...$requestedPeriod): self
    {
        if ([] === $requestedPeriod) {
            unset($this->requestedPeriod);
            return $this;
        }
        $this->requestedPeriod = $requestedPeriod;
        return $this;
    }

    /* class_default.php:201 */
    /**
     * @param string|\SimpleXMLElement $element
     * @param null|\OpenEMR\FHIR\Encoding\UnserializeConfig $config
     * @param null|\OpenEMR\FHIR\Versions\R4\Types\FHIRResource\FHIRDomainResource\FHIRAppointment $type
     * @return \OpenEMR\FHIR\Versions\R4\Types\FHIRResource\FHIRDomainResource\FHIRAppointment
     * @throws \Exception
     */
    public static function xmlUnserialize(string|\SimpleXMLElement $element,
                                          null|UnserializeConfig $config = null,
                                          null|ResourceTypeInterface $type = null): self
    {
        if (null === $type) {
            $type = new static();
        } else if (!($type instanceof FHIRAppointment)) {
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
                $type->setStatus(FHIRAppointmentStatus::xmlUnserialize($ce, $config));
            } else if (self::FIELD_CANCELATION_REASON === $cen) {
                $type->setCancelationReason(FHIRCodeableConcept::xmlUnserialize($ce, $config));
            } else if (self::FIELD_SERVICE_CATEGORY === $cen) {
                $type->addServiceCategory(FHIRCodeableConcept::xmlUnserialize($ce, $config));
            } else if (self::FIELD_SERVICE_TYPE === $cen) {
                $type->addServiceType(FHIRCodeableConcept::xmlUnserialize($ce, $config));
            } else if (self::FIELD_SPECIALTY === $cen) {
                $type->addSpecialty(FHIRCodeableConcept::xmlUnserialize($ce, $config));
            } else if (self::FIELD_APPOINTMENT_TYPE === $cen) {
                $type->setAppointmentType(FHIRCodeableConcept::xmlUnserialize($ce, $config));
            } else if (self::FIELD_REASON_CODE === $cen) {
                $type->addReasonCode(FHIRCodeableConcept::xmlUnserialize($ce, $config));
            } else if (self::FIELD_REASON_REFERENCE === $cen) {
                $type->addReasonReference(FHIRReference::xmlUnserialize($ce, $config));
            } else if (self::FIELD_PRIORITY === $cen) {
                $type->setPriority(FHIRUnsignedInt::xmlUnserialize($ce, $config));
            } else if (self::FIELD_DESCRIPTION === $cen) {
                $type->setDescription(FHIRString::xmlUnserialize($ce, $config));
            } else if (self::FIELD_SUPPORTING_INFORMATION === $cen) {
                $type->addSupportingInformation(FHIRReference::xmlUnserialize($ce, $config));
            } else if (self::FIELD_START === $cen) {
                $type->setStart(FHIRInstant::xmlUnserialize($ce, $config));
            } else if (self::FIELD_END === $cen) {
                $type->setEnd(FHIRInstant::xmlUnserialize($ce, $config));
            } else if (self::FIELD_MINUTES_DURATION === $cen) {
                $type->setMinutesDuration(FHIRPositiveInt::xmlUnserialize($ce, $config));
            } else if (self::FIELD_SLOT === $cen) {
                $type->addSlot(FHIRReference::xmlUnserialize($ce, $config));
            } else if (self::FIELD_CREATED === $cen) {
                $type->setCreated(FHIRDateTime::xmlUnserialize($ce, $config));
            } else if (self::FIELD_COMMENT === $cen) {
                $type->setComment(FHIRString::xmlUnserialize($ce, $config));
            } else if (self::FIELD_PATIENT_INSTRUCTION === $cen) {
                $type->setPatientInstruction(FHIRString::xmlUnserialize($ce, $config));
            } else if (self::FIELD_BASED_ON === $cen) {
                $type->addBasedOn(FHIRReference::xmlUnserialize($ce, $config));
            } else if (self::FIELD_PARTICIPANT === $cen) {
                $type->addParticipant(FHIRAppointmentParticipant::xmlUnserialize($ce, $config));
            } else if (self::FIELD_REQUESTED_PERIOD === $cen) {
                $type->addRequestedPeriod(FHIRPeriod::xmlUnserialize($ce, $config));
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
        if (isset($attributes[self::FIELD_START])) {
            if (isset($type->start)) {
                $type->start->setValue((string)$attributes[self::FIELD_START]);
            } else {
                $type->setStart((string)$attributes[self::FIELD_START]);
            }
            $type->_setXMLFieldValueLocation(self::FIELD_START, ValueXMLLocationEnum::PARENT_ATTRIBUTE);
        }
        if (isset($attributes[self::FIELD_END])) {
            if (isset($type->end)) {
                $type->end->setValue((string)$attributes[self::FIELD_END]);
            } else {
                $type->setEnd((string)$attributes[self::FIELD_END]);
            }
            $type->_setXMLFieldValueLocation(self::FIELD_END, ValueXMLLocationEnum::PARENT_ATTRIBUTE);
        }
        if (isset($attributes[self::FIELD_MINUTES_DURATION])) {
            if (isset($type->minutesDuration)) {
                $type->minutesDuration->setValue((string)$attributes[self::FIELD_MINUTES_DURATION]);
            } else {
                $type->setMinutesDuration((string)$attributes[self::FIELD_MINUTES_DURATION]);
            }
            $type->_setXMLFieldValueLocation(self::FIELD_MINUTES_DURATION, ValueXMLLocationEnum::PARENT_ATTRIBUTE);
        }
        if (isset($attributes[self::FIELD_CREATED])) {
            if (isset($type->created)) {
                $type->created->setValue((string)$attributes[self::FIELD_CREATED]);
            } else {
                $type->setCreated((string)$attributes[self::FIELD_CREATED]);
            }
            $type->_setXMLFieldValueLocation(self::FIELD_CREATED, ValueXMLLocationEnum::PARENT_ATTRIBUTE);
        }
        if (isset($attributes[self::FIELD_COMMENT])) {
            if (isset($type->comment)) {
                $type->comment->setValue((string)$attributes[self::FIELD_COMMENT]);
            } else {
                $type->setComment((string)$attributes[self::FIELD_COMMENT]);
            }
            $type->_setXMLFieldValueLocation(self::FIELD_COMMENT, ValueXMLLocationEnum::PARENT_ATTRIBUTE);
        }
        if (isset($attributes[self::FIELD_PATIENT_INSTRUCTION])) {
            if (isset($type->patientInstruction)) {
                $type->patientInstruction->setValue((string)$attributes[self::FIELD_PATIENT_INSTRUCTION]);
            } else {
                $type->setPatientInstruction((string)$attributes[self::FIELD_PATIENT_INSTRUCTION]);
            }
            $type->_setXMLFieldValueLocation(self::FIELD_PATIENT_INSTRUCTION, ValueXMLLocationEnum::PARENT_ATTRIBUTE);
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
            $xw->openRootNode('Appointment', $this->_getSourceXMLNS());
        }
        if (isset($this->status) && ValueXMLLocationEnum::PARENT_ATTRIBUTE === $this->_valueXMLLocations[self::FIELD_STATUS]) {
            $xw->writeAttribute(self::FIELD_STATUS, $this->status->_getValueAsString());
        }
        if (isset($this->priority) && ValueXMLLocationEnum::PARENT_ATTRIBUTE === $this->_valueXMLLocations[self::FIELD_PRIORITY]) {
            $xw->writeAttribute(self::FIELD_PRIORITY, $this->priority->_getValueAsString());
        }
        if (isset($this->description) && ValueXMLLocationEnum::PARENT_ATTRIBUTE === $this->_valueXMLLocations[self::FIELD_DESCRIPTION]) {
            $xw->writeAttribute(self::FIELD_DESCRIPTION, $this->description->_getValueAsString());
        }
        if (isset($this->start) && ValueXMLLocationEnum::PARENT_ATTRIBUTE === $this->_valueXMLLocations[self::FIELD_START]) {
            $xw->writeAttribute(self::FIELD_START, $this->start->_getValueAsString());
        }
        if (isset($this->end) && ValueXMLLocationEnum::PARENT_ATTRIBUTE === $this->_valueXMLLocations[self::FIELD_END]) {
            $xw->writeAttribute(self::FIELD_END, $this->end->_getValueAsString());
        }
        if (isset($this->minutesDuration) && ValueXMLLocationEnum::PARENT_ATTRIBUTE === $this->_valueXMLLocations[self::FIELD_MINUTES_DURATION]) {
            $xw->writeAttribute(self::FIELD_MINUTES_DURATION, $this->minutesDuration->_getValueAsString());
        }
        if (isset($this->created) && ValueXMLLocationEnum::PARENT_ATTRIBUTE === $this->_valueXMLLocations[self::FIELD_CREATED]) {
            $xw->writeAttribute(self::FIELD_CREATED, $this->created->_getValueAsString());
        }
        if (isset($this->comment) && ValueXMLLocationEnum::PARENT_ATTRIBUTE === $this->_valueXMLLocations[self::FIELD_COMMENT]) {
            $xw->writeAttribute(self::FIELD_COMMENT, $this->comment->_getValueAsString());
        }
        if (isset($this->patientInstruction) && ValueXMLLocationEnum::PARENT_ATTRIBUTE === $this->_valueXMLLocations[self::FIELD_PATIENT_INSTRUCTION]) {
            $xw->writeAttribute(self::FIELD_PATIENT_INSTRUCTION, $this->patientInstruction->_getValueAsString());
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
        if (isset($this->cancelationReason)) {
            $xw->startElement(self::FIELD_CANCELATION_REASON);
            $this->cancelationReason->xmlSerialize($xw, $config);
            $xw->endElement();
        }
        if (isset($this->serviceCategory)) {
            foreach ($this->serviceCategory as $v) {
                $xw->startElement(self::FIELD_SERVICE_CATEGORY);
                $v->xmlSerialize($xw, $config);
                $xw->endElement();
            }
        }
        if (isset($this->serviceType)) {
            foreach ($this->serviceType as $v) {
                $xw->startElement(self::FIELD_SERVICE_TYPE);
                $v->xmlSerialize($xw, $config);
                $xw->endElement();
            }
        }
        if (isset($this->specialty)) {
            foreach ($this->specialty as $v) {
                $xw->startElement(self::FIELD_SPECIALTY);
                $v->xmlSerialize($xw, $config);
                $xw->endElement();
            }
        }
        if (isset($this->appointmentType)) {
            $xw->startElement(self::FIELD_APPOINTMENT_TYPE);
            $this->appointmentType->xmlSerialize($xw, $config);
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
        if (isset($this->priority)
            && (ValueXMLLocationEnum::PARENT_ATTRIBUTE !== $this->_valueXMLLocations[self::FIELD_PRIORITY]
                || $this->priority->_nonValueFieldDefined())) {
            $xw->startElement(self::FIELD_PRIORITY);
            $this->priority->xmlSerialize($xw, $config, $this->_valueXMLLocations[self::FIELD_PRIORITY]);
            $xw->endElement();
        }
        if (isset($this->description)
            && (ValueXMLLocationEnum::PARENT_ATTRIBUTE !== $this->_valueXMLLocations[self::FIELD_DESCRIPTION]
                || $this->description->_nonValueFieldDefined())) {
            $xw->startElement(self::FIELD_DESCRIPTION);
            $this->description->xmlSerialize($xw, $config, $this->_valueXMLLocations[self::FIELD_DESCRIPTION]);
            $xw->endElement();
        }
        if (isset($this->supportingInformation)) {
            foreach ($this->supportingInformation as $v) {
                $xw->startElement(self::FIELD_SUPPORTING_INFORMATION);
                $v->xmlSerialize($xw, $config);
                $xw->endElement();
            }
        }
        if (isset($this->start)
            && (ValueXMLLocationEnum::PARENT_ATTRIBUTE !== $this->_valueXMLLocations[self::FIELD_START]
                || $this->start->_nonValueFieldDefined())) {
            $xw->startElement(self::FIELD_START);
            $this->start->xmlSerialize($xw, $config, $this->_valueXMLLocations[self::FIELD_START]);
            $xw->endElement();
        }
        if (isset($this->end)
            && (ValueXMLLocationEnum::PARENT_ATTRIBUTE !== $this->_valueXMLLocations[self::FIELD_END]
                || $this->end->_nonValueFieldDefined())) {
            $xw->startElement(self::FIELD_END);
            $this->end->xmlSerialize($xw, $config, $this->_valueXMLLocations[self::FIELD_END]);
            $xw->endElement();
        }
        if (isset($this->minutesDuration)
            && (ValueXMLLocationEnum::PARENT_ATTRIBUTE !== $this->_valueXMLLocations[self::FIELD_MINUTES_DURATION]
                || $this->minutesDuration->_nonValueFieldDefined())) {
            $xw->startElement(self::FIELD_MINUTES_DURATION);
            $this->minutesDuration->xmlSerialize($xw, $config, $this->_valueXMLLocations[self::FIELD_MINUTES_DURATION]);
            $xw->endElement();
        }
        if (isset($this->slot)) {
            foreach ($this->slot as $v) {
                $xw->startElement(self::FIELD_SLOT);
                $v->xmlSerialize($xw, $config);
                $xw->endElement();
            }
        }
        if (isset($this->created)
            && (ValueXMLLocationEnum::PARENT_ATTRIBUTE !== $this->_valueXMLLocations[self::FIELD_CREATED]
                || $this->created->_nonValueFieldDefined())) {
            $xw->startElement(self::FIELD_CREATED);
            $this->created->xmlSerialize($xw, $config, $this->_valueXMLLocations[self::FIELD_CREATED]);
            $xw->endElement();
        }
        if (isset($this->comment)
            && (ValueXMLLocationEnum::PARENT_ATTRIBUTE !== $this->_valueXMLLocations[self::FIELD_COMMENT]
                || $this->comment->_nonValueFieldDefined())) {
            $xw->startElement(self::FIELD_COMMENT);
            $this->comment->xmlSerialize($xw, $config, $this->_valueXMLLocations[self::FIELD_COMMENT]);
            $xw->endElement();
        }
        if (isset($this->patientInstruction)
            && (ValueXMLLocationEnum::PARENT_ATTRIBUTE !== $this->_valueXMLLocations[self::FIELD_PATIENT_INSTRUCTION]
                || $this->patientInstruction->_nonValueFieldDefined())) {
            $xw->startElement(self::FIELD_PATIENT_INSTRUCTION);
            $this->patientInstruction->xmlSerialize($xw, $config, $this->_valueXMLLocations[self::FIELD_PATIENT_INSTRUCTION]);
            $xw->endElement();
        }
        if (isset($this->basedOn)) {
            foreach ($this->basedOn as $v) {
                $xw->startElement(self::FIELD_BASED_ON);
                $v->xmlSerialize($xw, $config);
                $xw->endElement();
            }
        }
        if (isset($this->participant)) {
            foreach ($this->participant as $v) {
                $xw->startElement(self::FIELD_PARTICIPANT);
                $v->xmlSerialize($xw, $config);
                $xw->endElement();
            }
        }
        if (isset($this->requestedPeriod)) {
            foreach ($this->requestedPeriod as $v) {
                $xw->startElement(self::FIELD_REQUESTED_PERIOD);
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
     * @param null|\OpenEMR\FHIR\Versions\R4\Types\FHIRResource\FHIRDomainResource\FHIRAppointment $type
     * @return \OpenEMR\FHIR\Versions\R4\Types\FHIRResource\FHIRDomainResource\FHIRAppointment
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
        } else if (!($type instanceof FHIRAppointment)) {
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
            $type->setStatus(FHIRAppointmentStatus::jsonUnserialize($v, $config));
        }
        if (isset($decoded->cancelationReason) || property_exists($decoded, self::FIELD_CANCELATION_REASON)) {
            if (is_array($decoded->cancelationReason)) {
                $type->setCancelationReason(FHIRCodeableConcept::jsonUnserialize(reset($decoded->cancelationReason), $config));
            } else {
                $type->setCancelationReason(FHIRCodeableConcept::jsonUnserialize($decoded->cancelationReason, $config));
            }
        }
        if (isset($decoded->serviceCategory) || property_exists($decoded, self::FIELD_SERVICE_CATEGORY)) {
            if (is_object($decoded->serviceCategory)) {
                $vals = [$decoded->serviceCategory];
                $type->_setJSONFieldElideSingletonArray(self::FIELD_SERVICE_CATEGORY, true);
            } else {
                $vals = $decoded->serviceCategory;
            }
            foreach($vals as $v) {
                $type->addServiceCategory(FHIRCodeableConcept::jsonUnserialize($v, $config));
            }
        }
        if (isset($decoded->serviceType) || property_exists($decoded, self::FIELD_SERVICE_TYPE)) {
            if (is_object($decoded->serviceType)) {
                $vals = [$decoded->serviceType];
                $type->_setJSONFieldElideSingletonArray(self::FIELD_SERVICE_TYPE, true);
            } else {
                $vals = $decoded->serviceType;
            }
            foreach($vals as $v) {
                $type->addServiceType(FHIRCodeableConcept::jsonUnserialize($v, $config));
            }
        }
        if (isset($decoded->specialty) || property_exists($decoded, self::FIELD_SPECIALTY)) {
            if (is_object($decoded->specialty)) {
                $vals = [$decoded->specialty];
                $type->_setJSONFieldElideSingletonArray(self::FIELD_SPECIALTY, true);
            } else {
                $vals = $decoded->specialty;
            }
            foreach($vals as $v) {
                $type->addSpecialty(FHIRCodeableConcept::jsonUnserialize($v, $config));
            }
        }
        if (isset($decoded->appointmentType) || property_exists($decoded, self::FIELD_APPOINTMENT_TYPE)) {
            if (is_array($decoded->appointmentType)) {
                $type->setAppointmentType(FHIRCodeableConcept::jsonUnserialize(reset($decoded->appointmentType), $config));
            } else {
                $type->setAppointmentType(FHIRCodeableConcept::jsonUnserialize($decoded->appointmentType, $config));
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
        if (isset($decoded->priority)
            || isset($decoded->_priority)
            || property_exists($decoded, self::FIELD_PRIORITY)
            || property_exists($decoded, self::FIELD_PRIORITY_EXT)) {
            $v = $decoded->_priority ?? new \stdClass();
            $v->value = $decoded->priority ?? null;
            $type->setPriority(FHIRUnsignedInt::jsonUnserialize($v, $config));
        }
        if (isset($decoded->description)
            || isset($decoded->_description)
            || property_exists($decoded, self::FIELD_DESCRIPTION)
            || property_exists($decoded, self::FIELD_DESCRIPTION_EXT)) {
            $v = $decoded->_description ?? new \stdClass();
            $v->value = $decoded->description ?? null;
            $type->setDescription(FHIRString::jsonUnserialize($v, $config));
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
        if (isset($decoded->start)
            || isset($decoded->_start)
            || property_exists($decoded, self::FIELD_START)
            || property_exists($decoded, self::FIELD_START_EXT)) {
            $v = $decoded->_start ?? new \stdClass();
            $v->value = $decoded->start ?? null;
            $type->setStart(FHIRInstant::jsonUnserialize($v, $config));
        }
        if (isset($decoded->end)
            || isset($decoded->_end)
            || property_exists($decoded, self::FIELD_END)
            || property_exists($decoded, self::FIELD_END_EXT)) {
            $v = $decoded->_end ?? new \stdClass();
            $v->value = $decoded->end ?? null;
            $type->setEnd(FHIRInstant::jsonUnserialize($v, $config));
        }
        if (isset($decoded->minutesDuration)
            || isset($decoded->_minutesDuration)
            || property_exists($decoded, self::FIELD_MINUTES_DURATION)
            || property_exists($decoded, self::FIELD_MINUTES_DURATION_EXT)) {
            $v = $decoded->_minutesDuration ?? new \stdClass();
            $v->value = $decoded->minutesDuration ?? null;
            $type->setMinutesDuration(FHIRPositiveInt::jsonUnserialize($v, $config));
        }
        if (isset($decoded->slot) || property_exists($decoded, self::FIELD_SLOT)) {
            if (is_object($decoded->slot)) {
                $vals = [$decoded->slot];
                $type->_setJSONFieldElideSingletonArray(self::FIELD_SLOT, true);
            } else {
                $vals = $decoded->slot;
            }
            foreach($vals as $v) {
                $type->addSlot(FHIRReference::jsonUnserialize($v, $config));
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
        if (isset($decoded->comment)
            || isset($decoded->_comment)
            || property_exists($decoded, self::FIELD_COMMENT)
            || property_exists($decoded, self::FIELD_COMMENT_EXT)) {
            $v = $decoded->_comment ?? new \stdClass();
            $v->value = $decoded->comment ?? null;
            $type->setComment(FHIRString::jsonUnserialize($v, $config));
        }
        if (isset($decoded->patientInstruction)
            || isset($decoded->_patientInstruction)
            || property_exists($decoded, self::FIELD_PATIENT_INSTRUCTION)
            || property_exists($decoded, self::FIELD_PATIENT_INSTRUCTION_EXT)) {
            $v = $decoded->_patientInstruction ?? new \stdClass();
            $v->value = $decoded->patientInstruction ?? null;
            $type->setPatientInstruction(FHIRString::jsonUnserialize($v, $config));
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
        if (isset($decoded->participant) || property_exists($decoded, self::FIELD_PARTICIPANT)) {
            if (is_object($decoded->participant)) {
                $vals = [$decoded->participant];
                $type->_setJSONFieldElideSingletonArray(self::FIELD_PARTICIPANT, true);
            } else {
                $vals = $decoded->participant;
            }
            foreach($vals as $v) {
                $type->addParticipant(FHIRAppointmentParticipant::jsonUnserialize($v, $config));
            }
        }
        if (isset($decoded->requestedPeriod) || property_exists($decoded, self::FIELD_REQUESTED_PERIOD)) {
            if (is_object($decoded->requestedPeriod)) {
                $vals = [$decoded->requestedPeriod];
                $type->_setJSONFieldElideSingletonArray(self::FIELD_REQUESTED_PERIOD, true);
            } else {
                $vals = $decoded->requestedPeriod;
            }
            foreach($vals as $v) {
                $type->addRequestedPeriod(FHIRPeriod::jsonUnserialize($v, $config));
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
        if (isset($this->cancelationReason)) {
            $out->cancelationReason = $this->cancelationReason;
        }
        if (isset($this->serviceCategory) && [] !== $this->serviceCategory) {
            if ($this->_getJSONFieldElideSingletonArray(self::FIELD_SERVICE_CATEGORY) && 1 === count($this->serviceCategory)) {
                $out->serviceCategory = $this->serviceCategory[0];
            } else {
                $out->serviceCategory = $this->serviceCategory;
            }
        }
        if (isset($this->serviceType) && [] !== $this->serviceType) {
            if ($this->_getJSONFieldElideSingletonArray(self::FIELD_SERVICE_TYPE) && 1 === count($this->serviceType)) {
                $out->serviceType = $this->serviceType[0];
            } else {
                $out->serviceType = $this->serviceType;
            }
        }
        if (isset($this->specialty) && [] !== $this->specialty) {
            if ($this->_getJSONFieldElideSingletonArray(self::FIELD_SPECIALTY) && 1 === count($this->specialty)) {
                $out->specialty = $this->specialty[0];
            } else {
                $out->specialty = $this->specialty;
            }
        }
        if (isset($this->appointmentType)) {
            $out->appointmentType = $this->appointmentType;
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
        if (isset($this->supportingInformation) && [] !== $this->supportingInformation) {
            if ($this->_getJSONFieldElideSingletonArray(self::FIELD_SUPPORTING_INFORMATION) && 1 === count($this->supportingInformation)) {
                $out->supportingInformation = $this->supportingInformation[0];
            } else {
                $out->supportingInformation = $this->supportingInformation;
            }
        }
        if (isset($this->start)) {
            if (null !== ($val = $this->start->getValue())) {
                $out->start = $val;
            }
            if ($this->start->_nonValueFieldDefined()) {
                $ext = $this->start->jsonSerialize();
                unset($ext->value);
                $out->_start = $ext;
            }
        }
        if (isset($this->end)) {
            if (null !== ($val = $this->end->getValue())) {
                $out->end = $val;
            }
            if ($this->end->_nonValueFieldDefined()) {
                $ext = $this->end->jsonSerialize();
                unset($ext->value);
                $out->_end = $ext;
            }
        }
        if (isset($this->minutesDuration)) {
            if (null !== ($val = $this->minutesDuration->getValue())) {
                $out->minutesDuration = $val;
            }
            if ($this->minutesDuration->_nonValueFieldDefined()) {
                $ext = $this->minutesDuration->jsonSerialize();
                unset($ext->value);
                $out->_minutesDuration = $ext;
            }
        }
        if (isset($this->slot) && [] !== $this->slot) {
            if ($this->_getJSONFieldElideSingletonArray(self::FIELD_SLOT) && 1 === count($this->slot)) {
                $out->slot = $this->slot[0];
            } else {
                $out->slot = $this->slot;
            }
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
        if (isset($this->comment)) {
            if (null !== ($val = $this->comment->getValue())) {
                $out->comment = $val;
            }
            if ($this->comment->_nonValueFieldDefined()) {
                $ext = $this->comment->jsonSerialize();
                unset($ext->value);
                $out->_comment = $ext;
            }
        }
        if (isset($this->patientInstruction)) {
            if (null !== ($val = $this->patientInstruction->getValue())) {
                $out->patientInstruction = $val;
            }
            if ($this->patientInstruction->_nonValueFieldDefined()) {
                $ext = $this->patientInstruction->jsonSerialize();
                unset($ext->value);
                $out->_patientInstruction = $ext;
            }
        }
        if (isset($this->basedOn) && [] !== $this->basedOn) {
            if ($this->_getJSONFieldElideSingletonArray(self::FIELD_BASED_ON) && 1 === count($this->basedOn)) {
                $out->basedOn = $this->basedOn[0];
            } else {
                $out->basedOn = $this->basedOn;
            }
        }
        if (isset($this->participant) && [] !== $this->participant) {
            if ($this->_getJSONFieldElideSingletonArray(self::FIELD_PARTICIPANT) && 1 === count($this->participant)) {
                $out->participant = $this->participant[0];
            } else {
                $out->participant = $this->participant;
            }
        }
        if (isset($this->requestedPeriod) && [] !== $this->requestedPeriod) {
            if ($this->_getJSONFieldElideSingletonArray(self::FIELD_REQUESTED_PERIOD) && 1 === count($this->requestedPeriod)) {
                $out->requestedPeriod = $this->requestedPeriod[0];
            } else {
                $out->requestedPeriod = $this->requestedPeriod;
            }
        }
        $out->resourceType = $this->_getResourceType();
        return $out;
    }
}
